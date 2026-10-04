<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * Checks that outgoing mail is actually configured and reaches the provider.
 *
 * Worth having because the failure this replaces is silent: with MAIL_MAILER=log
 * every notification "succeeds" by writing to a file, so the app looks healthy
 * while no guest ever receives anything.
 */
class MailCheck extends Command
{
    protected $signature = 'mail:check
                            {--to= : Send a real test email to this address}
                            {--booking= : Send the real booking-confirmed email for this booking id}';

    protected $description = 'Diagnose outgoing email configuration and optionally send a test';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $from = config('mail.from.address');

        $this->components->info('Mail configuration');
        $this->line('  MAIL_MAILER        '.$mailer);

        if ($mailer === 'log') {
            $this->newLine();
            $this->components->error(
                'Mail is set to "log". Notifications are written to storage/logs/laravel.log '
                .'and nobody receives them. Set MAIL_MAILER=smtp.'
            );

            return self::FAILURE;
        }

        $transport = config("mail.mailers.{$mailer}");

        $this->line('  host               '.($transport['host'] ?? '—').':'.($transport['port'] ?? '—'));
        $this->line('  username           '.($transport['username'] ? $this->mask($transport['username']) : '<fg=red>(empty)</>'));
        $this->line('  password           '.($transport['password'] ? '(set)' : '<fg=red>(empty)</>'));
        $this->line('  from               '.$from.' ('.config('mail.from.name').')');
        $this->newLine();

        if (blank($transport['username'] ?? null) || blank($transport['password'] ?? null)) {
            $this->components->error('MAIL_USERNAME and MAIL_PASSWORD are required. Brevo → SMTP & API → SMTP.');

            return self::FAILURE;
        }

        // Reaching the SMTP banner proves DNS, routing and the port are fine,
        // separately from whether the credentials are accepted.
        $this->components->info('Connecting to the mail server');

        $errno = null;
        $errstr = null;
        $socket = @fsockopen($transport['host'], (int) $transport['port'], $errno, $errstr, 8);

        if (! $socket) {
            $this->components->error("Cannot reach {$transport['host']}:{$transport['port']} — {$errstr}");

            return self::FAILURE;
        }

        $this->line('  '.trim((string) fgets($socket, 512)));
        fclose($socket);
        $this->newLine();

        // Queued notifications only arrive if something is draining the queue.
        // Forgetting the worker is the usual reason mail "stops working" after a
        // deploy, so it gets called out rather than discovered later.
        $this->components->info('Queue');
        $this->line('  connection         '.config('queue.default'));

        if (config('queue.default') !== 'sync') {
            $pending = $this->pendingJobs();
            $this->line('  pending jobs       '.($pending === null ? 'unknown' : $pending));
            $this->line('  <fg=yellow>Notifications are queued — `php artisan queue:work` must be running,</>');
            $this->line('  <fg=yellow>or nothing is ever actually sent.</>');
        }

        $this->newLine();

        if ($bookingId = $this->option('booking')) {
            return $this->sendBookingEmail((int) $bookingId);
        }

        if ($to = $this->option('to')) {
            return $this->sendTestEmail($to);
        }

        $this->components->info('Configuration looks usable.');
        $this->line('  Send a test:            php artisan mail:check --to=you@example.com');
        $this->line('  Send a real booking:    php artisan mail:check --booking=1');

        return self::SUCCESS;
    }

    private function sendTestEmail(string $to): int
    {
        $this->components->info("Sending a test email to {$to}");

        try {
            // Sent immediately rather than queued: the point is to see the result
            // here and now, not to find out later whether a worker picked it up.
            Mail::raw(
                "This is a test from CoastalCharmz.\n\n"
                ."If you are reading it, outgoing email works.\n\n"
                .'Sent at '.now()->toDayDateTimeString().'.',
                fn ($message) => $message->to($to)->subject('CoastalCharmz — test email'),
            );
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());
            $this->newLine();
            $this->line('  A 535 here means Brevo rejected the credentials: use the SMTP key,');
            $this->line('  not your account password.');
            $this->line('  A sender error means '.config('mail.from.address').' is not a verified');
            $this->line('  sender in Brevo → Senders, Domains & Dedicated IPs.');

            return self::FAILURE;
        }

        $this->components->info('Accepted by the mail server. Check the inbox, and spam.');

        return self::SUCCESS;
    }

    private function sendBookingEmail(int $bookingId): int
    {
        $booking = Booking::with('apartment', 'user')->find($bookingId);

        if (! $booking) {
            $this->components->error("No booking with id {$bookingId}.");

            return self::FAILURE;
        }

        $this->components->info(
            "Sending the booking-confirmed email for {$booking->display_reference} to {$booking->user->email}"
        );

        try {
            // notifyNow bypasses the queue so a missing worker cannot hide the result.
            $booking->user->notifyNow(new BookingConfirmedNotification($booking));
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Sent.');

        return self::SUCCESS;
    }

    private function pendingJobs(): ?int
    {
        try {
            return Queue::size();
        } catch (Throwable) {
            return null;
        }
    }

    private function mask(string $value): string
    {
        return mb_strlen($value) <= 6
            ? str_repeat('*', mb_strlen($value))
            : mb_substr($value, 0, 4).'…'.mb_substr($value, -4);
    }
}
