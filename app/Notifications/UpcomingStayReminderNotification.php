<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent the day before check-in on a confirmed booking.
 *
 * App\Console\Commands\SendArrivalReminders is the only sender, and it stamps
 * Booking::$arrival_reminder_sent_at immediately after, so a guest never gets
 * this twice for the same stay.
 */
class UpcomingStayReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    /**
     * SMS is additive, not a replacement for email — it only goes out when
     * the guest actually gave us a phone number.
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (filled($notifiable->phone)) {
            $channels[] = SmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("See you tomorrow · {$this->booking->display_reference}")
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->view('emails.upcoming-stay-reminder', ['booking' => $this->booking]);
    }

    public function toSms(object $notifiable): string
    {
        $apartment = $this->booking->apartment;

        $checkInTime = \Carbon\Carbon::parse($apartment->check_in_from)->format('g:i A');

        return "CoastalCharmz: your stay at {$apartment->name} starts tomorrow. ".
            "Check-in from {$checkInTime}. Ref {$this->booking->display_reference}.";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'arrival_reminder',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->display_reference,
            'apartment_name' => $this->booking->apartment->name,
            'message' => "Your stay at {$this->booking->apartment->name} starts tomorrow.",
            'url' => route('bookings.confirmation', $this->booking),
        ];
    }
}
