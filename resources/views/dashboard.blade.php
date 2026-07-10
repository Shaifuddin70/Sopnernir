<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Dashboard') }}</h2>
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->isAdmin())
                    <x-action-button :href="route('admin.investments.index')" variant="secondary" class="text-xs">
                        {{ __('Investments') }}
                    </x-action-button>
                    <x-action-button :href="route('admin.monthly-payments.index')" variant="secondary" class="text-xs">
                        {{ __('Payments') }}
                    </x-action-button>
                @endif
                <x-action-button :href="route('investments.index')" variant="secondary" class="text-xs">
                    {{ __('Portfolio') }}
                </x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-page space-y-4 pb-2">
        <div class="grid gap-3 lg:grid-cols-2">
            <section class="dashboard-stat-panel" aria-label="{{ __('Platform summary') }}">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Platform') }}</h3>
                    @if (auth()->user()->isAdmin())
                        <span class="ui-chip">{{ __('Admin') }}</span>
                    @endif
                </div>
                <dl class="dashboard-stat-grid">
                    <div class="dashboard-stat">
                        <dt>{{ __('Investments') }}</dt>
                        <dd>{{ $platform['investments_total'] }}</dd>
                        <p>{{ __('Active: :n', ['n' => $platform['investments_active']]) }}</p>
                    </div>
                    <div class="dashboard-stat dashboard-stat--capital">
                        <dt>{{ __('Tagged capital') }}</dt>
                        <dd>{{ $platform['total_contributions'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Profit distributed') }}</dt>
                        <dd>{{ $platform['total_profit_distributed'] }}</dd>
                    </div>
                    <div class="dashboard-stat">
                        <dt>{{ __('Accrual months') }}</dt>
                        <dd>{{ $platform['accrual_periods'] }}</dd>
                        <p>{{ __(':n participants', ['n' => $platform['participant_rows']]) }}</p>
                    </div>
                </dl>
            </section>

            <section class="dashboard-stat-panel" aria-label="{{ __('Personal summary') }}">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Your portfolio') }}</h3>
                    @if ($personal['return_on_tagged_capital_pct'] !== null)
                        <span class="ui-chip tabular-nums">{{ __('Return :pct%', ['pct' => $personal['return_on_tagged_capital_pct']]) }}</span>
                    @endif
                </div>
                <dl class="dashboard-stat-grid">
                    <div class="dashboard-stat">
                        <dt>{{ __('Pools') }}</dt>
                        <dd>{{ $personal['investments_count'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--capital">
                        <dt>{{ __('Your capital') }}</dt>
                        <dd>{{ $personal['total_contribution'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Your profit') }}</dt>
                        <dd>{{ $personal['total_profit'] }}</dd>
                    </div>
                    <div class="dashboard-stat">
                        <dt>{{ __('Total balance') }}</dt>
                        <dd>{{ $personal['total_amount'] }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <div id="dashboard-charts-root" class="grid gap-3 lg:grid-cols-12">
            <script type="application/json" id="dashboard-charts-data">@json($charts)</script>
            <section class="dashboard-chart-panel lg:col-span-8">
                <h3 class="dashboard-chart-panel__title">{{ __('Profit trend (6 months)') }}</h3>
                <div class="dashboard-chart-panel__canvas">
                    <canvas
                        data-chart-type="profit-trend"
                        data-label="{{ __('Pool profit') }}"
                        aria-label="{{ __('Profit trend chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>

            <section class="dashboard-chart-panel lg:col-span-4">
                <h3 class="dashboard-chart-panel__title">{{ __('Capital vs profit') }}</h3>
                <div class="dashboard-chart-panel__canvas dashboard-chart-panel__canvas--square">
                    <canvas
                        data-chart-type="capital-vs-profit"
                        data-capital-label="{{ __('Capital') }}"
                        data-profit-label="{{ __('Profit') }}"
                        aria-label="{{ __('Capital versus profit chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>

            <section class="dashboard-chart-panel lg:col-span-6">
                <h3 class="dashboard-chart-panel__title">{{ __('Investment status') }}</h3>
                <div class="dashboard-chart-panel__canvas dashboard-chart-panel__canvas--short">
                    <canvas
                        data-chart-type="investment-status"
                        data-active-label="{{ __('Active') }}"
                        data-draft-label="{{ __('Draft') }}"
                        data-closed-label="{{ __('Closed') }}"
                        aria-label="{{ __('Investment status chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>

            <section class="dashboard-chart-panel lg:col-span-6">
                <h3 class="dashboard-chart-panel__title">{{ __('This month payments') }}</h3>
                <div class="dashboard-chart-panel__canvas dashboard-chart-panel__canvas--short">
                    <canvas
                        data-chart-type="monthly-payments"
                        data-paid-label="{{ __('Paid') }}"
                        data-unpaid-label="{{ __('Unpaid') }}"
                        aria-label="{{ __('Monthly payments chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>
        </div>

        <section class="ui-glass-panel min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line bg-surface-variant px-4 py-3 sm:px-5">
                <h3 class="text-sm font-semibold text-foreground">{{ __('Top investors') }}</h3>
                <span class="text-xs text-foreground-muted">{{ __('By total profit share') }}</span>
            </div>
            <div class="p-3 sm:p-4">
                <x-ajax-table-region :fetch-url="route('dashboard')" target-id="dashboard-top-investors-fragment" ajax-fragment="top_investors">
                    <x-table-search
                        :fetch-url="route('dashboard')"
                        target-id="dashboard-top-investors-fragment"
                        ajax-fragment="top_investors"
                        :placeholder="__('Search investors by name or email…')"
                    />
                    <div id="dashboard-top-investors-fragment">
                        @include('dashboard.partials.top-investors-fragment', compact('topInvestors'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
