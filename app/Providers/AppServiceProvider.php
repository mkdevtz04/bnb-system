<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\TwilioSmsGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Same "never break the page" philosophy documented in the README for
        // Firebase/Google sign-in: with no Twilio credentials configured, SMS
        // silently degrades to the log instead of throwing from inside a
        // queued job every time a reminder goes out.
        $this->app->bind(SmsGateway::class, function () {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.auth_token');
            $from = config('services.twilio.from');

            if (blank($sid) || blank($token) || blank($from)) {
                return new LogSmsGateway;
            }

            return new TwilioSmsGateway($sid, $token, $from);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
