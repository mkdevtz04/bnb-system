@extends('layouts.app')

@section('title', 'My bookings · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px;">

    <h1 style="font-size:28px; margin-bottom:6px;">My bookings</h1>
    <p class="muted" style="font-size:14.5px;">Your stays, past and upcoming.</p>

    <div class="mt-5 flex flex-wrap gap-2">
        @foreach ([
            '' => 'All',
            'upcoming' => 'Upcoming',
            'past' => 'Past',
            'cancelled' => 'Cancelled',
        ] as $key => $label)
            <a href="{{ route('bookings.history', array_filter(['filter' => $key])) }}"
               class="chip {{ $filter === $key ? 'chip-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="stack mt-6">
        @forelse ($bookings as $booking)
            <article class="card overflow-hidden flex flex-col sm:flex-row">
                <div class="prop-media sm:w-56 shrink-0" style="aspect-ratio: 4/3;">
                    @if ($booking->apartment->images->isNotEmpty())
                        <img src="{{ Storage::url($booking->apartment->images->first()->image_path) }}"
                             alt="{{ $booking->apartment->name }}" loading="lazy">
                    @else
                        <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
                    @endif
                </div>

                <div class="flex flex-1 flex-col gap-4 p-5 sm:flex-row sm:items-start">
                    <div class="flex-1 min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            @if ($booking->isPending())
                                <span class="badge badge-warning"><i class="fa-regular fa-clock"></i> Pending</span>
                            @elseif ($booking->isCancelled())
                                <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Cancelled</span>
                            @elseif ($booking->isCompleted())
                                <span class="badge badge-neutral"><i class="fa-solid fa-flag-checkered"></i> Completed</span>
                            @else
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                            @endif
                            <span class="muted price" style="font-size:12.5px;">{{ $booking->display_reference }}</span>
                        </div>

                        <h2 style="font-size:18px; margin-bottom:4px;">
                            <a href="{{ route('apartments.show', $booking->apartment) }}"
                               style="color:var(--brand-700); text-decoration:none;">{{ $booking->apartment->name }}</a>
                        </h2>

                        <p class="muted" style="font-size:13.5px;">
                            <i class="fa-regular fa-calendar"></i>
                            {{ $booking->check_in->format('D, j M Y') }} → {{ $booking->check_out->format('D, j M Y') }}
                            · {{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}
                            · {{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}
                        </p>

                        @if ($booking->canBeReviewed())
                            <div class="alert alert-info mt-3" style="font-size:13.5px;">
                                <i class="fa-regular fa-star mt-0.5"></i>
                                <span>How was your stay? Your review helps the next guest.</span>
                            </div>
                        @endif
                    </div>

                    <div class="sm:w-44 sm:text-right">
                        <div class="price font-bold" style="font-size:20px;">
                            {{ \App\Support\Money::format($booking->total_price, $booking->currency) }}
                        </div>
                        <div class="muted" style="font-size:12px;">Total incl. fees</div>

                        <div class="mt-3 flex flex-wrap gap-2 sm:justify-end">
                            @if ($booking->canBeReviewed())
                                <a href="{{ route('reviews.create', $booking) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-regular fa-star"></i> Review
                                </a>
                            @endif

                            <a href="{{ route('messages.show', $booking) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-regular fa-comments"></i> Message
                            </a>

                            @if ($booking->canBeCancelled())
                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                                      onsubmit="return confirm('Cancel this booking? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" style="color:var(--red-600);">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card card-pad text-center" style="padding: 56px 24px;">
                <i class="fa-regular fa-calendar" style="font-size:40px; color:var(--ink-300);"></i>
                <h2 style="font-size:20px; margin:16px 0 8px;">No bookings here yet</h2>
                <p class="muted" style="font-size:14.5px;">When you book a stay it will show up on this page.</p>
                <a href="{{ route('apartments.search') }}" class="btn btn-primary mt-5">Find a stay</a>
            </div>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="mt-8">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
