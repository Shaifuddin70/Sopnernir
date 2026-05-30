<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="space-y-6">

        @if ($platform !== null)
            <section class="ui-card" aria-labelledby="platform-overview-heading">
                <div class="ui-card-header">
                    <h2 id="platform-overview-heading" class="ui-card-header-title">
                        {{ __('Platform overview') }}
                    </h2>
                    <p class="ui-card-header-subtitle">
                        {{ __('Participant rows: :n', ['n' => $platform['participant_rows']]) }}
                    </p>
                </div>
                <div class="grid gap-px bg-line sm:grid-cols-3">
                    <article class="bg-surface-card p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                                    {{ __('Investments') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                                    {{ $platform['investments_total'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-muted text-primary ring-1 ring-primary/30"
                                aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-foreground-muted">
                            {{ __('Active: :count', ['count' => $platform['investments_active']]) }}
                        </p>
                    </article>
                    <article class="bg-surface-card p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                                    {{ __('Total tagged capital') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                                    {{ $platform['total_contributions'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success-muted text-success ring-1 ring-success/30"
                                aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-2 text-xs font-medium text-success">
                            {{ __('Total amount: :amount', ['amount' => $platform['total_amount']]) }}
                        </p>
                        <p class="mt-3 text-sm text-foreground-muted">{{ __('Sum of all participant contributions.') }}</p>
                    </article>
                    <article class="bg-surface-card p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                                    {{ __('Total profit distributed') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                                    {{ $platform['total_profit_distributed'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-muted text-warning ring-1 ring-warning/30"
                                aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-foreground-muted">
                            {{ __(':count accrual month(s)', ['count' => $platform['accrual_periods']]) }}
                        </p>
                    </article>
                </div>
            </section>
        @endif

        <section class="ui-card" aria-labelledby="portfolio-heading">
            <div class="ui-card-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="portfolio-heading" class="ui-card-header-title">
                        {{ __('Your portfolio') }}
                    </h2>
                    <p class="ui-card-header-subtitle max-w-2xl leading-relaxed">
                        @if ($personal['return_on_tagged_capital_pct'] !== null)
                            <span class="tabular-nums font-medium text-foreground">{{ __('Return') }}:
                                {{ $personal['return_on_tagged_capital_pct'] }}%</span>
                            <span class="mx-2 text-line" aria-hidden="true">·</span>
                        @endif
                        {{ __('Profit is your share each month; capital is your total contributions on pools you are on.') }}
                    </p>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="ui-stat-tile">
                        <p class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                            {{ __('Your investments') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                            {{ $personal['investments_count'] }}
                        </p>
                    </div>
                    <div class="ui-stat-tile-capital">
                        <p class="text-xs font-semibold uppercase tracking-wide text-success">
                            {{ __('Your tagged capital') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                            {{ $personal['total_contribution'] }}
                        </p>
                        <p class="mt-2 text-xs font-medium text-success">
                            {{ __('Total amount: :amount', ['amount' => $personal['total_amount']]) }}
                        </p>
                    </div>
                    <div class="ui-stat-tile-profit">
                        <p class="text-xs font-semibold uppercase tracking-wide text-warning">
                            {{ __('Your profit received') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">
                            {{ $personal['total_profit'] }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-card" aria-labelledby="top-investors-heading">
            <div class="ui-card-header">
                <h2 id="top-investors-heading" class="ui-card-header-title">
                    {{ __('Top investors') }}
                </h2>
                @if ($topInvestorsScope === 'global')
                    <p class="ui-card-header-subtitle">
                        {{ __('Ranked by total profit share from all accruals.') }}
                    </p>
                @else
                    <p class="ui-card-header-subtitle">
                        {{ __('Ranked by profit share on investments you are tagged on.') }}
                    </p>
                @endif
            </div>
            <div class="p-5 sm:p-6">
                <x-ajax-table-region :fetch-url="route('dashboard')" target-id="dashboard-top-investors-fragment"
                    ajax-fragment="top_investors">
                    <x-table-search :fetch-url="route('dashboard')" target-id="dashboard-top-investors-fragment"
                        ajax-fragment="top_investors" :placeholder="__('Search investors by name or email…')" />
                    <div id="dashboard-top-investors-fragment">
                        @include('dashboard.partials.top-investors-fragment', compact('topInvestors'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
