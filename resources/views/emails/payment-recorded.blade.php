<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge>{{ __('Payment recorded') }}</x-email.badge>

    <x-email.heading>{{ __('Your payment has been recorded.') }}</x-email.heading>

    <x-email.text>{{ __('Hello :name,', ['name' => $userName]) }}</x-email.text>
    <x-email.text muted spacing="0 0 24px">{{ __('Thank you for staying current with :app.', ['app' => $appName]) }}</x-email.text>

    @if ($amountLabel)
        <x-email.hero
            :label="$monthLabel"
            :value="$amountLabel"
        />
    @else
        <x-email.hero
            :label="__('Payment month')"
            :value="$monthLabel"
        />
    @endif

    <x-email.button :url="$actionUrl">{{ __('View your portfolio') }}</x-email.button>
</x-email.layout>
