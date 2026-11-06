@props([
    'mailMessage' => null,
    'title' => null,
])

@php
    $app = config('app.name');
    $logoUrl = \App\Support\BrandedMail::logoUrl($mailMessage);
    $title = $title ?: $app;
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <style>
        @media only screen and (max-width: 620px) {
            .email-card { width: 100% !important; }
            .email-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .hero-aside { display: none !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#E8EEF4;width:100%;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#E8EEF4;margin:0;padding:0;width:100%;">
    <tr>
        <td align="center" style="padding:28px 12px;">
            <table role="presentation" class="email-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background-color:#ffffff;border:1px solid #D7DEE7;border-radius:12px;overflow:hidden;">
                <tr>
                    <td bgcolor="#0C2340" class="email-pad" style="background-color:#0C2340;background-image:linear-gradient(90deg,#0C2340 0%,#14375C 100%);padding:28px 36px 24px;border-bottom:3px solid #C6A04A;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="middle" style="width:70%;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td valign="middle" style="padding-right:12px;">
                                                <img src="{{ $logoUrl }}" width="48" height="48" alt="{{ $app }}" style="display:block;width:48px;height:48px;border:0;" />
                                            </td>
                                            <td valign="middle">
                                                <div style="font-family:Georgia,'Times New Roman',serif;color:#ffffff;font-size:22px;line-height:1.15;font-weight:700;letter-spacing:0.04em;">{{ $app }}</div>
                                                <div style="font-family:Arial,Helvetica,sans-serif;color:#C6A04A;font-size:10px;line-height:1.4;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;padding-top:4px;">{{ __('Investment pools') }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td valign="middle" align="right" style="font-family:Georgia,'Times New Roman',serif;color:#E8D9A8;font-size:13px;line-height:1.35;font-style:italic;">
                                    {{ __('Build your financial future') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="email-pad" style="padding:32px 36px 8px;background-color:#ffffff;">
                        {{ $slot }}
                    </td>
                </tr>
                <tr>
                    <td class="email-pad" style="padding:8px 36px 28px;background-color:#ffffff;">
                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:14px;line-height:1.6;text-align:center;">
                            {{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $app]) }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="email-pad" bgcolor="#F4F7FA" style="background-color:#F4F7FA;padding:22px 36px;border-top:1px solid #E6EAF0;text-align:center;">
                        <p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;color:#7A8794;font-size:12px;line-height:1.5;">
                            © {{ date('Y') }} {{ $app }}. {{ __('All rights reserved.') }}
                        </p>
                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;color:#7A8794;font-size:12px;line-height:1.5;">
                            <a href="{{ config('app.url') }}" style="color:#0C2340;text-decoration:none;">{{ $app }}</a>
                            @if (config('mail.from.address'))
                                &nbsp;|&nbsp;
                                <a href="mailto:{{ config('mail.from.address') }}" style="color:#0C2340;text-decoration:none;">{{ __('Support') }}</a>
                            @endif
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
