<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Notifications\BookingConfirmedNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Show all users for management
     */
    public function users(Request $request)
    {
        $query = User::whereHas('bookings')->withCount('bookings')->latest();

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show booked report (Booked Users & Apartments)
     */
    public function bookedReport()
    {
        // Get unique users who have made a booking
        $bookedUsers = User::whereHas('bookings')->withCount('bookings')->latest()->get();
        
        // Get unique apartments that have been booked
        $bookedApartments = Apartment::whereHas('bookings')->withCount('bookings')->latest()->get();

        return view('admin.reports.booked', compact('bookedUsers', 'bookedApartments'));
    }


    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $totalApartments = Apartment::count();
        $recentActivity = Booking::with(['user', 'apartment'])->latest()->take(5)->get();
        $notifications = auth()->user()->unreadNotifications()->take(5)->get();
        $inquiryCount = Inquiry::count();

        return view('admin.dashboard', compact(
            'pendingBookings', 
            'confirmedBookings', 
            'totalApartments', 
            'recentActivity', 
            'notifications',
            'inquiryCount'
        ));
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

        // Notify guest
        $booking->user->notify(new BookingConfirmedNotification($booking));

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

    /**
     * Show the form for creating a new apartment
     */
    public function createApartment()
    {
        return view('admin.apartments.create');
    }

    /**
     * Store a newly created apartment
     */
    public function storeApartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'floor' => 'required|in:ground,upper',
            'description' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'status' => 'required|in:available,maintenance',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $apartment = Apartment::create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create([
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('admin.apartments')->with('success', 'Apartment created successfully!');
    }

    /**
     * Show the form for editing the specified apartment
     */
    public function editApartment(Apartment $apartment)
    {
        return view('admin.apartments.edit', compact('apartment'));
    }

    /**
     * Update the specified apartment
     */
    public function updateApartment(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'floor' => 'required|in:ground,upper',
            'description' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'status' => 'required|in:available,maintenance',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $apartment->update($validated);

        // Handle additional image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');
                $apartment->images()->create([
                    'image_path' => $path
                ]);
            }
        }

        return redirect()->route('admin.apartments')->with('success', 'Apartment updated successfully!');
    }

    /**
     * Remove the specified apartment from storage
     */
    public function destroyApartment(Apartment $apartment)
    {
        // Delete all images from storage
        foreach ($apartment->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }
        
        $apartment->delete(); // This should cascade if foreign keys are set, otherwise bookings/images will be orphaned.

        return redirect()->route('admin.apartments')->with('success', 'Apartment deleted successfully!');
    }

    /**
     * Delete a specific image
     */
    public function destroyImage(ApartmentImage $image)
    {
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        $image->delete();

        return back()->with('success', 'Image removed successfully!');
    }

    /**
     * Toggle the status of an apartment (available vs maintenance)
     */
    public function toggleStatus(Apartment $apartment)
    {
        $newStatus = $apartment->status === 'available' ? 'maintenance' : 'available';
        $apartment->update(['status' => $newStatus]);

        $statusMessage = $newStatus === 'available' ? 'available for booking' : 'put into maintenance mode (hidden from guests)';
        
        return back()->with('success', "Apartment #{$apartment->id} has been {$statusMessage}.");
    }
}
