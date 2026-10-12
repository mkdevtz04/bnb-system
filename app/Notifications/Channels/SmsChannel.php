<?php

namespace App\Notifications\Channels;

use App\Services\Sms\SmsGateway;
use Illuminate\Notifications\Notification;

/**
 * A custom Laravel notification channel, the same mechanism 'mail' and
 * 'database' already use. Any notification that lists SmsChannel::class in
 * its via() must define toSms(object $notifiable): string.
 */
class SmsChannel
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (blank($notifiable->phone) || ! method_exists($notification, 'toSms')) {
            return;
        }

        $this->gateway->send($notifiable->phone, $notification->toSms($notifiable));
    }
}
