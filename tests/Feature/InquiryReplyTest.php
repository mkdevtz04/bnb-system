<?php

namespace Tests\Feature;

use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Answering a contact-form message.
 *
 * This replaced a mailto: link, which delegated the job to whatever desktop mail
 * client the machine had configured — and on a machine without one, which is
 * normal for anyone using webmail, clicking Reply did nothing whatsoever.
 */
class InquiryReplyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Inquiry $inquiry;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->inquiry = Inquiry::create([
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'subject' => 'Monthly rate for the loft?',
            'message' => "Hi — we're relocating in March.\nDo you offer a monthly rate?",
        ]);
    }

    private function reply(string $body, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->admin)
            ->post(route('admin.inquiries.reply', $this->inquiry), ['reply' => $body]);
    }

    public function test_replying_emails_the_person_who_wrote_in(): void
    {
        $this->reply('Yes — we do six-week rates. I will send details.')
            ->assertSessionHasNoErrors();

        Mail::assertSent(InquiryReplyMail::class, fn (InquiryReplyMail $mail) => $mail->hasTo('priya@example.com'));
    }

    public function test_the_reply_is_recorded_against_the_enquiry(): void
    {
        $this->reply('Yes, we do monthly rates.');

        $this->inquiry->refresh();

        $this->assertSame('Yes, we do monthly rates.', $this->inquiry->reply);
        $this->assertNotNull($this->inquiry->replied_at);
        $this->assertSame($this->admin->id, $this->inquiry->replied_by);
        $this->assertTrue($this->inquiry->hasReply());
    }

    /**
     * Answering something is the clearest possible signal it has been seen.
     */
    public function test_replying_marks_the_enquiry_as_read(): void
    {
        $this->assertFalse($this->inquiry->refresh()->is_read);

        $this->reply('On it.');

        $this->assertTrue($this->inquiry->refresh()->is_read);
    }

    public function test_the_subject_is_the_original_prefixed_with_re(): void
    {
        $this->assertSame('Re: Monthly rate for the loft?', $this->inquiry->replySubject());
    }

    public function test_an_already_prefixed_subject_is_not_prefixed_twice(): void
    {
        $this->inquiry->update(['subject' => 'Re: Monthly rate']);

        $this->assertSame('Re: Monthly rate', $this->inquiry->fresh()->replySubject());
    }

    public function test_a_missing_subject_still_produces_a_sensible_one(): void
    {
        $this->inquiry->update(['subject' => null]);

        $this->assertSame('Re: your enquiry', $this->inquiry->fresh()->replySubject());
    }

    /**
     * The person who wrote in must be able to answer the answer — and that has to
     * reach the host, not the address the mail happened to be sent from.
     */
    public function test_the_reply_can_itself_be_replied_to(): void
    {
        config()->set('mail.reply_to.address', 'host@coastalcharms.test');

        $this->reply('Details attached.');

        Mail::assertSent(InquiryReplyMail::class, function (InquiryReplyMail $mail) {
            return $mail->envelope()->replyTo[0]->address === 'host@coastalcharms.test';
        });
    }

    /**
     * Sent inline rather than queued, so that "sent" means sent. See the note on
     * InquiryReplyMail for why this one differs from the booking notifications.
     */
    public function test_the_reply_is_not_queued_so_that_sent_means_sent(): void
    {
        $this->assertFalse(
            is_subclass_of(InquiryReplyMail::class, \Illuminate\Contracts\Queue\ShouldQueue::class),
            'A queued reply reports success before it has been delivered.',
        );
    }

    public function test_multi_line_replies_keep_their_structure(): void
    {
        $this->reply("Hi Priya,\n\nYes — six weeks works.\n\n\n\n\nBest,\nAmara");

        $this->assertSame(
            "Hi Priya,\n\nYes — six weeks works.\n\nBest,\nAmara",
            $this->inquiry->refresh()->reply,
        );
    }

    public function test_an_empty_reply_is_rejected(): void
    {
        $this->reply('')->assertSessionHasErrors('reply');

        Mail::assertNothingSent();
        $this->assertNull($this->inquiry->refresh()->replied_at);
    }

    public function test_a_guest_cannot_reply_to_enquiries(): void
    {
        $this->reply('Let me answer that', User::factory()->create(['role' => 'user']))
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_signing_in_is_required(): void
    {
        $this->post(route('admin.inquiries.reply', $this->inquiry), ['reply' => 'Hello'])
            ->assertRedirect(route('login'));

        Mail::assertNothingSent();
    }

    public function test_the_page_shows_a_reply_box_rather_than_only_a_mailto_link(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee(route('admin.inquiries.reply', $this->inquiry), escape: false)
            ->assertSee('Send reply');
    }

    public function test_a_sent_reply_is_shown_on_the_page(): void
    {
        $this->reply('We do indeed offer monthly rates.');

        $this->actingAs($this->admin)
            ->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('We do indeed offer monthly rates.')
            ->assertSee('Replied');
    }

    public function test_the_awaiting_reply_filter_hides_answered_enquiries(): void
    {
        Inquiry::create([
            'name' => 'Unanswered Person',
            'email' => 'waiting@example.com',
            'message' => 'Anyone there?',
        ]);

        $this->reply('Answered.');

        // Asserted against the message body, not the name or address: the success
        // flash from the reply above names both, and renders on this very page.
        $this->actingAs($this->admin)
            ->get(route('admin.inquiries', ['awaiting' => 1]))
            ->assertOk()
            ->assertSee('Anyone there?')
            ->assertDontSee('Do you offer a monthly rate?');
    }
}
