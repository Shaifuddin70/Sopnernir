{{ $appName }} — {{ __('Investment pools') }}

{{ __('Hello :name,', ['name' => $userName]) }}

{{ __('We received a request to reset the password for your :app account.', ['app' => $appName]) }}

{{ __('Reset password') }}: {{ $actionUrl }}

{{ __('This password reset link will expire in :count minutes.', ['count' => $expire]) }}
{{ __('If you did not request a password reset, you can ignore this email.') }}

{{ __('Thank you for choosing :app. We appreciate your trust in us.', ['app' => $appName]) }}
