@extends('layouts.app')

@section('title', 'Reports · Admin')

@section('content')
<div class="shell-wide" style="padding: 32px 16px 48px;">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <h1 style="font-size:28px;">Reports</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Dashboard
        </a>
    </header>

    {{-- Revenue by month --}}
    <section class="card overflow-hidden mb-6">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--ink-200);">
            <h2 style="font-size:17px;">Confirmed revenue by month</h2>
        </div>

        @if ($monthly->isEmpty())
            <p class="muted text-center" style="padding:40px;">No confirmed bookings yet.</p>
        @else
            @php $peak = max($monthly->max('revenue'), 1); @endphp
            <div class="card-pad stack">
                @foreach ($monthly as $row)
                    <div>
                        <div class="mb-1.5 flex items-center justify-between" style="font-size:13.5px;">
                            <span>{{ \Carbon\Carbon::parse($row->period . '-01')->format('F Y') }}</span>
                            <span class="muted">
                                {{ $row->bookings }} {{ Str::plural('booking', $row->bookings) }} ·
                                <strong class="price" style="color:var(--ink-900);">
                                    {{ \App\Support\Money::format($row->revenue) }}
                                </strong>
                            </span>
                        </div>
                        <div class="score-bar">
                            <span style="width: {{ round(($row->revenue / $peak) * 100) }}%;"></span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Properties --}}
        <section class="card overflow-hidden">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--ink-200);">
                <h2 style="font-size:17px;">Properties by bookings</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Property</th><th>Bookings</th><th>Revenue</th><th>Score</th></tr></thead>
                    <tbody>
                        @forelse ($bookedApartments as $apartment)
                            <tr>
                                <td><strong>{{ $apartment->name }}</strong></td>
                                <td class="price">{{ $apartment->bookings_count }}</td>
                                <td class="price">{{ \App\Support\Money::format($apartment->bookings_sum_total_price ?? 0) }}</td>
                                <td>
                                    @if ($apartment->reviews_avg_overall)
                                        <span class="score score-sm">{{ number_format($apartment->reviews_avg_overall, 1) }}</span>
                                    @else
                                        <span class="muted" style="font-size:12.5px;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="muted text-center" style="padding:32px;">No data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Guests --}}
        <section class="card overflow-hidden">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--ink-200);">
                <h2 style="font-size:17px;">Guests by bookings</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Guest</th><th>Bookings</th><th>Spend</th></tr></thead>
                    <tbody>
                        @forelse ($bookedUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->display_name }}</strong>
                                    <div class="muted" style="font-size:12.5px;">{{ $user->email }}</div>
                                </td>
                                <td class="price">{{ $user->bookings_count }}</td>
                                <td class="price">{{ \App\Support\Money::format($user->bookings_sum_total_price ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="muted text-center" style="padding:32px;">No data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
