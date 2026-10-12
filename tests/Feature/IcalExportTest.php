<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sabre\VObject\Reader;
use Tests\TestCase;

/**
 * The public .ics feed other platforms subscribe to. It carries no auth
 * beyond the apartment's own token, which is why it must never leak a
 * guest's name or email, and a wrong token must behave as if the resource
 * does not exist at all.
 */
class IcalExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_confirmed_booking_appears_as_a_busy_event(): void
    {
        $apartment = Apartment::factory()->create();

        $booking = Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(10)->toDateString(),
            CarbonImmutable::today()->addDays(13)->toDateString(),
        )->create(['apartment_id' => $apartment->id]);

        $response = $this->get(route('apartments.ical.export', [$apartment, $apartment->ical_token]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        $events = Reader::read($response->getContent())->select('VEVENT');

        $this->assertCount(1, $events);
        $this->assertSame($booking->check_in->format('Ymd'), (string) $events[0]->DTSTART);
        $this->assertSame($booking->check_out->format('Ymd'), (string) $events[0]->DTEND);
    }

    public function test_the_feed_never_names_the_guest(): void
    {
        $apartment = Apartment::factory()->create();
        $guest = User::factory()->create(['name' => 'Jordan Kite']);

        Booking::factory()->confirmed()->create([
            'apartment_id' => $apartment->id,
            'user_id' => $guest->id,
        ]);

        $response = $this->get(route('apartments.ical.export', [$apartment, $apartment->ical_token]));

        $response->assertOk();
        $this->assertStringNotContainsString('Jordan Kite', $response->getContent());
        $this->assertStringNotContainsString($guest->email, $response->getContent());
    }

    /**
     * A date mirrored in from a connected feed is not this property's own
     * decision, so it must not be re-exported back out to a third calendar.
     */
    public function test_only_manual_closures_are_exported_not_ical_mirrored_ones(): void
    {
        $apartment = Apartment::factory()->create();

        BlockedDate::create([
            'apartment_id' => $apartment->id,
            'date' => CarbonImmutable::today()->addDays(20)->toDateString(),
            'reason' => 'Maintenance',
        ]);

        $feed = $apartment->icalFeeds()->create(['label' => 'Airbnb', 'url' => 'https://example.test/cal.ics']);

        BlockedDate::create([
            'apartment_id' => $apartment->id,
            'date' => CarbonImmutable::today()->addDays(30)->toDateString(),
            'source' => 'ical',
            'ical_feed_id' => $feed->id,
            'external_uid' => 'abc',
        ]);

        $response = $this->get(route('apartments.ical.export', [$apartment, $apartment->ical_token]));

        $events = Reader::read($response->getContent())->select('VEVENT');
        $this->assertCount(1, $events);
    }

    public function test_a_wrong_token_is_not_found(): void
    {
        $apartment = Apartment::factory()->create();

        $this->get(route('apartments.ical.export', [$apartment, 'wrongtoken1234']))->assertNotFound();
    }
}
