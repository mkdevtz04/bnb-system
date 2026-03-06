@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Manage Apartments</h1>
            <div class="flex space-x-4">
                <a 
                    href="{{ route('admin.dashboard') }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg transition flex items-center"
                >
                    ← Back to Dashboard
                </a>
                <a 
                    href="{{ route('admin.apartments.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-bold flex items-center"
                >
                    + Add Apartment
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Apartments Grid -->
        @if($apartments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($apartments as $apartment)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                        <!-- Image -->
                        @if($apartment->images->count() > 0)
                            <div class="h-40 bg-gray-200">
                                <img src="{{ asset('storage/' . $apartment->images->first()->image_path) }}" alt="{{ $apartment->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="h-40 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                <span class="text-5xl">🏢</span>
                            </div>
                        @endif

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="text-xs font-mono font-bold text-gray-500 mb-1">ID: #APT-{{ str_pad($apartment->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $apartment->name }}</h3>
                                    <p class="text-sm text-gray-600 capitalize">📍 {{ $apartment->floor }} Floor</p>
                                </div>
                                
                                <form action="{{ route('admin.apartments.toggle-status', $apartment->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to change the availability of this apartment?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold transition hover:opacity-80 cursor-pointer {{ $apartment->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800 shadow-sm border border-red-200' }}" title="Click to toggle availability">
                                        {{ Str::upper($apartment->status) }} 
                                        {!! $apartment->status === 'available' ? '✓' : '✗' !!}
                                    </button>
                                </form>
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
                                    href="{{ route('admin.apartments.edit', $apartment->id) }}"
                                    class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg font-semibold transition text-center block text-sm"
                                >
                                    Edit Apartment
                                </a>
                                <button 
                                    type="button"
                                    class="w-full bg-amber-200 hover:bg-amber-300 text-amber-900 px-4 py-2 rounded-lg font-semibold transition text-center text-sm"
                                    onclick="alert('Block dates feature coming soon')"
                                >
                                    Block Dates
                                </button>
                                <form action="{{ route('admin.apartments.destroy', $apartment->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this apartment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit"
                                        class="w-full bg-red-100 hover:bg-red-200 text-red-800 px-4 py-2 rounded-lg font-semibold transition text-center text-sm"
                                    >
                                        Delete Apartment
                                    </button>
                                </form>
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
