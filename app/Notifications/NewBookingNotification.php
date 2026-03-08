<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
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
        return (new MailMessage)
            ->subject('New Booking Received: #' . str_pad($this->booking->id, 6, '0', STR_PAD_LEFT))
            ->greeting('Hello Admin!')
            ->line('A new booking request has been placed for ' . $this->booking->apartment->name . '.')
            ->line('Guest: ' . $this->booking->user->name)
            ->line('Dates: ' . $this->booking->check_in->format('M d, Y') . ' to ' . $this->booking->check_out->format('M d, Y'))
            ->line('Total Price: $' . number_format($this->booking->total_price))
            ->action('View Bookings', route('admin.bookings'))
            ->line('Please confirm or reject this booking in the admin panel.');
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
            'message' => 'New booking request for ' . $this->booking->apartment->name
        ];
    }
}
