@extends('layouts.app')

@section('title', 'Confirm your booking · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 24px 16px 48px; max-width: 980px;">

    {{-- Funnel progress, so the guest knows how many steps are left. --}}
    <ol class="mb-6 flex items-center gap-2" style="font-size:13px;">
        <li class="badge badge-success"><i class="fa-solid fa-check"></i> Your stay</li>
        <li class="muted">—</li>
        <li class="badge badge-info">2. Review &amp; confirm</li>
        <li class="muted">—</li>
        <li class="badge badge-neutral">3. Booked</li>
    </ol>

    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">

        <div class="stack">
            <section class="card card-pad">
                <h1 style="font-size:24px; margin-bottom:6px;">Review your booking</h1>
                <p class="muted" style="font-size:14.5px;">
                    Nothing is charged now. The host confirms your request, usually within a few hours.
                </p>
            </section>

            <section class="card card-pad">
                <h2 style="font-size:18px; margin-bottom:16px;">Your stay</h2>
                <dl class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="field-label">Check-in</dt>
                        <dd style="font-size:15px; font-weight:600;">{{ $checkIn->format('D, j M Y') }}</dd>
                        <dd class="muted" style="font-size:13px;">
                            From {{ \Carbon\Carbon::parse($apartment->check_in_from)->format('g:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="field-label">Check-out</dt>
                        <dd style="font-size:15px; font-weight:600;">{{ $checkOut->format('D, j M Y') }}</dd>
                        <dd class="muted" style="font-size:13px;">
                            Until {{ \Carbon\Carbon::parse($apartment->check_out_until)->format('g:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="field-label">Guests</dt>
                        <dd style="font-size:15px; font-weight:600;">
                            {{ $quote->guests }} {{ Str::plural('guest', $quote->guests) }}
                        </dd>
                        <dd class="muted" style="font-size:13px;">Sleeps up to {{ $apartment->max_guests }}</dd>
                    </div>
                </dl>
            </section>

            <section class="card card-pad">
                <h2 style="font-size:18px; margin-bottom:16px;">Who's staying</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="field-label">Name</span>
                        <p style="font-size:15px;">{{ auth()->user()->display_name }}</p>
                    </div>
                    <div>
                        <span class="field-label">Email</span>
                        <p style="font-size:15px;">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <span class="field-label">Phone</span>
                        <p style="font-size:15px;">
                            {{ auth()->user()->phone ?: '—' }}
                            @unless (auth()->user()->phone)
                                <a href="{{ route('profile.edit') }}" style="color:var(--brand-700); font-size:13px;">Add one</a>
                            @endunless
                        </p>
                    </div>
                </div>
            </section>

            <form method="POST" action="{{ route('bookings.store') }}" class="card card-pad">
                @csrf
                <input type="hidden" name="apartment_id" value="{{ $apartment->id }}">
                <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
                <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
                <input type="hidden" name="guests" value="{{ $quote->guests }}">

                <label class="flex items-start gap-3" style="font-size:14px; line-height:1.55;">
                    <input type="checkbox" name="terms" required class="mt-1" style="width:17px; height:17px;">
                    <span>
                        I agree to the house rules and understand this booking is a request that the
                        host confirms. Free cancellation any time before check-in.
                    </span>
                </label>

                <button type="submit" class="btn btn-primary btn-lg btn-block mt-5">
                    <i class="fa-solid fa-lock"></i> Request to book
                </button>
            </form>
        </div>

        {{-- Summary --}}
        <aside>
            <div class="card overflow-hidden" style="position: sticky; top: calc(var(--nav-h) + 16px);">
                @if ($apartment->images->isNotEmpty())
                    <div class="prop-media" style="height: 150px;">
                        <img src="{{ Storage::url($apartment->images->first()->image_path) }}" alt="{{ $apartment->name }}">
                    </div>
                @endif

                <div class="card-pad">
                    <h3 style="font-size:17px; margin-bottom:4px;">{{ $apartment->name }}</h3>
                    <p class="muted" style="font-size:13px;">
                        <i class="fa-solid fa-location-dot"></i> {{ $apartment->location_line }}
                    </p>

                    <div class="mt-5" style="border-top:1px solid var(--ink-200); padding-top:16px;">
                        <h4 class="field-label">Price breakdown</h4>

                        {{-- Every figure below comes from PricingService, the same
                             object that populates the booking row on submit. --}}
                        <div class="mt-2 flex justify-between" style="font-size:14px;">
                            <span>{{ \App\Support\Money::format($quote->nightlyRate, $quote->currency) }} &times; {{ $quote->nights }} {{ Str::plural('night', $quote->nights) }}</span>
                            <span class="price">{{ \App\Support\Money::format($quote->subtotal, $quote->currency) }}</span>
                        </div>

                        @if ($quote->cleaningFee > 0)
                            <div class="mt-2 flex justify-between" style="font-size:14px;">
                                <span>Cleaning fee</span>
                                <span class="price">{{ \App\Support\Money::format($quote->cleaningFee, $quote->currency) }}</span>
                            </div>
                        @endif

                        <div class="mt-2 flex justify-between" style="font-size:14px;">
                            <span>Service fee</span>
                            <span class="price">{{ \App\Support\Money::format($quote->serviceFee, $quote->currency) }}</span>
                        </div>

                        <div class="mt-2 flex justify-between" style="font-size:14px;">
                            <span>Taxes</span>
                            <span class="price">{{ \App\Support\Money::format($quote->taxes, $quote->currency) }}</span>
                        </div>

                        <div class="mt-4 flex justify-between font-bold"
                             style="font-size:18px; border-top:1px solid var(--ink-200); padding-top:14px;">
                            <span>Total</span>
                            <span class="price">{{ \App\Support\Money::format($quote->total, $quote->currency) }}</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4" style="font-size:13px;">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        <span>Pay the host directly at check-in.</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
