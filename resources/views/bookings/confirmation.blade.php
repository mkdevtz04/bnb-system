@extends('layouts.app')

@section('title', 'Booking ' . $booking->display_reference . ' · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 760px;">

    <div class="card card-pad text-center" style="padding: 40px 24px;">
        <div class="mx-auto mb-4 flex items-center justify-center"
             style="width:64px; height:64px; border-radius:var(--r-full); background:var(--green-50);">
            <i class="fa-solid fa-check" style="font-size:26px; color:var(--green-600);"></i>
        </div>

        <h1 style="font-size:26px;">You're booked in</h1>
        <p class="muted mt-2" style="font-size:15px;">
            We've sent the details to {{ $booking->user->email }}.
        </p>

        <div class="mt-5 inline-flex items-center gap-2 panel" style="padding:10px 18px;">
            <span class="muted" style="font-size:13px;">Confirmation</span>
            <strong class="price" style="font-size:16px; letter-spacing:.06em;">{{ $booking->display_reference }}</strong>
        </div>

        <div class="mt-4">
            @if ($booking->isPending())
                <span class="badge badge-warning"><i class="fa-regular fa-clock"></i> Awaiting host confirmation</span>
            @elseif ($booking->isConfirmed())
                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
            @else
                <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Cancelled</span>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden mt-6">
        @if ($booking->apartment->images->isNotEmpty())
            <div class="prop-media" style="height: 200px;">
                <img src="{{ Storage::url($booking->apartment->images->first()->image_path) }}"
                     alt="{{ $booking->apartment->name }}">
            </div>
        @endif

        <div class="card-pad">
            <h2 style="font-size:20px; margin-bottom:4px;">{{ $booking->apartment->name }}</h2>
            <p class="muted" style="font-size:13.5px;">
                <i class="fa-solid fa-location-dot"></i> {{ $booking->apartment->location_line }}
            </p>

            <dl class="mt-5 grid gap-5 sm:grid-cols-3" style="border-top:1px solid var(--ink-200); padding-top:18px;">
                <div>
                    <dt class="field-label">Check-in</dt>
                    <dd style="font-size:15px; font-weight:600;">{{ $booking->check_in->format('D, j M Y') }}</dd>
                </div>
                <div>
                    <dt class="field-label">Check-out</dt>
                    <dd style="font-size:15px; font-weight:600;">{{ $booking->check_out->format('D, j M Y') }}</dd>
                </div>
                <div>
                    <dt class="field-label">Guests</dt>
                    <dd style="font-size:15px; font-weight:600;">{{ $booking->guests }}</dd>
                </div>
            </dl>

            <div class="mt-5" style="border-top:1px solid var(--ink-200); padding-top:18px;">
                <div class="flex justify-between" style="font-size:14px;">
                    <span>{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</span>
                    <span class="price">{{ \App\Support\Money::format($booking->subtotal, $booking->currency) }}</span>
                </div>
                <div class="mt-2 flex justify-between" style="font-size:14px;">
                    <span>Fees</span>
                    <span class="price">{{ \App\Support\Money::format($booking->service_fee, $booking->currency) }}</span>
                </div>
                <div class="mt-2 flex justify-between" style="font-size:14px;">
                    <span>Taxes</span>
                    <span class="price">{{ \App\Support\Money::format($booking->taxes, $booking->currency) }}</span>
                </div>
                <div class="mt-3 flex justify-between font-bold"
                     style="font-size:18px; border-top:1px solid var(--ink-200); padding-top:12px;">
                    <span>Total</span>
                    <span class="price">{{ \App\Support\Money::format($booking->total_price, $booking->currency) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('bookings.history') }}" class="btn btn-primary">My bookings</a>
        <a href="{{ route('messages.show', $booking) }}" class="btn btn-secondary">
            <i class="fa-regular fa-comments"></i> Message the host
        </a>
        <a href="{{ route('apartments.search') }}" class="btn btn-ghost">Book another stay</a>
    </div>
</div>
@endsection
