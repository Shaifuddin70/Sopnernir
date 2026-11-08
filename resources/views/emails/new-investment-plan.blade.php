<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge>{{ $isTagged ? __("You're included") : __('New investment plan') }}</x-email.badge>

    <x-email.heading>{{ $headline }}</x-email.heading>

    <x-email.text>{{ __('Hello :name,', ['name' => $userName]) }}</x-email.text>
    <x-email.text muted spacing="0 0 8px">{{ $intro }}</x-email.text>
    <x-email.text muted spacing="0 0 24px">{{ __('Here is your investment summary.') }}</x-email.text>

    @if ($heroValue)
        <x-email.hero
            :label="$heroLabel"
            :value="$heroValue"
            :status="$heroStatus"
        />
    @endif

    <x-email.details :title="__('Investment details')" :rows="$details" />

    @if ($callout)
        <x-email.callout :label="$callout['label']" :value="$callout['value']">
            {{ $callout['text'] }}
        </x-email.callout>
    @endif

    <x-email.button :url="$actionUrl">{{ $actionText }}</x-email.button>
</x-email.layout>
