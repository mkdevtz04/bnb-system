@extends('layouts.app')

@section('title', 'Admin · CoastalCharmz')

@section('content')
<div class="shell-wide" style="padding: 32px 16px 48px;">

    <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 style="font-size:28px;">Host dashboard</h1>
            <p class="muted" style="font-size:14.5px;">{{ now()->format('l, j F Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.apartments.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add property
            </a>
            <a href="{{ route('admin.bookings') }}" class="btn btn-secondary">Manage bookings</a>
        </div>
    </header>

    {{-- Key numbers --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['fa-clock', 'Awaiting confirmation', $pendingBookings, 'warning', route('admin.bookings', ['status' => 'pending'])],
            ['fa-circle-check', 'Confirmed bookings', $confirmedBookings, 'success', route('admin.bookings', ['status' => 'confirmed'])],
            ['fa-bed', 'Occupied tonight', $occupiedTonight . ' / ' . $totalApartments, 'info', route('admin.apartments')],
            ['fa-envelope', 'Unread messages', $unreadInquiries, 'neutral', route('admin.inquiries')],
        ] as [$icon, $label, $value, $tone, $link])
            <a href="{{ $link }}" class="card card-pad card-hover" style="text-decoration:none;">
                <div class="flex items-center justify-between">
                    <span class="badge badge-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                    <i class="fa-solid fa-chevron-right muted" style="font-size:11px;"></i>
                </div>
                <div class="price font-bold mt-3" style="font-size:28px; color:var(--ink-900);">{{ $value }}</div>
                <div class="muted" style="font-size:13px;">{{ $label }}</div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">

        {{-- Recent bookings --}}
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid var(--ink-200);">
                <h2 style="font-size:17px;">Latest bookings</h2>
                <a href="{{ route('admin.bookings') }}" class="btn btn-ghost btn-sm">See all</a>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th><th>Guest</th><th>Property</th><th>Dates</th><th>Total</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivity as $booking)
                            <tr>
                                <td class="price" style="font-size:12.5px;">{{ $booking->display_reference }}</td>
                                <td>{{ $booking->user->display_name }}</td>
                                <td>{{ $booking->apartment->name }}</td>
                                <td style="white-space:nowrap;">
                                    {{ $booking->check_in->format('j M') }} – {{ $booking->check_out->format('j M') }}
                                </td>
                                <td class="price">{{ \App\Support\Money::format($booking->total_price, $booking->currency) }}</td>
                                <td>
                                    @if ($booking->isPending())
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif ($booking->isConfirmed())
                                        <span class="badge badge-success">Confirmed</span>
                                    @else
                                        <span class="badge badge-danger">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="muted text-center" style="padding:32px;">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Side panels --}}
        <aside class="stack">
            <div class="card card-pad">
                <h2 style="font-size:17px; margin-bottom:12px;">Performance</h2>
                <div class="flex items-center justify-between py-2">
                    <span class="muted" style="font-size:13.5px;">Confirmed revenue</span>
                    <strong class="price">{{ \App\Support\Money::format($revenue) }}</strong>
                </div>
                <div class="flex items-center justify-between py-2" style="border-top:1px solid var(--ink-200);">
                    <span class="muted" style="font-size:13.5px;">Average score</span>
                    @if ($averageScore > 0)
                        <span class="score score-sm">{{ number_format($averageScore, 1) }}</span>
                    @else
                        <span class="muted" style="font-size:13px;">No reviews</span>
                    @endif
                </div>
                <a href="{{ route('admin.reports.booked') }}" class="btn btn-secondary btn-block mt-4">Full report</a>
            </div>

            <div class="card card-pad">
                <div class="mb-3 flex items-center justify-between">
                    <h2 style="font-size:17px;">Notifications</h2>
                    @if ($notifications->isNotEmpty())
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button class="btn btn-ghost btn-sm">Clear</button>
                        </form>
                    @endif
                </div>
                <div class="divide-y-soft">
                    @forelse ($notifications as $note)
                        <div class="py-3">
                            <p style="font-size:13.5px; line-height:1.5;">{{ $note->data['message'] ?? 'Update' }}</p>
                            <span class="muted" style="font-size:12px;">{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="muted py-3" style="font-size:13.5px;">Nothing new.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
