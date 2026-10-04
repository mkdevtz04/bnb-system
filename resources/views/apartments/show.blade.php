@extends('layouts.app')

@section('title', $apartment->name . ' · CoastalCharmz')
@section('meta_description', Str::limit(strip_tags($apartment->description), 150))

@section('content')
@php
    $images = $apartment->images;
    $amenityIcons = [
        'wifi' => 'fa-wifi', 'kitchen' => 'fa-kitchen-set', 'air_conditioning' => 'fa-snowflake',
        'parking' => 'fa-square-parking', 'workspace' => 'fa-laptop', 'washer' => 'fa-soap',
        'tv' => 'fa-tv', 'balcony' => 'fa-umbrella-beach',
    ];
@endphp

<div class="shell" style="padding: 24px 16px 48px;">

    <nav aria-label="Breadcrumb" class="muted mb-4" style="font-size:13px;">
        <a href="{{ route('apartments.search') }}" style="color:var(--brand-700); text-decoration:none;">Search</a>
        <span class="mx-1.5">/</span>
        <span>{{ $apartment->name }}</span>
    </nav>

    <header class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 style="font-size:30px; line-height:1.2;">{{ $apartment->name }}</h1>
            <p class="muted mt-1.5 flex items-center gap-1.5" style="font-size:14px;">
                <i class="fa-solid fa-location-dot"></i>
                {{ $apartment->address ? $apartment->address . ', ' : '' }}{{ $apartment->location_line }}
            </p>
        </div>
        <x-score-badge :score="$apartment->review_score" :count="$apartment->review_count"
                       :label="$apartment->review_label" size="lg" class="flex-row-reverse" />
    </header>

    {{-- Gallery --}}
    @if ($images->isNotEmpty())
        <div class="mb-8 grid gap-2" style="grid-template-columns: repeat(4, 1fr); border-radius: var(--r-lg); overflow: hidden;">
            <a href="{{ Storage::url($images[0]->image_path) }}" target="_blank" rel="noopener"
               class="prop-media" style="grid-column: span 2; grid-row: span 2; aspect-ratio: 1/1;">
                <img src="{{ Storage::url($images[0]->image_path) }}" alt="{{ $apartment->name }}">
            </a>
            @foreach ($images->slice(1, 4) as $image)
                <a href="{{ Storage::url($image->image_path) }}" target="_blank" rel="noopener"
                   class="prop-media" style="aspect-ratio: 1/1;">
                    <img src="{{ Storage::url($image->image_path) }}" alt="{{ $apartment->name }}" loading="lazy">
                </a>
            @endforeach
        </div>
    @else
        <div class="prop-media card mb-8" style="height: 320px;">
            <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-[1fr_360px]">

        {{-- Details --}}
        <div class="stack">
            <section class="card card-pad">
                <h2 style="font-size:21px; margin-bottom:12px;">About this place</h2>
                <p class="muted" style="font-size:15px; line-height:1.7; white-space:pre-line;">{{ $apartment->description }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2" style="border-top:1px solid var(--ink-200); padding-top:20px;">
                    <div class="flex items-center gap-2.5" style="font-size:14.5px;">
                        <i class="fa-solid fa-bed muted w-5"></i> {{ $apartment->bedrooms }} {{ Str::plural('bedroom', $apartment->bedrooms) }}
                    </div>
                    <div class="flex items-center gap-2.5" style="font-size:14.5px;">
                        <i class="fa-solid fa-bath muted w-5"></i> {{ $apartment->bathrooms }} {{ Str::plural('bathroom', $apartment->bathrooms) }}
                    </div>
                    <div class="flex items-center gap-2.5" style="font-size:14.5px;">
                        <i class="fa-solid fa-user-group muted w-5"></i> Sleeps {{ $apartment->max_guests }}
                    </div>
                    <div class="flex items-center gap-2.5" style="font-size:14.5px;">
                        <i class="fa-solid fa-stairs muted w-5"></i> <span class="capitalize">{{ $apartment->floor }}</span> floor
                    </div>
                </div>
            </section>

            @if (filled($apartment->amenities))
                <section class="card card-pad">
                    <h2 style="font-size:21px; margin-bottom:16px;">Amenities</h2>
                    {{-- Driven by the property's own data. The old page hard-coded
                         the same eight amenities onto every listing. --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($apartment->amenities as $amenity)
                            <div class="flex items-center gap-2.5" style="font-size:14.5px;">
                                <i class="fa-solid {{ $amenityIcons[$amenity] ?? 'fa-circle-check' }}"
                                   style="color:var(--green-600); width:20px;"></i>
                                {{ Str::of($amenity)->replace('_', ' ')->title() }}
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="card card-pad">
                <h2 style="font-size:21px; margin-bottom:16px;">House rules</h2>
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="field-label">Check-in</dt>
                        <dd style="font-size:15px;">From {{ \Carbon\Carbon::parse($apartment->check_in_from)->format('g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="field-label">Check-out</dt>
                        <dd style="font-size:15px;">Until {{ \Carbon\Carbon::parse($apartment->check_out_until)->format('g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="field-label">Minimum stay</dt>
                        <dd style="font-size:15px;">{{ $apartment->min_nights }} {{ Str::plural('night', $apartment->min_nights) }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Reviews --}}
            <section class="card card-pad" id="reviews">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 style="font-size:21px;">Guest reviews</h2>
                    @if ($apartment->review_count > 0)
                        <a href="{{ route('apartments.reviews', $apartment) }}" class="btn btn-secondary btn-sm">
                            Read all {{ $apartment->review_count }}
                        </a>
                    @endif
                </div>

                @if ($categoryScores)
                    <div class="mb-6 grid gap-x-8 gap-y-3 sm:grid-cols-2">
                        @foreach ($categoryScores as $category)
                            <div>
                                <div class="mb-1 flex items-center justify-between" style="font-size:13.5px;">
                                    <span>{{ $category['label'] }}</span>
                                    <strong class="price">{{ number_format($category['score'], 1) }}</strong>
                                </div>
                                <div class="score-bar"><span style="width: {{ $category['score'] * 10 }}%;"></span></div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="divide-y-soft">
                    @forelse ($apartment->reviews as $review)
                        <article class="py-4">
                            <div class="mb-2 flex items-center gap-3">
                                <span class="avatar">{{ $review->user->initials }}</span>
                                <div class="flex-1">
                                    <div class="font-semibold" style="font-size:14px;">{{ $review->user->display_name }}</div>
                                    <div class="muted" style="font-size:12.5px;">{{ $review->created_at->format('M Y') }}</div>
                                </div>
                                <span class="score score-sm">{{ number_format($review->overall, 1) }}</span>
                            </div>
                            @if ($review->title)
                                <h3 style="font-size:15px; font-family:var(--font-body); margin-bottom:6px;">{{ $review->title }}</h3>
                            @endif
                            @if ($review->liked)
                                <p style="font-size:14px; line-height:1.6;">
                                    <i class="fa-solid fa-thumbs-up" style="color:var(--green-600);"></i> {{ $review->liked }}
                                </p>
                            @endif
                            @if ($review->disliked)
                                <p class="muted mt-1.5" style="font-size:14px; line-height:1.6;">
                                    <i class="fa-solid fa-thumbs-down"></i> {{ $review->disliked }}
                                </p>
                            @endif
                        </article>
                    @empty
                        <p class="muted py-6 text-center" style="font-size:14.5px;">
                            No reviews yet — be the first to stay and tell us how it went.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- Booking sidebar --}}
        <aside>
            <div class="card card-pad" style="position: sticky; top: calc(var(--nav-h) + 16px);"
                 x-data="stayPicker(@js([
                     'quoteUrl' => route('apartments.quote', $apartment),
                     'maxGuests' => $apartment->max_guests,
                     'initial' => $quote?->toArray(),
                 ]))">

                <div class="mb-4 flex items-baseline gap-1.5">
                    <span class="price font-bold" style="font-size:27px;">
                        {{ \App\Support\Money::format($apartment->price_per_night, config('booking.currency')) }}
                    </span>
                    <span class="muted" style="font-size:14px;">per night</span>
                </div>

                @if ($apartment->status !== 'available')
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <span>This property is temporarily unavailable.</span>
                    </div>
                @else
                    <form method="GET" action="{{ route('bookings.create', $apartment) }}" class="stack">
                        <div class="field">
                            <label class="field-label" for="stay">Your stay</label>
                            <input type="text" id="stay" class="input" placeholder="Select your dates" readonly
                                   data-datepicker="range"
                                   data-disabled-dates="{{ json_encode($unavailableDates) }}">
                            <input type="hidden" name="check_in" value="{{ $checkIn?->toDateString() }}">
                            <input type="hidden" name="check_out" value="{{ $checkOut?->toDateString() }}">
                        </div>

                        <div class="field">
                            <label class="field-label" for="guests">Guests</label>
                            <select name="guests" id="guests" class="select" x-model="guests" @change="refresh">
                                @for ($i = 1; $i <= $apartment->max_guests; $i++)
                                    <option value="{{ $i }}">{{ $i }} {{ Str::plural('guest', $i) }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Price breakdown, fetched from the server. The client no
                             longer computes any figure the guest is shown. --}}
                        <div x-show="quote" x-cloak class="panel" style="padding:14px;">
                            <div class="flex justify-between" style="font-size:14px;">
                                <span x-text="`${money(quote.nightly_rate)} x ${quote.nights} nights`"></span>
                                <span class="price" x-text="money(quote.subtotal)"></span>
                            </div>
                            <template x-if="quote && quote.cleaning_fee > 0">
                                <div class="mt-2 flex justify-between" style="font-size:14px;">
                                    <span>Cleaning fee</span>
                                    <span class="price" x-text="money(quote.cleaning_fee)"></span>
                                </div>
                            </template>
                            <div class="mt-2 flex justify-between" style="font-size:14px;">
                                <span>Service fee</span>
                                <span class="price" x-text="money(quote.service_fee)"></span>
                            </div>
                            <div class="mt-2 flex justify-between" style="font-size:14px;">
                                <span>Taxes</span>
                                <span class="price" x-text="money(quote.taxes)"></span>
                            </div>
                            <div class="mt-3 flex justify-between font-bold"
                                 style="font-size:16.5px; border-top:1px solid var(--ink-200); padding-top:12px;">
                                <span>Total</span>
                                <span class="price" x-text="money(quote.total)"></span>
                            </div>
                        </div>

                        <div x-show="unavailable" x-cloak class="alert alert-error">
                            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                            <span>Those dates are not available. Try another stay.</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block"
                                x-bind:disabled="!quote || unavailable">
                            <span x-text="quote ? 'Reserve' : 'Select dates'"></span>
                        </button>
                    </form>

                    <p class="muted mt-3 text-center" style="font-size:12.5px;">
                        <i class="fa-solid fa-lock"></i> You won't be charged yet.
                    </p>
                @endif
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
    function stayPicker(config) {
        return {
            quote: config.initial || null,
            unavailable: false,
            guests: 1,
            loading: false,

            init() {
                // The range picker announces a complete stay; ask the server what
                // it costs rather than working it out here.
                this.$root.addEventListener('stay:selected', (e) => this.refresh(e.detail));
                this.$root.addEventListener('stay:cleared', () => {
                    this.quote = null;
                    this.unavailable = false;
                });
            },

            async refresh(detail) {
                const form = this.$root.querySelector('form');
                const checkIn = detail?.checkIn ?? form.querySelector('[name="check_in"]').value;
                const checkOut = detail?.checkOut ?? form.querySelector('[name="check_out"]').value;

                if (!checkIn || !checkOut) return;

                this.loading = true;
                try {
                    const params = new URLSearchParams({ check_in: checkIn, check_out: checkOut, guests: this.guests });
                    const res = await fetch(`${config.quoteUrl}?${params}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) throw new Error('quote failed');

                    const data = await res.json();
                    this.quote = data.quote;
                    this.unavailable = !data.available;
                } catch {
                    this.quote = null;
                    this.unavailable = false;
                } finally {
                    this.loading = false;
                }
            },

            money(value) {
                return new Intl.NumberFormat(undefined, {
                    style: 'currency',
                    currency: @js(config('booking.currency')),
                    maximumFractionDigits: 2,
                }).format(value ?? 0);
            },
        };
    }
</script>
@endpush
@endsection
