@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pending Bookings -->
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm font-semibold">PENDING BOOKINGS</p>
                        <p class="text-5xl font-bold mt-2">{{ $pendingBookings }}</p>
                    </div>
                    <span class="text-4xl">⏳</span>
                </div>
            </div>

            <!-- Confirmed Bookings -->
            <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-semibold">CONFIRMED BOOKINGS</p>
                        <p class="text-5xl font-bold mt-2">{{ $confirmedBookings }}</p>
                    </div>
                    <span class="text-4xl">✓</span>
                </div>
            </div>

            <!-- Total Apartments -->
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-semibold">TOTAL APARTMENTS</p>
                        <p class="text-5xl font-bold mt-2">{{ $totalApartments }}</p>
                    </div>
                    <span class="text-4xl">🏢</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a 
                href="{{ route('admin.bookings') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-yellow-500"
            >
                <h3 class="text-xl font-bold text-gray-900 mb-2">Manage Bookings</h3>
                <p class="text-gray-600">Review and confirm pending bookings</p>
            </a>

            <a 
                href="{{ route('admin.apartments') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-l-4 border-blue-500"
            >
                <h3 class="text-xl font-bold text-gray-900 mb-2">Manage Apartments</h3>
                <p class="text-gray-600">View and manage your properties</p>
            </a>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b bg-gray-50">
                <h2 class="text-2xl font-bold text-gray-900">Recent Bookings</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Booking ID</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Guest</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Apartment</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-in</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-out</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">#000001</td>
                            <td class="px-6 py-4 text-sm text-gray-600">John Doe</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">Downtown Loft</td>
                            <td class="px-6 py-4 text-sm text-gray-600">Mar 10, 2026</td>
                            <td class="px-6 py-4 text-sm text-gray-600">Mar 15, 2026</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-blue-600">$600</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
