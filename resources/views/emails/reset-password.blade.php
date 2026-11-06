<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge tone="warning">{{ __('Password reset') }}</x-email.badge>

    <h1 style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:28px;line-height:1.25;font-weight:700;">
        {{ __('Reset your password.') }}
    </h1>

    <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:15px;line-height:1.6;">
        {{ __('Hello :name,', ['name' => $userName]) }}
    </p>
    <p style="margin:0 0 24px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:15px;line-height:1.6;">
        {{ __('We received a request to reset the password for your :app account.', ['app' => $appName]) }}
    </p>

    <x-email.button :url="$actionUrl">{{ __('Reset password') }}</x-email.button>

    <p style="margin:0 0 8px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:14px;line-height:1.6;">
        {{ __('This password reset link will expire in :count minutes.', ['count' => $expire]) }}
    </p>
    <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:14px;line-height:1.6;">
        {{ __('If you did not request a password reset, you can ignore this email.') }}
    </p>
</x-email.layout>
