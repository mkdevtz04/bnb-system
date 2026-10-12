<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * The default SMS gateway: writes the message to the log instead of sending
 * it anywhere. This is deliberately the fallback rather than an error, for
 * the same reason MAIL_MAILER=log exists — an SMS reminder is a nice-to-have,
 * and a missing TWILIO_SID should never turn into an exception that fails a
 * queued job or a scheduled command. Bound by
 * App\Providers\AppServiceProvider whenever services.twilio.sid is blank.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $body): void
    {
        Log::info('SMS (no provider configured, not actually sent)', [
            'to' => $to,
            'body' => $body,
        ]);
    }
}
