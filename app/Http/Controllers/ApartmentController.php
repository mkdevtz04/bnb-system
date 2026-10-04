<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchApartmentsRequest;
use App\Models\Apartment;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    /**
     * Homepage.
     */
    public function index()
    {
        $apartments = Apartment::bookable()
            ->with('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'overall')
            ->latest()
            ->limit(6)
            ->get();

        return view('welcome', compact('apartments'));
    }

    /**
     * Search results, with the filters and sorts a traveller expects.
     */
    public function search(SearchApartmentsRequest $request)
    {
        $checkIn = $request->checkIn();
        $checkOut = $request->checkOut();

        $query = Apartment::bookable()
            ->with('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'overall')
            ->sleeps($request->guests());

        // Only narrow by availability once we have a complete stay; a half-filled
        // date box should still show the full inventory rather than nothing.
        if ($checkIn && $checkOut) {
            $this->availability->scopeAvailableBetween($query, $checkIn, $checkOut);
        }

        $query->when($request->minPrice(), fn ($q, $min) => $q->where('price_per_night', '>=', $min))
            ->when($request->maxPrice(), fn ($q, $max) => $q->where('price_per_night', '<=', $max))
            ->when($request->bedrooms(), fn ($q, $beds) => $q->where('bedrooms', '>=', $beds))
            ->when($request->floor(), fn ($q, $floor) => $q->where('floor', $floor))
            ->when($request->minScore(), fn ($q, $score) => $q->having('reviews_avg_overall', '>=', $score));

        match ($request->sort()) {
            'price_asc' => $query->orderBy('price_per_night'),
            'price_desc' => $query->orderByDesc('price_per_night'),
            'score' => $query->orderByDesc('reviews_avg_overall'),
            default => $query->orderByRaw('reviews_avg_overall IS NULL')->orderByDesc('reviews_avg_overall'),
        };

        $apartments = $query->paginate(12)->withQueryString();

        // Each card shows the real total for the chosen dates, not just a nightly
        // rate the guest has to multiply out themselves.
        if ($checkIn && $checkOut) {
            $apartments->getCollection()->each(function (Apartment $apartment) use ($checkIn, $checkOut, $request) {
                $apartment->stay_quote = $this->pricing->quote(
                    $apartment,
                    $checkIn,
                    $checkOut,
                    min($request->guests() ?? 1, $apartment->max_guests),
                );
            });
        }

        return view('apartments.index', [
            'apartments' => $apartments,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'nights' => $checkIn && $checkOut ? $this->pricing->nights($checkIn, $checkOut) : null,
        ]);
    }

    public function show(Request $request, Apartment $apartment)
    {
        $apartment->load([
            'images',
            'reviews' => fn ($q) => $q->with('user')->latest()->limit(6),
        ])->loadCount('reviews')->loadAvg('reviews', 'overall');

        $checkIn = $request->date('check_in')
            ? \Carbon\CarbonImmutable::parse($request->date('check_in'))->startOfDay()
            : null;
        $checkOut = $request->date('check_out')
            ? \Carbon\CarbonImmutable::parse($request->date('check_out'))->startOfDay()
            : null;

        $quote = null;
        if ($checkIn && $checkOut && $checkOut > $checkIn) {
            $quote = $this->pricing->quote($apartment, $checkIn, $checkOut, 1);
        }

        return view('apartments.show', [
            'apartment' => $apartment,
            'unavailableDates' => $this->availability->unavailableDates($apartment),
            'categoryScores' => $this->categoryScores($apartment),
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'quote' => $quote,
        ]);
    }

    /**
     * Live price for the property page, so the sidebar stops guessing.
     */
    public function quote(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        $checkIn = \Carbon\CarbonImmutable::parse($validated['check_in'])->startOfDay();
        $checkOut = \Carbon\CarbonImmutable::parse($validated['check_out'])->startOfDay();
        $guests = min((int) ($validated['guests'] ?? 1), $apartment->max_guests);

        return response()->json([
            'available' => $this->availability->isAvailable($apartment, $checkIn, $checkOut),
            'quote' => $this->pricing->quote($apartment, $checkIn, $checkOut, $guests)->toArray(),
        ]);
    }

    public function guestDashboard(Request $request)
    {
        $user = $request->user();

        return view('dashboard', [
            'apartments' => Apartment::bookable()
                ->with('images')
                ->withCount('reviews')
                ->withAvg('reviews', 'overall')
                ->limit(6)
                ->get(),
            'upcoming' => $user->bookings()
                ->with('apartment.images')
                ->holding()
                ->whereDate('check_out', '>=', today())
                ->orderBy('check_in')
                ->limit(3)
                ->get(),
            'reviewable' => $user->bookings()
                ->with('apartment')
                ->where('status', 'confirmed')
                ->whereDate('check_out', '<', today())
                ->whereDoesntHave('review')
                ->limit(3)
                ->get(),
            'notifications' => $user->unreadNotifications()->limit(5)->get(),
        ]);
    }

    /**
     * Average of each review category, for the score breakdown bars.
     */
    private function categoryScores(Apartment $apartment): array
    {
        if ($apartment->reviews_count === 0) {
            return [];
        }

        $averages = $apartment->reviews()
            ->selectRaw(implode(', ', array_map(
                fn (string $c) => "AVG({$c}) as {$c}",
                array_keys(\App\Models\Review::CATEGORIES),
            )))
            ->first();

        return collect(\App\Models\Review::CATEGORIES)
            ->map(fn (string $label, string $key) => [
                'label' => $label,
                'score' => round((float) $averages->{$key}, 1),
            ])
            ->values()
            ->all();
    }
}
