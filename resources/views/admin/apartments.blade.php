@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Manage Apartments</h1>
            <a 
                href="{{ route('admin.dashboard') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg transition"
            >
                ← Back to Dashboard
            </a>
        </div>

        <!-- Apartments Grid -->
        @if($apartments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($apartments as $apartment)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                        <!-- Image -->
                        <div class="h-40 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                            <span class="text-5xl">🏢</span>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $apartment->name }}</h3>
                                    <p class="text-sm text-gray-600 capitalize">📍 {{ $apartment->floor }} Floor</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $apartment->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ Str::upper($apartment->status) }}
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="grid grid-cols-2 gap-2 mb-4 text-sm text-gray-600">
                                <div>🛏️ {{ $apartment->bedrooms }} Beds</div>
                                <div>🚿 {{ $apartment->bathrooms }} Bath</div>
                                <div>👥 {{ $apartment->max_guests }} Guests</div>
                                <div class="font-bold text-blue-600">${{ number_format($apartment->price_per_night) }}/night</div>
                            </div>

                            <!-- Booking Stats -->
                            <div class="bg-gray-50 rounded p-3 mb-4 text-sm">
                                <p class="text-gray-600">Active Bookings: <span class="font-bold">{{ $apartment->bookings()->where('status', 'confirmed')->count() }}</span></p>
                                <p class="text-gray-600">Pending Bookings: <span class="font-bold">{{ $apartment->bookings()->where('status', 'pending')->count() }}</span></p>
                            </div>

                            <!-- Image Count -->
                            <p class="text-sm text-gray-600 mb-4">📷 {{ $apartment->images->count() }} images</p>

                            <!-- Actions -->
                            <div class="space-y-2">
                                <a 
                                    href="{{ route('apartments.show', $apartment->id) }}"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition text-center block text-sm"
                                >
                                    View Details
                                </a>
                                <button 
                                    type="button"
                                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-semibold transition text-center text-sm"
                                    onclick="alert('Edit feature coming soon')"
                                >
                                    Edit
                                </button>
                                <button 
                                    type="button"
                                    class="w-full bg-amber-200 hover:bg-amber-300 text-amber-900 px-4 py-2 rounded-lg font-semibold transition text-center text-sm"
                                    onclick="alert('Block dates feature coming soon')"
                                >
                                    Block Dates
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($apartments->hasPages())
                <div class="mt-8">
                    {{ $apartments->links() }}
                </div>
            @endif
        @else
            <div class="bg-gray-50 rounded-lg p-12 text-center">
                <p class="text-gray-600 text-lg">No apartments found</p>
            </div>
        @endif
    </div>
</div>
@endsection
