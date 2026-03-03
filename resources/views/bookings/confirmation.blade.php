@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-5xl">⏳</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Pending</h1>
                <p class="text-lg text-gray-600">Your booking request has been received</p>
            </div>

            <!-- Booking Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Booking Details</h2>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Booking ID -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Booking ID</p>
                        <p class="font-bold text-lg text-gray-900">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Status</p>
                        <p class="font-bold text-lg">
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                ⏳ Pending Confirmation
                            </span>
                        </p>
                    </div>

                    <!-- Apartment -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Apartment</p>
                        <p class="font-bold text-gray-900">{{ $booking->apartment->name }}</p>
                    </div>

                    <!-- Guest Name -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Guest Name</p>
                        <p class="font-bold text-gray-900">{{ $booking->user->name }}</p>
                    </div>

                    <!-- Check-in -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Check-in Date</p>
                        <p class="font-bold text-gray-900">{{ $booking->check_in->format('M d, Y') }}</p>
                    </div>

                    <!-- Check-out -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Check-out Date</p>
                        <p class="font-bold text-gray-900">{{ $booking->check_out->format('M d, Y') }}</p>
                    </div>

                    <!-- Nights -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Number of Nights</p>
                        <p class="font-bold text-gray-900">{{ $booking->nights }} {{ $booking->nights === 1 ? 'night' : 'nights' }}</p>
                    </div>

                    <!-- Total Price -->
                    <div>
                        <p class="text-gray-600 text-sm mb-1">Total Price</p>
                        <p class="font-bold text-2xl text-blue-600">${{ number_format($booking->total_price) }}</p>
                    </div>
                </div>
            </div>

            <!-- What Happens Next -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-4">What Happens Next?</h3>
                <ol class="space-y-3 text-gray-700">
                    <li class="flex items-start gap-3">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm">1</span>
                        <span>Our admin team will review your booking request</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm">2</span>
                        <span>You will receive a confirmation email within 24 hours</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm">3</span>
                        <span>Once confirmed, you can check in at the property</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm">4</span>
                        <span>Enjoy your stay!</span>
                    </li>
                </ol>
            </div>

            <!-- Important Info -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-3">Important Information</h3>
                <ul class="space-y-2 text-gray-700 text-sm">
                    <li>✓ Check-in time: 3:00 PM</li>
                    <li>✓ Check-out time: 11:00 AM</li>
                    <li>✓ Free cancellation up to 7 days before check-in</li>
                    <li>✓ A confirmation email has been sent to {{ $booking->user->email }}</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a 
                    href="{{ route('bookings.history') }}"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold py-3 px-6 rounded-lg transition text-center"
                >
                    View All Bookings
                </a>
                <a 
                    href="{{ route('home') }}"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition text-center"
                >
                    Continue Exploring
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
