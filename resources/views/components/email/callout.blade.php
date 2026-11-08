@props(['label' => null, 'value' => null])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
    <tr>
        <td class="em-callout" style="background-color:#e3f2fd;border:1px solid #bbdefb;border-radius:16px;padding:18px 20px;">
            @if ($label)
                <div class="em-callout-label" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0d47a1;font-size:11px;line-height:1.3;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;margin:0 0 6px;">{{ $label }}</div>
            @endif
            @if ($value)
                <div class="em-callout-value" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a1c1e;font-size:26px;line-height:1.2;font-weight:700;margin:0 0 4px;letter-spacing:-0.01em;">{{ $value }}</div>

            @endif
            <div class="em-callout-text" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#3b5a7d;font-size:13px;line-height:1.5;">{{ $slot }}</div>
        </td>
    </tr>
</table>
