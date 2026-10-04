@extends('layouts.app')

@section('title', 'Reviews for ' . $apartment->name . ' · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 840px;">

    <nav aria-label="Breadcrumb" class="muted mb-4" style="font-size:13px;">
        <a href="{{ route('apartments.show', $apartment) }}" style="color:var(--brand-700); text-decoration:none;">
            {{ $apartment->name }}
        </a>
        <span class="mx-1.5">/</span><span>Reviews</span>
    </nav>

    <header class="card card-pad mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 style="font-size:24px;">Guest reviews</h1>
            <p class="muted" style="font-size:14px;">{{ $apartment->name }}</p>
        </div>
        <x-score-badge :score="$apartment->review_score" :count="$apartment->review_count"
                       :label="$apartment->review_label" size="lg" class="flex-row-reverse" />
    </header>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach (['' => 'Most recent', 'highest' => 'Highest scored', 'lowest' => 'Lowest scored'] as $key => $label)
            <a href="{{ route('apartments.reviews', array_filter(['apartment' => $apartment->slug ?? $apartment->id, 'sort' => $key])) }}"
               class="chip {{ request('sort', '') === $key ? 'chip-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="stack">
        @forelse ($reviews as $review)
            <article class="card card-pad">
                <div class="mb-3 flex items-center gap-3">
                    <span class="avatar">{{ $review->user->initials }}</span>
                    <div class="flex-1">
                        <div class="font-semibold" style="font-size:14.5px;">{{ $review->user->display_name }}</div>
                        <div class="muted" style="font-size:12.5px;">{{ $review->created_at->format('j M Y') }}</div>
                    </div>
                    <span class="score">{{ number_format($review->overall, 1) }}</span>
                </div>

                @if ($review->title)
                    <h2 style="font-size:16px; font-family:var(--font-body); margin-bottom:8px;">{{ $review->title }}</h2>
                @endif

                @if ($review->liked)
                    <p style="font-size:14.5px; line-height:1.6;">
                        <i class="fa-solid fa-thumbs-up" style="color:var(--green-600);"></i> {{ $review->liked }}
                    </p>
                @endif

                @if ($review->disliked)
                    <p class="muted mt-2" style="font-size:14.5px; line-height:1.6;">
                        <i class="fa-solid fa-thumbs-down"></i> {{ $review->disliked }}
                    </p>
                @endif

                <div class="mt-4 grid gap-x-6 gap-y-2 sm:grid-cols-3"
                     style="border-top:1px solid var(--ink-200); padding-top:14px;">
                    @foreach ($review->scores as $score)
                        <div class="flex items-center justify-between" style="font-size:12.5px;">
                            <span class="muted">{{ $score['label'] }}</span>
                            <strong class="price">{{ $score['score'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="card card-pad text-center" style="padding:48px 24px;">
                <i class="fa-regular fa-comment" style="font-size:36px; color:var(--ink-300);"></i>
                <p class="muted mt-3" style="font-size:14.5px;">No reviews for this property yet.</p>
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        <div class="mt-8">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
