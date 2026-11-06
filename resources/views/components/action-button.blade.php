@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $base = 'inline-flex h-10 items-center justify-center rounded-full px-4 text-sm font-medium shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50';
    $variantClass = match ($variant) {
        'secondary' => 'border border-white/70 bg-white/80 text-primary backdrop-blur-md hover:bg-white focus:ring-primary',
        'success' => 'bg-success text-on-primary hover:brightness-110 focus:ring-success',
        'danger' => 'bg-error text-on-primary hover:brightness-110 focus:ring-error',
        default => 'bg-primary text-on-primary hover:bg-primary-hover focus:ring-primary',
    };
    $classes = trim($base.' '.$variantClass);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->except('type')->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    @php($btnType = $attributes->get('type', 'submit'))
    <button type="{{ $btnType }}" {{ $attributes->except('type')->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
