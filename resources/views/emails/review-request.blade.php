@php
    $apartment = $booking->apartment;
    $sub = fn ($text) => '<br><span style="font-size:12px;color:#6b8c8a;font-weight:400">'.e($text).'</span>';

    $rows = [
        'Property' => e($apartment->name),
        'Dates' => e($booking->check_in->format('j M').' → '.$booking->check_out->format('j M Y'))
            .$sub($booking->nights.' '.Str::plural('night', $booking->nights)),
    ];
@endphp

<x-mail.layout
    heading="How was your stay?"
    :greeting="'Hi ' . $booking->user->display_name . '!'"
    icon="check"
    tone="brand"
    :preview="'Tell us about your stay at ' . $apartment->name">

    <p style="margin:0 0 20px; font-size:15px; line-height:1.65; color:#44605e; text-align:center;">
        We hope you enjoyed <strong style="color:#14303a;">{{ $apartment->name }}</strong>.
        A quick review helps other travellers — and takes less than a minute.
    </p>

    <x-mail.reference :reference="$booking->display_reference" />

    <x-mail.details :rows="$rows" />

    <x-mail.button :url="route('reviews.create', $booking)" label="Leave a review" />

    <p style="margin:0 0 8px; font-size:13px; line-height:1.65; color:#6b8c8a; text-align:center;">
        Something wasn't right? Reply to this email and we'll sort it out directly.
    </p>
</x-mail.layout>
