@props(['disabled' => false])

@php
    $isPassword = $attributes->get('type') === 'password';
@endphp

@if ($isPassword)
    <div class="relative" x-data="{ show: false }">
        <input
            @disabled($disabled)
            {{ $attributes->except('type')->merge(['class' => 'ui-input pe-12']) }}
            :type="show ? 'text' : 'password'"
            type="password"
        >
        <button
            type="button"
            class="absolute bottom-0 right-0 z-10 flex h-[2.75rem] items-center px-3 text-foreground-muted transition hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 rounded-r-lg"
            @click="show = !show"
            :aria-pressed="show.toString()"
            x-bind:aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
        >
            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
            </svg>
            <svg x-cloak x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L21 21" />
            </svg>
        </button>
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'ui-input']) }}>
@endif
