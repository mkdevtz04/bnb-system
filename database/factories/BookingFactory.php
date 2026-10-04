<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn = CarbonImmutable::today()->addDays(fake()->numberBetween(1, 60));
        $nights = fake()->numberBetween(1, 7);
        $rate = fake()->numberBetween(60, 400);
        $subtotal = $rate * $nights;
        $serviceFee = round($subtotal * 0.05, 2);
        $taxes = round(($subtotal + $serviceFee) * 0.10, 2);

        return [
            'reference' => 'CC'.strtoupper(Str::random(8)),
            'user_id' => User::factory(),
            'apartment_id' => Apartment::factory(),
            'guests' => fake()->numberBetween(1, 4),
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkIn->addDays($nights)->toDateString(),
            'nights' => $nights,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'taxes' => $taxes,
            'total_price' => $subtotal + $serviceFee + $taxes,
            'currency' => 'USD',
            'status' => 'pending',
        ];
    }

    /**
     * Pin the stay to exact dates, which is what availability tests care about.
     */
    public function stay(string $checkIn, string $checkOut): static
    {
        return $this->state(function () use ($checkIn, $checkOut) {
            $in = CarbonImmutable::parse($checkIn);
            $out = CarbonImmutable::parse($checkOut);

            return [
                'check_in' => $in->toDateString(),
                'check_out' => $out->toDateString(),
                'nights' => (int) $in->diffInDays($out),
            ];
        });
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'check_in' => CarbonImmutable::today()->subDays(10)->toDateString(),
            'check_out' => CarbonImmutable::today()->subDays(7)->toDateString(),
            'nights' => 3,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }
}
