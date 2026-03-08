@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Manage Bookings</h1>
            <a 
                href="{{ route('admin.dashboard') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg transition"
            >
                ← Back to Dashboard
            </a>
        </div>

        <!-- Filter Buttons -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-2 flex-wrap">
            <a 
                href="{{ route('admin.bookings') }}"
                class="px-4 py-2 rounded-lg font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-900' }} transition"
            >
                All Bookings
            </a>
            <a 
                href="{{ route('admin.bookings', ['status' => 'pending']) }}"
                class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-900' }} transition"
            >
                ⏳ Pending
            </a>
            <a 
                href="{{ route('admin.bookings', ['status' => 'confirmed']) }}"
                class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'confirmed' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-900' }} transition"
            >
                ✓ Confirmed
            </a>
            <a 
                href="{{ route('admin.bookings', ['status' => 'cancelled']) }}"
                class="px-4 py-2 rounded-lg font-semibold {{ request('status') === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-900' }} transition"
            >
                ✕ Cancelled
            </a>
        </div>

        <!-- Bookings Table -->
        @if($bookings->count() > 0)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">ID</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Guest</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Apartment</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Check-in</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Check-out</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Nights</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Price</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($bookings as $booking)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $booking->user->name }}</p>
                                            <p class="text-gray-600">{{ $booking->user->email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $booking->apartment->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->check_in->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->check_out->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->nights }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-600">${{ number_format($booking->total_price) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($booking->status === 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold inline-block">
                                                ⏳ Pending
                                            </span>
                                        @elseif($booking->status === 'confirmed')
                                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold inline-block">
                                                ✓ Confirmed
                                            </span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold inline-block">
                                                ✕ Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm space-y-2">
                                        @if($booking->status === 'pending')
                                            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold transition block w-full text-center"
                                                >
                                                    Confirm
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold transition block w-full text-center"
                                                >
                                                    Reject
                                                </button>
                                            </form>
                                        @elseif($booking->status === 'confirmed')
                                            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit"
                                                    onclick="return confirm('Are you sure?')"
                                                    class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs font-semibold transition block w-full text-center"
                                                >
                                                    Cancel
                                                </button>
                                            </form>
                                        @else
                                        @endif

                                        @if($booking->status !== 'cancelled')
                                            <a href="{{ route('messages.show', $booking) }}" class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded text-xs font-bold transition block w-full text-center">
                                                <i class="fa-solid fa-message"></i> Chat
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($bookings->hasPages())
                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        @else
            <div class="bg-gray-50 rounded-lg p-12 text-center">
                <p class="text-gray-600 text-lg">No bookings found</p>
            </div>
        @endif
    </div>
</div>
@endsection
