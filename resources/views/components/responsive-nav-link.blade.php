@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex w-full items-center rounded-xl bg-primary-muted px-3 py-2.5 text-sm font-medium leading-none text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 transition duration-150 ease-in-out [&_svg]:block'
            : 'flex w-full items-center rounded-xl px-3 py-2.5 text-sm font-medium leading-none text-foreground-muted hover:bg-surface-variant hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 transition duration-150 ease-in-out [&_svg]:block';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
