@props([
    'fetchUrl',
    'targetId',
    'param' => 'search',
    'ajaxFragment' => null,
    'placeholder' => null,
    'resetPageKeys' => ['page', 'periods_page', 'participants_page', 'profit_page', 'top_investors_page'],
    'debounce' => 350,
])
@php
    $placeholder = $placeholder ?? __('Search…');
@endphp
<form
    method="get"
    role="search"
    x-data="liveSearchAjax({
        fetchUrl: @js($fetchUrl),
        targetId: @js($targetId),
        param: @js($param),
        ajaxFragment: @js($ajaxFragment),
        resetPageKeys: @js($resetPageKeys),
        debounceMs: @js((int) $debounce),
        initialValue: @js(request($param)),
    })"
    @submit.prevent="runFetch()"
    {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-center gap-2']) }}
>
    <label for="ts-{{ $param }}" class="sr-only">{{ __('Search') }}</label>
    <input
        id="ts-{{ $param }}"
        type="search"
        name="{{ $param }}"
        x-model="value"
        @input="onInput()"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        class="block min-w-[12rem] flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-md text-sm"
        :class="{ 'opacity-60': loading }"
    />
    <x-action-button
        type="button"
        variant="secondary"
        class="text-sm"
        x-show="value && value.length"
        x-cloak
        @click="clear()"
    >{{ __('Clear') }}</x-action-button>
    <span x-show="error" x-cloak class="text-sm text-red-600">{{ __('Could not load results.') }}</span>
</form>
