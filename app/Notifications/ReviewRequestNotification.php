<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent the day after checkout on a completed, unreviewed booking.
 *
 * App\Console\Commands\SendReviewRequests is the only sender, and it stamps
 * Booking::$review_request_sent_at immediately after, so a guest never gets
 * this twice for the same stay. It never fires once Review::canBeReviewed()
 * says no — the guest already reviewed, or the booking was never confirmed.
 */
class ReviewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("How was {$this->booking->apartment->name}?")
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->view('emails.review-request', ['booking' => $this->booking]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_request',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->display_reference,
            'apartment_name' => $this->booking->apartment->name,
            'message' => "Got a minute to review your stay at {$this->booking->apartment->name}?",
            'url' => route('reviews.create', $this->booking),
        ];
    }
}
