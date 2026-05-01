@props(['compact' => false])

@if ($compact)
    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <circle cx="24" cy="24" r="20" fill="currentColor" fill-opacity="0.12" />
        <circle cx="24" cy="24" r="19" fill="none" stroke="currentColor" stroke-opacity="0.25" stroke-width="2" />
        <text x="24" y="30" text-anchor="middle" font-size="18" font-weight="700" letter-spacing="0.5"
            fill="currentColor">S</text>
    </svg>
@else
    <svg viewBox="0 0 240 48" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <circle cx="24" cy="24" r="20" fill="currentColor" fill-opacity="0.12" />
        <circle cx="24" cy="24" r="19" fill="none" stroke="currentColor" stroke-opacity="0.25" stroke-width="2" />
        <text x="24" y="30" text-anchor="middle" font-size="18" font-weight="700" letter-spacing="0.5"
            fill="currentColor">S</text>
        <text x="54" y="30" font-size="20" font-weight="700" letter-spacing="0.2"
            fill="currentColor">Shopnonir</text>
    </svg>
@endif
