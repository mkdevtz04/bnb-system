@extends('layouts.app')

@section('title', 'Edit ' . $apartment->name . ' · Admin')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 900px;">

    <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 style="font-size:26px;">{{ $apartment->name }}</h1>
            <a href="{{ route('apartments.show', $apartment) }}" target="_blank"
               style="color:var(--brand-700); font-size:13.5px; text-decoration:none;">
                View public page <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
            </a>
        </div>
        <a href="{{ route('admin.apartments') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Back
        </a>
    </header>

    {{-- Close dates, Connected calendars and Rate calendar all live further down
         this same page, past the full edit form — easy to scroll past entirely,
         so this jumps straight there instead. --}}
    <nav class="flex flex-wrap gap-2 mb-6">
        <a href="#close-dates" class="badge badge-neutral" style="text-decoration:none;">
            <i class="fa-solid fa-calendar-xmark"></i> Close dates
        </a>
        <a href="#connected-calendars" class="badge badge-neutral" style="text-decoration:none;">
            <i class="fa-solid fa-arrows-rotate"></i> Connected calendars
        </a>
        <a href="#rate-calendar" class="badge badge-neutral" style="text-decoration:none;">
            <i class="fa-solid fa-tags"></i> Rate calendar
        </a>
    </nav>

    <form method="POST" action="{{ route('admin.apartments.update', $apartment) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        @include('admin.apartments._form', ['apartment' => $apartment])

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary btn-lg">Save changes</button>
            <a href="{{ route('admin.apartments') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

    {{-- Photo deletion forms, kept outside the edit form since forms cannot nest. --}}
    @foreach ($apartment->images as $image)
        <form id="del-img-{{ $image->id }}" method="POST"
              action="{{ route('admin.apartments.images.destroy', $image) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endforeach

    {{-- Manual calendar closures. These are now the only thing blocked_dates
         holds: confirmed bookings block their own nights automatically. --}}
    <section id="close-dates" class="card card-pad mt-8">
        <h2 style="font-size:18px; margin-bottom:6px;">Close dates</h2>
        <p class="muted" style="font-size:13.5px; margin-bottom:16px;">
            Take nights off sale for maintenance or personal use.
        </p>

        <form method="POST" action="{{ route('admin.apartments.block-dates', $apartment) }}"
              class="grid gap-3 sm:grid-cols-[1fr_1fr_1.4fr_auto] sm:items-end">
            @csrf
            <div class="field">
                <label class="field-label" for="start_date">From</label>
                <input type="date" id="start_date" name="start_date" required class="input"
                       min="{{ now()->toDateString() }}">
            </div>
            <div class="field">
                <label class="field-label" for="end_date">To</label>
                <input type="date" id="end_date" name="end_date" required class="input"
                       min="{{ now()->toDateString() }}">
            </div>
            <div class="field">
                <label class="field-label" for="reason">Reason (optional)</label>
                <input type="text" id="reason" name="reason" maxlength="255" class="input"
                       placeholder="e.g. Deep clean">
            </div>
            <button class="btn btn-primary">Close</button>
        </form>

        @if ($apartment->blockedDates->isNotEmpty())
            <div class="mt-5 flex flex-wrap gap-2" style="border-top:1px solid var(--ink-200); padding-top:16px;">
                @foreach ($apartment->blockedDates->sortBy('date') as $blocked)
                    <span class="badge badge-neutral">
                        {{ $blocked->date->format('j M Y') }}
                        @if ($blocked->reason)
                            <span class="muted">· {{ $blocked->reason }}</span>
                        @endif
                        <button type="button" aria-label="Reopen"
                                onclick="document.getElementById('unblock-{{ $blocked->id }}').submit()"
                                style="color:var(--red-600);">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </span>
                    <form id="unblock-{{ $blocked->id }}" method="POST"
                          action="{{ route('admin.apartments.unblock-date', $blocked) }}" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Connected calendars: other platforms read this property's own export
         URL, and this property reads theirs. bookings:sync-ical-feeds polls
         every feed below hourly. --}}
    <section id="connected-calendars" class="card card-pad mt-8">
        <h2 style="font-size:18px; margin-bottom:6px;">Connected calendars</h2>
        <p class="muted" style="font-size:13.5px; margin-bottom:16px;">
            Sync this property's availability with Airbnb, VRBO or any other calendar that
            can import or export an .ics feed.
        </p>

        <div class="field">
            <label class="field-label">This property's export link</label>
            <div class="flex gap-2">
                @php $icalUrl = route('apartments.ical.export', [$apartment, $apartment->ical_token]); @endphp
                <input type="text" readonly class="input" value="{{ $icalUrl }}"
                       onclick="this.select()" style="font-size:13px;">
                <button type="button" class="btn btn-secondary"
                        onclick="navigator.clipboard.writeText('{{ $icalUrl }}')">Copy</button>
            </div>
            <p class="field-hint">Paste this into Airbnb/VRBO's "import calendar" field so they stop offering nights booked here.</p>
        </div>

        @if ($apartment->icalFeeds->isNotEmpty())
            <div class="stack mt-5" style="border-top:1px solid var(--ink-200); padding-top:16px;">
                @foreach ($apartment->icalFeeds as $feed)
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <strong style="font-size:14px;">{{ $feed->label }}</strong>
                            <div class="muted" style="font-size:12.5px; word-break:break-all;">{{ $feed->url }}</div>
                            <div style="font-size:12px;">
                                @if ($feed->last_sync_status === 'ok')
                                    <span style="color:var(--green-600, #0a7a66);">
                                        <i class="fa-solid fa-check"></i> Synced {{ $feed->last_synced_at?->diffForHumans() }}
                                    </span>
                                @elseif ($feed->last_sync_status === 'error')
                                    <span style="color:var(--red-600);">
                                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $feed->last_sync_error }}
                                    </span>
                                @else
                                    <span class="muted">Not yet synced</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.apartments.ical-feeds.sync', $feed) }}">
                                @csrf
                                <button class="btn btn-ghost btn-sm" type="submit">Sync now</button>
                            </form>
                            <form method="POST" action="{{ route('admin.apartments.ical-feeds.destroy', $feed) }}"
                                  onsubmit="return confirm('Disconnect {{ $feed->label }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.apartments.ical-feeds.store', $apartment) }}"
              class="grid gap-3 sm:grid-cols-[1fr_2fr_auto] sm:items-end mt-5"
              style="border-top:1px solid var(--ink-200); padding-top:16px;">
            @csrf
            <div class="field">
                <label class="field-label" for="ical_label">Label</label>
                <input type="text" id="ical_label" name="label" required maxlength="60" class="input"
                       placeholder="e.g. Airbnb">
            </div>
            <div class="field">
                <label class="field-label" for="ical_url">Import URL (.ics)</label>
                <input type="url" id="ical_url" name="url" required maxlength="2048" class="input"
                       placeholder="https://www.airbnb.com/calendar/ical/....ics">
            </div>
            <button class="btn btn-primary">Connect</button>
        </form>
        @error('ical') <p class="field-error mt-2">{{ $message }}</p> @enderror
    </section>

    {{-- Per-date rate overrides. A date with no override here simply costs the
         base nightly rate set above; PricingService falls back to it. --}}
    <section id="rate-calendar" class="card card-pad mt-8">
        <h2 style="font-size:18px; margin-bottom:6px;">Rate calendar</h2>
        <p class="muted" style="font-size:13.5px; margin-bottom:16px;">
            Charge more for specific dates — a weekend, a holiday week — without changing the
            base nightly rate every other night still uses.
        </p>

        <form method="POST" action="{{ route('admin.apartments.rates.store', $apartment) }}">
            @csrf
            <div class="grid gap-3 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end">
                <div class="field">
                    <label class="field-label" for="rate_start">From</label>
                    <input type="date" id="rate_start" name="start_date" required class="input"
                           min="{{ now()->toDateString() }}">
                </div>
                <div class="field">
                    <label class="field-label" for="rate_end">To</label>
                    <input type="date" id="rate_end" name="end_date" required class="input"
                           min="{{ now()->toDateString() }}">
                </div>
                <div class="field">
                    <label class="field-label" for="rate_price">Nightly rate</label>
                    <input type="number" id="rate_price" name="price_per_night" required min="0" step="0.01"
                           class="input" placeholder="{{ $apartment->price_per_night }}">
                </div>
                <button class="btn btn-primary">Set rate</button>
            </div>

            <div class="field mt-3">
                <span class="field-label">Only on these days (optional)</span>
                <div class="flex flex-wrap gap-3 mt-1">
                    @foreach (['0' => 'Sun', '1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat'] as $value => $label)
                        <label class="flex items-center gap-1" style="font-size:13px;">
                            <input type="checkbox" name="days_of_week[]" value="{{ $value }}">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="field-hint">Leave all unchecked to price every date in the range.</p>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.apartments.rates.destroy', $apartment) }}"
              class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end mt-5"
              style="border-top:1px solid var(--ink-200); padding-top:16px;">
            @csrf @method('DELETE')
            <div class="field">
                <label class="field-label" for="clear_start">From</label>
                <input type="date" id="clear_start" name="start_date" required class="input">
            </div>
            <div class="field">
                <label class="field-label" for="clear_end">To</label>
                <input type="date" id="clear_end" name="end_date" required class="input">
            </div>
            <button class="btn btn-ghost">Revert to base rate</button>
        </form>
        @error('rate') <p class="field-error mt-2">{{ $message }}</p> @enderror
    </section>
</div>
@endsection
