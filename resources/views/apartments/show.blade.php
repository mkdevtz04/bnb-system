@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Main Image Section -->
        <div class="mb-8">
            @if($apartment->images->count() > 0)
                <img 
                    src="{{ asset('storage/' . $apartment->images->first()->image_path) }}" 
                    alt="{{ $apartment->name }}"
                    class="w-full h-96 object-cover rounded-lg shadow-lg"
                >
            @else
                <div class="w-full h-96 bg-gray-300 rounded-lg flex items-center justify-center text-gray-500">
                    No Image Available
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Apartment Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900">{{ $apartment->name }}</h1>
                            <p class="text-gray-600 mt-2">Floor: 
                                <span class="font-semibold capitalize">{{ $apartment->floor }}</span>
                            </p>
                        </div>
                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                            {{ Str::upper($apartment->status) }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6 pb-6 border-b">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">About</h2>
                        <p class="text-gray-600 leading-relaxed">{{ $apartment->description }}</p>
                    </div>

                    <!-- Amenities -->
                    <div class="mb-6 pb-6 border-b">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Amenities</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">🛏️</span>
                                <div>
                                    <p class="text-gray-600">Bedrooms</p>
                                    <p class="font-bold text-lg">{{ $apartment->bedrooms }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">🚿</span>
                                <div>
                                    <p class="text-gray-600">Bathrooms</p>
                                    <p class="font-bold text-lg">{{ $apartment->bathrooms }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">👥</span>
                                <div>
                                    <p class="text-gray-600">Max Guests</p>
                                    <p class="font-bold text-lg">{{ $apartment->max_guests }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">💰</span>
                                <div>
                                    <p class="text-gray-600">Price per Night</p>
                                    <p class="font-bold text-lg">${{ number_format($apartment->price_per_night) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Images Gallery -->
                    @if($apartment->images->count() > 1)
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Gallery</h2>
                            <div class="grid grid-cols-3 gap-4">
                                @foreach($apartment->images as $image)
                                    <img 
                                        src="{{ asset('storage/' . $image->image_path) }}" 
                                        alt="{{ $apartment->name }}"
                                        class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-75 transition"
                                    >
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Booking Widget -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm">Price per night</p>
                        <p class="text-4xl font-bold text-gray-900">${{ number_format($apartment->price_per_night) }}</p>
                    </div>

                    @auth
                        <a 
                            href="{{ route('bookings.create', $apartment->id) }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition text-center block"
                        >
                            Book Now
                        </a>
                    @else
                        <a 
                            href="{{ route('login') }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition text-center block"
                        >
                            Login to Book
                        </a>
                    @endauth

                    <!-- Host Info (if available) -->
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="font-bold text-gray-900 mb-2">Important Info</h3>
                        <ul class="text-gray-600 text-sm space-y-2">
                            <li>✓ Check-in: 3:00 PM</li>
                            <li>✓ Check-out: 11:00 AM</li>
                            <li>✓ Free cancellation within 7 days</li>
                            <li>✓ Instant confirmation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Apartments -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Similar Apartments</h2>
            <!-- Add related apartments logic here -->
        </div>
    </div>
</div>
@endsection
