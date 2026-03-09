@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    <!-- Top Header (Booking.com style dark blue) -->
    <div class="bg-[#003B95] text-white pt-8 pb-20 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight">Partner Extranet</h1>
            <p class="mt-2 text-blue-100 text-lg font-medium">Manage your properties, reservations, and business performance.</p>
        </div>
    </div>

    <!-- Main Content wrapper pulled up over the header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
        
        <!-- Action items / Alerts -->
        @if($pendingBookings > 0)
        <div class="bg-white rounded-lg shadow-md p-5 mb-6 border-l-4 border-yellow-400 flex flex-col md:flex-row items-start md:items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
                <span class="mr-4 bg-yellow-100 p-2 rounded-full">
                    <img src="{{ asset('icons/wall-clock.png') }}" class="w-8 h-8" alt="Alert">
                </span>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Action Required</h3>
                    <p class="text-sm text-gray-600 font-medium">You have <strong class="text-gray-900">{{ $pendingBookings }} pending bookings</strong> that need your confirmation.</p>
                </div>
            </div>
            <a href="{{ route('admin.bookings', ['status' => 'pending']) }}" class="bg-[#0071C2] hover:bg-[#005999] shadow-sm text-white px-6 py-2.5 rounded font-bold text-sm transition text-center w-full md:w-auto">
                Review Bookings
            </a>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-md p-5 mb-6 border-l-4 border-green-500 flex items-center justify-between">
            <div class="flex items-center">
                <span class="mr-4 bg-green-100 p-2 rounded-full">
                    <img src="{{ asset('icons/check-mark.png') }}" class="w-8 h-8" alt="Caught up">
                </span>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">You're all caught up!</h3>
                    <p class="text-sm text-gray-600 font-medium">No pending bookings require your attention right now.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Performance Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Pending Bookings Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition group cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 font-bold text-xs tracking-wider uppercase">Pending</h3>
                    <div class="p-1.5 bg-yellow-50 rounded-lg group-hover:scale-110 transition-transform">
                        <img src="{{ asset('icons/time-managament.png') }}" class="w-8 h-8" alt="Pending">
                    </div>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-1">{{ $pendingBookings }}</div>
                <div class="text-sm text-gray-500 font-medium border-t border-gray-100 pt-3 mt-3">Awaiting host confirmation</div>
            </div>

            <!-- Confirmed Bookings Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition group cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 font-bold text-xs tracking-wider uppercase">Confirmed</h3>
                    <div class="p-1.5 bg-green-50 rounded-lg group-hover:scale-110 transition-transform">
                        <img src="{{ asset('icons/booking.png') }}" class="w-8 h-8" alt="Confirmed">
                    </div>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-1">{{ $confirmedBookings }}</div>
                <div class="text-sm text-green-600 font-bold flex items-center border-t border-gray-100 pt-3 mt-3">
                    <span class="mr-1 text-lg leading-none">↑</span> Active stays
                </div>
            </div>

            <!-- Inquiries Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition group cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 font-bold text-xs tracking-wider uppercase">Inquiries</h3>
                    <div class="p-2.5 bg-purple-50 rounded-lg text-purple-600 group-hover:scale-110 transition-transform">📧</div>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-1">{{ $inquiryCount }}</div>
                <div class="text-sm text-gray-500 font-medium border-t border-gray-100 pt-3 mt-3">New contact messages</div>
            </div>

            <!-- Apartments Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition group">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 font-bold text-xs tracking-wider uppercase">Properties</h3>
                    <div class="p-1.5 bg-blue-50 rounded-lg group-hover:scale-110 transition-transform">
                        <img src="{{ asset('icons/building.png') }}" class="w-8 h-8" alt="Properties">
                    </div>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-1">{{ $totalApartments }}</div>
                <div class="text-sm border-t border-gray-100 pt-3 mt-3">
                    <a href="{{ route('admin.apartments') }}" class="text-[#0071C2] hover:text-[#005999] font-bold hover:underline inline-flex items-center">
                        Manage inventory <span class="ml-1">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <!-- Left Column: Quick Actions & Notifications -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Notifications Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span>Recent Alerts</span>
                        @if($notifications->count() > 0)
                            <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $notifications->count() }} NEW</span>
                        @endif
                    </h2>
                    <div class="space-y-4">
                        @forelse($notifications as $notification)
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 relative group">
                                <p class="text-sm font-bold text-gray-900 mb-1">{{ $notification->data['message'] }}</p>
                                <div class="text-xs text-gray-600 space-y-0.5">
                                    <p>Guest: {{ $notification->data['guest_name'] }}</p>
                                    <p>Dates: {{ \Carbon\Carbon::parse($notification->data['check_in'])->format('M d') }} - {{ \Carbon\Carbon::parse($notification->data['check_out'])->format('M d') }}</p>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                <a href="{{ route('admin.bookings') }}" class="absolute inset-0 z-0"></a>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <p class="text-gray-400 text-sm">No new notifications</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 border-b border-gray-100 pb-3">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('admin.apartments.create') }}" class="flex items-center justify-between w-full p-4 border border-gray-200 rounded-lg hover:border-[#0071C2] hover:bg-blue-50 transition group shadow-sm bg-white">
                            <div class="flex items-center text-gray-700 font-semibold group-hover:text-[#0071C2]">
                                <span class="mr-3">
                                    <img src="{{ asset('icons/add.png') }}" class="w-6 h-6" alt="Add">
                                </span> Add new property
                            </div>
                            <span class="text-gray-300 font-bold group-hover:text-[#0071C2]">→</span>
                        </a>
                        <a href="{{ route('admin.bookings') }}" class="flex items-center justify-between w-full p-4 border border-gray-200 rounded-lg hover:border-[#0071C2] hover:bg-blue-50 transition group shadow-sm bg-white">
                            <div class="flex items-center text-gray-700 font-semibold group-hover:text-[#0071C2]">
                                <span class="mr-3">
                                    <img src="{{ asset('icons/booking.png') }}" class="w-6 h-6" alt="Bookings">
                                </span> View reservations
                            </div>
                            <span class="text-gray-300 font-bold group-hover:text-[#0071C2]">→</span>
                        </a>
                        <a href="{{ route('admin.users') }}" class="flex items-center justify-between w-full p-4 border border-gray-200 rounded-lg hover:border-[#0071C2] hover:bg-blue-50 transition group shadow-sm bg-white">
                            <div class="flex items-center text-gray-700 font-semibold group-hover:text-[#0071C2]">
                                <span class="mr-3 text-xl bg-purple-100 p-1.5 rounded-lg text-purple-600"><i class="fa-solid fa-users-gear"></i></span> User Management
                            </div>
                            <span class="text-gray-300 font-bold group-hover:text-[#0071C2]">→</span>
                        </a>
                        <a href="{{ route('admin.reports.booked') }}" class="flex items-center justify-between w-full p-4 border border-gray-200 rounded-lg hover:border-[#0071C2] hover:bg-blue-50 transition group shadow-sm bg-white">
                            <div class="flex items-center text-gray-700 font-semibold group-hover:text-[#0071C2]">
                                <span class="mr-3 text-xl bg-green-100 p-1.5 rounded-lg text-green-600"><i class="fa-solid fa-chart-line"></i></span> Booked Report
                            </div>
                            <span class="text-gray-300 font-bold group-hover:text-[#0071C2]">→</span>
                        </a>
                    </div>
                </div>

                <!-- Support Widget -->
                <div class="bg-gradient-to-br from-[#003B95] to-[#005999] rounded-xl shadow-md p-6 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center mb-3">
                            <span class="text-2xl mr-2">🎧</span>
                            <h3 class="font-bold text-xl">Need help?</h3>
                        </div>
                        <p class="text-blue-100 text-sm mb-6 font-medium leading-relaxed">Contact our Partner Support team, available 24/7 to assist you with reservations and property management.</p>
                        <button class="bg-white text-[#003B95] w-full py-2.5 rounded font-bold transition hover:bg-gray-100 shadow-sm">Contact Support</button>
                    </div>
                    <!-- Decorative circle -->
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full"></div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                        <h2 class="text-lg font-bold text-gray-900">Recent Activity</h2>
                        <a href="{{ route('admin.bookings') }}" class="text-sm font-bold text-[#0071C2] hover:text-[#005999] hover:underline px-3 py-1 rounded-full hover:bg-blue-50 transition">View all</a>
                    </div>
                    
                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-white border-b border-gray-100">
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50/30">Guest</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50/30">Property</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50/30">Dates</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50/30">Status</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-50/30">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentActivity as $activity)
                                <tr class="hover:bg-blue-50/50 transition cursor-default">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @php
                                                $initials = collect(explode(' ', $activity->user->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
                                            @endphp
                                            <div class="h-10 w-10 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-[#003B95] font-bold text-sm shadow-sm opacity-90">{{ strtoupper($initials) }}</div>
                                            <div class="ml-4">
                                                <p class="text-sm font-bold text-gray-900">{{ $activity->user->name }}</p>
                                                <p class="text-xs font-medium text-gray-500 mt-0.5">Ref: #{{ str_pad($activity->id, 7, '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-900">{{ $activity->apartment->name }}</p>
                                        <p class="text-xs font-medium text-gray-500 mt-0.5">ID: #APT-{{ str_pad($activity->apartment->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-900">{{ $activity->check_in->format('M d') }} - {{ $activity->check_out->format('M d') }}</p>
                                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $activity->nights }} {{ Str::plural('night', $activity->nights) }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($activity->status === 'pending')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded shadow-sm bg-yellow-100 text-yellow-800 border border-yellow-200">Pending</span>
                                        @elseif($activity->status === 'confirmed')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded shadow-sm bg-green-100 text-green-800 border border-green-200">Confirmed</span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded shadow-sm bg-gray-100 text-gray-800 border border-gray-200">{{ ucfirst($activity->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('messages.show', $activity) }}" class="text-[#0071C2] hover:text-[#005999] font-bold text-xs flex items-center gap-1">
                                            <i class="fa-solid fa-message"></i> Chat
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <span class="text-sm font-medium">No recent activity yet.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
