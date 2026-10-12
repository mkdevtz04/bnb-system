@php
    $apartment = $booking->apartment;
    $time = fn ($value) => \Carbon\Carbon::parse($value)->format('g:i A');
    $sub = fn ($text) => '<br><span style="font-size:12px;color:#6b8c8a;font-weight:400">'.e($text).'</span>';

    $rows = [
        'Property' => e($apartment->name),
        'Address' => e(collect([$apartment->address, $apartment->location_line])->filter()->implode(', ')),
        'Check-in' => e($booking->check_in->format('D, j M Y')).$sub('from '.$time($apartment->check_in_from)),
        'Check-out' => e($booking->check_out->format('D, j M Y')).$sub('until '.$time($apartment->check_out_until)),
        'Guests' => e($booking->guests.' '.Str::plural('guest', $booking->guests)),
    ];
@endphp

<x-mail.layout
    heading="Your stay starts tomorrow"
    :greeting="'Hi ' . $booking->user->display_name . '!'"
    icon="clock"
    tone="brand"
    :preview="'See you tomorrow at ' . $apartment->name">

    <p style="margin:0 0 20px; font-size:15px; line-height:1.65; color:#44605e; text-align:center;">
        Just a reminder — your stay at
        <strong style="color:#14303a;">{{ $apartment->name }}</strong>
        begins tomorrow. Here's everything you need for check-in.
    </p>

    <x-mail.reference :reference="$booking->display_reference" />

    <x-mail.details :rows="$rows" />

    <x-mail.button :url="route('bookings.confirmation', $booking)" label="View your booking" />

    <p style="margin:0 0 8px; font-size:13px; line-height:1.65; color:#6b8c8a; text-align:center;">
        Running early or late? Reply to this email, or message the host from your booking.
    </p>
</x-mail.layout>
