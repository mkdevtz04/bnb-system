<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\NewBookingNotification;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Who gets told what, and how it is delivered.
 *
 * These matter more than they look. Until recently MAIL_MAILER was "log", so
 * every one of these notifications "succeeded" by writing to a file and no guest
 * ever received anything — the app reported perfect health while the email half
 * of it did nothing at all.
 */
class BookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private User $admin;

    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->guest = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->apartment = Apartment::factory()->create(['max_guests' => 4]);
    }

    /**
     * Mail delivery must not happen inside the web request.
     *
     * Without ShouldQueue, confirming a booking blocks on the SMTP conversation,
     * and a slow or unreachable mail server turns into a failed confirmation —
     * the booking is the important thing, the email is not.
     */
    public function test_every_booking_notification_is_queued(): void
    {
        foreach ([
            NewBookingNotification::class,
            BookingConfirmedNotification::class,
            BookingCancelledNotification::class,
        ] as $notification) {
            $this->assertTrue(
                is_subclass_of($notification, ShouldQueue::class),
                "{$notification} must implement ShouldQueue so mail never blocks a booking.",
            );
        }
    }

    public function test_every_booking_notification_goes_to_mail_and_the_bell(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        foreach ([
            new NewBookingNotification($booking),
            new BookingConfirmedNotification($booking),
            new BookingCancelledNotification($booking),
        ] as $notification) {
            $this->assertSame(
                ['mail', 'database'],
                $notification->via($this->guest),
                get_class($notification).' should reach the guest by email and in the app.',
            );
        }
    }

    public function test_the_host_is_told_when_a_booking_comes_in(): void
    {
        $this->actingAs($this->guest)->post(route('bookings.store'), [
            'apartment_id' => $this->apartment->id,
            'check_in' => CarbonImmutable::today()->addDays(10)->toDateString(),
            'check_out' => CarbonImmutable::today()->addDays(13)->toDateString(),
            'guests' => 2,
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo($this->admin, NewBookingNotification::class);
        Notification::assertNotSentTo($this->guest, NewBookingNotification::class);
    }

    public function test_the_guest_is_told_when_their_booking_is_confirmed(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
            'status' => 'pending',
        ]);

        app(BookingService::class)->confirm($booking);

        Notification::assertSentTo($this->guest, BookingConfirmedNotification::class);
    }

    public function test_the_guest_is_told_when_the_host_cancels(): void
    {
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        app(BookingService::class)->cancel($booking, $this->admin);

        Notification::assertSentTo($this->guest, BookingCancelledNotification::class);
    }

    /**
     * A guest cancelling their own booking should not be emailed about it — they
     * just did it, and they are already looking at the confirmation.
     */
    public function test_a_guest_cancelling_their_own_booking_is_not_emailed(): void
    {
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        app(BookingService::class)->cancel($booking, $this->guest);

        Notification::assertNotSentTo($this->guest, BookingCancelledNotification::class);
    }

    /**
     * Losing a competing request is exactly the moment someone needs telling,
     * because nothing they did caused it.
     */
    public function test_a_displaced_request_tells_the_guest_it_was_released(): void
    {
        $loser = User::factory()->create(['role' => 'user']);

        $winning = Booking::factory()->stay(
            CarbonImmutable::today()->addDays(10)->toDateString(),
            CarbonImmutable::today()->addDays(13)->toDateString(),
        )->create(['user_id' => $this->guest->id, 'apartment_id' => $this->apartment->id]);

        Booking::factory()->stay(
            CarbonImmutable::today()->addDays(11)->toDateString(),
            CarbonImmutable::today()->addDays(14)->toDateString(),
        )->create(['user_id' => $loser->id, 'apartment_id' => $this->apartment->id]);

        app(BookingService::class)->confirm($winning);

        Notification::assertSentTo($this->guest, BookingConfirmedNotification::class);
        Notification::assertSentTo($loser, BookingCancelledNotification::class);
    }

    /**
     * Rendering catches a broken route(), a missing relation or a bad date format
     * in a template — all of which would otherwise surface only as a failed queue
     * job after a real guest made a real booking.
     */
    public function test_every_email_renders(): void
    {
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        foreach ([
            BookingConfirmedNotification::class,
            NewBookingNotification::class,
            BookingCancelledNotification::class,
        ] as $class) {
            $mail = (new $class($booking))->toMail($this->guest);
            $html = (string) $mail->render();

            $this->assertStringContainsString($booking->apartment->name, $html, "{$class} lost the property name.");
            $this->assertStringContainsString('Coastal Charms', $html, "{$class} lost the branding.");
        }
    }

    /**
     * The reference is what a guest quotes back. The database id must never be
     * what identifies a booking to the outside world: a sequential number reveals
     * how much business has been taken and invites guessing at its neighbours.
     */
    public function test_emails_identify_a_booking_by_reference_not_database_id(): void
    {
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
            'reference' => 'CC7K2M9XQ4',
        ]);

        foreach ([
            BookingConfirmedNotification::class,
            NewBookingNotification::class,
            BookingCancelledNotification::class,
        ] as $class) {
            $mail = (new $class($booking))->toMail($this->guest);

            $this->assertStringContainsString('CC-7K2M-9XQ4', $mail->subject, "{$class} subject.");
            $this->assertStringContainsString('CC-7K2M-9XQ4', (string) $mail->render(), "{$class} body.");

            // The old format was "#000001" — a zero-padded primary key.
            $this->assertStringNotContainsString(
                '#'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                $mail->subject,
                "{$class} still leaks the database id.",
            );
        }
    }

    /**
     * The host must be able to answer the guest by hitting Reply.
     *
     * These emails are sent from the site's own address, so with no Reply-To the
     * host's reply went straight back to the address it came from — their own
     * inbox — and the guest never heard a word.
     */
    public function test_the_hosts_booking_email_replies_to_the_guest(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        $mail = (new NewBookingNotification($booking))->toMail($this->admin);

        $this->assertSame(
            [[$this->guest->email, $this->guest->display_name]],
            $mail->replyTo,
            'Replying to a booking request must reach the guest who made it.',
        );
    }

    /**
     * And a guest told to "just reply to this email" must reach the host, even
     * once the sending address becomes something nobody reads.
     */
    public function test_guest_emails_reply_to_the_host(): void
    {
        config()->set('mail.reply_to.address', 'host@coastalcharms.test');
        config()->set('mail.reply_to.name', 'Coastal Charms');

        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        foreach ([BookingConfirmedNotification::class, BookingCancelledNotification::class] as $class) {
            $mail = (new $class($booking))->toMail($this->guest);

            $this->assertSame(
                [['host@coastalcharms.test', 'Coastal Charms']],
                $mail->replyTo,
                "{$class} must let the guest reply to a person.",
            );
        }
    }

    public function test_the_display_reference_is_grouped_for_reading_aloud(): void
    {
        $booking = Booking::factory()->create(['reference' => 'CC7K2M9XQ4']);

        $this->assertSame('CC-7K2M-9XQ4', $booking->display_reference);
    }

    /**
     * Generated references avoid characters that are misread when written down or
     * spoken: 0/O, 1/I/L, and U.
     */
    public function test_generated_references_use_an_unambiguous_alphabet(): void
    {
        $apartment = Apartment::factory()->create(['max_guests' => 4]);

        for ($i = 0; $i < 25; $i++) {
            $booking = app(BookingService::class)->create(
                user: $this->guest,
                apartment: $apartment,
                checkIn: CarbonImmutable::today()->addDays(2 + ($i * 3)),
                checkOut: CarbonImmutable::today()->addDays(4 + ($i * 3)),
                guests: 1,
            );

            $this->assertMatchesRegularExpression(
                '/^CC[23456789ABCDEFGHJKMNPQRSTVWXYZ]{8}$/',
                $booking->reference,
                "Reference {$booking->reference} contains an easily misread character.",
            );
        }
    }
}
