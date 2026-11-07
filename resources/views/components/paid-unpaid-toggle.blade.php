@props([
    'paid' => false,
    'mixed' => false,
    'bulk' => false,
])

@php
    $paidOn = $paid && ! $mixed;
    $unpaidOn = ! $paid && ! $mixed;
    $base = 'rounded-full px-2.5 py-1 text-xs font-medium leading-none transition sm:text-sm';
    $off = 'text-foreground-muted hover:bg-surface-card hover:text-foreground';
    $paidClass = $paidOn ? 'bg-success text-white shadow-sm' : $off;
    $unpaidClass = $unpaidOn ? 'bg-warning text-white shadow-sm' : $off;
@endphp

@if ($bulk)
    <div
        {{ $attributes->class('ui-paid-toggle ui-paid-toggle--bulk') }}
        data-check-all-paid
        data-confirm-paid="{{ __('Mark every investor in this list as paid?') }}"
        data-confirm-unpaid="{{ __('Mark every investor in this list as unpaid?') }}"
        role="group"
        aria-label="{{ __('Mark all paid or unpaid') }}"
    >
        <span class="ui-paid-toggle__all">{{ __('All') }}</span>
        <span class="ui-paid-toggle__bulk-choices">
            <button
                type="button"
                data-paid-choice="1"
                class="ui-paid-toggle__bulk-btn {{ $paidOn ? 'is-paid' : '' }}"
                aria-pressed="{{ $paidOn ? 'true' : 'false' }}"
            >
                {{ __('Paid') }}
            </button>
            <button
                type="button"
                data-paid-choice="0"
                class="ui-paid-toggle__bulk-btn {{ $unpaidOn ? 'is-unpaid' : '' }}"
                aria-pressed="{{ $unpaidOn ? 'true' : 'false' }}"
            >
                {{ __('Unpaid') }}
            </button>
        </span>
    </div>
@else
    <div
        {{ $attributes->class('ui-paid-toggle inline-flex items-center rounded-full border border-white/70 bg-white/70 p-0.5 shadow-sm backdrop-blur-md dark:border-white/10 dark:bg-white/10') }}
        data-paid-toggle
        role="group"
        aria-label="{{ __('Paid or unpaid') }}"
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
@endif
