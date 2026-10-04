<x-mail.layout
    heading="A reply from Coastal Charms"
    :greeting="'Hi ' . $inquiry->name . ','"
    icon="bell"
    tone="brand"
    :preview="Str::limit(preg_replace('/\s+/', ' ', $body), 120)">

    {{-- The host's own words, kept as typed. pre-wrap because the reply box is a
         textarea and paragraphs matter. --}}
    <div style="font-size:15px; line-height:1.7; color:#2c4845; white-space:pre-wrap;
                overflow-wrap:anywhere; margin:0 0 24px;">{{ $body }}</div>

    {{-- What they originally asked, so the reply makes sense on its own without
         them having to dig out their own message. --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
           style="margin:0 0 24px;">
        <tr>
            <td style="padding:16px 18px; background-color:#f7fbfa; border-left:3px solid #d6e8e4;
                       border-radius:0 10px 10px 0;">
                <p style="margin:0 0 8px; font-size:11px; letter-spacing:0.08em;
                          text-transform:uppercase; color:#6b8c8a; font-weight:600;">
                    You wrote on {{ $inquiry->created_at->format('j M Y') }}
                </p>
                @if ($inquiry->subject)
                    <p style="margin:0 0 6px; font-size:14px; color:#14303a; font-weight:600;">
                        {{ $inquiry->subject }}
                    </p>
                @endif
                <div style="font-size:13.5px; line-height:1.6; color:#6b8c8a;
                            white-space:pre-wrap; overflow-wrap:anywhere;">{{ $inquiry->message }}</div>
            </td>
        </tr>
    </table>

    <x-mail.button :url="route('apartments.search')" label="Browse our apartments" />
</x-mail.layout>
