<?php

namespace Database\Factories;

use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Apartment>
 */
class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        $bedrooms = fake()->numberBetween(1, 4);

        return [
            'name' => fake()->streetName().' '.fake()->randomElement(['Suite', 'Residence', 'Apartment', 'Loft']),
            'floor' => fake()->randomElement(['ground', 'upper']),
            'description' => fake()->paragraphs(3, true),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'amenities' => fake()->randomElements(
                ['wifi', 'kitchen', 'air_conditioning', 'parking', 'workspace', 'washer', 'tv', 'balcony'],
                fake()->numberBetween(3, 6),
            ),
            'price_per_night' => fake()->numberBetween(60, 400),
            'cleaning_fee' => fake()->randomElement([0, 20, 35, 50]),
            'max_guests' => $bedrooms * 2,
            'bedrooms' => $bedrooms,
            'bathrooms' => fake()->numberBetween(1, $bedrooms),
            'min_nights' => 1,
            'status' => 'available',
        ];
    }

    public function underMaintenance(): static
    {
        return $this->state(fn () => ['status' => 'maintenance']);
    }
}
