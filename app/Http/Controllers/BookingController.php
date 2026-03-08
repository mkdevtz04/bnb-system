<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\NewBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    /**
     * Show the form for creating a new booking
     */
    public function create(Apartment $apartment)
    {
        $this->authorize('create', Booking::class);

        return view('bookings.create', compact('apartment'));
    }

    /**
     * Store a newly created booking in database
     */
    public function store(Request $request)
    {
        $this->authorize('create', Booking::class);

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $apartment = Apartment::findOrFail($validated['apartment_id']);

        // Check if dates are already booked
        $existingBooking = Booking::where('apartment_id', $apartment->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('check_in', [$request->check_in, $request->check_out])
                    ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('check_in', '<=', $request->check_in)
                            ->where('check_out', '>=', $request->check_out);
                    });
            })->exists();

        if ($existingBooking) {
            return back()->withErrors(['booking' => 'These dates are not available']);
        }

        // Calculate nights and total price
        $checkIn = new \DateTime($validated['check_in']);
        $checkOut = new \DateTime($validated['check_out']);
        $nights = $checkIn->diff($checkOut)->days;
        $totalPrice = $nights * $apartment->price_per_night;

        // Create booking
        $booking = auth()->user()->bookings()->create([
            'apartment_id' => $apartment->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'nights' => $nights,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        // Notify Admin
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewBookingNotification($booking));

        return redirect()->route('bookings.confirmation', $booking)
            ->with('success', 'Booking created! Your dates are now reserved pending admin confirmation.');
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation(Booking $booking)
    {
        $this->authorize('view', $booking);

        return view('bookings.confirmation', compact('booking'));
    }

    /**
     * Display user's booking history
     */
    public function history()
    {
        $bookings = auth()->user()->bookings()
            ->with('apartment')
            ->latest()
            ->paginate(10);

        return view('bookings.history', compact('bookings'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking)
    {
        $this->authorize('delete', $booking);

        if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
            return back()->withErrors(['booking' => 'Cannot cancel this booking']);
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully');
    }
}
