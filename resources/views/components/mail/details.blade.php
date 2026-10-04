@props(['rows' => [], 'total' => null])

{{-- Label/value rows. Two columns rather than a definition list, because email
     clients collapse the spacing on <dl> unpredictably. --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
       style="border:1px solid #e6eeec; border-radius:12px; margin-bottom:24px;">
    @foreach ($rows as $label => $value)
        <tr>
            <td style="padding:13px 20px; font-size:14px; color:#6b8c8a;
                       {{ ! $loop->first ? 'border-top:1px solid #eef4f3;' : '' }}">
                {{ $label }}
            </td>
            <td align="right"
                style="padding:13px 20px; font-size:14px; color:#14303a; font-weight:600;
                       {{ ! $loop->first ? 'border-top:1px solid #eef4f3;' : '' }}">
                {!! $value !!}
            </td>
        </tr>
    @endforeach

    @if ($total)
        <tr>
            <td style="padding:15px 20px; font-size:15px; color:#0d4f5c; font-weight:700;
                       border-top:2px solid #e6eeec; background-color:#f7fbfa;
                       border-bottom-left-radius:12px;">
                Total
            </td>
            <td align="right"
                style="padding:15px 20px; font-size:17px; color:#0d4f5c; font-weight:700;
                       border-top:2px solid #e6eeec; background-color:#f7fbfa;
                       border-bottom-right-radius:12px;">
                {{ $total }}
            </td>
        </tr>
    @endif
</table>
