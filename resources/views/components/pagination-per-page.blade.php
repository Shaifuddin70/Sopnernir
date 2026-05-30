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
@endphp

<div
    class="flex flex-wrap items-center gap-2 text-sm"
    x-data="{
        ...paginationPerPage({
            fetchUrl: @js($fetchUrl),
            targetId: @js($targetId),
            param: @js($param),
            resetPageKey: @js($resetPageKey),
            ajaxFragment: @js($ajaxFragment),
            initialValue: @js((string) $current),
        }),
        open: false,
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <span class="whitespace-nowrap text-foreground-muted">{{ __('Per page') }}</span>

    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            :disabled="loading"
            class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface-secondary px-3 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-surface-variant focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 disabled:opacity-50"
        >
            <span x-text="value"></span>
            <svg class="h-4 w-4 shrink-0 text-foreground-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute bottom-full left-0 z-50 mb-1 min-w-[5rem] rounded-lg border border-line bg-surface-card py-1 shadow-elevation-3"
        >
            @foreach ($opts as $n)
                <button
                    type="button"
                    @click="value = '{{ $n }}'; open = false; apply();"
                    class="block w-full px-4 py-1.5 text-left text-sm font-medium transition-colors"
                    :class="value === '{{ $n }}' ? 'text-primary bg-primary-muted' : 'text-foreground hover:bg-surface-secondary'"
                >
                    {{ $n }}
                </button>
            @endforeach
        </div>
    </div>

    <span x-show="error" x-cloak class="text-sm text-error">{{ __('Could not load results.') }}</span>
</div>
