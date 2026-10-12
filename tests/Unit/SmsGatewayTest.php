<?php

namespace Tests\Unit;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\TwilioSmsGateway;
use Tests\TestCase;

/**
 * With no Twilio credentials configured, the app must still boot and send
 * arrival reminders — just without a real text going anywhere. See
 * App\Providers\AppServiceProvider for the binding this exercises.
 */
class SmsGatewayTest extends TestCase
{
    public function test_it_falls_back_to_the_log_gateway_with_no_credentials_configured(): void
    {
        config()->set('services.twilio.sid', null);
        config()->set('services.twilio.auth_token', null);
        config()->set('services.twilio.from', null);

        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }

    public function test_it_uses_twilio_once_fully_configured(): void
    {
        config()->set('services.twilio.sid', 'ACxxx');
        config()->set('services.twilio.auth_token', 'secret');
        config()->set('services.twilio.from', '+15551234567');

        $this->assertInstanceOf(TwilioSmsGateway::class, app(SmsGateway::class));
    }

    public function test_an_incomplete_configuration_still_falls_back_to_the_log_gateway(): void
    {
        config()->set('services.twilio.sid', 'ACxxx');
        config()->set('services.twilio.auth_token', null);
        config()->set('services.twilio.from', '+15551234567');

        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }
}
