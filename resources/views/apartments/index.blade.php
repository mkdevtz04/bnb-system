@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Available Apartments</h1>

        <!-- Search Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('apartments.search') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Check-in</label>
                    <input 
                        type="text" 
                        id="check_in" 
                        name="check_in" 
                        value="{{ request('check_in') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Select date"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Check-out</label>
                    <input 
                        type="text" 
                        id="check_out" 
                        name="check_out" 
                        value="{{ request('check_out') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Select date"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Guests</label>
                    <select name="guests" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="1" {{ request('guests') == '1' ? 'selected' : '' }}>1 Guest</option>
                        <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2 Guests</option>
                        <option value="3" {{ request('guests') == '3' ? 'selected' : '' }}>3 Guests</option>
                        <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>4 Guests</option>
                        <option value="5" {{ request('guests') == '5' ? 'selected' : '' }}>5+ Guests</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition"
                    >
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Apartments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($apartments as $apartment)
                <div class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition duration-300 overflow-hidden">
                    <div class="relative h-48 overflow-hidden bg-gray-300">
                        @if($apartment->images->count() > 0)
                            <img 
                                src="{{ \Storage::url($apartment->images->first()->image_path) }}" 
                                alt="{{ $apartment->name }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                No Image
                            </div>
                        @endif
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">Available</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $apartment->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($apartment->description, 100) }}</p>

                        <div class="flex items-center gap-4 mb-4 text-gray-700 text-sm">
                            <div>🛏️ {{ $apartment->bedrooms }} Beds</div>
                            <div>🚿 {{ $apartment->bathrooms }} Bath</div>
                            <div>👥 {{ $apartment->max_guests }} Guests</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div><span class="text-2xl font-bold text-gray-900">${{ number_format($apartment->price_per_night) }}</span><span class="text-gray-600 text-sm">/night</span></div>
                            <a 
                                href="{{ route('apartments.show', $apartment->id) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                            >
                                View
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <p class="text-gray-500 text-lg">No apartments found.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($apartments->hasPages())
            <div class="mt-8">
                {{ $apartments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Flatpickr Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#check_in", {
        minDate: "today",
        dateFormat: "Y-m-d",
        disableMobile: true
    });

    flatpickr("#check_out", {
        minDate: "today",
        dateFormat: "Y-m-d",
        disableMobile: true
    });
</script>
@endsection
