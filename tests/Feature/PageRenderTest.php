<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every page renders for the role that is meant to see it, and does not render
 * for anyone else. Cheap to run, and it catches a view referencing a variable a
 * controller no longer passes.
 */
class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private User $admin;

    private Apartment $apartment;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guest = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->apartment = Apartment::factory()->create();

        $this->booking = Booking::factory()->confirmed()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        BlockedDate::create([
            'apartment_id' => $this->apartment->id,
            'date' => CarbonImmutable::today()->addDays(200)->toDateString(),
            'reason' => 'Owner stay',
        ]);

        Inquiry::create([
            'name' => 'Sam Rivers',
            'email' => 'sam@example.com',
            'subject' => 'Long stay',
            'message' => 'Do you do monthly rates?',
        ]);
    }

    public static function guestPages(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'booking history' => ['bookings.history'],
            'profile' => ['profile.edit'],
        ];
    }

    #[DataProvider('guestPages')]
    public function test_guest_pages_render(string $route): void
    {
        $this->actingAs($this->guest)->get(route($route))->assertOk();
    }

    public function test_booking_pages_render(): void
    {
        $this->actingAs($this->guest)
            ->get(route('bookings.confirmation', $this->booking))->assertOk();

        $this->actingAs($this->guest)
            ->get(route('messages.show', $this->booking))->assertOk();
    }

    public function test_the_booking_funnel_renders(): void
    {
        $this->actingAs($this->guest)->get(route('bookings.create', [
            $this->apartment,
            'check_in' => CarbonImmutable::today()->addDays(120)->toDateString(),
            'check_out' => CarbonImmutable::today()->addDays(123)->toDateString(),
            'guests' => 2,
        ]))->assertOk()->assertSee('Review your booking', escape: false);
    }

    public function test_the_review_form_renders_for_a_completed_stay(): void
    {
        $booking = Booking::factory()->past()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        $this->actingAs($this->guest)->get(route('reviews.create', $booking))->assertOk();
    }

    public function test_the_review_list_renders(): void
    {
        $booking = Booking::factory()->past()->create([
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
        ]);

        Review::create([
            'booking_id' => $booking->id,
            'user_id' => $this->guest->id,
            'apartment_id' => $this->apartment->id,
            'cleanliness' => 9, 'comfort' => 8, 'location' => 10,
            'facilities' => 7, 'staff' => 9, 'value' => 8,
            'title' => 'Lovely spot',
            'liked' => 'Spotless and quiet.',
        ]);

        $this->get(route('apartments.reviews', $this->apartment))->assertOk()->assertSee('Lovely spot');
        $this->get(route('apartments.show', $this->apartment))->assertOk();
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'bookings' => ['admin.bookings'],
            'apartments' => ['admin.apartments'],
            'new apartment' => ['admin.apartments.create'],
            'users' => ['admin.users'],
            'inquiries' => ['admin.inquiries'],
            'reports' => ['admin.reports.booked'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_render(string $route): void
    {
        $this->actingAs($this->admin)->get(route($route))->assertOk();
    }

    public function test_the_apartment_edit_page_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.apartments.edit', $this->apartment))
            ->assertOk();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_are_closed_to_guests(string $route): void
    {
        $this->actingAs($this->guest)->get(route($route))->assertForbidden();
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_are_closed_to_visitors(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }
}
