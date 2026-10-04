<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private User $admin;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guest = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => Apartment::factory()->create()->id,
        ]);
    }

    private function send(User $as, string $body)
    {
        return $this->actingAs($as)->post(route('messages.store', $this->booking), ['message' => $body]);
    }

    // ── The composer ────────────────────────────────────────────────────────

    /**
     * Shift+Enter inserts a newline in the browser, so the server has to keep it.
     * A single-line input could never produce this, which is why the field is a
     * textarea and the column is text.
     */
    public function test_a_multi_line_message_keeps_its_line_breaks(): void
    {
        $this->send($this->guest, "Hi there\nTwo questions:\n\n1. Is parking free?\n2. Early check-in?")
            ->assertSessionHasNoErrors();

        $this->assertSame(
            "Hi there\nTwo questions:\n\n1. Is parking free?\n2. Early check-in?",
            Message::firstOrFail()->message,
        );
    }

    /**
     * Windows browsers send CRLF, others LF. Storing them as-is would make the
     * same message render with different spacing depending on who typed it.
     */
    public function test_windows_line_endings_are_normalised(): void
    {
        $this->send($this->guest, "First line\r\nSecond line");

        $this->assertSame("First line\nSecond line", Message::firstOrFail()->message);
    }

    /**
     * Someone leaning on Enter should not be able to push the conversation off
     * the screen for everybody else.
     */
    public function test_long_runs_of_blank_lines_are_collapsed(): void
    {
        $this->send($this->guest, "Start\n\n\n\n\n\n\n\nEnd");

        $this->assertSame("Start\n\nEnd", Message::firstOrFail()->message);
    }

    public function test_surrounding_whitespace_is_trimmed(): void
    {
        $this->send($this->guest, "\n\n  Hello  \n\n");

        $this->assertSame('Hello', Message::firstOrFail()->message);
    }

    public function test_an_empty_message_is_rejected(): void
    {
        $this->send($this->guest, '   ')->assertSessionHasErrors('message');

        $this->assertSame(0, Message::count());
    }

    public function test_the_thread_preserves_line_breaks_when_displayed(): void
    {
        $this->send($this->guest, "Line one\nLine two");

        $this->actingAs($this->guest)
            ->get(route('messages.show', $this->booking))
            ->assertOk()
            // Without pre-wrap the newline would render as a single space.
            ->assertSee('white-space: pre-wrap', escape: false);
    }

    // ── Routing of messages ─────────────────────────────────────────────────

    public function test_a_guest_writes_to_the_host(): void
    {
        $this->send($this->guest, 'Hello');

        $message = Message::firstOrFail();
        $this->assertSame($this->guest->id, $message->sender_id);
        $this->assertSame($this->admin->id, $message->receiver_id);
    }

    public function test_the_host_writes_back_to_the_guest(): void
    {
        $this->send($this->admin, 'Hello back');

        $message = Message::firstOrFail();
        $this->assertSame($this->admin->id, $message->sender_id);
        $this->assertSame($this->guest->id, $message->receiver_id);
    }

    public function test_an_unrelated_guest_cannot_read_or_write_the_thread(): void
    {
        $stranger = User::factory()->create(['role' => 'user']);

        $this->actingAs($stranger)->get(route('messages.show', $this->booking))->assertForbidden();
        $this->send($stranger, 'Let me in')->assertForbidden();

        $this->assertSame(0, Message::count());
    }

    // ── The inbox ───────────────────────────────────────────────────────────

    public function test_the_inbox_lists_a_guests_own_threads_only(): void
    {
        $this->send($this->guest, 'Mine');

        $otherGuest = User::factory()->create(['role' => 'user']);
        $otherBooking = Booking::factory()->create([
            'user_id' => $otherGuest->id,
            'apartment_id' => Apartment::factory()->create(['name' => 'Someone Elses Place'])->id,
        ]);
        Message::create([
            'booking_id' => $otherBooking->id,
            'sender_id' => $otherGuest->id,
            'receiver_id' => $this->admin->id,
            'message' => 'Theirs',
        ]);

        $this->actingAs($this->guest)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee($this->booking->apartment->name)
            ->assertDontSee('Someone Elses Place');
    }

    public function test_the_host_sees_every_thread(): void
    {
        $this->send($this->guest, 'From guest one');

        $otherGuest = User::factory()->create(['role' => 'user']);
        $otherBooking = Booking::factory()->create([
            'user_id' => $otherGuest->id,
            'apartment_id' => Apartment::factory()->create(['name' => 'Harbour Studio Two'])->id,
        ]);
        Message::create([
            'booking_id' => $otherBooking->id,
            'sender_id' => $otherGuest->id,
            'receiver_id' => $this->admin->id,
            'message' => 'From guest two',
        ]);

        $this->actingAs($this->admin)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee($this->booking->apartment->name)
            ->assertSee('Harbour Studio Two');
    }

    public function test_bookings_with_no_messages_are_not_listed(): void
    {
        $this->actingAs($this->guest)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('No messages yet');
    }

    public function test_the_inbox_counts_what_is_unread(): void
    {
        $this->send($this->admin, 'One');
        $this->send($this->admin, 'Two');

        $this->actingAs($this->guest)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('2 new');
    }

    /**
     * Opening a thread is what marks it read, so the badge has to clear.
     */
    public function test_opening_a_thread_clears_its_unread_count(): void
    {
        $this->send($this->admin, 'Unread so far');

        $this->actingAs($this->guest)->get(route('messages.show', $this->booking))->assertOk();

        $this->assertSame(0, Message::where('receiver_id', $this->guest->id)->where('is_read', false)->count());

        $this->actingAs($this->guest)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertDontSee('1 new');
    }

    public function test_the_inbox_requires_signing_in(): void
    {
        $this->get(route('messages.index'))->assertRedirect(route('login'));
    }
}
