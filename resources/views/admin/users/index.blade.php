@extends('layouts.app')

@section('title', 'Guests · Admin')

@section('content')
<div class="shell-wide" style="padding: 32px 16px 48px;">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 style="font-size:28px;">Guests</h1>
            <p class="muted" style="font-size:14.5px;">{{ $users->total() }} accounts</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Dashboard
        </a>
    </header>

    <div class="card card-pad mb-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.users') }}" class="chip {{ request()->boolean('guests_only') ? '' : 'chip-active' }}">
                Everyone
            </a>
            <a href="{{ route('admin.users', ['guests_only' => 1]) }}"
               class="chip {{ request()->boolean('guests_only') ? 'chip-active' : '' }}">Has booked</a>
        </div>

        <form method="GET" class="flex items-center gap-2">
            @if (request()->boolean('guests_only'))
                <input type="hidden" name="guests_only" value="1">
            @endif
            <input type="search" name="q" class="input" style="width:220px;"
                   placeholder="Name or email" value="{{ request('q') }}">
            <button class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Guest</th><th>Contact</th><th>Role</th><th>Bookings</th><th>Lifetime value</th><th>Joined</th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="avatar">{{ $user->initials }}</span>
                                    <strong>{{ $user->display_name }}</strong>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}"
                                   style="color:var(--brand-700); text-decoration:none;">{{ $user->email }}</a>
                                @if ($user->phone)
                                    <div class="muted" style="font-size:12.5px;">{{ $user->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $user->isAdmin() ? 'badge-info' : 'badge-neutral' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="price">{{ $user->bookings_count }}</td>
                            <td class="price">{{ \App\Support\Money::format($user->bookings_sum_total_price ?? 0) }}</td>
                            <td class="muted" style="white-space:nowrap;">{{ $user->created_at->format('j M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted text-center" style="padding:44px;">No guests match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-6">{{ $users->links() }}</div>
    @endif
</div>
@endsection
