@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $base = 'inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50';
    $variantClass = match ($variant) {
        'secondary' => 'border border-line bg-surface-secondary text-primary hover:bg-primary-muted focus:ring-primary',
        'success' => 'bg-success text-on-primary shadow-elevation-1 hover:brightness-110 focus:ring-success',
        'danger' => 'bg-error text-on-primary shadow-elevation-1 hover:brightness-110 focus:ring-error',
        default => 'bg-primary text-on-primary shadow-elevation-1 hover:bg-primary-hover hover:shadow-elevation-2 focus:ring-primary',
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
