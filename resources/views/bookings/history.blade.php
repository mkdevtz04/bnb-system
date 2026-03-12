@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">My Bookings</h1>

        @if($bookings->count() > 0)
            <div class="space-y-4">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <!-- Apartment Image -->
                            <div class="w-full md:w-48 h-48 bg-gray-100 flex-shrink-0">
                                @if($booking->apartment->images->count() > 0)
                                    <img src="{{ \Storage::url($booking->apartment->images->first()->image_path) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-blue-50">
                                        <i class="fa-solid fa-hotel text-blue-200 text-4xl"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Booking Info -->
                            <div class="flex-1 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">{{ $booking->apartment->name }}</h3>
                                        <p class="text-gray-600">Booking ID: #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div>
                                        @if($booking->status === 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-semibold">
                                                ⏳ Pending
                                            </span>
                                        @elseif($booking->status === 'confirmed')
                                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                                                ✓ Confirmed
                                            </span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="bg-red-100 text-red-800 px-4 py-2 rounded-full font-semibold">
                                                ✕ Cancelled
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Details Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <p class="text-gray-600 text-sm">Check-in</p>
                                        <p class="font-bold text-gray-900">{{ $booking->check_in->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-sm">Check-out</p>
                                        <p class="font-bold text-gray-900">{{ $booking->check_out->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-sm">Duration</p>
                                        <p class="font-bold text-gray-900">{{ $booking->nights }} {{ $booking->nights === 1 ? 'night' : 'nights' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 text-sm">Total Price</p>
                                        <p class="font-bold text-lg text-blue-600">${{ number_format($booking->total_price) }}</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3">
                                    <a 
                                        href="{{ route('apartments.show', $booking->apartment->id) }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                                    >
                                        View Apartment
                                    </a>

                                    @if($booking->status !== 'cancelled')
                                        <a 
                                            href="{{ route('messages.show', $booking) }}"
                                            class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2"
                                        >
                                            <i class="fa-solid fa-comments"></i>
                                            Chat with Host
                                        </a>
                                    @endif

                                    @if($booking->status === 'pending' || $booking->status === 'confirmed')
                                        <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                onclick="return confirm('Are you sure you want to cancel this booking?')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                                            >
                                                Cancel Booking
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($bookings->hasPages())
                <div class="mt-8">
                    {{ $bookings->links() }}
                </div>
            @endif
        @else
            <div class="bg-gray-50 rounded-lg p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">You haven't made any bookings yet</p>
                <a 
                    href="{{ route('home') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition"
                >
                    Start Exploring Apartments
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
