@props([
    'mailMessage' => null,
    'title' => null,
])

@php
    $app = config('app.name');
    $logoUrl = \App\Support\BrandedMail::logoUrl($mailMessage);
    $title = $title ?: $app;
    $appUrl = rtrim((string) config('app.url'), '/');
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
        }

        /* Light palette (default) */
        .em-body { background-color: #eef3f8; }
        .em-card { background-color: #ffffff; border-color: #e0e6ee; }
        .em-brand { background-color: #0d47a1; background-image: linear-gradient(135deg, #0d47a1 0%, #1976d2 70%, #2e7d32 150%); }
        .em-brand-title { color: #ffffff; }
        .em-brand-sub { color: #cfe6ff; }
        .em-logo { background-color: #ffffff !important; border-radius: 14px; }
        .em-text { color: #1a1c1e; }
        .em-muted { color: #5f6368; }
        .em-h1 { color: #0d47a1; }
        .em-divider { background-color: #e0e6ee; }
        .em-footer { background-color: #f5f7fa; border-color: #e0e6ee; }
        .em-footer-text { color: #7a8794; }
        .em-link { color: #1976d2; }
        .em-hero { background-color: #0d47a1; background-image: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%); }
        .em-hero-label { color: #bfdcff; }
        .em-hero-value { color: #ffffff; }
        .em-hero-status { color: #a5d6a7; }
        .em-bar { background-color: #c6a04a; }
        .em-bar-strong { background-color: #e8d9a8; }
        .em-badge-success { background-color: #e8f5e9; color: #2e7d32; }
        .em-badge-warning { background-color: #fff3e0; color: #ed6c02; }
        .em-badge-info { background-color: #e3f2fd; color: #0d47a1; }
        .em-callout { background-color: #e3f2fd; border-color: #bbdefb; }
        .em-callout-label { color: #0d47a1; }
        .em-callout-value { color: #1a1c1e; }
        .em-callout-text { color: #3b5a7d; }
        .em-details { border-color: #e0e6ee; }
        .em-details-row { border-color: #e0e6ee; }
        .em-details-label { color: #5f6368; }
        .em-details-value { color: #1a1c1e; }
        .em-details-title { color: #1a1c1e; }
        .em-accent { background-color: #1976d2; }
        .em-btn { background-color: #1976d2; }
        .em-btn-text { color: #ffffff; }

        @media (prefers-color-scheme: dark) {
            .em-body { background-color: #0b1220 !important; }
            .em-card { background-color: #151e30 !important; border-color: #2a364a !important; }
            .em-brand { background-color: #0f1b33 !important; background-image: linear-gradient(135deg, #0f1b33 0%, #16345e 70%, #1b3f2a 150%) !important; }
            .em-brand-title { color: #e8eef7 !important; }
            .em-brand-sub { color: #9ec5f0 !important; }
            .em-logo { background-color: #ffffff !important; }
            .em-text { color: #e8eef7 !important; }
            .em-muted { color: #9aa8bc !important; }
            .em-h1 { color: #90caf9 !important; }
            .em-divider { background-color: #2a364a !important; }
            .em-footer { background-color: #121a2b !important; border-color: #2a364a !important; }
            .em-footer-text { color: #8795a9 !important; }
            .em-link { color: #90caf9 !important; }
            .em-hero { background-color: #16345e !important; background-image: linear-gradient(135deg, #16345e 0%, #1f4d8a 100%) !important; }
            .em-hero-label { color: #bfdcff !important; }
            .em-hero-value { color: #ffffff !important; }
            .em-hero-status { color: #a5d6a7 !important; }
            .em-badge-success { background-color: #16301c !important; color: #a5d6a7 !important; }
            .em-badge-warning { background-color: #3a2a12 !important; color: #ffcc80 !important; }
            .em-badge-info { background-color: #1a2f4a !important; color: #90caf9 !important; }
            .em-callout { background-color: #1a2f4a !important; border-color: #2a4a74 !important; }
            .em-callout-label { color: #90caf9 !important; }
            .em-callout-value { color: #e8eef7 !important; }
            .em-callout-text { color: #b9c8dd !important; }
            .em-details { border-color: #2a364a !important; }
            .em-details-row { border-color: #2a364a !important; }
            .em-details-label { color: #9aa8bc !important; }
            .em-details-value { color: #e8eef7 !important; }
            .em-details-title { color: #e8eef7 !important; }
            .em-accent { background-color: #64b5f6 !important; }
            .em-btn { background-color: #64b5f6 !important; }
            .em-btn-text { color: #0b1220 !important; }
        }

        [data-ogsc] .em-body { background-color: #0b1220 !important; }
        [data-ogsc] .em-card { background-color: #151e30 !important; border-color: #2a364a !important; }
        [data-ogsc] .em-text { color: #e8eef7 !important; }
        [data-ogsc] .em-muted { color: #9aa8bc !important; }
        [data-ogsc] .em-h1 { color: #90caf9 !important; }
        [data-ogsc] .em-footer { background-color: #121a2b !important; border-color: #2a364a !important; }
        [data-ogsc] .em-footer-text { color: #8795a9 !important; }
        [data-ogsc] .em-link { color: #90caf9 !important; }
        [data-ogsc] .em-details-row { border-color: #2a364a !important; }
        [data-ogsc] .em-details-label { color: #9aa8bc !important; }
        [data-ogsc] .em-details-value { color: #e8eef7 !important; }
        [data-ogsc] .em-details-title { color: #e8eef7 !important; }
        [data-ogsc] .em-callout { background-color: #1a2f4a !important; border-color: #2a4a74 !important; }
        [data-ogsc] .em-callout-label { color: #90caf9 !important; }
        [data-ogsc] .em-callout-value { color: #e8eef7 !important; }
        [data-ogsc] .em-callout-text { color: #b9c8dd !important; }
        [data-ogsc] .em-btn { background-color: #64b5f6 !important; }
        [data-ogsc] .em-btn-text { color: #0b1220 !important; }
        [data-ogsc] .em-logo { background-color: #ffffff !important; }

        @media only screen and (max-width: 620px) {
            .em-body { background-color: #ffffff !important; }
            .em-shell { padding: 0 !important; }
            .email-card {
                width: 100% !important;
                max-width: 100% !important;
                border: 0 !important;
                border-radius: 0 !important;
            }
            .email-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .hero-aside { display: none !important; }
            .em-brand-tagline { display: none !important; }
            .em-brand { padding: 18px 20px 16px !important; border-radius: 0 !important; }
            .em-logo { width: 40px !important; height: 40px !important; }
            .em-brand-title { font-size: 18px !important; }
            .em-brand-sub { font-size: 10px !important; letter-spacing: 0.1em !important; }
            .em-h1 { font-size: 22px !important; line-height: 1.3 !important; margin-bottom: 12px !important; }
            .em-text, .em-muted { font-size: 14px !important; }
            .em-hero-wrap { margin: 0 0 22px !important; }
            .em-hero { padding: 18px 20px !important; border-radius: 0 !important; }
            .em-hero-value { font-size: 24px !important; }
            .em-hero-status { font-size: 12px !important; }
            .em-callout-value { font-size: 20px !important; }
            .em-callout-text { font-size: 12px !important; }
            .em-details-title { font-size: 16px !important; }
            .em-details-label, .em-details-value { font-size: 13px !important; padding: 11px 12px !important; }
            .em-btn-text { font-size: 14px !important; padding: 12px 22px !important; }
            .em-footer-text { font-size: 11px !important; }
        }

        @media only screen and (max-width: 620px) and (prefers-color-scheme: dark) {
            .em-body { background-color: #151e30 !important; }
            .email-card { background-color: #151e30 !important; }
        }
    </style>
</head>
<body class="em-body" style="margin:0;padding:0;background-color:#eef3f8;width:100%;-webkit-text-size-adjust:100%;">
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">{{ $title }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="em-body" style="background-color:#eef3f8;margin:0;padding:0;width:100%;">
    <tr>
        <td align="center" class="em-shell" style="padding:28px 12px;">
            <table role="presentation" class="email-card em-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background-color:#ffffff;border:1px solid #e0e6ee;border-radius:20px;overflow:hidden;">
                <tr>
                    <td class="email-pad em-brand" bgcolor="#0d47a1" style="background-color:#0d47a1;background-image:linear-gradient(135deg,#0d47a1 0%,#1976d2 70%,#2e7d32 150%);padding:26px 36px 22px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="middle">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td valign="middle" style="padding-right:14px;line-height:0;">
                                                <!--[if mso]>
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff"><tr><td bgcolor="#ffffff" style="padding:0;">
                                                <![endif]-->
                                                <img src="{{ $logoUrl }}" class="em-logo" width="52" height="52" alt="{{ $app }}" style="display:block;width:52px;height:52px;border:0;background-color:#ffffff;border-radius:14px;" />
                                                <!--[if mso]>
                                                </td></tr></table>
                                                <![endif]-->
                                            </td>
                                            <td valign="middle">
                                                <div class="em-brand-title" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#ffffff;font-size:22px;line-height:1.15;font-weight:700;letter-spacing:-0.01em;">{{ $app }}</div>
                                                <div class="em-brand-sub" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#cfe6ff;font-size:11px;line-height:1.4;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;padding-top:4px;">{{ __('Investment pools') }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td valign="middle" align="right" class="em-brand-tagline em-brand-sub" style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#cfe6ff;font-size:13px;line-height:1.35;">
                                    {{ __('Build your financial future') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="height:4px;line-height:4px;font-size:0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="50%" bgcolor="#64b5f6" style="background-color:#64b5f6;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
                                <td width="30%" bgcolor="#2e7d32" style="background-color:#2e7d32;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
                                <td width="20%" class="em-bar" bgcolor="#c6a04a" style="background-color:#c6a04a;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="email-pad em-card" style="padding:32px 36px 8px;background-color:#ffffff;">
                        {{ $slot }}
                    </td>
                </tr>
                <tr>
                    <td class="email-pad em-card" style="padding:8px 36px 28px;background-color:#ffffff;">
                        <p class="em-muted" style="margin:0;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#5f6368;font-size:14px;line-height:1.6;text-align:center;">
                            {{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $app]) }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="email-pad em-footer" bgcolor="#f5f7fa" style="background-color:#f5f7fa;padding:22px 36px;border-top:1px solid #e0e6ee;text-align:center;">
                        <p class="em-footer-text" style="margin:0 0 6px;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#7a8794;font-size:12px;line-height:1.5;">
                            © {{ date('Y') }} {{ $app }}. {{ __('All rights reserved.') }}
                        </p>
                        <p class="em-footer-text" style="margin:0;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#7a8794;font-size:12px;line-height:1.5;">
                            <a href="{{ $appUrl }}" class="em-link" style="color:#1976d2;text-decoration:none;font-weight:600;">{{ $app }}</a>
                            @if (config('mail.from.address'))
                                &nbsp;·&nbsp;
                                <a href="mailto:{{ config('mail.from.address') }}" class="em-link" style="color:#1976d2;text-decoration:none;font-weight:600;">{{ __('Support') }}</a>
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
