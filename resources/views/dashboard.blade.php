<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="ui-page-header-title shrink-0">{{ __('Dashboard') }}</h2>
            <div class="header-action-scroll">
                @if (auth()->user()->isAdmin())
                    <x-action-button :href="route('admin.investments.index')" variant="secondary">
                        {{ __('Investments') }}
                    </x-action-button>
                    <x-action-button :href="route('admin.monthly-payments.index')" variant="secondary">
                        {{ __('Monthly Installment') }}
                    </x-action-button>
                @endif
                <x-action-button :href="route('investments.index')" variant="secondary">
                    {{ __('Portfolio') }}
                </x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-page space-y-3 pb-2 sm:space-y-4">
        <div class="dashboard-summary-grid grid gap-3 lg:grid-cols-2">
            <section class="dashboard-stat-panel dashboard-summary-grid__personal" aria-label="{{ __('Personal summary') }}">
                <div class="dashboard-stat-panel__head">
                    <div class="min-w-0">
                        <h3 class="dashboard-stat-panel__title">{{ __('Your portfolio') }}</h3>
                        @if (! empty($personal['as_of_date']))
                            <p class="dashboard-stat-panel__meta">
                                {{ __('As of :date', ['date' => \Carbon\Carbon::parse($personal['as_of_date'])->translatedFormat('j M Y')]) }}
                            </p>
                        @endif
                    </div>
                    @if ($personal['return_on_tagged_capital_pct'] !== null)
                        <span class="ui-chip tabular-nums">{{ __('Return :pct%', ['pct' => $personal['return_on_tagged_capital_pct']]) }}</span>
                    @endif
                </div>

                <div class="dashboard-hero">
                    <p class="dashboard-hero__label">{{ __('Profit til today') }}</p>
                    <p class="dashboard-hero__value">{{ $personal['profit_til_today'] }}</p>
                    <p class="dashboard-hero__hint">
                        {{ trans_choice(':count pool|:count pools', $personal['investments_count'], ['count' => $personal['investments_count']]) }}
                    </p>
                </div>

                <dl class="dashboard-stat-grid dashboard-stat-grid--personal">
                    <div class="dashboard-stat dashboard-stat--capital">
                        <dt>{{ __('Total capital') }}</dt>
                        <dd>{{ $personal['total_capital'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--invested">
                        <dt>{{ __('Invested capital') }}</dt>
                        <dd>{{ $personal['invested_capital'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Total profit') }}</dt>
                        <dd>{{ $personal['total_projected_profit'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Profit til today') }}</dt>
                        <dd>{{ $personal['profit_til_today'] }}</dd>
                    </div>
                </dl>
            </section>

            <section class="dashboard-stat-panel dashboard-summary-grid__platform" aria-label="{{ __('Platform summary') }}">
                <div class="dashboard-stat-panel__head">
                    <div class="min-w-0">
                        <h3 class="dashboard-stat-panel__title">{{ __('Platform') }}</h3>
                        @if (! empty($platform['as_of_date']))
                            <p class="dashboard-stat-panel__meta">
                                {{ __('As of :date', ['date' => \Carbon\Carbon::parse($platform['as_of_date'])->translatedFormat('j M Y')]) }}
                                @if (auth()->user()->isAdmin())
                                    · {{ __('Admin') }}
                                @endif
                            </p>
                        @elseif (auth()->user()->isAdmin())
                            <p class="dashboard-stat-panel__meta">{{ __('Admin') }}</p>
                        @endif
                    </div>
                </div>
                <dl class="dashboard-stat-grid">
                    <div class="dashboard-stat dashboard-stat--capital">
                        <dt>{{ __('Total capital') }}</dt>
                        <dd>{{ $platform['total_capital'] }}</dd>
                        <p>{{ __('From payments') }}</p>
                    </div>
                    <div class="dashboard-stat dashboard-stat--invested">
                        <dt>{{ __('Invested capital') }}</dt>
                        <dd>{{ $platform['invested_capital'] }}</dd>
                        <p>{{ __('Active pools: :n', ['n' => $platform['investments_active']]) }}</p>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Total profit') }}</dt>
                        <dd>{{ $platform['total_projected_profit'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Profit til today') }}</dt>
                        <dd>{{ $platform['profit_til_today'] }}</dd>
                        <p>{{ __(':n members', ['n' => $platform['member_count'] ?? 0]) }}</p>
                    </div>
                </dl>
            </section>
        </div>

        <div id="dashboard-charts-root" class="dashboard-charts-grid grid gap-3 lg:grid-cols-12">
            <script type="application/json" id="dashboard-charts-data">@json($charts)</script>
            <section class="dashboard-chart-panel dashboard-chart-panel--trend lg:col-span-8">
                <h3 class="dashboard-chart-panel__title">{{ __('Profit trend (6 months)') }}</h3>
                <div class="dashboard-chart-panel__canvas">
                    <canvas
                        data-chart-type="profit-trend"
                        data-label="{{ __('Profit til period') }}"
                        aria-label="{{ __('Profit trend chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>

            <section class="dashboard-chart-panel dashboard-chart-panel--split lg:col-span-4">
                <h3 class="dashboard-chart-panel__title">{{ __('Capital vs profit til today') }}</h3>
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

            <section class="dashboard-chart-panel dashboard-chart-panel--split lg:col-span-6">
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

            <section class="dashboard-chart-panel dashboard-chart-panel--split lg:col-span-6">
                <h3 class="dashboard-chart-panel__title">{{ __('This month payments') }}</h3>
                <div class="dashboard-chart-panel__canvas dashboard-chart-panel__canvas--short">
                    <canvas
                        data-chart-type="monthly-payments"
                        data-paid-label="{{ __('Paid') }}"
                        data-unpaid-label="{{ __('Unpaid') }}"
                        aria-label="{{ __('Monthly Installment chart') }}"
                        role="img"
                    ></canvas>
                </div>
            </section>
        </div>

        <section class="dashboard-chart-panel dashboard-investors min-w-0">
            <div class="dashboard-investors__head">
                <div class="min-w-0">
                    <h3 class="dashboard-chart-panel__title !mb-0">{{ __('Top investors') }}</h3>
                    <p class="dashboard-stat-panel__meta">{{ __('By profit til today') }}</p>
                </div>
            </div>
            <div>
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
