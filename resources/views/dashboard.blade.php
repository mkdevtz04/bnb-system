@extends('layouts.app')

@section('title', 'Dashboard · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px;">

    <header class="mb-7">
        <h1 style="font-size:28px;">Welcome back, {{ auth()->user()->display_name }}</h1>
        <p class="muted" style="font-size:14.5px;">Here's where your stays stand.</p>
    </header>

    <div class="mb-8">
        <x-search-bar />
    </div>

    {{-- Stays needing a review --}}
    @if ($reviewable->isNotEmpty())
        <section class="mb-8">
            <h2 style="font-size:19px; margin-bottom:12px;">How was your stay?</h2>
            <div class="stack">
                @foreach ($reviewable as $booking)
                    <div class="card card-pad flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <strong style="font-size:15px;">{{ $booking->apartment->name }}</strong>
                            <p class="muted" style="font-size:13px;">
                                {{ $booking->check_in->format('j M') }} – {{ $booking->check_out->format('j M Y') }}
                            </p>
                        </div>
                        <a href="{{ route('reviews.create', $booking) }}" class="btn btn-primary btn-sm">
                            <i class="fa-regular fa-star"></i> Write a review
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid gap-8 lg:grid-cols-[1fr_320px]">

        {{-- Upcoming --}}
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 style="font-size:19px;">Upcoming stays</h2>
                <a href="{{ route('bookings.history') }}" class="btn btn-ghost btn-sm">See all</a>
            </div>

            <div class="stack">
                @forelse ($upcoming as $booking)
                    <article class="card overflow-hidden flex flex-col sm:flex-row">
                        <div class="prop-media sm:w-44 shrink-0" style="aspect-ratio: 4/3;">
                            @if ($booking->apartment->images->isNotEmpty())
                                <img src="{{ Storage::url($booking->apartment->images->first()->image_path) }}"
                                     alt="{{ $booking->apartment->name }}" loading="lazy">
                            @else
                                <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
                            @endif
                        </div>

                        <div class="flex-1 p-5">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                @if ($booking->isPending())
                                    <span class="badge badge-warning"><i class="fa-regular fa-clock"></i> Pending</span>
                                @else
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                                @endif
                                <span class="muted price" style="font-size:12.5px;">{{ $booking->display_reference }}</span>
                            </div>

                            <h3 style="font-size:17px; margin-bottom:4px;">{{ $booking->apartment->name }}</h3>
                            <p class="muted" style="font-size:13.5px;">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $booking->check_in->format('D, j M') }} → {{ $booking->check_out->format('D, j M Y') }}
                            </p>

                            <div class="mt-3 flex items-center gap-2">
                                <a href="{{ route('bookings.confirmation', $booking) }}" class="btn btn-secondary btn-sm">Details</a>
                                <a href="{{ route('messages.show', $booking) }}" class="btn btn-ghost btn-sm">
                                    <i class="fa-regular fa-comments"></i> Message
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card card-pad text-center" style="padding:44px 24px;">
                        <i class="fa-regular fa-calendar" style="font-size:34px; color:var(--ink-300);"></i>
                        <p class="muted mt-3" style="font-size:14.5px;">No upcoming stays. Time to plan one?</p>
                        <a href="{{ route('apartments.search') }}" class="btn btn-primary mt-4">Find a stay</a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Sidebar --}}
        <aside class="stack">
            <div class="card card-pad">
                <h2 style="font-size:17px; margin-bottom:12px;">Notifications</h2>
                <div class="divide-y-soft">
                    @forelse ($notifications as $note)
                        <div class="py-3">
                            <p style="font-size:13.5px; line-height:1.5;">{{ $note->data['message'] ?? 'Booking update' }}</p>
                            <span class="muted" style="font-size:12px;">{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="muted py-3" style="font-size:13.5px;">You're all caught up.</p>
                    @endforelse
                </div>
            </div>

            <div class="card card-pad">
                <h2 style="font-size:17px; margin-bottom:6px;">Need a hand?</h2>
                <p class="muted" style="font-size:13.5px; line-height:1.6;">
                    Message the host from any booking, or use the contact form on the homepage.
                </p>
                <a href="{{ url('/#contact') }}" class="btn btn-secondary btn-block mt-3">Contact the host</a>
            </div>
        </aside>
    </div>

    {{-- Browse --}}
    <section class="mt-10">
        <h2 style="font-size:19px; margin-bottom:12px;">Other places to stay</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($apartments as $apt)
                <article class="card card-hover overflow-hidden">
                    <a href="{{ route('apartments.show', $apt) }}" class="prop-media block" style="aspect-ratio:3/2;">
                        @if ($apt->images->isNotEmpty())
                            <img src="{{ Storage::url($apt->images->first()->image_path) }}" alt="{{ $apt->name }}" loading="lazy">
                        @else
                            <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
                        @endif
                    </a>
                    <div class="card-pad">
                        <div class="flex items-start justify-between gap-3">
                            <h3 style="font-size:16px;">
                                <a href="{{ route('apartments.show', $apt) }}"
                                   style="color:var(--brand-700); text-decoration:none;">{{ $apt->name }}</a>
                            </h3>
                            @if ($apt->review_score)
                                <span class="score score-sm">{{ number_format($apt->review_score, 1) }}</span>
                            @endif
                        </div>
                        <div class="price font-bold mt-3" style="font-size:18px;">
                            {{ \App\Support\Money::format($apt->price_per_night) }}
                            <span class="muted font-normal" style="font-size:12px;">/ night</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
