@extends('layouts.app')

@section('title', 'Bookings · Admin')

@section('content')
<div class="shell-wide" style="padding: 32px 16px 48px;">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 style="font-size:28px;">Bookings</h1>
            <p class="muted" style="font-size:14.5px;">{{ $bookings->total() }} in total</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Dashboard
        </a>
    </header>

    <div class="card card-pad mb-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $key => $label)
                <a href="{{ route('admin.bookings', array_filter(['status' => $key, 'q' => request('q')])) }}"
                   class="chip {{ request('status', '') === $key ? 'chip-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="search" name="q" class="input" style="width:220px;"
                   placeholder="Reference, name or email" value="{{ request('q') }}">
            <button class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th><th>Guest</th><th>Property</th><th>Stay</th>
                        <th>Guests</th><th>Total</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="price" style="font-size:12.5px; white-space:nowrap;">{{ $booking->display_reference }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $booking->user->display_name }}</div>
                                <div class="muted" style="font-size:12.5px;">{{ $booking->user->email }}</div>
                            </td>
                            <td>{{ $booking->apartment->name }}</td>
                            <td style="white-space:nowrap;">
                                {{ $booking->check_in->format('j M Y') }}
                                <div class="muted" style="font-size:12.5px;">
                                    {{ $booking->nights }} {{ Str::plural('night', $booking->nights) }} →
                                    {{ $booking->check_out->format('j M') }}
                                </div>
                            </td>
                            <td>{{ $booking->guests }}</td>
                            <td class="price" style="white-space:nowrap;">
                                {{ \App\Support\Money::format($booking->total_price, $booking->currency) }}
                            </td>
                            <td>
                                @if ($booking->isPending())
                                    <span class="badge badge-warning">Pending</span>
                                @elseif ($booking->isConfirmed())
                                    <span class="badge badge-success">Confirmed</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    @if ($booking->isPending())
                                        <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                                            @csrf
                                            <button class="btn btn-primary btn-sm">Confirm</button>
                                        </form>
                                    @endif

                                    @unless ($booking->isCancelled())
                                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}"
                                              onsubmit="return confirm('Cancel this booking and release the dates?')">
                                            @csrf
                                            <button class="btn btn-ghost btn-sm" style="color:var(--red-600);">Cancel</button>
                                        </form>
                                    @endunless

                                    <a href="{{ route('messages.show', $booking) }}" class="btn btn-ghost btn-sm"
                                       title="Message guest"><i class="fa-regular fa-comments"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="muted text-center" style="padding:44px;">No bookings match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($bookings->hasPages())
        <div class="mt-6">{{ $bookings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
