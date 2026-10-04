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
    <section class="card card-pad mt-8">
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
</div>
@endsection
