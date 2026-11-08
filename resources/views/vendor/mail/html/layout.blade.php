<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
:root { color-scheme: light dark; supported-color-schemes: light dark; }

@media (prefers-color-scheme: dark) {
body, .wrapper { background-color: #0b1220 !important; color: #9aa8bc !important; }
.inner-body { background-color: #151e30 !important; border-color: #2a364a !important; box-shadow: none !important; }
.header, .footer, .subcopy { border-color: #2a364a !important; }
.header a, .header-name { color: #90caf9 !important; }
.header-tagline, p, .table td, .footer p, .panel-content, .panel-content p { color: #9aa8bc !important; }
h1, h2, h3, .table th { color: #e8eef7 !important; }
.table th { border-color: #2a364a !important; }
a, .footer a { color: #90caf9 !important; }
.panel { border-left-color: #64b5f6 !important; }
.panel-content { background-color: #1a2f4a !important; }
.button-blue, .button-primary { background-color: #64b5f6 !important; border-color: #64b5f6 !important; color: #0b1220 !important; }
.button-green, .button-success { background-color: #66bb6a !important; border-color: #66bb6a !important; color: #0b1220 !important; }
.button-red, .button-error { background-color: #ef5350 !important; border-color: #ef5350 !important; color: #ffffff !important; }
}

@media only screen and (max-width: 600px) {
.inner-body,
.content,
.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}
</style>
{!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="wrapper-inner" align="center">
<table class="content" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
