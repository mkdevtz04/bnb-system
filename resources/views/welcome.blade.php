@extends('layouts.app')

@section('title', 'CoastalCharmz · Serviced apartments, booked direct')

@section('content')

{{-- Hero. White-led: a soft teal wash instead of the old navy panel, with the
     photograph kept faint behind it so the page still has depth. Everything is
     centred on the search, which is the one thing a visitor came here to use. --}}
<section style="position:relative; background:var(--surface); overflow:hidden;
                border-bottom:1px solid var(--ink-200);">
    <div style="position:absolute; inset:0; background-image:url('{{ asset('images/luxury-decor.jpg') }}');
                background-size:cover; background-position:center; opacity:.10;"></div>
    <div style="position:absolute; inset:0;
                background:linear-gradient(180deg, var(--brand-50) 0%, rgba(255,255,255,.72) 55%, var(--surface) 100%);"></div>

    <div class="shell" style="position:relative; padding: 72px 16px 88px; text-align:center;">
        <span class="badge badge-brand" style="margin-bottom:18px;">
            <i class="fa-solid fa-location-dot"></i> Book direct with the host
        </span>

        <h1 style="font-size:clamp(32px, 5vw, 52px); line-height:1.1; max-width:20ch; margin:0 auto;">
            Apartments by the coast, booked direct
        </h1>

        <p class="muted" style="font-size:clamp(16px,2vw,19px); margin:16px auto 0; max-width:54ch; line-height:1.6;">
            No middleman and no hidden charges. You see the full price, fees and taxes
            included, before you commit to anything.
        </p>

        {{-- .searchbar-wrap centres this. --}}
        <div style="margin-top:36px;">
            <x-search-bar />
        </div>

        <div class="mt-7 flex flex-wrap justify-center gap-x-7 gap-y-2 muted" style="font-size:13.5px;">
            <span><i class="fa-solid fa-circle-check" style="color:var(--brand-600);"></i> Free cancellation</span>
            <span><i class="fa-solid fa-circle-check" style="color:var(--brand-600);"></i> No booking fee</span>
            <span><i class="fa-solid fa-circle-check" style="color:var(--brand-600);"></i> Pay at the property</span>
        </div>
    </div>
</section>

{{-- Properties --}}
<section id="apartments" class="shell" style="padding: 56px 16px 0;">
    <header class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 style="font-size:27px;">Our apartments</h2>
            <p class="muted" style="font-size:14.5px;">Every one of them managed by us, in person.</p>
        </div>
        <a href="{{ route('apartments.search') }}" class="btn btn-secondary">See all &amp; check dates</a>
    </header>

    @if ($apartments->isEmpty())
        <div class="card card-pad text-center" style="padding:48px 24px;">
            <i class="fa-solid fa-building" style="font-size:36px; color:var(--ink-300);"></i>
            <p class="muted mt-4" style="font-size:15px;">No properties are listed just yet. Check back soon.</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($apartments as $apartment)
                @php $image = $apartment->images->first(); @endphp
                <article class="card card-hover overflow-hidden">
                    <a href="{{ route('apartments.show', $apartment) }}" class="prop-media block" style="aspect-ratio:3/2;">
                        @if ($image)
                            <img src="{{ Storage::url($image->image_path) }}" alt="{{ $apartment->name }}" loading="lazy">
                        @else
                            <span class="prop-empty"><i class="fa-solid fa-building"></i></span>
                        @endif
                    </a>

                    <div class="card-pad">
                        <div class="mb-2 flex items-start justify-between gap-3">
                            <h3 style="font-size:17px; line-height:1.3;">
                                <a href="{{ route('apartments.show', $apartment) }}"
                                   style="color:var(--brand-700); text-decoration:none;">{{ $apartment->name }}</a>
                            </h3>
                            @if ($apartment->review_score)
                                <span class="score score-sm">{{ number_format($apartment->review_score, 1) }}</span>
                            @endif
                        </div>

                        <p class="muted" style="font-size:13px;">
                            <i class="fa-solid fa-location-dot"></i> {{ $apartment->location_line }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span class="badge badge-neutral">{{ $apartment->bedrooms }} bed</span>
                            <span class="badge badge-neutral">Sleeps {{ $apartment->max_guests }}</span>
                        </div>

                        <div class="mt-4 flex items-end justify-between"
                             style="border-top:1px solid var(--ink-200); padding-top:14px;">
                            <div>
                                <div class="price font-bold" style="font-size:20px;">
                                    {{ \App\Support\Money::format($apartment->price_per_night, config('booking.currency')) }}
                                </div>
                                <div class="muted" style="font-size:12px;">per night</div>
                            </div>
                            <a href="{{ route('apartments.show', $apartment) }}" class="btn btn-primary btn-sm">Check dates</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

{{-- Why book direct --}}
<section id="amenities" class="shell" style="padding: 56px 16px 0;">
    <h2 class="text-center" style="font-size:27px;">Why book with us</h2>
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['fa-tags', 'The price you see', 'Fees and taxes are shown up front, before you book.'],
            ['fa-calendar-check', 'Free cancellation', 'Change your mind any time before check-in.'],
            ['fa-key', 'Managed in person', 'Same-day response from the people who run the building.'],
            ['fa-shield-halved', 'Pay at the property', 'No card details taken to hold a booking.'],
        ] as [$icon, $title, $copy])
            <div class="card card-pad text-center">
                <div class="mx-auto mb-3 flex items-center justify-center"
                     style="width:48px; height:48px; border-radius:var(--r-full); background:var(--brand-50);">
                    <i class="fa-solid {{ $icon }}" style="color:var(--brand-700); font-size:19px;"></i>
                </div>
                <h3 style="font-size:16px; font-family:var(--font-body); margin-bottom:6px;">{{ $title }}</h3>
                <p class="muted" style="font-size:13.5px; line-height:1.6;">{{ $copy }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- Contact. The route and controller for this existed from the start; the form
     itself was never built, so there was no way to reach either. --}}
<section id="contact" class="shell" style="padding: 56px 16px 0; max-width: 760px;">
    <div class="card card-pad">
        <h2 style="font-size:25px; margin-bottom:6px;">Message the host</h2>
        <p class="muted" style="font-size:14.5px; margin-bottom:20px;">
            Questions about a stay, a group booking, or a long let? Ask away.
        </p>

        <form method="POST" action="{{ route('contact.store') }}" class="stack">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="c-name">Your name</label>
                    <input type="text" id="c-name" name="name" required maxlength="255"
                           class="input @error('name') input-error @enderror"
                           value="{{ old('name', auth()->user()?->display_name) }}">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="c-email">Email</label>
                    <input type="email" id="c-email" name="email" required maxlength="255"
                           class="input @error('email') input-error @enderror"
                           value="{{ old('email', auth()->user()?->email) }}">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="c-subject">Subject <span class="muted">(optional)</span></label>
                <input type="text" id="c-subject" name="subject" maxlength="255"
                       class="input @error('subject') input-error @enderror" value="{{ old('subject') }}">
                @error('subject') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label class="field-label" for="c-message">Message</label>
                <textarea id="c-message" name="message" rows="5" required maxlength="5000"
                          class="textarea @error('message') input-error @enderror">{{ old('message') }}</textarea>
                @error('message') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa-regular fa-paper-plane"></i> Send message
            </button>
        </form>
    </div>
</section>

@guest
    @include('partials.auth-modal')
@endguest
@endsection
