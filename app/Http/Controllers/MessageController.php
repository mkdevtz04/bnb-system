<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Every conversation this person is part of.
     *
     * One page for both roles rather than two near-identical ones: a thread
     * belongs to a booking, and the only difference is which bookings you can
     * see. The host sees all of them, a guest sees their own.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $threads = Booking::query()
            ->unless($user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('messages')
            ->with(['apartment.images', 'user', 'latestMessage.sender'])
            ->withCount(['messages as unread_count' => fn ($q) => $q
                ->where('receiver_id', $user->id)
                ->where('is_read', false),
            ])
            // Newest conversation first, by its last message rather than by the
            // booking's own timestamps — a thread is "recent" when someone last
            // said something in it.
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('messages.booking_id', 'bookings.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(15);

        return view('messages.index', compact('threads', 'user'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('message', $booking);

        $messages = $booking->messages()->with('sender')->oldest()->get();

        $booking->messages()
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('messages.show', compact('booking', 'messages'));
    }

    public function store(Request $request, Booking $booking)
    {
        $this->authorize('message', $booking);

        $validated = $request->validate([
            // Newlines are allowed and preserved: the composer sends on Enter and
            // adds a line on Shift+Enter, so a message can legitimately be several
            // paragraphs.
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $receiverId = $this->receiverFor($booking);

        if ($receiverId === null) {
            return back()->withErrors([
                'message' => 'There is no host account to receive this message yet.',
            ]);
        }

        $booking->messages()->create([
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'message' => $this->normalise($validated['message']),
        ]);

        return back()->withFragment('latest');
    }

    /**
     * A guest writes to the host, the host writes back to the guest.
     *
     * This used to dereference User::where('role','admin')->first()->id with no
     * null check, so the chat fatally errored on any install without an admin.
     */
    private function receiverFor(Booking $booking): ?int
    {
        if (auth()->user()->isAdmin()) {
            return $booking->user_id;
        }

        return User::where('role', 'admin')->value('id');
    }

    /**
     * Tidy the whitespace a multi-line composer tends to collect.
     *
     * Line endings are normalised so the same message does not render differently
     * depending on the sender's browser, and a run of blank lines is capped so a
     * stray Enter cannot push the rest of the thread off screen.
     */
    private function normalise(string $message): string
    {
        $message = str_replace(["\r\n", "\r"], "\n", $message);
        $message = preg_replace("/\n{3,}/", "\n\n", $message);

        return trim($message);
    }
}
