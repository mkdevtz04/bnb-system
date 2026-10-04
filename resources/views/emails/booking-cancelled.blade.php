@php
    $apartment = $booking->apartment;

    $rows = [
        'Property' => e($apartment->name),
        'Check-in' => e($booking->check_in->format('D, j M Y')),
        'Check-out' => e($booking->check_out->format('D, j M Y')),
    ];
@endphp

<x-mail.layout
    heading="Your booking has been cancelled"
    :greeting="'Hi ' . $booking->user->display_name . ','"
    icon="cross"
    tone="danger"
    :preview="'Cancelled: ' . $apartment->name . ', ' . $booking->check_in->format('j M Y')">

    <p style="margin:0 0 20px; font-size:15px; line-height:1.65; color:#44605e; text-align:center;">
        Your stay at <strong style="color:#14303a;">{{ $apartment->name }}</strong>
        has been cancelled by the host. Nothing was charged, so there is nothing to refund.
    </p>

    <x-mail.reference :reference="$booking->display_reference" />

    <x-mail.details :rows="$rows" />

    <x-mail.button :url="route('apartments.search')" label="Find another stay" />

    <p style="margin:0 0 8px; font-size:13px; line-height:1.65; color:#6b8c8a; text-align:center;">
        If you think this is a mistake, reply to this email and we'll look into it.
    </p>
</x-mail.layout>
