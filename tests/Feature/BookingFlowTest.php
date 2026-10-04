<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->guest = User::factory()->create(['role' => 'user']);
        $this->apartment = Apartment::factory()->create([
            'price_per_night' => 100,
            'cleaning_fee' => 50,
            'max_guests' => 4,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'apartment_id' => $this->apartment->id,
            'check_in' => CarbonImmutable::today()->addDays(10)->toDateString(),
            'check_out' => CarbonImmutable::today()->addDays(13)->toDateString(),
            'guests' => 2,
        ], $overrides);
    }

    /**
     * The whole point of moving pricing server-side: what the guest was quoted
     * is what lands in the database.
     *
     * 3 nights x 100 = 300, + 50 cleaning = 350, + 5% service = 17.50,
     * + 10% tax on 367.50 = 36.75, total 404.25.
     */
    public function test_the_stored_price_is_the_servers_own_quote(): void
    {
        $this->actingAs($this->guest)->post(route('bookings.store'), $this->payload());

        $booking = Booking::firstOrFail();

        $this->assertSame(3, $booking->nights);
        $this->assertEquals(300.00, (float) $booking->subtotal);
        $this->assertEquals(67.50, (float) $booking->service_fee); // cleaning + service
        $this->assertEquals(36.75, (float) $booking->taxes);
        $this->assertEquals(404.25, (float) $booking->total_price);
    }

    public function test_the_guest_count_is_persisted(): void
    {
        $this->actingAs($this->guest)->post(route('bookings.store'), $this->payload(['guests' => 3]));

        $this->assertSame(3, Booking::firstOrFail()->guests);
    }

    /**
     * The property page collected a guest count the server threw away, so a
     * party of ten could book a place that sleeps four.
     */
    public function test_a_party_larger_than_the_property_is_rejected(): void
    {
        $response = $this->actingAs($this->guest)
            ->post(route('bookings.store'), $this->payload(['guests' => 10]));

        $response->assertSessionHasErrors('dates');
        $this->assertSame(0, Booking::count());
    }

    public function test_booking_over_a_blocked_night_is_rejected(): void
    {
        BlockedDate::create([
            'apartment_id' => $this->apartment->id,
            'date' => CarbonImmutable::today()->addDays(11)->toDateString(),
            'reason' => 'Deep clean',
        ]);

        $response = $this->actingAs($this->guest)->post(route('bookings.store'), $this->payload());

        $response->assertSessionHasErrors('dates');
        $this->assertSame(0, Booking::count());
    }

    public function test_booking_over_an_existing_stay_is_rejected(): void
    {
        Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(9)->toDateString(),
            CarbonImmutable::today()->addDays(12)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $response = $this->actingAs($this->guest)->post(route('bookings.store'), $this->payload());

        $response->assertSessionHasErrors('dates');
        $this->assertSame(1, Booking::count());
    }

    public function test_a_past_check_in_is_rejected(): void
    {
        $response = $this->actingAs($this->guest)->post(route('bookings.store'), $this->payload([
            'check_in' => CarbonImmutable::today()->subDay()->toDateString(),
        ]));

        $response->assertSessionHasErrors('check_in');
    }

    public function test_guests_cannot_book_while_signed_out(): void
    {
        $this->post(route('bookings.store'), $this->payload())->assertRedirect(route('login'));

        $this->assertSame(0, Booking::count());
    }

    public function test_a_guest_cannot_view_someone_elses_booking(): void
    {
        $booking = Booking::factory()->create(['apartment_id' => $this->apartment->id]);

        $this->actingAs($this->guest)
            ->get(route('bookings.confirmation', $booking))
            ->assertForbidden();
    }

    /**
     * The regression from the admin panel: cancel wrote the new status first and
     * then asked whether the booking had been confirmed, so the branch releasing
     * its dates could never run and the nights stayed off sale forever.
     */
    public function test_cancelling_a_confirmed_booking_puts_the_dates_back_on_sale(): void
    {
        $checkIn = CarbonImmutable::today()->addDays(10);
        $checkOut = CarbonImmutable::today()->addDays(13);

        $booking = Booking::factory()->confirmed()
            ->stay($checkIn->toDateString(), $checkOut->toDateString())
            ->create(['apartment_id' => $this->apartment->id]);

        $availability = app(AvailabilityService::class);
        $this->assertFalse($availability->isAvailable($this->apartment, $checkIn, $checkOut));

        app(BookingService::class)->cancel($booking, User::factory()->create(['role' => 'admin']));

        $this->assertTrue(
            $availability->isAvailable($this->apartment->fresh(), $checkIn, $checkOut),
            'Cancelling a confirmed booking must release its nights.',
        );
    }

    /**
     * Two guests could both hold pending bookings for the same dates, and the
     * admin panel would happily confirm both of them. Confirming one must now
     * win outright and release the other request.
     */
    public function test_confirming_a_booking_displaces_competing_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $first = Booking::factory()->stay(
            CarbonImmutable::today()->addDays(10)->toDateString(),
            CarbonImmutable::today()->addDays(13)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $second = Booking::factory()->stay(
            CarbonImmutable::today()->addDays(11)->toDateString(),
            CarbonImmutable::today()->addDays(14)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $this->actingAs($admin)->post(route('admin.bookings.confirm', $first))
            ->assertSessionHasNoErrors();

        $this->assertSame('confirmed', $first->fresh()->status);

        // The competing request is released rather than left pending against
        // dates it can never be granted.
        $this->assertSame('cancelled', $second->fresh()->status);

        // And it cannot be resurrected by confirming it afterwards.
        $this->actingAs($admin)->post(route('admin.bookings.confirm', $second))
            ->assertSessionHasErrors();

        $this->assertSame('cancelled', $second->fresh()->status);
    }

    public function test_a_confirmed_stay_blocks_confirming_an_overlapping_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(10)->toDateString(),
            CarbonImmutable::today()->addDays(13)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $request = Booking::factory()->stay(
            CarbonImmutable::today()->addDays(11)->toDateString(),
            CarbonImmutable::today()->addDays(14)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $this->actingAs($admin)->post(route('admin.bookings.confirm', $request))
            ->assertSessionHasErrors();

        $this->assertSame('pending', $request->fresh()->status);
    }

    public function test_same_day_turnover_can_be_booked_end_to_end(): void
    {
        Booking::factory()->confirmed()->stay(
            CarbonImmutable::today()->addDays(7)->toDateString(),
            CarbonImmutable::today()->addDays(10)->toDateString(),
        )->create(['apartment_id' => $this->apartment->id]);

        $this->actingAs($this->guest)
            ->post(route('bookings.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Booking::count());
    }
}
