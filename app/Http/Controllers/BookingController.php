<?php

namespace App\Http\Controllers;

use App\Exceptions\DatesUnavailableException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Apartment;
use App\Models\Booking;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly PricingService $pricing,
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * Step one of the funnel: review the stay and the price before committing.
     */
    public function create(Request $request, Apartment $apartment)
    {
        $this->authorize('create', Booking::class);

        $validated = $request->validate([
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        $checkIn = CarbonImmutable::parse($validated['check_in'])->startOfDay();
        $checkOut = CarbonImmutable::parse($validated['check_out'])->startOfDay();
        $guests = min((int) ($validated['guests'] ?? 1), $apartment->max_guests);

        if (! $this->availability->isAvailable($apartment, $checkIn, $checkOut)) {
            return redirect()
                ->route('apartments.show', $apartment)
                ->withErrors(['dates' => 'Those dates are no longer available.']);
        }

        // The summary the guest reads is the server's own quote, so the figure on
        // screen is the figure that gets stored.
        $quote = $this->pricing->quote($apartment, $checkIn, $checkOut, $guests);

        return view('bookings.create', [
            'apartment' => $apartment->load('images'),
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'guests' => $guests,
            'quote' => $quote,
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $apartment = Apartment::findOrFail($request->validated('apartment_id'));

        try {
            $booking = $this->bookings->create(
                user: $request->user(),
                apartment: $apartment,
                checkIn: $request->checkIn(),
                checkOut: $request->checkOut(),
                guests: $request->guests(),
            );
        } catch (DatesUnavailableException $e) {
            return back()->withInput()->withErrors(['dates' => $e->getMessage()]);
        }

        return redirect()
            ->route('bookings.confirmation', $booking)
            ->with('success', 'Your stay is reserved. We will confirm it shortly.');
    }

    public function confirmation(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('bookings.confirmation', [
            'booking' => $booking->load('apartment.images', 'user'),
        ]);
    }

    public function history(Request $request)
    {
        $filter = $request->string('filter')->toString();

        $bookings = $request->user()->bookings()
            ->with(['apartment.images', 'review'])
            ->when($filter === 'upcoming', fn ($q) => $q->holding()->whereDate('check_in', '>=', today()))
            ->when($filter === 'past', fn ($q) => $q->where('check_out', '<', today()))
            ->when($filter === 'cancelled', fn ($q) => $q->where('status', 'cancelled'))
            ->latest('check_in')
            ->paginate(10)
            ->withQueryString();

        return view('bookings.history', compact('bookings', 'filter'));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('delete', $booking);

        if (! $booking->canBeCancelled()) {
            return back()->withErrors([
                'booking' => 'This booking can no longer be cancelled.',
            ]);
        }

        $this->bookings->cancel($booking, auth()->user());

        return back()->with('success', 'Booking cancelled. Those dates are back on sale.');
    }
}
