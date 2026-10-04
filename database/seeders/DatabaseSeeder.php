<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Enough realistic data to exercise every screen: properties with reviews,
 * bookings in each status, a manual calendar closure and a contact message.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Amara Host',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+255 700 000 001',
                'email_verified_at' => now(),
            ],
        );

        $guest = User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'Jordan Kite',
                'password' => bcrypt('password'),
                'role' => 'user',
                'phone' => '+255 700 000 002',
                'email_verified_at' => now(),
            ],
        );

        $otherGuests = User::factory()->count(6)->create(['role' => 'user']);

        $properties = collect([
            [
                'name' => 'Seabreeze Garden Suite',
                'floor' => 'ground',
                'price_per_night' => 120,
                'cleaning_fee' => 35,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'max_guests' => 4,
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'amenities' => ['wifi', 'kitchen', 'air_conditioning', 'parking', 'workspace'],
                'description' => "A calm two-bedroom on the ground floor, opening straight onto a walled garden.\n\nThe kitchen is fully equipped, the wifi is fast enough to work on, and the beach is a six-minute walk through quiet residential streets.",
            ],
            [
                'name' => 'Skyline Loft',
                'floor' => 'upper',
                'price_per_night' => 185,
                'cleaning_fee' => 45,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'max_guests' => 6,
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'amenities' => ['wifi', 'kitchen', 'air_conditioning', 'balcony', 'tv', 'washer'],
                'description' => "Top-floor loft with a wraparound balcony and long views over the bay.\n\nThree bedrooms, two bathrooms, and a living space built around a very large sofa. Best at sunset.",
            ],
            [
                'name' => 'Harbour Studio',
                'floor' => 'ground',
                'price_per_night' => 75,
                'cleaning_fee' => 20,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'max_guests' => 2,
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'amenities' => ['wifi', 'kitchen', 'workspace', 'tv'],
                'description' => "A compact studio for one or two, a block back from the harbour.\n\nSimple, spotless and quiet, with a proper desk and a good chair for anyone working.",
            ],
        ])->map(fn (array $attributes) => Apartment::updateOrCreate(
            ['name' => $attributes['name']],
            $attributes + ['status' => 'available', 'min_nights' => 1],
        ));

        $pricing = app(PricingService::class);

        // Past stays, so there is something to review.
        foreach ($properties as $index => $property) {
            foreach (range(1, 3) as $n) {
                $checkIn = CarbonImmutable::today()->subDays(($n * 21) + ($index * 4));
                $checkOut = $checkIn->addDays(rand(2, 5));
                $reviewer = $otherGuests->random();
                $quote = $pricing->quote($property, $checkIn, $checkOut, rand(1, $property->max_guests));

                $booking = Booking::create([
                    'reference' => 'CC'.strtoupper(\Illuminate\Support\Str::random(8)),
                    'user_id' => $reviewer->id,
                    'apartment_id' => $property->id,
                    'check_in' => $checkIn->toDateString(),
                    'check_out' => $checkOut->toDateString(),
                    'status' => 'confirmed',
                    'confirmed_at' => $checkIn->subDays(5),
                    ...$quote->toBookingAttributes(),
                ]);

                Review::create([
                    'booking_id' => $booking->id,
                    'user_id' => $reviewer->id,
                    'apartment_id' => $property->id,
                    'cleanliness' => rand(7, 10),
                    'comfort' => rand(7, 10),
                    'location' => rand(7, 10),
                    'facilities' => rand(6, 10),
                    'staff' => rand(8, 10),
                    'value' => rand(6, 10),
                    'title' => collect([
                        'Exactly as described', 'Would stay again', 'Great base for the week',
                        'Quiet and comfortable', 'Excellent value',
                    ])->random(),
                    'liked' => collect([
                        'Spotlessly clean and the host replied within minutes.',
                        'The location is hard to beat — everything within walking distance.',
                        'Comfortable bed, strong wifi, and very quiet at night.',
                    ])->random(),
                    'disliked' => rand(0, 1) ? collect([
                        'The kettle was a bit temperamental.',
                        'Street noise in the early morning, but nothing serious.',
                    ])->random() : null,
                ]);
            }
        }

        // A live booking for the demo guest, plus one request awaiting the host.
        $upcoming = CarbonImmutable::today()->addDays(14);
        $quote = $pricing->quote($properties[0], $upcoming, $upcoming->addDays(4), 2);

        Booking::updateOrCreate(
            ['user_id' => $guest->id, 'apartment_id' => $properties[0]->id, 'check_in' => $upcoming->toDateString()],
            [
                'reference' => 'CCDEMO001',
                'check_out' => $upcoming->addDays(4)->toDateString(),
                'status' => 'confirmed',
                'confirmed_at' => now(),
                ...$quote->toBookingAttributes(),
            ],
        );

        $pending = CarbonImmutable::today()->addDays(40);
        $pendingQuote = $pricing->quote($properties[1], $pending, $pending->addDays(3), 4);

        Booking::updateOrCreate(
            ['user_id' => $guest->id, 'apartment_id' => $properties[1]->id, 'check_in' => $pending->toDateString()],
            [
                'reference' => 'CCDEMO002',
                'check_out' => $pending->addDays(3)->toDateString(),
                'status' => 'pending',
                ...$pendingQuote->toBookingAttributes(),
            ],
        );

        // A manual closure — the only thing blocked_dates now holds.
        $maintenance = CarbonImmutable::today()->addDays(70);
        foreach (range(0, 2) as $offset) {
            BlockedDate::updateOrCreate(
                ['apartment_id' => $properties[2]->id, 'date' => $maintenance->addDays($offset)->toDateString()],
                ['reason' => 'Annual maintenance'],
            );
        }

        \App\Models\Inquiry::updateOrCreate(
            ['email' => 'priya@example.com'],
            [
                'name' => 'Priya Sharma',
                'subject' => 'Monthly rate for the loft?',
                'message' => "Hi — we're relocating for work in March and need somewhere for six weeks. Do you offer a monthly rate on the Skyline Loft?",
                'is_read' => false,
            ],
        );

        $this->command?->info('Seeded. Sign in as admin@admin.com or user@user.com, password: password');
    }
}
