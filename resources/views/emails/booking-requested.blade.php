@php
    $apartment = $booking->apartment;
    $guest = $booking->user;
    $money = fn ($amount) => \App\Support\Money::format($amount, $booking->currency);

    $sub = fn ($text) => '<br><span style="font-size:12px;color:#6b8c8a;font-weight:400">'.e($text).'</span>';

    $rows = [
        'Guest' => e($guest->display_name).$sub($guest->email),
        'Property' => e($apartment->name),
        'Dates' => e($booking->check_in->format('j M').' → '.$booking->check_out->format('j M Y'))
            .$sub($booking->nights.' '.Str::plural('night', $booking->nights)),
        'Guests' => e($booking->guests.' '.Str::plural('guest', $booking->guests)),
    ];
@endphp

{{-- Goes to the host, not the guest: the tone is "here is a decision waiting for
     you", and the action is the admin panel rather than the guest's booking. --}}
<x-mail.layout
    heading="New booking request"
    greeting="Hello!"
    icon="bell"
    tone="gold"
    :reply-note="'Reply to this email to write to ' . $guest->display_name . ' directly.'"
    :preview="$guest->display_name . ' wants ' . $apartment->name . ' from ' . $booking->check_in->format('j M')">

    <p style="margin:0 0 20px; font-size:15px; line-height:1.65; color:#44605e; text-align:center;">
        <strong style="color:#14303a;">{{ $guest->display_name }}</strong> has requested
        <strong style="color:#14303a;">{{ $apartment->name }}</strong>.
        The dates are held until you confirm or cancel.
    </p>

    <x-mail.reference :reference="$booking->display_reference" />

    <x-mail.details :rows="$rows" :total="$money($booking->total_price)" />

    <x-mail.button :url="route('admin.bookings', ['status' => 'pending'])" label="Review this request" />

    <p style="margin:0 0 8px; font-size:13px; line-height:1.65; color:#6b8c8a; text-align:center;">
        Confirming releases any other request competing for the same nights.<br>
        Hit reply to answer {{ $guest->display_name }} at
        <a href="mailto:{{ $guest->email }}" style="color:#0a7a66;">{{ $guest->email }}</a>.
    </p>
</x-mail.layout>
