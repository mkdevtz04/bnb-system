<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Services\Ical\IcalImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * App\Services\Ical\IcalImportService keeps one connected calendar's mirrored
 * blocked_dates rows in step with that calendar. The property that matters
 * most: a re-sync must add and remove only the rows it owns, and never a
 * host's own manual block or a different feed's row.
 */
class IcalImportTest extends TestCase
{
    use RefreshDatabase;

    private function ics(array $events): string
    {
        $body = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//Test//EN\r\n";

        foreach ($events as $event) {
            $body .= "BEGIN:VEVENT\r\n";
            $body .= "UID:{$event['uid']}\r\n";
            $body .= "DTSTAMP:20270101T000000Z\r\n";
            $body .= "DTSTART;VALUE=DATE:{$event['start']}\r\n";
            $body .= "DTEND;VALUE=DATE:{$event['end']}\r\n";
            $body .= "SUMMARY:Reserved\r\n";
            $body .= "END:VEVENT\r\n";
        }

        return $body.'END:VCALENDAR';
    }

    private function blockedDates(Apartment $apartment): array
    {
        return BlockedDate::where('apartment_id', $apartment->id)
            ->get()
            ->map(fn (BlockedDate $row) => $row->date->toDateString())
            ->sort()
            ->values()
            ->all();
    }

    public function test_syncing_a_feed_blocks_the_dates_it_covers(): void
    {
        $apartment = Apartment::factory()->create();
        $feed = $apartment->icalFeeds()->create(['label' => 'Airbnb', 'url' => 'https://example.test/cal.ics']);

        Http::fake([
            $feed->url => Http::response($this->ics([
                ['uid' => 'evt1@airbnb', 'start' => '20270410', 'end' => '20270413'],
            ])),
        ]);

        app(IcalImportService::class)->sync($feed);

        $this->assertSame(['2027-04-10', '2027-04-11', '2027-04-12'], $this->blockedDates($apartment));
        $this->assertTrue(BlockedDate::fromIcal($feed->id)->where('apartment_id', $apartment->id)->exists());

        $this->assertSame('ok', $feed->refresh()->last_sync_status);
    }

    public function test_a_resync_removes_dates_no_longer_on_the_feed_and_adds_new_ones(): void
    {
        $apartment = Apartment::factory()->create();
        $feed = $apartment->icalFeeds()->create(['label' => 'Airbnb', 'url' => 'https://example.test/cal.ics']);

        Http::fake([
            $feed->url => Http::sequence()
                ->push($this->ics([['uid' => 'evt1', 'start' => '20270410', 'end' => '20270413']]))
                ->push($this->ics([['uid' => 'evt1', 'start' => '20270411', 'end' => '20270414']])),
        ]);

        $importer = app(IcalImportService::class);
        $importer->sync($feed);
        $importer->sync($feed);

        $this->assertSame(['2027-04-11', '2027-04-12', '2027-04-13'], $this->blockedDates($apartment));
    }

    /**
     * The regression that matters most for this table: a connected feed must
     * never claim a date the host already closed by hand, and must never
     * delete it on a later sync either.
     */
    public function test_a_hosts_manual_block_is_never_touched_by_a_sync(): void
    {
        $apartment = Apartment::factory()->create();

        BlockedDate::create([
            'apartment_id' => $apartment->id,
            'date' => '2027-04-11',
            'reason' => 'Deep clean',
        ]);

        $feed = $apartment->icalFeeds()->create(['label' => 'Airbnb', 'url' => 'https://example.test/cal.ics']);

        Http::fake([
            $feed->url => Http::response($this->ics([
                ['uid' => 'evt1', 'start' => '20270410', 'end' => '20270413'],
            ])),
        ]);

        app(IcalImportService::class)->sync($feed);

        $manual = BlockedDate::where('apartment_id', $apartment->id)->where('date', '2027-04-11')->first();
        $this->assertSame('manual', $manual->source);
        $this->assertNull($manual->ical_feed_id);

        // The feed then drops the whole range it used to report. The manual
        // row must survive even though it falls inside that now-empty range.
        Http::fake([$feed->url => Http::response($this->ics([]))]);
        app(IcalImportService::class)->sync($feed);

        $this->assertNotNull(BlockedDate::where('apartment_id', $apartment->id)->where('date', '2027-04-11')->first());
    }

    public function test_a_failed_fetch_marks_the_feed_failed(): void
    {
        $apartment = Apartment::factory()->create();
        $feed = $apartment->icalFeeds()->create(['label' => 'Airbnb', 'url' => 'https://example.test/cal.ics']);

        Http::fake([$feed->url => Http::response('', 500)]);

        $this->artisan('bookings:sync-ical-feeds')->assertExitCode(1);

        $this->assertSame('error', $feed->refresh()->last_sync_status);
    }
}
