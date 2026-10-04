<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification implements ShouldQueue
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
        $guest = $this->booking->user;

        return (new MailMessage)
            ->subject("New booking request · {$this->booking->display_reference}")
            // Reply goes to the guest, not back to ourselves.
            //
            // This email is sent FROM the site's own address, so without a
            // Reply-To the host hitting Reply in their mail client wrote to the
            // address the mail came from — their own — and the guest never heard
            // anything. The one person the host actually wants to answer is the
            // one who just asked for the booking.
            ->replyTo($guest->email, $guest->display_name)
            ->view('emails.booking-requested', ['booking' => $this->booking]);
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
            'guest_name' => $this->booking->user->name,
            'check_in' => $this->booking->check_in->format('Y-m-d'),
            'check_out' => $this->booking->check_out->format('Y-m-d'),
            'total_price' => $this->booking->total_price,
            'message' => 'New booking request for '.$this->booking->apartment->name,
        ];
    }
}
