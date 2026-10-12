<?php

namespace App\Services\Ical;

use App\Models\ApartmentIcalFeed;
use App\Models\BlockedDate;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Sabre\VObject\Reader;

/**
 * Keeps one ApartmentIcalFeed's mirrored blocked_dates rows in step with the
 * external calendar it points at.
 *
 * Every row this writes is tagged source = 'ical' and owned by this feed's id
 * (BlockedDate::fromIcal()), so a re-sync only ever adds or removes rows it
 * created itself — never a host's manual block, and never another connected
 * feed's row. A date that is already closed for any other reason is left
 * exactly as it is: the night is already off sale either way, and claiming it
 * here would let a later sync of *this* feed delete a closure it does not own.
 */
class IcalImportService
{
    /**
     * @throws RuntimeException if the feed cannot be fetched or parsed
     */
    public function sync(ApartmentIcalFeed $feed): void
    {
        $response = Http::timeout(15)->get($feed->url);

        if (! $response->successful()) {
            throw new RuntimeException("The calendar returned HTTP {$response->status()}.");
        }

        $parsed = $this->parse($response->body());

        DB::transaction(function () use ($feed, $parsed) {
            // A plain array, not an Eloquent Collection: Eloquent\Collection's
            // except()/only() filter by each model's primary key regardless of
            // any keyBy() applied first, which would silently ignore the date
            // keys below and either keep or drop the wrong rows entirely.
            $existing = BlockedDate::where('apartment_id', $feed->apartment_id)
                ->fromIcal($feed->id)
                ->get()
                ->keyBy(fn (BlockedDate $row) => $row->date->toDateString())
                ->all();

            $existingDates = array_keys($existing);
            $parsedDates = array_keys($parsed);

            $newDates = array_diff($parsedDates, $existingDates);

            if ($newDates !== []) {
                $rows = $this->rowsForNewDates($feed, $parsed, $newDates);

                if ($rows !== []) {
                    BlockedDate::insert($rows);
                }
            }

            $staleDates = array_diff($existingDates, $parsedDates);
            $staleIds = array_map(fn (string $date) => $existing[$date]->id, $staleDates);

            if ($staleIds !== []) {
                BlockedDate::whereIn('id', $staleIds)->delete();
            }
        });

        $feed->markSynced();
    }

    /**
     * @param  array<string, string>  $parsed
     * @param  array<int, string>  $newDates
     * @return array<int, array<string, mixed>>
     */
    private function rowsForNewDates(ApartmentIcalFeed $feed, array $parsed, array $newDates): array
    {
        // Never claim a date something else already closed, whether that is a
        // host's manual block or a different connected feed's own row.
        $alreadyTaken = BlockedDate::where('apartment_id', $feed->apartment_id)
            ->whereIn('date', $newDates)
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        return collect($newDates)
            ->diff($alreadyTaken)
            ->map(fn (string $date) => [
                'apartment_id' => $feed->apartment_id,
                'date' => $date,
                'reason' => 'Synced from '.$feed->label,
                'source' => 'ical',
                'ical_feed_id' => $feed->id,
                'external_uid' => $parsed[$date],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string> date (Y-m-d) => the UID of the event that
     *                               covers it. Where two events overlap the
     *                               same night, whichever is parsed last wins
     *                               the attribution — the night is blocked
     *                               either way, so which UID it is credited
     *                               to does not affect availability.
     */
    public function parse(string $icsContents): array
    {
        $calendar = Reader::read($icsContents, Reader::OPTION_FORGIVING);

        $datesToUid = [];

        foreach ($calendar->select('VEVENT') as $vevent) {
            if (! isset($vevent->DTSTART)) {
                continue;
            }

            $uid = isset($vevent->UID) ? (string) $vevent->UID : sha1($vevent->serialize());

            $start = $this->toDate($vevent->DTSTART);
            $end = isset($vevent->DTEND) ? $this->toDate($vevent->DTEND) : $start->addDay();

            if ($end <= $start) {
                continue;
            }

            foreach (CarbonPeriod::create($start, $end->subDay()) as $night) {
                $datesToUid[$night->toDateString()] = $uid;
            }
        }

        return $datesToUid;
    }

    private function toDate(mixed $property): CarbonImmutable
    {
        return CarbonImmutable::parse($property->getDateTime()->format('Y-m-d'))->startOfDay();
    }
}
