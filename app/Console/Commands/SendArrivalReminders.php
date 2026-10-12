<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\UpcomingStayReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Nudges a guest the day before they arrive. Scheduled daily in
 * routes/console.php; arrival_reminder_sent_at is stamped right after sending
 * so a guest never gets this twice for the same stay, even if the command
 * runs more than once on the same day.
 */
class SendArrivalReminders extends Command
{
    protected $signature = 'bookings:send-arrival-reminders';

    protected $description = 'Email (and text, if a phone number is on file) guests checking in tomorrow';

    public function handle(): int
    {
        $tomorrow = CarbonImmutable::tomorrow()->toDateString();

        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('check_in', $tomorrow)
            ->whereNull('arrival_reminder_sent_at')
            ->with(['user', 'apartment'])
            ->get();

        foreach ($bookings as $booking) {
            $booking->user->notify(new UpcomingStayReminderNotification($booking));
            $booking->forceFill(['arrival_reminder_sent_at' => now()])->save();
        }

        $this->components->info("{$bookings->count()} arrival reminder(s) sent for check-ins on {$tomorrow}.");

        return self::SUCCESS;
    }
}
