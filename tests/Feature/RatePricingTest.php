<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\ApartmentRate;
use App\Models\User;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Per-date rate overrides. A property with none behaves exactly as it always
 * did — every night at price_per_night — which is the property these tests
 * lead with, before checking the override itself.
 */
class RatePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_stay_with_no_overrides_prices_every_night_at_the_base_rate(): void
    {
        $apartment = Apartment::factory()->create(['price_per_night' => 100, 'cleaning_fee' => 0]);

        $quote = app(PricingService::class)->quote(
            $apartment,
            CarbonImmutable::parse('2027-05-01'),
            CarbonImmutable::parse('2027-05-04'),
        );

        $this->assertSame(300.0, $quote->subtotal);
        $this->assertSame(100.0, $quote->nightlyRate);
        $this->assertFalse($quote->hasVariableRate());
    }

    public function test_a_per_date_override_is_charged_only_for_its_own_night(): void
    {
        $apartment = Apartment::factory()->create(['price_per_night' => 100, 'cleaning_fee' => 0]);

        ApartmentRate::create([
            'apartment_id' => $apartment->id,
            'date' => '2027-05-02',
            'price_per_night' => 250,
        ]);

        $quote = app(PricingService::class)->quote(
            $apartment,
            CarbonImmutable::parse('2027-05-01'),
            CarbonImmutable::parse('2027-05-04'),
        );

        $this->assertSame(450.0, $quote->subtotal); // 100 + 250 + 100
        $this->assertTrue($quote->hasVariableRate());
        $this->assertSame(100.0, $quote->nightlyBreakdown['2027-05-01']);
        $this->assertSame(250.0, $quote->nightlyBreakdown['2027-05-02']);
        $this->assertSame(100.0, $quote->nightlyBreakdown['2027-05-03']);
    }

    public function test_an_override_outside_the_stay_is_ignored(): void
    {
        $apartment = Apartment::factory()->create(['price_per_night' => 100, 'cleaning_fee' => 0]);

        ApartmentRate::create([
            'apartment_id' => $apartment->id,
            'date' => '2027-06-01',
            'price_per_night' => 500,
        ]);

        $quote = app(PricingService::class)->quote(
            $apartment,
            CarbonImmutable::parse('2027-05-01'),
            CarbonImmutable::parse('2027-05-04'),
        );

        $this->assertSame(300.0, $quote->subtotal);
    }

    /**
     * The guest never occupies the check-out night — the same half-open rule
     * AvailabilityService applies to bookings applies to pricing too.
     */
    public function test_the_checkout_night_itself_is_never_priced(): void
    {
        $apartment = Apartment::factory()->create(['price_per_night' => 100, 'cleaning_fee' => 0]);

        ApartmentRate::create([
            'apartment_id' => $apartment->id,
            'date' => '2027-05-04',
            'price_per_night' => 999,
        ]);

        $quote = app(PricingService::class)->quote(
            $apartment,
            CarbonImmutable::parse('2027-05-01'),
            CarbonImmutable::parse('2027-05-04'),
        );

        $this->assertArrayNotHasKey('2027-05-04', $quote->nightlyBreakdown);
        $this->assertSame(300.0, $quote->subtotal);
    }

    public function test_clearing_a_rate_range_reverts_to_the_base_rate(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $apartment = Apartment::factory()->create(['price_per_night' => 100]);

        ApartmentRate::create([
            'apartment_id' => $apartment->id,
            'date' => '2027-05-02',
            'price_per_night' => 250,
        ]);

        $this->actingAs($admin)->delete(route('admin.apartments.rates.destroy', $apartment), [
            'start_date' => '2027-05-02',
            'end_date' => '2027-05-02',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $apartment->rates()->count());
    }

    public function test_setting_a_rate_only_on_selected_days_of_the_week(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        $apartment = Apartment::factory()->create(['price_per_night' => 100]);

        // Only one of the three days in range shares 2027-05-01's weekday.
        $this->actingAs($admin)->post(route('admin.apartments.rates.store', $apartment), [
            'start_date' => '2027-05-01',
            'end_date' => '2027-05-03',
            'price_per_night' => 180,
            'days_of_week' => [CarbonImmutable::parse('2027-05-01')->dayOfWeek],
        ])->assertSessionHasNoErrors();

        $dates = $apartment->rates()->pluck('date')->map(fn ($d) => $d->toDateString())->sort()->values()->all();

        $this->assertSame(['2027-05-01'], $dates);
    }
}
