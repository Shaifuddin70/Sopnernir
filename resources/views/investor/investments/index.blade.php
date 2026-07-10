<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('My investments') }}</h2>
            @if ($dailyProfitRowsTotal > 0)
                <x-action-button :href="route('investments.all')" variant="secondary" class="text-sm">
                    {{ __('All pools') }} ({{ $dailyProfitRowsTotal }})
                </x-action-button>
            @endif
        </div>
    </x-slot>

    <div class="portfolio-page space-y-4 pb-2">
        @include('investor.investments.partials.daily-profit-overview', [
            'dailyProfitSummary' => $dailyProfitSummary,
            'dailyProfitRows' => $dailyProfitRows,
            'dailyProfitRowsTotal' => $dailyProfitRowsTotal,
        ])

        <section class="dashboard-stat-panel min-w-0">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Posted profit by month') }}</h3>
                <span class="text-sm text-foreground-muted">{{ __('Recorded accrual payouts') }}</span>
            </div>
            <div class="p-3">
                <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" ajax-fragment="profit">
                    <x-table-search
                        :fetch-url="route('investments.index')"
                        target-id="portfolio-profit-fragment"
                        param="profit_search"
                        ajax-fragment="profit"
                        :placeholder="__('Search by pool, month, or amount…')"
                    />
                    <div id="portfolio-profit-fragment">
                        @include('investor.investments.partials.profit-month-fragment', compact('profitByMonth'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
