@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    <div class="bg-[#003B95] text-white pt-8 pb-20 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight">Analytics & Reports</h1>
            <p class="mt-2 text-blue-100 text-lg font-medium">Summary of booked users and apartment performance.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- Booked Users Section --}}
            <div class="bg-white rounded-[20px] shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-[#003B95]"><i class="fa-solid fa-users fa-xl"></i></div>
                    <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tighter">Booked Users</h2>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white border-b border-gray-50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400">User Details</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 text-center">Total Bookings</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($bookedUsers as $user)
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-[#003B95] font-black uppercase italic">{{ substr($user->name, 0, 1) }}</div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $user->name }}</p>
                                            <p class="text-[10px] text-gray-500 font-medium">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-4 py-1 bg-[#003B95] text-white text-xs font-black rounded-full shadow-sm">{{ $user->bookings_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="#" class="text-[10px] font-black text-[#003B95] hover:underline uppercase italic">View Profile</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-10 text-center text-gray-400 italic">No users have booked yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Booked Apartments Section --}}
            <div class="bg-white rounded-[20px] shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700"><i class="fa-solid fa-building fa-xl"></i></div>
                    <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tighter">Inventory Performance</h2>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white border-b border-gray-50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400">Apartment</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 text-center">Booking Freq.</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($bookedApartments as $apt)
                            <tr class="hover:bg-green-50/30 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $apt->name }}</p>
                                        <p class="text-[10px] text-gray-500 font-medium uppercase tracking-widest">{{ $apt->floor }} Floor • ${{ number_format($apt->price_per_night) }}/night</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-4 py-1 bg-green-600 text-white text-xs font-black rounded-full shadow-sm">{{ $apt->bookings_count }} Times</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2 py-1 {{ $apt->status === 'available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-[10px] font-black uppercase italic rounded">
                                        {{ $apt->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-10 text-center text-gray-400 italic">No apartments have been booked yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
