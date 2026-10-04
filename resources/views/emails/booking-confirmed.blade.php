@php
    $apartment = $booking->apartment;
    $money = fn ($amount) => \App\Support\Money::format($amount, $booking->currency);

    $time = fn ($value) => \Carbon\Carbon::parse($value)->format('g:i A');
    $sub = fn ($text) => '<br><span style="font-size:12px;color:#6b8c8a;font-weight:400">'.e($text).'</span>';

    $rows = [
        'Property' => e($apartment->name),
        'Check-in' => e($booking->check_in->format('D, j M Y')).$sub('from '.$time($apartment->check_in_from)),
        'Check-out' => e($booking->check_out->format('D, j M Y')).$sub('until '.$time($apartment->check_out_until)),
        'Guests' => e($booking->guests.' '.Str::plural('guest', $booking->guests)),
        'Nights' => e($booking->nights.' '.Str::plural('night', $booking->nights)),
    ];
@endphp

<x-mail.layout
    heading="Your stay is confirmed"
    :greeting="'Hi ' . $booking->user->display_name . '!'"
    icon="check"
    tone="brand"
    :preview="'Confirmed: ' . $apartment->name . ', ' . $booking->check_in->format('j M Y')">

    <p style="margin:0 0 20px; font-size:15px; line-height:1.65; color:#44605e; text-align:center;">
        Good news — the host has confirmed your booking at
        <strong style="color:#14303a;">{{ $apartment->name }}</strong>.
        Everything below is settled; just turn up on the day.
    </p>

    <x-mail.reference :reference="$booking->display_reference" />

    <x-mail.details :rows="$rows" :total="$money($booking->total_price)" />

    <x-mail.button :url="route('bookings.confirmation', $booking)" label="View your booking" />

    <p style="margin:0 0 8px; font-size:13px; line-height:1.65; color:#6b8c8a; text-align:center;">
        You'll pay the host directly at the property — nothing is charged now.
        Need to change something? Reply to this email, or message the host from your booking.
    </p>
</x-mail.layout>
