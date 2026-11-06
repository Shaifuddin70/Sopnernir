@props(['url', 'logo' => null])
<tr>
<td class="header" align="center">
<table class="header-brand" align="center" cellpadding="0" cellspacing="0" role="presentation">
@if ($logo)
<tr>
<td align="center" style="padding-bottom: 12px;">
<a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; text-decoration: none;">
<img src="{{ $logo }}" width="72" height="72" alt="{{ config('app.name') }}" class="logo" style="border-radius: 50%;">
</a>
</td>
</tr>
@endif
<tr>
<td align="center">
<a href="{{ $url }}" class="header-name" target="_blank" rel="noopener">
{{ config('app.name') }}
</a>
</td>
</tr>
<tr>
<td align="center" class="header-tagline">
{{ __('Investment pools') }}
</td>
</tr>
</table>
</td>
</tr>
