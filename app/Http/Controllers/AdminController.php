<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $totalApartments = Apartment::count();

        return view('admin.dashboard', compact('pendingBookings', 'confirmedBookings', 'totalApartments'));
    }

    /**
     * Show all bookings
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'apartment'])->latest();

        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->paginate(20);

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * Confirm a pending booking
     */
    public function confirmBooking(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only pending bookings can be confirmed']);
        }

        $booking->update(['status' => 'confirmed']);

        // Block the dates for this apartment
        $checkIn = new \DateTime($booking->check_in);
        $checkOut = new \DateTime($booking->check_out);

        while ($checkIn < $checkOut) {
            BlockedDate::firstOrCreate([
                'apartment_id' => $booking->apartment_id,
                'date' => $checkIn->format('Y-m-d'),
            ]);
            $checkIn->modify('+1 day');
        }

        return back()->with('success', 'Booking confirmed and dates blocked');
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return back()->withErrors(['booking' => 'Booking already cancelled']);
        }

        $booking->update(['status' => 'cancelled']);

        // Remove blocked dates if booking was confirmed
        if ($booking->status === 'confirmed') {
            BlockedDate::where('apartment_id', $booking->apartment_id)
                ->whereBetween('date', [$booking->check_in, $booking->check_out])
                ->delete();
        }

        return back()->with('success', 'Booking cancelled');
    }

    /**
     * Show apartments management
     */
    public function apartments()
    {
        $apartments = Apartment::with(['images', 'bookings'])->paginate(10);

        return view('admin.apartments', compact('apartments'));
    }

    /**
     * Block specific dates for an apartment
     */
    public function blockDates(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $start = new \DateTime($validated['start_date']);
        $end = new \DateTime($validated['end_date']);

        while ($start <= $end) {
            BlockedDate::firstOrCreate([
                'apartment_id' => $apartment->id,
                'date' => $start->format('Y-m-d'),
            ]);
            $start->modify('+1 day');
        }

        return back()->with('success', 'Dates blocked successfully');
    }
}
