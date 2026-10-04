<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availability;

    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availability = app(AvailabilityService::class);
        $this->apartment = Apartment::factory()->create();
    }

    private function check(string $in, string $out): bool
    {
        return $this->availability->isAvailable(
            $this->apartment,
            CarbonImmutable::parse($in),
            CarbonImmutable::parse($out),
        );
    }

    public function test_free_dates_are_available(): void
    {
        $this->assertTrue($this->check('2027-03-01', '2027-03-05'));
    }

    public function test_a_fully_overlapping_stay_is_rejected(): void
    {
        Booking::factory()->confirmed()->stay('2027-03-01', '2027-03-10')->create([
            'apartment_id' => $this->apartment->id,
        ]);

        $this->assertFalse($this->check('2027-03-03', '2027-03-06'));
    }

    public function test_a_partially_overlapping_stay_is_rejected(): void
    {
        Booking::factory()->confirmed()->stay('2027-03-05', '2027-03-10')->create([
            'apartment_id' => $this->apartment->id,
        ]);

        $this->assertFalse($this->check('2027-03-03', '2027-03-07'));
        $this->assertFalse($this->check('2027-03-08', '2027-03-12'));
    }

    /**
     * The regression that matters most: the old inclusive whereBetween() check
     * rejected an arrival on the day a previous guest departed.
     */
    public function test_same_day_turnover_is_allowed(): void
    {
        Booking::factory()->confirmed()->stay('2027-03-01', '2027-03-05')->create([
            'apartment_id' => $this->apartment->id,
        ]);

        $this->assertTrue($this->check('2027-03-05', '2027-03-09'));
    }

    public function test_departing_on_the_day_another_stay_begins_is_allowed(): void
    {
        Booking::factory()->confirmed()->stay('2027-03-05', '2027-03-09')->create([
            'apartment_id' => $this->apartment->id,
        ]);

        $this->assertTrue($this->check('2027-03-01', '2027-03-05'));
    }

    public function test_cancelled_bookings_release_their_dates(): void
    {
        Booking::factory()->cancelled()->stay('2027-03-01', '2027-03-10')->create([
            'apartment_id' => $this->apartment->id,
        ]);

        $this->assertTrue($this->check('2027-03-03', '2027-03-06'));
    }

    public function test_pending_bookings_still_hold_their_dates(): void
    {
        Booking::factory()->stay('2027-03-01', '2027-03-10')->create([
            'apartment_id' => $this->apartment->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($this->check('2027-03-03', '2027-03-06'));
    }

    /**
     * Blocked dates were previously only used to grey out the calendar, so a
     * direct POST booked straight through them.
     */
    public function test_a_blocked_night_makes_the_stay_unavailable(): void
    {
        BlockedDate::create([
            'apartment_id' => $this->apartment->id,
            'date' => '2027-03-03',
            'reason' => 'Maintenance',
        ]);

        $this->assertFalse($this->check('2027-03-01', '2027-03-05'));
    }

    public function test_a_block_on_the_checkout_day_does_not_affect_the_stay(): void
    {
        BlockedDate::create([
            'apartment_id' => $this->apartment->id,
            'date' => '2027-03-05',
        ]);

        // The guest never occupies the check-out night.
        $this->assertTrue($this->check('2027-03-01', '2027-03-05'));
    }

    public function test_a_property_under_maintenance_is_never_available(): void
    {
        $this->apartment->update(['status' => 'maintenance']);

        $this->assertFalse($this->check('2027-03-01', '2027-03-05'));
    }

    public function test_a_zero_night_stay_is_rejected(): void
    {
        $this->assertFalse($this->check('2027-03-01', '2027-03-01'));
        $this->assertFalse($this->check('2027-03-05', '2027-03-01'));
    }

    public function test_unavailable_dates_exclude_the_checkout_day(): void
    {
        Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(5)->toDateString(),
            CarbonImmutable::today()->addDays(8)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $dates = $this->availability->unavailableDates($this->apartment);

        $this->assertContains(CarbonImmutable::today()->addDays(5)->toDateString(), $dates);
        $this->assertContains(CarbonImmutable::today()->addDays(7)->toDateString(), $dates);
        $this->assertNotContains(CarbonImmutable::today()->addDays(8)->toDateString(), $dates);
    }
}
