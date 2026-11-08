@props(['tone' => 'success'])

@php
    [$class, $styles] = match ($tone) {
        'warning' => ['em-badge-warning', 'background-color:#fff3e0;color:#ed6c02;'],
        'info' => ['em-badge-info', 'background-color:#e3f2fd;color:#0d47a1;'],
        default => ['em-badge-success', 'background-color:#e8f5e9;color:#2e7d32;'],
    };
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;">
    <tr>
        <td class="{{ $class }}" style="{{ $styles }}border-radius:20px;padding:8px 14px;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:11px;line-height:1;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">
            ✓ {{ $slot }}
        </td>
    </tr>
</table>
