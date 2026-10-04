<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
            ->subject("Booking cancelled · {$this->booking->display_reference}")
            // A guest told to "just reply to this email" must reach a person, even
            // if the sending address later becomes a no-reply.
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->view('emails.booking-cancelled', ['booking' => $this->booking]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->display_reference,
            'apartment_name' => $this->booking->apartment->name,
            'message' => "Your booking at {$this->booking->apartment->name} was cancelled.",
            'url' => route('bookings.history'),
        ];
    }
}
