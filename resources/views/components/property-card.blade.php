@props([
    'apartment',
    'checkIn' => null,
    'checkOut' => null,
    'nights' => null,
])

@php
    $image = $apartment->images->first();
    $quote = $apartment->stay_quote ?? null;
    $params = array_filter([
        'check_in' => $checkIn?->toDateString(),
        'check_out' => $checkOut?->toDateString(),
    ]);
    // Carry the chosen dates through to the property page so the calendar and
    // the price the guest just saw are still selected when they land.
    $url = route('apartments.show', array_merge([$apartment], $params));
@endphp

<article class="card card-hover overflow-hidden flex flex-col sm:flex-row">
    <a href="{{ $url }}" class="prop-media block sm:w-64 md:w-72 shrink-0" style="aspect-ratio: 4/3;">
        @if ($image)
            <img src="{{ Storage::url($image->image_path) }}" alt="{{ $apartment->name }}" loading="lazy">
        @else
            <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-4 p-5 sm:flex-row">
        <div class="flex-1 min-w-0">
            <h3 class="truncate" style="font-size:19px; margin-bottom:4px;">
                <a href="{{ $url }}"
                   style="color:var(--brand-700); text-decoration:none;">{{ $apartment->name }}</a>
            </h3>

            <p class="muted flex items-center gap-1.5" style="font-size:13px;">
                <i class="fa-solid fa-location-dot"></i>{{ $apartment->location_line }}
            </p>

            <div class="mt-3 flex flex-wrap gap-1.5">
                <span class="badge badge-neutral">{{ $apartment->bedrooms }} bed</span>
                <span class="badge badge-neutral">{{ $apartment->bathrooms }} bath</span>
                <span class="badge badge-neutral">Sleeps {{ $apartment->max_guests }}</span>
                <span class="badge badge-neutral capitalize">{{ $apartment->floor }} floor</span>
            </div>

            <p class="muted mt-3 hidden sm:block" style="font-size:13.5px; line-height:1.55;">
                {{ Str::limit(strip_tags($apartment->description), 130) }}
            </p>
        </div>

        <div class="flex flex-col items-start gap-3 sm:w-48 sm:items-end sm:text-right sm:border-l sm:pl-5"
             style="border-color:var(--ink-200);">
            <x-score-badge
                :score="$apartment->review_score"
                :count="$apartment->review_count"
                :label="$apartment->review_label"
                size="sm"
                class="sm:flex-row-reverse sm:text-right" />

            <div class="mt-auto w-full">
                @if ($quote)
                    <div class="muted" style="font-size:12.5px;">
                        {{ $quote->nights }} {{ Str::plural('night', $quote->nights) }}
                    </div>
                    <div class="price font-bold" style="font-size:24px; color:var(--ink-900);">
                        {{ \App\Support\Money::format($quote->total, $quote->currency) }}
                    </div>
                    <div class="muted" style="font-size:12px;">Includes fees &amp; taxes</div>
                @else
                    <div class="price font-bold" style="font-size:22px; color:var(--ink-900);">
                        {{ \App\Support\Money::format($apartment->price_per_night, config('booking.currency')) }}
                    </div>
                    <div class="muted" style="font-size:12px;">per night</div>
                @endif

                <a href="{{ $url }}"
                   class="btn btn-primary btn-block mt-3">
                    See availability <i class="fa-solid fa-chevron-right" style="font-size:11px;"></i>
                </a>
            </div>
        </div>
    </div>
</article>
