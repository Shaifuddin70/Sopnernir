@props([])

<button
    type="button"
    data-theme-toggle
    {{ $attributes->merge([
        'class' => 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/70 bg-white/70 text-foreground shadow-sm backdrop-blur-md transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15',
        'aria-label' => __('Toggle theme'),
        'title' => __('Toggle theme'),
    ]) }}
>
    <svg data-theme-icon="sun" class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="3.5" stroke-width="1.8" />
        <path stroke-linecap="round" stroke-width="1.8" d="M12 3.5v1.6M12 18.9v1.6M4.9 4.9l1.1 1.1M18 18l1.1 1.1M3.5 12h1.6M18.9 12h1.6M4.9 19.1l1.1-1.1M18 6l1.1-1.1" />
    </svg>
    <svg data-theme-icon="moon" class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 14.2A7.8 7.8 0 0 1 9.8 4 7.4 7.4 0 1 0 20 14.2Z" />
    </svg>
</button>
