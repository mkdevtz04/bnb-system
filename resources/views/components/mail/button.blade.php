@props(['url', 'label'])

{{-- Bulletproof button: a table with a padded link, not a styled <a> or an
     image. Outlook ignores padding on inline elements, so the cell carries it. --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
        <td align="center" style="padding:8px 0 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" style="background-color:#0a7a66; border-radius:10px;">
                        <a href="{{ $url }}"
                           style="display:inline-block; padding:14px 32px; font-size:15px;
                                  font-weight:600; color:#ffffff; text-decoration:none;
                                  border-radius:10px;">
                            {{ $label }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
