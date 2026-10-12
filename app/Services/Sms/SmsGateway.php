<?php

namespace App\Services\Sms;

/**
 * The one thing anything that sends a text message needs to do. Swapped out
 * entirely by App\Providers\AppServiceProvider depending on whether a real
 * provider is configured — nothing that depends on this interface needs to
 * know or care which implementation it got.
 */
interface SmsGateway
{
    public function send(string $to, string $body): void;
}
