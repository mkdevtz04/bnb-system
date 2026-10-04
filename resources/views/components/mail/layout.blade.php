@props([
    'preview' => null,
    'icon' => 'check',
    'tone' => 'brand',
    'heading',
    'greeting' => null,
    // Who a reply reaches differs by audience, so the footer says which. The
    // host's copy replies to the guest; a guest's copy replies to the host.
    'replyNote' => 'Questions? Just reply to this email.',
])

@php
    // Email HTML is not web HTML. Every style is inline, the layout is tables
    // rather than flex or grid, and nothing relies on a <style> block, because
    // Outlook and several webmail clients strip or ignore all three.
    $palette = [
        'brand' => ['bg' => '#ebfcf8', 'fg' => '#0a7a66'],
        'gold' => ['bg' => '#f7eeda', 'fg' => '#a97d1e'],
        'danger' => ['bg' => '#fdecec', 'fg' => '#9b2c2c'],
    ][$tone] ?? ['bg' => '#ebfcf8', 'fg' => '#0a7a66'];

    // Simple glyphs rather than icon fonts or SVG: an icon font will not load and
    // inline SVG is stripped by Gmail.
    $glyph = ['check' => '&#10003;', 'clock' => '&#9203;', 'cross' => '&#10005;', 'bell' => '&#9873;'][$icon] ?? '&#10003;';

    // The logo travels with the message rather than being fetched from a URL.
    // A remote <img> pointing at this app would have to be publicly reachable,
    // which it is not in development — Gmail cannot load localhost — and remote
    // images are blocked by default in several clients anyway.
    //
    // $message only exists while a mail is genuinely being sent; when the view is
    // rendered for a preview or a test there is nothing to attach to, so it falls
    // back to a URL.
    $logoPath = public_path('icons/coastalcharms-email.png');
    $logo = isset($message) && is_file($logoPath)
        ? $message->embed($logoPath)
        : asset('icons/coastalcharms-email.png');
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $heading }}</title>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#f1f6f5;
             font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
             -webkit-font-smoothing:antialiased;">

    {{-- The line shown beside the subject in an inbox list. Hidden in the body. --}}
    @if ($preview)
        <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
            {{ $preview }}
        </div>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
           style="background-color:#f1f6f5;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560"
                       style="width:560px; max-width:100%; background-color:#ffffff;
                              border-radius:16px; overflow:hidden;
                              box-shadow:0 1px 3px rgba(13,79,92,0.08);">

                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding:36px 32px 8px;">
                            <a href="{{ url('/') }}" style="text-decoration:none;">
                                <img src="{{ $logo }}"
                                     width="150" alt="Coastal Charms Zanzibar"
                                     style="width:150px; max-width:150px; height:auto; display:block; border:0;">
                            </a>
                        </td>
                    </tr>

                    {{-- Hero glyph --}}
                    <tr>
                        <td align="center" style="padding:16px 32px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" valign="middle"
                                        style="width:72px; height:72px; background-color:{{ $palette['bg'] }};
                                               border-radius:36px; font-size:32px; line-height:72px;
                                               color:{{ $palette['fg'] }};">
                                        {!! $glyph !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Heading --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 0;">
                            <h1 style="margin:0; font-family:Georgia,'Times New Roman',serif;
                                       font-size:26px; line-height:1.25; color:#0d4f5c; font-weight:700;">
                                {{ $heading }}
                            </h1>
                            @if ($greeting)
                                <p style="margin:14px 0 0; font-size:16px; font-weight:600; color:#14303a;">
                                    {{ $greeting }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:16px 32px 8px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 32px; border-top:1px solid #e6eeec;">
                            <p style="margin:16px 0 0; font-size:13px; line-height:1.6; color:#6b8c8a;">
                                Thanks,<br>
                                <strong style="color:#0d4f5c;">The Coastal Charms team</strong>
                            </p>
                            <p style="margin:14px 0 0; font-size:12px; line-height:1.6; color:#93aeac;">
                                Coastal Charms &middot; Zanzibar<br>
                                {{ $replyNote }}
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="margin:20px 0 0; font-size:11px; color:#9fb6b4;">
                    &copy; {{ date('Y') }} Coastal Charms Zanzibar
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
