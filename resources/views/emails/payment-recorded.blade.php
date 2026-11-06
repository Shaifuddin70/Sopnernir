<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge>{{ __('Payment recorded') }}</x-email.badge>

    <h1 style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:28px;line-height:1.25;font-weight:700;">
        {{ __('Your payment has been recorded.') }}
    </h1>

    <p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#12233A;font-size:15px;line-height:1.6;">
        {{ __('Hello :name,', ['name' => $userName]) }}
    </p>
    <p style="margin:0 0 24px;font-family:Arial,Helvetica,sans-serif;color:#5C6B7A;font-size:15px;line-height:1.6;">
        {{ __('Thank you for staying current with :app.', ['app' => $appName]) }}
    </p>

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
