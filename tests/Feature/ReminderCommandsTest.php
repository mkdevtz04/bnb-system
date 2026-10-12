<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\ReviewRequestNotification;
use App\Notifications\UpcomingStayReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The two scheduled commands that reach a guest without a human sending
 * anything. Both stamp a *_sent_at column right after sending — these tests
 * exist mainly to pin that idempotency down, since a scheduler tick that
 * double-sends is the specific failure mode a "sent at" guard exists to rule
 * out.
 */
class ReminderCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_checking_in_tomorrow_gets_one_reminder_even_if_the_command_runs_twice(): void
    {
        Notification::fake();

        $guest = User::factory()->create(['role' => 'user']);
        $apartment = Apartment::factory()->create();

        $booking = Booking::factory()->confirmed()->stay(
            CarbonImmutable::tomorrow()->toDateString(),
            CarbonImmutable::tomorrow()->addDays(3)->toDateString(),
        )->create(['user_id' => $guest->id, 'apartment_id' => $apartment->id]);

        $this->artisan('bookings:send-arrival-reminders')->assertExitCode(0);
        $this->artisan('bookings:send-arrival-reminders')->assertExitCode(0);

        Notification::assertSentToTimes($guest, UpcomingStayReminderNotification::class, 1);
        $this->assertNotNull($booking->refresh()->arrival_reminder_sent_at);
    }

    public function test_a_booking_checking_in_further_out_is_not_reminded_yet(): void
    {
        Notification::fake();

        $guest = User::factory()->create(['role' => 'user']);
        $apartment = Apartment::factory()->create();

        Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(5)->toDateString(),
            CarbonImmutable::today()->addDays(8)->toDateString(),
        )->create(['user_id' => $guest->id, 'apartment_id' => $apartment->id]);

        $this->artisan('bookings:send-arrival-reminders');

        Notification::assertNotSentTo($guest, UpcomingStayReminderNotification::class);
    }

    public function test_a_guest_who_checked_out_yesterday_is_asked_to_review_once(): void
    {
        Notification::fake();

        $guest = User::factory()->create(['role' => 'user']);
        $apartment = Apartment::factory()->create();

        $booking = Booking::factory()->confirmed()->stay(
            CarbonImmutable::yesterday()->subDays(3)->toDateString(),
            CarbonImmutable::yesterday()->toDateString(),
        )->create(['user_id' => $guest->id, 'apartment_id' => $apartment->id]);

        $this->artisan('bookings:send-review-requests');
        $this->artisan('bookings:send-review-requests');

        Notification::assertSentToTimes($guest, ReviewRequestNotification::class, 1);
        $this->assertNotNull($booking->refresh()->review_request_sent_at);
    }

    public function test_a_guest_who_already_reviewed_is_not_asked_again(): void
    {
        Notification::fake();

        $guest = User::factory()->create(['role' => 'user']);
        $apartment = Apartment::factory()->create();

        $booking = Booking::factory()->confirmed()->stay(
            CarbonImmutable::yesterday()->subDays(3)->toDateString(),
            CarbonImmutable::yesterday()->toDateString(),
        )->create(['user_id' => $guest->id, 'apartment_id' => $apartment->id]);

        Review::create([
            'booking_id' => $booking->id,
            'user_id' => $guest->id,
            'apartment_id' => $apartment->id,
            'cleanliness' => 9,
            'comfort' => 9,
            'location' => 9,
            'facilities' => 9,
            'staff' => 9,
            'value' => 9,
            'title' => 'Great stay',
            'liked' => 'Everything',
        ]);

        $this->artisan('bookings:send-review-requests');

        Notification::assertNotSentTo($guest, ReviewRequestNotification::class);
    }

    public function test_the_arrival_reminder_adds_sms_only_when_a_phone_number_is_on_file(): void
    {
        $withPhone = User::factory()->create(['phone' => '+255700000099']);
        $withoutPhone = User::factory()->create(['phone' => null]);
        $booking = Booking::factory()->confirmed()->create();

        $notification = new UpcomingStayReminderNotification($booking);

        $this->assertContains(SmsChannel::class, $notification->via($withPhone));
        $this->assertNotContains(SmsChannel::class, $notification->via($withoutPhone));
    }

    public function test_the_new_notification_emails_render(): void
    {
        $guest = User::factory()->create();
        $apartment = Apartment::factory()->create();
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $guest->id,
            'apartment_id' => $apartment->id,
        ]);

        $reminder = (new UpcomingStayReminderNotification($booking))->toMail($guest);
        $this->assertStringContainsString($apartment->name, (string) $reminder->render());

        $review = (new ReviewRequestNotification($booking))->toMail($guest);
        $this->assertStringContainsString($apartment->name, (string) $review->render());
    }
}
