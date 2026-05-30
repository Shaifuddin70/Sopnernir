@props(['compact' => false])

@if ($compact)
    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}"
        {{ $attributes->class(['block h-9 w-9 shrink-0 object-contain']) }} />
@else
    <span {{ $attributes->class(['inline-flex items-center gap-2.5']) }}>
        <img src="{{ asset('images/logo.jpg') }}" alt=""
            class="block h-9 w-9 shrink-0 object-contain" aria-hidden="true" />
        <span class="text-base font-semibold tracking-tight text-foreground">{{ config('app.name') }}</span>
    </span>
@endif
