@props([
    'paginator',
    'param',
    'resetPageKey' => 'page',
    'fetchUrl',
    'targetId',
    'ajaxFragment' => null,
    /** @var list<int>|null */
    'options' => null,
])
@php
    $opts = $options ?? \App\Support\PaginationPerPage::ALLOWED;
    $current = (int) $paginator->perPage();
    if (! in_array($current, $opts, true)) {
        $opts = array_values(array_unique(array_merge([$current], $opts)));
        sort($opts, SORT_NUMERIC);
    }
    $fieldId = 'pp-'.preg_replace('/[^a-zA-Z0-9_-]+/', '-', $param);
@endphp
<div
    class="flex flex-wrap items-center gap-2 text-sm text-gray-700"
    x-data="paginationPerPage({
        fetchUrl: @js($fetchUrl),
        targetId: @js($targetId),
        param: @js($param),
        resetPageKey: @js($resetPageKey),
        ajaxFragment: @js($ajaxFragment),
        initialValue: @js((string) $current),
    })"
>
    <label for="{{ $fieldId }}" class="whitespace-nowrap">{{ __('Per page') }}</label>
    <select
        id="{{ $fieldId }}"
        name="{{ $param }}"
        x-model="value"
        @change="apply()"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        :disabled="loading"
    >
        @foreach ($opts as $n)
            <option value="{{ $n }}">{{ $n }}</option>
        @endforeach
    </select>
    <span x-show="error" x-cloak class="text-sm text-red-600">{{ __('Could not load results.') }}</span>
</div>
