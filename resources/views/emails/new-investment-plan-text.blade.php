{{ $appName }} — {{ __('Investment pools') }}

{{ __('Hello :name,', ['name' => $userName]) }}

{{ $intro }}
{{ __('Here is your investment summary.') }}

@if ($heroValue)
{{ $heroLabel }}: {{ $heroValue }}
@endif

@foreach ($details as $row)
{{ $row['label'] }}: {{ $row['value'] }}
@endforeach

@if ($callout)
{{ $callout['label'] }}{{ $callout['value'] ? ': '.$callout['value'] : '' }}
{{ $callout['text'] }}
@endif

{{ $actionText }}: {{ $actionUrl }}

{{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $appName]) }}
