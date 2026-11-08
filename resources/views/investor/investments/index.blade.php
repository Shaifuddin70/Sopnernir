<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading">
            <div class="portfolio-header-main">
                <h2 class="ui-page-header-title">{{ __('My investments') }}</h2>
                @if ($dailyProfitRowsTotal > 0)
                    <x-action-button
                        :href="route('investments.all')"
                        variant="secondary"
                        class="portfolio-all-pools-btn shrink-0"
                    >
                        {{ __('All pools') }}
                        <span class="ms-1 tabular-nums">({{ $dailyProfitRowsTotal }})</span>
                    </x-action-button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="portfolio-page space-y-3 pb-2 sm:space-y-4">
        @include('investor.investments.partials.daily-profit-overview', [
            'dailyProfitSummary' => $dailyProfitSummary,
            'dailyProfitRows' => $dailyProfitRows,
            'dailyProfitRowsTotal' => $dailyProfitRowsTotal,
        ])

        <div class="portfolio-secondary-grid grid items-start gap-3 xl:grid-cols-2">
            <section class="dashboard-stat-panel portfolio-chart-panel min-w-0" aria-label="{{ __('Profit by month') }}">
                <div class="dashboard-stat-panel__head">
                    <div class="min-w-0">
                        <h3 class="dashboard-stat-panel__title">{{ __('Profit by month') }}</h3>
                        <p class="dashboard-stat-panel__meta">{{ __('Recorded accrual payouts') }}</p>
                    </div>
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

            <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" ajax-fragment="profit" class="dashboard-stat-panel min-w-0">
                <div class="dashboard-stat-panel__head">
                    <div class="min-w-0">
                        <h3 class="dashboard-stat-panel__title">{{ __('Posted profit by month') }}</h3>
                    </div>
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
        </div>
    </div>
</x-app-layout>
