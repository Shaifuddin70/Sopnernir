@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $base = 'inline-flex items-center justify-center rounded-md border bg-white px-2.5 py-1.5 text-xs font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-50';
    $variantClass = match ($variant) {
        'secondary' => 'border-gray-300 text-gray-700 hover:bg-gray-50',
        'danger' => 'border-red-200 text-red-700 hover:bg-red-50',
        default => 'border-indigo-200 text-indigo-800 hover:bg-indigo-50',
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
