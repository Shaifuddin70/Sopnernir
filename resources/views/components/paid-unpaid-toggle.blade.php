@props([
    'paid' => false,
    'mixed' => false,
    'bulk' => false,
])

@php
    $paidOn = $paid && ! $mixed;
    $unpaidOn = ! $paid && ! $mixed;
    $base = 'rounded-md px-2.5 py-1 text-xs font-medium leading-none transition sm:text-sm';
    $off = 'text-foreground-muted hover:bg-surface-card hover:text-foreground';
    $paidClass = $paidOn ? 'bg-success text-white shadow-sm' : $off;
    $unpaidClass = $unpaidOn ? 'bg-warning text-white shadow-sm' : $off;
@endphp

<div
    {{ $attributes->class('ui-paid-toggle inline-flex items-center rounded-lg border border-line bg-surface-secondary p-0.5') }}
    @if ($bulk) data-check-all-paid @else data-paid-toggle @endif
    role="group"
    aria-label="{{ $bulk ? __('Mark all paid or unpaid') : __('Paid or unpaid') }}"
>
    <button
        type="button"
        data-paid-choice="1"
        class="{{ $base }} {{ $paidClass }}"
        aria-pressed="{{ $paidOn ? 'true' : 'false' }}"
    >
        {{ __('Paid') }}
    </button>
    <button
        type="button"
        data-paid-choice="0"
        class="{{ $base }} {{ $unpaidClass }}"
        aria-pressed="{{ $unpaidOn ? 'true' : 'false' }}"
    >
        {{ __('Unpaid') }}
    </button>
</div>
