<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

/**
 * Writes the booking emails to disk as HTML so they can be opened in a browser.
 *
 * Email templates are hard to eyeball from source — the layout is tables and the
 * styling is inline — and sending real mail to check a margin is a poor feedback
 * loop.
 */
class MailPreview extends Command
{
    protected $signature = 'mail:preview
                            {--booking= : Use a specific booking id}
                            {--to= : Also send them to this address, to check a real client}';

    protected $description = 'Render the booking emails to storage/app/mail-preview for inspection';

    public function handle(): int
    {
        $booking = $this->option('booking')
            ? Booking::with('apartment', 'user')->find($this->option('booking'))
            : Booking::with('apartment', 'user')->latest('id')->first();

        if (! $booking) {
            $this->components->error('No bookings to preview. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        $dir = storage_path('app/mail-preview');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->components->info("Rendering emails for {$booking->display_reference}");

        foreach ([
            'confirmed' => \App\Notifications\BookingConfirmedNotification::class,
            'requested' => \App\Notifications\NewBookingNotification::class,
            'cancelled' => \App\Notifications\BookingCancelledNotification::class,
        ] as $name => $class) {
            $mail = (new $class($booking))->toMail($booking->user);
            $path = "{$dir}/{$name}.html";

            file_put_contents($path, (string) $mail->render());

            $this->line(sprintf('  %-10s %-52s %s', $name, $mail->subject, $path));
        }

        $this->newLine();
        $this->line('  Open them in a browser to check the layout.');

        if ($to = $this->option('to')) {
            $this->newLine();
            $this->components->info("Sending them to {$to}");

            // Sent through the real mailer so the embedded logo, the inlined
            // styles and the client's own rendering are all exercised — a browser
            // preview proves none of those.
            foreach ([
                'confirmed' => \App\Notifications\BookingConfirmedNotification::class,
                'requested' => \App\Notifications\NewBookingNotification::class,
                'cancelled' => \App\Notifications\BookingCancelledNotification::class,
            ] as $name => $class) {
                try {
                    \Illuminate\Support\Facades\Notification::route('mail', $to)
                        ->notifyNow(new $class($booking));

                    $this->line("  sent: {$name}");
                } catch (\Throwable $e) {
                    $this->components->error("{$name}: ".$e->getMessage());

                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }
}
