@props(['url'])

<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:8px auto 24px;">
    <tr>
        <td align="center" bgcolor="#0C2340" style="background-color:#0C2340;border-radius:28px;">
            <a href="{{ $url }}" target="_blank" rel="noopener" style="display:inline-block;padding:14px 28px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1;font-weight:700;color:#ffffff;text-decoration:none;">
                {{ $slot }} &nbsp;&rarr;
            </a>
        </td>
    </tr>
</table>
