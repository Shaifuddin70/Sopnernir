@props([
    'user' => null,
    'name' => null,
    'imageUrl' => null,
    'subtitle' => null,
    'size' => 'sm',
])

@php
    $displayName = $name ?? $user?->name ?? '';
@endphp

<div {{ $attributes->merge(['class' => 'ui-user-identity']) }}>
    <span class="ui-user-identity__avatar">
        <x-user-avatar :user="$user" :name="$displayName" :image-url="$imageUrl" :size="$size" />
    </span>
    <div class="min-w-0">
        @if ($displayName !== '')
            <span class="block truncate font-medium text-foreground">{{ $displayName }}</span>
        @endif
        @if ($subtitle)
            <span class="block truncate text-xs text-foreground-muted">{{ $subtitle }}</span>
        @endif
        {{ $slot }}
    </div>
</div>
