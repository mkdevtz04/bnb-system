<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // The subject carries the reference, not the database id. A padded
        // sequential id told every guest how many bookings the business had ever
        // taken, and invited guessing at the ones either side of it.
        return (new MailMessage)
            ->subject("Your stay is confirmed · {$this->booking->display_reference}")
            // A guest told to "just reply to this email" must reach a person, even
            // if the sending address later becomes a no-reply.
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->view('emails.booking-confirmed', ['booking' => $this->booking]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'apartment_name' => $this->booking->apartment->name,
            'check_in' => $this->booking->check_in->format('Y-m-d'),
            'check_out' => $this->booking->check_out->format('Y-m-d'),
            'message' => 'Your booking for '.$this->booking->apartment->name.' has been confirmed!',
        ];
    }
}
