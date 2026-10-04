@extends('layouts.app')

@section('title', 'Properties · Admin')

@section('content')
<div class="shell-wide" style="padding: 32px 16px 48px;">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 style="font-size:28px;">Properties</h1>
            <p class="muted" style="font-size:14.5px;">{{ $apartments->total() }} listed</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.apartments.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add property
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                <i class="fa-solid fa-chevron-left"></i> Dashboard
            </a>
        </div>
    </header>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($apartments as $apartment)
            <article class="card overflow-hidden">
                <div class="prop-media" style="aspect-ratio:3/2;">
                    @if ($apartment->images->isNotEmpty())
                        <img src="{{ Storage::url($apartment->images->first()->image_path) }}"
                             alt="{{ $apartment->name }}" loading="lazy">
                    @else
                        <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
                    @endif

                    <span class="badge {{ $apartment->status === 'available' ? 'badge-success' : 'badge-warning' }}"
                          style="position:absolute; top:10px; left:10px;">
                        {{ $apartment->status === 'available' ? 'On sale' : 'Maintenance' }}
                    </span>
                </div>

                <div class="card-pad">
                    <div class="flex items-start justify-between gap-3">
                        <h2 style="font-size:17px; line-height:1.3;">{{ $apartment->name }}</h2>
                        @if ($apartment->reviews_avg_overall)
                            <span class="score score-sm">{{ number_format($apartment->reviews_avg_overall, 1) }}</span>
                        @endif
                    </div>

                    <p class="muted mt-1" style="font-size:13px;">
                        <i class="fa-solid fa-location-dot"></i> {{ $apartment->location_line }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="badge badge-neutral">{{ $apartment->bedrooms }} bed</span>
                        <span class="badge badge-neutral">Sleeps {{ $apartment->max_guests }}</span>
                        <span class="badge badge-info">{{ $apartment->bookings_count }} bookings</span>
                    </div>

                    <div class="mt-4 flex items-center justify-between"
                         style="border-top:1px solid var(--ink-200); padding-top:14px;">
                        <div class="price font-bold" style="font-size:19px;">
                            {{ \App\Support\Money::format($apartment->price_per_night) }}
                            <span class="muted font-normal" style="font-size:12px;">/ night</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.apartments.edit', $apartment) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>

                        <form method="POST" action="{{ route('admin.apartments.toggle-status', $apartment) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-ghost btn-sm">
                                {{ $apartment->status === 'available' ? 'Take off sale' : 'Put on sale' }}
                            </button>
                        </form>

                        <a href="{{ route('apartments.show', $apartment) }}" class="btn btn-ghost btn-sm" target="_blank">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>

                        <form method="POST" action="{{ route('admin.apartments.destroy', $apartment) }}"
                              onsubmit="return confirm('Delete {{ $apartment->name }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost btn-sm" style="color:var(--red-600);">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="card card-pad text-center sm:col-span-2 lg:col-span-3" style="padding:56px 24px;">
                <i class="fa-solid fa-building" style="font-size:40px; color:var(--ink-300);"></i>
                <h2 style="font-size:20px; margin:16px 0 8px;">No properties yet</h2>
                <p class="muted" style="font-size:14.5px;">Add your first property to start taking bookings.</p>
                <a href="{{ route('admin.apartments.create') }}" class="btn btn-primary mt-5">
                    <i class="fa-solid fa-plus"></i> Add property
                </a>
            </div>
        @endforelse
    </div>

    @if ($apartments->hasPages())
        <div class="mt-8">{{ $apartments->links() }}</div>
    @endif
</div>
@endsection
