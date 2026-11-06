@props([
    'label',
    'value',
    'status' => null,
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
    <tr>
        <td bgcolor="#0C2340" style="background-color:#0C2340;border-radius:14px;padding:24px 26px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="middle" style="padding-right:12px;">
                        <div style="font-family:Arial,Helvetica,sans-serif;color:#C6A04A;font-size:11px;line-height:1.3;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;margin:0 0 8px;">{{ $label }}</div>
                        <div style="font-family:Arial,Helvetica,sans-serif;color:#ffffff;font-size:32px;line-height:1.15;font-weight:700;margin:0 0 10px;">{{ $value }}</div>
                        @if ($status)
                            <div style="font-family:Arial,Helvetica,sans-serif;color:#7DDBA3;font-size:13px;line-height:1.4;font-weight:600;">✓ {{ $status }}</div>
                        @endif
                    </td>
                    <td class="hero-aside" valign="middle" align="right" width="88" style="width:88px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="right">
                            <tr>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:14px;background-color:#C6A04A;border-radius:2px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:22px;background-color:#C6A04A;border-radius:2px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:18px;background-color:#C6A04A;border-radius:2px;">&nbsp;</div></td>
                                <td valign="bottom" style="padding:0 3px;"><div style="width:8px;height:30px;background-color:#E8D9A8;border-radius:2px;">&nbsp;</div></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
