@props(['tone' => 'success'])

@php
    $styles = match ($tone) {
        'warning' => 'background-color:#F8F1DC;color:#8A6A1F;',
        'info' => 'background-color:#E3F2FD;color:#0D47A1;',
        default => 'background-color:#E6F6EC;color:#1F8A4D;',
    };
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;">
    <tr>
        <td style="{{ $styles }}border-radius:20px;padding:8px 14px;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">
            ✓ {{ $slot }}
        </td>
    </tr>
</table>
