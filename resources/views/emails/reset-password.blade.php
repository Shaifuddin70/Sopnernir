<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge tone="warning">{{ __('Password reset') }}</x-email.badge>

    <x-email.heading>{{ __('Reset your password.') }}</x-email.heading>

    <x-email.text>{{ __('Hello :name,', ['name' => $userName]) }}</x-email.text>
    <x-email.text muted spacing="0 0 24px">{{ __('We received a request to reset the password for your :app account.', ['app' => $appName]) }}</x-email.text>

    <x-email.button :url="$actionUrl">{{ __('Reset password') }}</x-email.button>

    <x-email.text muted size="14" spacing="0 0 8px">{{ __('This password reset link will expire in :count minutes.', ['count' => $expire]) }}</x-email.text>
    <x-email.text muted size="14" spacing="0 0 16px">{{ __('If you did not request a password reset, you can ignore this email.') }}</x-email.text>
</x-email.layout>
