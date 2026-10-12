<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends over Twilio's plain REST API via Laravel's own Http client, rather
 * than pulling in the twilio/sdk package — this app needs exactly one
 * endpoint, and a full SDK dependency is a lot of surface area for that.
 *
 * Bound by App\Providers\AppServiceProvider only once TWILIO_SID,
 * TWILIO_AUTH_TOKEN and TWILIO_FROM are all set; see config/services.php.
 */
class TwilioSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $sid,
        private readonly string $authToken,
        private readonly string $from,
    ) {}

    public function send(string $to, string $body): void
    {
        $response = Http::asForm()
            ->withBasicAuth($this->sid, $this->authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                'To' => $to,
                'From' => $this->from,
                'Body' => $body,
            ]);

        if ($response->failed()) {
            $message = $response->json('message') ?? $response->body();

            throw new RuntimeException("Twilio rejected the message: {$message}");
        }
    }
}
