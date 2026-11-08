<x-email.layout :mail-message="$message ?? null" :title="$subject">
    <x-email.badge tone="info">{{ __('Notice board') }}</x-email.badge>

    <x-email.heading>{{ $noticeTitle }}</x-email.heading>

    <x-email.text>{{ __('Hello :name,', ['name' => $userName]) }}</x-email.text>
    <x-email.text muted spacing="0 0 24px">{{ __('A new notice has been posted on :app.', ['app' => $appName]) }}</x-email.text>

    @if ($excerpt)
        <x-email.callout :label="__('Summary')">
            {{ $excerpt }}
        </x-email.callout>
    @endif

    @php
        $rows = [];
        if ($publishedAt) {
            $rows[] = ['label' => __('Published'), 'value' => $publishedAt];
        }
        if ($attachmentCount > 0) {
            $rows[] = ['label' => __('Attachments'), 'value' => (string) $attachmentCount];
        }
    @endphp
    <x-email.details :title="__('Notice details')" :rows="$rows" />

    <x-email.button :url="$actionUrl">{{ __('View notice') }}</x-email.button>
</x-email.layout>
