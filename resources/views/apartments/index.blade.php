@extends('layouts.app')

@section('title', 'Search results · CoastalCharmz')

@section('content')
{{-- Sticky search bar, so refining a search never means scrolling back up.
     White band with a border rather than a dark panel, and the bar itself stays
     centred via .searchbar-wrap. --}}
<div style="background: var(--surface); border-bottom: 1px solid var(--ink-200);
            position: sticky; top: var(--nav-h); z-index: 30; box-shadow: var(--shadow-sm);">
    <div class="shell-wide" style="padding: 14px 16px;">
        <x-search-bar :check-in="$checkIn" :check-out="$checkOut" />
    </div>
</div>

<div class="shell-wide" style="padding: 24px 16px 48px;">
    <div class="grid gap-6 lg:grid-cols-[260px_1fr]">

        {{-- Filters --}}
        <aside x-data="{ open: false }">
            <button type="button" class="btn btn-secondary btn-block lg:hidden" @click="open = !open">
                <i class="fa-solid fa-sliders"></i> Filters
            </button>

            <form method="GET" action="{{ route('apartments.search') }}"
                  class="card card-pad mt-3 lg:mt-0 lg:!block" x-show="open" x-cloak
                  x-bind:class="{ 'hidden': !open }"
                  style="position: sticky; top: calc(var(--nav-h) + 92px);">

                {{-- Preserve the current stay while changing filters. --}}
                <input type="hidden" name="check_in" value="{{ request('check_in') }}">
                <input type="hidden" name="check_out" value="{{ request('check_out') }}">
                <input type="hidden" name="guests" value="{{ request('guests') }}">

                <h3 style="font-size:16px; margin-bottom:16px;">Filter by</h3>

                <div class="field mb-5">
                    <span class="field-label">Price per night</span>
                    <div class="flex items-center gap-2">
                        <input type="number" name="min_price" min="0" class="input" placeholder="Min"
                               value="{{ request('min_price') }}">
                        <span class="muted">–</span>
                        <input type="number" name="max_price" min="0" class="input" placeholder="Max"
                               value="{{ request('max_price') }}">
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="field-label" for="f-score">Review score</label>
                    <select name="min_score" id="f-score" class="select">
                        <option value="">Any score</option>
                        <option value="9" @selected(request('min_score') == '9')>Exceptional: 9+</option>
                        <option value="8" @selected(request('min_score') == '8')>Very good: 8+</option>
                        <option value="7" @selected(request('min_score') == '7')>Good: 7+</option>
                    </select>
                </div>

                <div class="field mb-5">
                    <label class="field-label" for="f-beds">Bedrooms</label>
                    <select name="bedrooms" id="f-beds" class="select">
                        <option value="">Any</option>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" @selected(request('bedrooms') == $i)>{{ $i }}+</option>
                        @endfor
                    </select>
                </div>

                <div class="field mb-5">
                    <label class="field-label" for="f-floor">Floor</label>
                    <select name="floor" id="f-floor" class="select">
                        <option value="">Any</option>
                        <option value="ground" @selected(request('floor') === 'ground')>Ground</option>
                        <option value="upper" @selected(request('floor') === 'upper')>Upper</option>
                    </select>
                </div>

                <button class="btn btn-primary btn-block">Apply filters</button>

                @if (request()->hasAny(['min_price', 'max_price', 'min_score', 'bedrooms', 'floor']))
                    <a href="{{ route('apartments.search', array_filter([
                        'check_in' => request('check_in'),
                        'check_out' => request('check_out'),
                        'guests' => request('guests'),
                    ])) }}" class="btn btn-ghost btn-block mt-2">Clear filters</a>
                @endif
            </form>
        </aside>

        {{-- Results --}}
        <section>
            <header class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 style="font-size:26px;">
                        {{ $apartments->total() }} {{ Str::plural('property', $apartments->total()) }} available
                    </h1>
                    @if ($checkIn && $checkOut)
                        <p class="muted" style="font-size:14px;">
                            {{ $checkIn->format('D, j M') }} – {{ $checkOut->format('D, j M Y') }}
                            · {{ $nights }} {{ Str::plural('night', $nights) }}
                        </p>
                    @else
                        <p class="muted" style="font-size:14px;">Add dates to see live availability and total prices.</p>
                    @endif
                </div>

                <form method="GET" class="flex items-center gap-2">
                    @foreach (request()->except('sort', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label class="field-label !mb-0" for="sort">Sort</label>
                    <select name="sort" id="sort" class="select" style="width:auto;" onchange="this.form.submit()">
                        <option value="recommended" @selected(request('sort', 'recommended') === 'recommended')>Recommended</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                        <option value="score" @selected(request('sort') === 'score')>Best reviewed</option>
                    </select>
                </form>
            </header>

            <div class="stack">
                @forelse ($apartments as $apartment)
                    <x-property-card :apartment="$apartment" :check-in="$checkIn" :check-out="$checkOut" :nights="$nights" />
                @empty
                    <div class="card card-pad text-center" style="padding: 56px 24px;">
                        <i class="fa-regular fa-calendar-xmark" style="font-size:40px; color:var(--ink-300);"></i>
                        <h2 style="font-size:20px; margin:16px 0 8px;">Nothing free for those dates</h2>
                        <p class="muted" style="font-size:14.5px;">Try shifting your dates or relaxing the filters.</p>
                        <a href="{{ route('apartments.search') }}" class="btn btn-secondary mt-5">Start a new search</a>
                    </div>
                @endforelse
            </div>

            @if ($apartments->hasPages())
                <div class="mt-8">{{ $apartments->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
