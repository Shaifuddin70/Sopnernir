@props([
    'paginator' => null,
    'index' => 0,
])

@php
    $serial = ($paginator && $paginator->firstItem() !== null)
        ? $paginator->firstItem() + $index
        : $index + 1;
@endphp

<td {{ $attributes->merge(['class' => 'w-12 whitespace-nowrap text-center tabular-nums text-foreground-muted']) }}>
    {{ $serial }}
</td>
