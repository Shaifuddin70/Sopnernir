<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('My investments') }}</h2>
            @if ($dailyProfitRowsTotal > 0)
                <div class="header-action-scroll">
                    <x-action-button :href="route('investments.all')" variant="secondary">
                        {{ __('All pools') }} ({{ $dailyProfitRowsTotal }})
                    </x-action-button>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="portfolio-page space-y-4 pb-2">
        @include('investor.investments.partials.daily-profit-overview', [
            'dailyProfitSummary' => $dailyProfitSummary,
            'dailyProfitRows' => $dailyProfitRows,
            'dailyProfitRowsTotal' => $dailyProfitRowsTotal,
        ])

        <div class="grid items-start gap-3 xl:grid-cols-2">
            <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" ajax-fragment="profit" class="dashboard-stat-panel min-w-0">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Posted profit by month') }}</h3>
                    <x-table-search
                        :fetch-url="route('investments.index')"
                        target-id="portfolio-profit-fragment"
                        param="profit_search"
                        ajax-fragment="profit"
                        :placeholder="__('Search by pool, month, or amount…')"
                        class="!mb-0 w-full sm:w-72"
                    />
                </div>
                <div class="p-3 pt-1">
                    <div id="portfolio-profit-fragment">
                        @include('investor.investments.partials.profit-month-fragment', compact('profitByMonth'))
                    </div>
                </div>
            </x-ajax-table-region>

            <section class="dashboard-stat-panel min-w-0" aria-label="{{ __('Profit by month') }}">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Profit by month') }}</h3>
                    <span class="text-sm text-foreground-muted">{{ __('Recorded accrual payouts') }}</span>
                </div>
                <div class="px-3 pb-4 pt-1">
                    @if ($profitMonthChart['values'] === [])
                        <p class="px-2 py-10 text-center text-sm text-foreground-muted">{{ __('No accrual payouts recorded yet.') }}</p>
                    @else
                        <div class="portfolio-profit-chart">
                            <script type="application/json" id="portfolio-profit-chart-data">@json($profitMonthChart)</script>
                            <canvas
                                data-chart-type="portfolio-profit-month"
                                data-label="{{ __('Profit') }}"
                                aria-label="{{ __('Profit by month') }}"
                                role="img"
                            ></canvas>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
