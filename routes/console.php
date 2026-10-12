<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keeps connected external calendars (Airbnb, VRBO, ...) in step with this
// system's own availability, in both directions. A worker must be running
// for this to actually fire — see php artisan schedule:work in local dev, or
// the system cron entry documented in the README for production.
Schedule::command('bookings:sync-ical-feeds')->hourly();

// Both of these are idempotent (they stamp a *_sent_at column after sending),
// so a missed or re-run tick never double-sends.
Schedule::command('bookings:send-arrival-reminders')->dailyAt('09:00');
Schedule::command('bookings:send-review-requests')->dailyAt('10:00');
