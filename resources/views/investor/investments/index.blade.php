<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">{{ __('My investments') }}</h2>
    </x-slot>

    <div class="space-y-4">
        @if ($investments->total() > 0 || $totalTaggedCapital > 0 || $portfolioProfitTotal > 0)
            <div class="rounded-lg border border-line bg-surface-secondary p-3 text-sm text-foreground shadow-sm sm:p-4">
                <h3 class="text-sm font-semibold text-foreground sm:text-base">{{ __('Your totals across all pools') }}</h3>
                <dl class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">{{ __('Tagged capital') }}
                        </dt>
                        <dd class="mt-1 text-base font-semibold tabular-nums text-foreground sm:text-lg">
                            {{ number_format($totalTaggedCapital, 2, '.', '') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Your profit received') }}</dt>
                        <dd class="mt-1 text-base font-semibold tabular-nums text-foreground sm:text-lg">
                            {{ number_format($portfolioProfitTotal, 2, '.', '') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                            {{ __('Return on tagged capital') }}</dt>
                        <dd class="mt-1 text-base font-semibold tabular-nums text-foreground sm:text-lg">
                            @if ($returnOnTaggedCapitalPct !== null)
                                {{ $returnOnTaggedCapitalPct }}%
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-foreground-muted">
                    {{ __('Profit is your share each month; capital is your total contributions on pools you are on.') }}
                </p>
                <p class="mt-2 text-xs">
                    <x-action-button :href="route('dashboard')"
                        class="text-sm font-medium">{{ __('Back to dashboard') }}</x-action-button>
                </p>
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2 lg:items-start">
            <section class="ui-card min-w-0">
                <div class="ui-card-header">
                    <h3 class="ui-card-header-title">{{ __('Pools') }}</h3>
                </div>
                <div class="p-3 sm:p-4">
                    <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-pools-fragment" ajax-fragment="pools">
                        <x-table-search :fetch-url="route('investments.index')" target-id="portfolio-pools-fragment" ajax-fragment="pools"
                            :placeholder="__('Search pools by title…')" />
                        <div id="portfolio-pools-fragment">
                            @include(
                                'investor.investments.partials.pools-fragment',
                                compact('investments', 'portfolioProfitTotal'))
                        </div>
                    </x-ajax-table-region>
                </div>
            </section>

            <section class="ui-card min-w-0">
                <div class="ui-card-header">
                    <h3 class="ui-card-header-title">{{ __('Profit by month') }}</h3>
                    <p class="ui-card-header-subtitle">
                        {{ __('Every tagged investor receives a share each month on each pool they are on, from the pool’s first month through the current month.') }}
                    </p>
                </div>
                <div class="p-3 sm:p-4">
                    <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" ajax-fragment="profit">
                        <x-table-search :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" param="profit_search"
                            ajax-fragment="profit" :placeholder="__('Search by pool title, month, or amount…')" />
                        <div id="portfolio-profit-fragment">
                            @include(
                                'investor.investments.partials.profit-month-fragment',
                                compact('profitByMonth'))
                        </div>
                    </x-ajax-table-region>
                </div>
            </section>
        </div>
    </div>

</x-app-layout>
