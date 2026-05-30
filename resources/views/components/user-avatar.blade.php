@props([
    'user' => null,
    'name' => null,
    'imageUrl' => null,
    'size' => 'sm',
])

@php
    $displayName = $name ?? $user?->name ?? '';
    $url = $imageUrl ?? ($user?->profileImageUrl());
    $initial = strtoupper(\Illuminate\Support\Str::substr($displayName ?: $user?->email ?: '?', 0, 1));
    $sizeClasses = match ($size) {
        'lg' => 'h-10 w-10 text-sm',
        'md' => 'h-9 w-9 text-xs',
        default => 'h-8 w-8 text-xs',
    };
@endphp

@if ($url)
    <img
        {{ $attributes->class([$sizeClasses, 'shrink-0 rounded-full object-cover ring-1 ring-line']) }}
        src="{{ $url }}"
        alt=""
    />
@else
    <span
        {{ $attributes->class([
            'flex shrink-0 items-center justify-center rounded-full bg-primary-muted font-semibold text-primary ring-1 ring-primary/30',
            $sizeClasses,
        ]) }}
        aria-hidden="true"
    >{{ $initial }}</span>
@endif
