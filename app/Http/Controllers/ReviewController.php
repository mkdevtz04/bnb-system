<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * Reviews are the spine of a travel site's trust model, so the rule that makes
 * them worth anything is enforced here: you can only review a stay you actually
 * took, and only once.
 */
class ReviewController extends Controller
{
    public function create(Booking $booking)
    {
        $this->authorize('review', $booking);

        return view('reviews.create', [
            'booking' => $booking->load('apartment.images'),
            'categories' => Review::CATEGORIES,
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorize('review', $booking);

        $rules = ['title' => ['nullable', 'string', 'max:120'],
            'liked' => ['nullable', 'string', 'max:2000'],
            'disliked' => ['nullable', 'string', 'max:2000']];

        foreach (array_keys(Review::CATEGORIES) as $category) {
            $rules[$category] = ['required', 'integer', 'min:1', 'max:10'];
        }

        $validated = $request->validate($rules);

        $booking->review()->create([
            ...$validated,
            'user_id' => $booking->user_id,
            'apartment_id' => $booking->apartment_id,
        ]);

        return redirect()
            ->route('apartments.show', $booking->apartment)
            ->with('success', 'Thanks for reviewing your stay.');
    }

    /**
     * Full review list for one property, paginated away from the summary shown
     * on the property page.
     */
    public function index(Request $request, \App\Models\Apartment $apartment)
    {
        $reviews = $apartment->reviews()
            ->with('user')
            ->when($request->input('sort') === 'highest', fn ($q) => $q->orderByDesc('overall'))
            ->when($request->input('sort') === 'lowest', fn ($q) => $q->orderBy('overall'))
            ->when(! $request->input('sort'), fn ($q) => $q->latest())
            ->paginate(10)
            ->withQueryString();

        return view('reviews.index', compact('apartment', 'reviews'));
    }
}
