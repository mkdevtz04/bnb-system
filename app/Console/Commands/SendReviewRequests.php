<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\ReviewRequestNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Asks a guest for a review the day after they check out. Scheduled daily in
 * routes/console.php; review_request_sent_at is stamped right after sending,
 * same idempotency guard as SendArrivalReminders. whereDoesntHave('review')
 * means a guest who already left one — perhaps unprompted, the same day they
 * left — is never asked again.
 */
class SendReviewRequests extends Command
{
    protected $signature = 'bookings:send-review-requests';

    protected $description = 'Email guests who checked out yesterday and have not yet reviewed their stay';

    public function handle(): int
    {
        $yesterday = CarbonImmutable::yesterday()->toDateString();

        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('check_out', $yesterday)
            ->whereNull('review_request_sent_at')
            ->whereDoesntHave('review')
            ->with(['user', 'apartment'])
            ->get();

        foreach ($bookings as $booking) {
            $booking->user->notify(new ReviewRequestNotification($booking));
            $booking->forceFill(['review_request_sent_at' => now()])->save();
        }

        $this->components->info("{$bookings->count()} review request(s) sent for check-outs on {$yesterday}.");

        return self::SUCCESS;
    }
}
