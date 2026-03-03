<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    /**
     * Display a listing of apartments on homepage
     */
    public function index()
    {
        $apartments = Apartment::where('status', 'available')
            ->with('images')
            ->limit(6)
            ->get();

        return view('welcome', compact('apartments'));
    }

    /**
     * Show the apartment listing page with search
     */
    public function search(Request $request)
    {
        $query = Apartment::where('status', 'available')->with('images');

        if ($request->has('check_in') && $request->has('check_out')) {
            // Filter apartments based on availability
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', ['confirmed', 'pending'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in', '<=', $checkIn)
                                    ->where('check_out', '>=', $checkOut);
                            });
                    });
            });
        }

        if ($request->has('guests') && $request->input('guests')) {
            $guests = (int) $request->input('guests');
            $query->where('max_guests', '>=', $guests);
        }

        $apartments = $query->paginate(12);

        return view('apartments.index', compact('apartments'));
    }

    /**
     * Display the specified apartment
     */
    public function show(Apartment $apartment)
    {
        $apartment->load('images', 'bookings');

        return view('apartments.show', compact('apartment'));
    }
}
