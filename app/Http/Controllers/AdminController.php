<?php

namespace App\Http\Controllers;

use App\Exceptions\DatesUnavailableException;
use App\Mail\InquiryReplyMail;
use App\Models\Apartment;
use App\Models\ApartmentImage;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Review;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function dashboard()
    {
        $revenue = Booking::where('status', 'confirmed')->sum('total_price');

        return view('admin.dashboard', [
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'confirmedBookings' => Booking::where('status', 'confirmed')->count(),
            'totalApartments' => Apartment::count(),
            'occupiedTonight' => Booking::where('status', 'confirmed')
                ->whereDate('check_in', '<=', today())
                ->whereDate('check_out', '>', today())
                ->count(),
            'revenue' => $revenue,
            'averageScore' => round((float) Review::avg('overall'), 1),
            'unreadInquiries' => Inquiry::unread()->count(),
            'recentActivity' => Booking::with(['user', 'apartment'])->latest()->take(6)->get(),
            'notifications' => auth()->user()->unreadNotifications()->take(5)->get(),
        ]);
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::with(['user', 'apartment'])
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('q'), function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('reference', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }

    public function confirmBooking(Booking $booking)
    {
        try {
            $this->bookings->confirm($booking);
        } catch (DatesUnavailableException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return back()->with('success', "Booking {$booking->display_reference} confirmed.");
    }

    public function cancelBooking(Booking $booking)
    {
        if ($booking->isCancelled()) {
            return back()->withErrors(['booking' => 'That booking is already cancelled.']);
        }

        $this->bookings->cancel($booking, auth()->user());

        return back()->with('success', "Booking {$booking->display_reference} cancelled and the dates released.");
    }

    public function users(Request $request)
    {
        $users = User::withCount('bookings')
            ->withSum('bookings', 'total_price')
            ->when($request->input('q'), function ($query, $term) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->boolean('guests_only'), fn ($q) => $q->whereHas('bookings'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function bookedReport()
    {
        return view('admin.reports.booked', [
            'bookedUsers' => User::whereHas('bookings')
                ->withCount('bookings')
                ->withSum('bookings', 'total_price')
                ->orderByDesc('bookings_count')
                ->get(),
            'bookedApartments' => Apartment::whereHas('bookings')
                ->withCount('bookings')
                ->withSum('bookings', 'total_price')
                ->withAvg('reviews', 'overall')
                ->orderByDesc('bookings_count')
                ->get(),
            'monthly' => Booking::selectRaw($this->monthExpression().' as period, COUNT(*) as bookings, SUM(total_price) as revenue')
                ->where('status', 'confirmed')
                ->groupBy('period')
                ->orderByDesc('period')
                ->limit(12)
                ->get(),
        ]);
    }

    /**
     * Guest messages from the public contact form. The dashboard has shown a
     * count of these since the feature was half-built; this is the screen it was
     * counting towards.
     */
    public function inquiries(Request $request)
    {
        $inquiries = Inquiry::with('repliedBy')
            ->when($request->boolean('unread'), fn ($q) => $q->unread())
            ->when($request->boolean('awaiting'), fn ($q) => $q->awaitingReply())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inquiries.index', compact('inquiries'));
    }

    public function markInquiryRead(Inquiry $inquiry)
    {
        $inquiry->markAsRead();

        return back()->with('success', 'Marked as read.');
    }

    /**
     * Answer a contact-form message from inside the app.
     *
     * This replaces a mailto: link, which only ever worked on a machine with a
     * desktop mail client configured as the default handler — so for anyone using
     * webmail, clicking Reply did precisely nothing. Sending it here also means
     * the reply is recorded against the enquiry instead of living only in whoever
     * happened to send it from their personal inbox.
     */
    public function replyToInquiry(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $body = trim(preg_replace("/\n{3,}/", "\n\n", str_replace(["\r\n", "\r"], "\n", $validated['reply'])));

        try {
            Mail::to($inquiry->email, $inquiry->name)->send(new InquiryReplyMail($inquiry, $body));
        } catch (\Throwable $e) {
            report($e);

            // The reply is not recorded, because it was not sent. Marking it
            // answered here would hide an unanswered guest behind a green tick.
            return back()->withInput()->withErrors([
                'reply' => 'That could not be sent: '.$e->getMessage(),
            ]);
        }

        $inquiry->forceFill([
            'reply' => $body,
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
            'is_read' => true,
        ])->save();

        return back()->with('success', "Your reply has been sent to {$inquiry->email}.");
    }

    public function apartments()
    {
        $apartments = Apartment::with('images')
            ->withCount(['bookings', 'reviews'])
            ->withAvg('reviews', 'overall')
            ->paginate(10);

        return view('admin.apartments', compact('apartments'));
    }

    public function createApartment()
    {
        return view('admin.apartments.create', ['apartment' => new Apartment]);
    }

    public function storeApartment(Request $request)
    {
        $apartment = Apartment::create($this->validateApartment($request));

        $this->storeImages($request, $apartment);

        return redirect()->route('admin.apartments')
            ->with('success', "{$apartment->name} is live.");
    }

    public function editApartment(Apartment $apartment)
    {
        return view('admin.apartments.edit', [
            'apartment' => $apartment->load('images', 'blockedDates'),
        ]);
    }

    public function updateApartment(Request $request, Apartment $apartment)
    {
        $apartment->update($this->validateApartment($request));

        $this->storeImages($request, $apartment);

        return redirect()->route('admin.apartments')
            ->with('success', "{$apartment->name} updated.");
    }

    public function destroyApartment(Apartment $apartment)
    {
        // Refuse to delete a property people are still travelling to; the FK
        // cascade would take their bookings with it.
        $liveBookings = $apartment->bookings()
            ->holding()
            ->whereDate('check_out', '>=', today())
            ->count();

        if ($liveBookings > 0) {
            return back()->withErrors([
                'apartment' => "{$apartment->name} has {$liveBookings} upcoming booking(s). Cancel them first, or put the property into maintenance to hide it.",
            ]);
        }

        DB::transaction(function () use ($apartment) {
            foreach ($apartment->images as $image) {
                $this->deleteImageFile($image);
            }

            $apartment->delete();
        });

        return redirect()->route('admin.apartments')->with('success', 'Property deleted.');
    }

    public function destroyImage(ApartmentImage $image)
    {
        $this->deleteImageFile($image);
        $image->delete();

        return back()->with('success', 'Image removed.');
    }

    public function toggleStatus(Apartment $apartment)
    {
        $status = $apartment->status === 'available' ? 'maintenance' : 'available';
        $apartment->update(['status' => $status]);

        return back()->with('success', $status === 'available'
            ? "{$apartment->name} is back on sale."
            : "{$apartment->name} is hidden from guests.");
    }

    public function blockDates(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $start = CarbonImmutable::parse($validated['start_date']);
        $end = CarbonImmutable::parse($validated['end_date']);

        $rows = [];
        for ($date = $start; $date <= $end; $date = $date->addDay()) {
            $rows[] = [
                'apartment_id' => $apartment->id,
                'date' => $date->toDateString(),
                'reason' => $validated['reason'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // upsert against the (apartment_id, date) unique index, so re-blocking an
        // already-closed night just refreshes the reason instead of failing.
        BlockedDate::upsert($rows, ['apartment_id', 'date'], ['reason', 'updated_at']);

        return back()->with('success', count($rows).' night(s) closed.');
    }

    public function unblockDate(BlockedDate $blockedDate)
    {
        $blockedDate->delete();

        return back()->with('success', 'Night reopened.');
    }

    /**
     * Month bucket for the revenue report.
     *
     * Tests run on SQLite and production runs on MySQL, and the two spell date
     * formatting differently, so the expression is chosen per driver rather than
     * hard-coded to whichever engine happened to be in front of us.
     */
    private function monthExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', check_in)",
            'pgsql' => "to_char(check_in, 'YYYY-MM')",
            default => "DATE_FORMAT(check_in, '%Y-%m')",
        };
    }

    private function validateApartment(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'floor' => ['required', 'in:ground,upper'],
            'description' => ['required', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:60'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:30'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'min_nights' => ['nullable', 'integer', 'min:1', 'max:30'],
            'status' => ['required', 'in:available,maintenance'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);
    }

    private function storeImages(Request $request, Apartment $apartment): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $image) {
            $apartment->images()->create([
                'image_path' => $image->store('apartments', $this->disk()),
            ]);
        }
    }

    private function deleteImageFile(ApartmentImage $image): void
    {
        $disk = Storage::disk($this->disk());

        if ($disk->exists($image->image_path)) {
            $disk->delete($image->image_path);
        }
    }

    /**
     * Images must be written to a disk that can serve them publicly. The default
     * "local" disk roots at storage/app/private, which Storage::url() cannot
     * reach, so uploads made under the shipped .env.example rendered as broken
     * images.
     */
    private function disk(): string
    {
        $disk = config('filesystems.default');

        return $disk === 'local' ? 'public' : $disk;
    }
}
