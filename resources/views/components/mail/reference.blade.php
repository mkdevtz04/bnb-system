@props(['reference'])

{{-- The booking reference, given the weight it deserves: this is the one thing a
     guest will come back to the email looking for. Monospace and wide letter
     spacing so each character is unambiguous when read aloud or copied. --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
        <td align="center" style="padding:4px 0 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center"
                        style="background-color:#f7fbfa; border:1px solid #d6e8e4;
                               border-radius:12px; padding:16px 28px;">
                        <p style="margin:0 0 6px; font-size:11px; letter-spacing:0.09em;
                                  text-transform:uppercase; color:#6b8c8a; font-weight:600;">
                            Booking reference
                        </p>
                        <p style="margin:0; font-family:'SF Mono',Menlo,Consolas,monospace;
                                  font-size:21px; letter-spacing:0.12em; color:#0d4f5c; font-weight:700;">
                            {{ $reference }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
