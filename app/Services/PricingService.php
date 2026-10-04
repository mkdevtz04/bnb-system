<?php

namespace App\Services;

use App\Models\Apartment;
use App\Support\Quote;
use Carbon\CarbonImmutable;

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

        $nightlyRate = (float) $apartment->price_per_night;
        $subtotal = $this->round($nightlyRate * $nights);
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
        );
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
