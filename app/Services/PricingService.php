<?php

namespace App\Services;

use App\Models\Apartment;
use App\Support\Quote;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

/**
 * Works out what a stay costs.
 *
 * The browser used to compute the price three different ways — the booking form
 * added a 5% fee and 10% tax, the property page showed a hard-coded $0 fee, and
 * the server stored plain nights x rate. Whatever the guest agreed to, they were
 * charged something else. Every figure now comes from here, and the client only
 * ever renders a quote it was handed.
 */
class PricingService
{
    public function quote(Apartment $apartment, CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $guests = 1): Quote
    {
        $nights = $this->nights($checkIn, $checkOut);

        $breakdown = $this->nightlyBreakdown($apartment, $checkIn, $checkOut);
        $subtotal = $this->round(array_sum($breakdown));

        // The average of what was actually charged, not a second, independent
        // read of the base rate — so this still equals price_per_night exactly
        // for the common case of a property with no date overrides at all.
        $nightlyRate = $nights > 0 ? $this->round($subtotal / $nights) : (float) $apartment->price_per_night;

        $cleaningFee = $this->round((float) $apartment->cleaning_fee);

        $serviceFee = $this->round(($subtotal + $cleaningFee) * $this->serviceFeeRate());
        $taxes = $this->round(($subtotal + $cleaningFee + $serviceFee) * $this->taxRate());

        return new Quote(
            nights: $nights,
            guests: $guests,
            nightlyRate: $nightlyRate,
            subtotal: $subtotal,
            cleaningFee: $cleaningFee,
            serviceFee: $serviceFee,
            taxes: $taxes,
            total: $this->round($subtotal + $cleaningFee + $serviceFee + $taxes),
            currency: $this->currency(),
            nightlyBreakdown: $breakdown,
        );
    }

    /**
     * The price charged for each individual night of the stay, keyed by
     * Y-m-d. A date with a matching apartment_rates row costs that override;
     * every other date falls back to the apartment's own price_per_night, so
     * a property that has never had an override priced returns exactly what
     * it always did — nights identical copies of the base rate.
     *
     * @return array<string, float>
     */
    public function nightlyBreakdown(Apartment $apartment, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        $baseRate = (float) $apartment->price_per_night;

        // Half-open: the last night of the stay is checkOut minus one day, the
        // same convention AvailabilityService::unavailableDates() uses for the
        // nights a booking actually occupies.
        $lastNight = $checkOut->subDay();

        // One query for the whole stay rather than one per night.
        $overrides = $apartment->rates()
            ->whereBetween('date', [$checkIn->toDateString(), $lastNight->toDateString()])
            ->pluck('price_per_night', 'date')
            ->map(fn ($price) => (float) $price)
            ->all();

        $breakdown = [];
        foreach (CarbonPeriod::create($checkIn, $lastNight) as $night) {
            $key = $night->toDateString();
            $breakdown[$key] = $overrides[$key] ?? $baseRate;
        }

        return $breakdown;
    }

    /**
     * Nights in a half-open stay: arriving the 1st and leaving the 4th is 3 nights.
     */
    public function nights(CarbonImmutable $checkIn, CarbonImmutable $checkOut): int
    {
        return (int) $checkIn->startOfDay()->diffInDays($checkOut->startOfDay());
    }

    public function serviceFeeRate(): float
    {
        return (float) config('booking.service_fee_rate', 0.05);
    }

    public function taxRate(): float
    {
        return (float) config('booking.tax_rate', 0.10);
    }

    public function currency(): string
    {
        return (string) config('booking.currency', 'USD');
    }

    private function round(float $amount): float
    {
        return round($amount, 2);
    }
}
