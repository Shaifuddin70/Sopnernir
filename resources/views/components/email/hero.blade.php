@props([
    'label',
    'value',
    'status' => null,
])

<table role="presentation" class="em-hero-wrap" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
    <tr>
        <td class="em-hero" bgcolor="#0d47a1" style="background-color:#0d47a1;background-image:linear-gradient(135deg,#0d47a1 0%,#1976d2 100%);border-radius:18px;padding:24px 26px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="middle" style="padding-right:12px;">
                        <div class="em-hero-label" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#bfdcff;font-size:11px;line-height:1.3;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;margin:0 0 8px;">{{ $label }}</div>
                        <div class="em-hero-value" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#ffffff;font-size:32px;line-height:1.15;font-weight:700;margin:0 0 10px;letter-spacing:-0.01em;">{{ $value }}</div>
                        @if ($status)
                            <div class="em-hero-status" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#a5d6a7;font-size:13px;line-height:1.4;font-weight:600;">✓ {{ $status }}</div>
                        @endif
                    </td>
                    <td class="hero-aside" valign="middle" align="right" width="88" style="width:88px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="right">
                            <tr>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:14px;background-color:#66bb6a;border-radius:3px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:22px;background-color:#81c784;border-radius:3px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:18px;background-color:#a5d6a7;border-radius:3px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:30px;background-color:#c6a04a;border-radius:3px;">&nbsp;</div></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
