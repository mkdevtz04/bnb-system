@extends('layouts.app')

@section('title', 'Review your stay · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 720px;">

    <h1 style="font-size:26px; margin-bottom:6px;">Review your stay</h1>
    <p class="muted" style="font-size:14.5px;">
        {{ $booking->apartment->name }} ·
        {{ $booking->check_in->format('j M') }} – {{ $booking->check_out->format('j M Y') }}
    </p>

    <form method="POST" action="{{ route('reviews.store', $booking) }}" class="card card-pad mt-6 stack"
          x-data="{ scores: {} }">
        @csrf

        <section>
            <h2 style="font-size:18px; margin-bottom:4px;">Rate each part of your stay</h2>
            <p class="muted" style="font-size:13.5px; margin-bottom:18px;">
                1 is poor, 10 is excellent. Your overall score is the average of these.
            </p>

            <div class="stack">
                @foreach ($categories as $key => $label)
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="field-label !mb-0" for="r-{{ $key }}">{{ $label }}</label>
                            <span class="score score-sm" x-text="scores['{{ $key }}'] ?? '—'"></span>
                        </div>
                        <input type="range" id="r-{{ $key }}" name="{{ $key }}" min="1" max="10" step="1"
                               value="{{ old($key, 8) }}" class="w-full"
                               x-init="scores['{{ $key }}'] = $el.value"
                               @input="scores['{{ $key }}'] = $event.target.value"
                               style="accent-color: var(--brand-700);">
                        <div class="muted flex justify-between" style="font-size:11px;">
                            <span>1</span><span>10</span>
                        </div>
                        @error($key) <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <section style="border-top:1px solid var(--ink-200); padding-top:20px;">
            <div class="field">
                <label class="field-label" for="title">Sum it up <span class="muted">(optional)</span></label>
                <input type="text" id="title" name="title" maxlength="120" class="input"
                       placeholder="e.g. Spotless and perfectly located" value="{{ old('title') }}">
                @error('title') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field mt-4">
                <label class="field-label" for="liked">What did you like?</label>
                <textarea id="liked" name="liked" rows="4" maxlength="2000" class="textarea"
                          placeholder="The things that made the stay good.">{{ old('liked') }}</textarea>
                @error('liked') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field mt-4">
                <label class="field-label" for="disliked">Anything that could be better?</label>
                <textarea id="disliked" name="disliked" rows="4" maxlength="2000" class="textarea"
                          placeholder="Honest feedback helps the host and the next guest.">{{ old('disliked') }}</textarea>
                @error('disliked') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary btn-lg">Publish review</button>
            <a href="{{ route('bookings.history') }}" class="btn btn-ghost">Not now</a>
        </div>
    </form>
</div>
@endsection
