{{ $appName }} — {{ __('Investment pools') }}

{{ __('Hello :name,', ['name' => $userName]) }}

{{ __('Your payment has been recorded.') }}
{{ __('Payment month') }}: {{ $monthLabel }}
@if ($amountLabel)
{{ __('Amount') }}: {{ $amountLabel }}
@endif
{{ __('Thank you for staying current with :app.', ['app' => $appName]) }}

{{ __('View your portfolio') }}: {{ $actionUrl }}

{{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $appName]) }}
