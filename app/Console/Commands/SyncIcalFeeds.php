<?php

namespace App\Console\Commands;

use App\Models\ApartmentIcalFeed;
use App\Services\Ical\IcalImportService;
use Illuminate\Console\Command;

/**
 * Polls every connected external calendar and mirrors its busy dates into
 * blocked_dates, so a night booked on Airbnb or VRBO is closed here too.
 * Scheduled hourly in routes/console.php; run by hand to check a feed that
 * just started failing.
 */
class SyncIcalFeeds extends Command
{
    protected $signature = 'bookings:sync-ical-feeds {feed? : Sync only this ApartmentIcalFeed id}';

    protected $description = 'Sync connected external calendars (Airbnb, VRBO, ...) into blocked_dates';

    public function handle(IcalImportService $importer): int
    {
        $feeds = ApartmentIcalFeed::with('apartment')
            ->when($this->argument('feed'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        if ($feeds->isEmpty()) {
            $this->components->info('No connected calendars to sync.');

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($feeds as $feed) {
            $label = "{$feed->apartment->name} · {$feed->label}";

            try {
                $importer->sync($feed);
                $this->components->twoColumnDetail($label, '<fg=green>synced</>');
            } catch (\Throwable $e) {
                $feed->markFailed($e->getMessage());
                report($e);
                $failures++;
                $this->components->twoColumnDetail($label, '<fg=red>failed</> '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->line($feeds->count().' feed(s) checked, '.$failures.' failed.');

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
