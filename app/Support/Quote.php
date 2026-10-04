<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An immutable price breakdown for one stay.
 *
 * Passed to the views so the summary the guest reads is literally the numbers
 * that will be written to the booking row.
 */
class Quote implements Arrayable
{
    public function __construct(
        public readonly int $nights,
        public readonly int $guests,
        public readonly float $nightlyRate,
        public readonly float $subtotal,
        public readonly float $cleaningFee,
        public readonly float $serviceFee,
        public readonly float $taxes,
        public readonly float $total,
        public readonly string $currency,
    ) {}

    /**
     * The columns this quote contributes to a bookings row.
     */
    public function toBookingAttributes(): array
    {
        return [
            'nights' => $this->nights,
            'guests' => $this->guests,
            'subtotal' => $this->subtotal,
            'service_fee' => $this->serviceFee + $this->cleaningFee,
            'taxes' => $this->taxes,
            'total_price' => $this->total,
            'currency' => $this->currency,
        ];
    }

    public function toArray(): array
    {
        return [
            'nights' => $this->nights,
            'guests' => $this->guests,
            'nightly_rate' => $this->nightlyRate,
            'subtotal' => $this->subtotal,
            'cleaning_fee' => $this->cleaningFee,
            'service_fee' => $this->serviceFee,
            'taxes' => $this->taxes,
            'total' => $this->total,
            'currency' => $this->currency,
        ];
    }
}
