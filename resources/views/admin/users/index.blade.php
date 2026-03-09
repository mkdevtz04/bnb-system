@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    <div class="bg-[#003B95] text-white pt-8 pb-20 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight">User Management</h1>
            <p class="mt-2 text-blue-100 text-lg font-medium">Manage user roles and permissions.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-bold text-gray-900 italic">Registered Bookers</h2>
                <form action="{{ route('admin.users') }}" method="GET" class="w-full md:w-96 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-[#003B95] focus:ring-2 focus:ring-[#003B95] transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500 tracking-widest">User</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500 tracking-widest">Email</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500 tracking-widest text-center">Total Bookings</th>
                            <th class="px-6 py-4 text-xs font-black uppercase text-gray-500 tracking-widest text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-[#003B95] font-black italic">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-gray-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-blue-100 text-[#003B95] rounded-full text-xs font-black">{{ $user->bookings_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs text-gray-500 font-bold italic">Active Booker</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic font-medium">
                                No registered bookers found matching your search.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-gray-100 italic font-bold">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
