<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display the chat/messages for a specific booking
     */
    public function show(Booking $booking)
    {
        // Ensure user is authorized to view this chat
        if (auth()->id() !== $booking->user_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $messages = $booking->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        // Mark income messages as read
        $booking->messages()->where('receiver_id', auth()->id())->update(['is_read' => true]);

        return view('messages.show', compact('booking', 'messages'));
    }

    /**
     * Store a new message
     */
    public function store(Request $request, Booking $booking)
    {
        // Ensure user is authorized to send message
        if (auth()->id() !== $booking->user_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Determine receiver
        $receiver_id = (auth()->user()->role === 'admin') ? $booking->user_id : User::where('role', 'admin')->first()->id;

        $booking->messages()->create([
            'sender_id' => auth()->id(),
            'receiver_id' => $receiver_id,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Message sent!');
    }
}
