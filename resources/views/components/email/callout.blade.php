@props(['label' => null, 'value' => null])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
    <tr>
        <td style="background-color:#F8F1DC;border:1px solid #E8D9A8;border-radius:12px;padding:18px 20px;">
            @if ($label)
                <div style="font-family:Arial,Helvetica,sans-serif;color:#8A6A1F;font-size:11px;line-height:1.3;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;margin:0 0 6px;">{{ $label }}</div>
            @endif
            @if ($value)
                <div style="font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:26px;line-height:1.2;font-weight:700;margin:0 0 4px;">{{ $value }}</div>
            @endif
            <div style="font-family:Arial,Helvetica,sans-serif;color:#6B5A2A;font-size:13px;line-height:1.5;">{{ $slot }}</div>
        </td>
    </tr>
</table>
