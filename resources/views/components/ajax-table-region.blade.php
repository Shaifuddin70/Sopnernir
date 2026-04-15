@props([
    'fetchUrl',
    'targetId',
    'ajaxFragment' => null,
])
<div
    x-data="ajaxTableRegion({
        fetchUrl: @js($fetchUrl),
        targetId: @js($targetId),
        ajaxFragment: @js($ajaxFragment),
    })"
    @click.capture="onNavClick($event)"
    {{ $attributes }}
>
    {{ $slot }}
</div>
