<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification
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
            ->subject('Booking Confirmed! #' . str_pad($this->booking->id, 6, '0', STR_PAD_LEFT))
            ->greeting('Great news, ' . $this->booking->user->name . '!')
            ->line('Your booking for ' . $this->booking->apartment->name . ' has been confirmed by the host.')
            ->line('Check-in: ' . $this->booking->check_in->format('M d, Y'))
            ->line('Check-out: ' . $this->booking->check_out->format('M d, Y'))
            ->line('Total Price Paid: $' . number_format($this->booking->total_price))
            ->action('View My Bookings', route('bookings.history'))
            ->line('We look forward to hosting you. Safe travels!');
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
            'message' => 'Your booking for ' . $this->booking->apartment->name . ' has been confirmed!'
        ];
    }
}
