<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge>{{ $isTagged ? __("You're included") : __('New investment plan') }}</x-email.badge>

    <h1 style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:28px;line-height:1.25;font-weight:700;">
        {{ $headline }}
    </h1>

    <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:15px;line-height:1.6;">
        {{ __('Hello :name,', ['name' => $userName]) }}
    </p>
    <p style="margin:0 0 8px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:15px;line-height:1.6;">
        {{ $intro }}
    </p>
    <p style="margin:0 0 24px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:15px;line-height:1.6;">
        {{ __('Here is your investment summary.') }}
    </p>

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
