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
    {{ $attributes->merge(['class' => 'ui-table-search-form']) }}
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
        class="ui-table-search block min-w-[14rem] flex-1 sm:max-w-lg"
        :class="{ 'opacity-60': loading }"
    />
    <x-action-button
        type="button"
        variant="secondary"
        class="shrink-0 text-sm"
        x-show="value && value.length"
        x-cloak
        @click="clear()"
    >{{ __('Clear') }}</x-action-button>
    <span x-show="error" x-cloak class="text-sm text-error">{{ __('Could not load results.') }}</span>
</form>
