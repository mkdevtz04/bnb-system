@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Chat Header --}}
        <div class="bg-white rounded-t-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-comments text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Chat regarding {{ $booking->apartment->name }}</h1>
                    <p class="text-xs text-gray-500 font-medium tracking-wide uppercase">Booking ID: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.bookings') : route('bookings.history') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">
                <i class="fa-solid fa-xmark mr-1"></i> Close
            </a>
        </div>

        {{-- Chat Messages Area --}}
        <div class="bg-white border-x border-gray-200 h-[500px] overflow-y-auto p-6 space-y-4 flex flex-col" id="chat-messages">
            @forelse($messages as $msg)
                <div class="flex flex-col {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                    <div class="max-w-[70%] {{ $msg->sender_id === auth()->id() ? 'bg-blue-600 text-white rounded-tl-2xl rounded-tr-2xl rounded-bl-2xl' : 'bg-gray-100 text-gray-800 rounded-tl-2xl rounded-tr-2xl rounded-br-2xl' }} p-4 shadow-sm">
                        <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 font-medium">{{ $msg->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-comment-slash text-gray-200 text-3xl"></i>
                    </div>
                    <p class="text-gray-400 font-medium">No messages yet. Start the conversation!</p>
                </div>
            @endforelse
        </div>

        {{-- Message Input --}}
        <div class="bg-gray-50 rounded-b-2xl border border-gray-200 p-4">
            <form action="{{ route('messages.store', $booking) }}" method="POST" class="flex gap-4">
                @csrf
                <input 
                    type="text" 
                    name="message" 
                    placeholder="Type your message here..." 
                    class="flex-1 bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm"
                    required
                    autocomplete="off"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition shadow-md flex items-center gap-2">
                    <span>Send</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom on load
    const messageContainer = document.getElementById('chat-messages');
    messageContainer.scrollTop = messageContainer.scrollHeight;
</script>
@endsection
