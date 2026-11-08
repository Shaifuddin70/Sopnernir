@php
    $summary = $dailyProfitSummary;
    $rows = $dailyProfitRows;
    $rowsTotal = $dailyProfitRowsTotal ?? $rows->count();
    $showPlatform = $showPlatformStats ?? false;
    $platform = $platformDailySummary ?? null;
    $asOfLabel = \Carbon\Carbon::parse($summary['as_of_date'])->translatedFormat('j M Y');
    $previewLimit = $portfolioPreviewLimit ?? 5;
    $previewRows = $rows->take($previewLimit);
    $hasMoreInvestments = $rowsTotal > $previewLimit;
    $viewAllUrl = isset($viewRoute) && $viewRoute === 'phone-access'
        ? route('phone-access.investments.all')
        : route('investments.all');
@endphp

<div class="space-y-3 sm:space-y-4">
    @if ($showPlatform && $platform)
        <section class="dashboard-stat-panel" aria-label="{{ __('Platform overview') }}">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Platform overview') }}</h3>
            </div>
            <dl class="dashboard-stat-grid dashboard-stat-grid--platform">
                <div class="dashboard-stat dashboard-stat--capital">
                    <dt>{{ __('Total capital') }}</dt>
                    <dd>{{ $platform['total_capital'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--invested">
                    <dt>{{ __('Invested capital') }}</dt>
                    <dd>{{ $platform['invested_capital'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Total profit') }}</dt>
                    <dd>{{ $platform['total_projected_profit'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Profit til today') }}</dt>
                    <dd>{{ $platform['profit_til_today'] }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Withdrawn') }}</dt>
                    <dd>{{ $platform['total_withdrawn'] ?? '0.00' }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Members') }}</dt>
                    <dd>{{ $platform['member_count'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Per person') }}</dt>
                    <dd>{{ $platform['per_person_profit_til_today'] }}</dd>
                </div>
            </dl>
        </section>
    @endif

    <section class="dashboard-stat-panel portfolio-summary-panel" aria-label="{{ __('Portfolio summary') }}">
        <div class="dashboard-stat-panel__head">
            <div class="min-w-0">
                <h3 class="dashboard-stat-panel__title">{{ __('Portfolio summary') }}</h3>
                <p class="dashboard-stat-panel__meta">
                    {{ __('As of :date', ['date' => $asOfLabel]) }}
                    @if ($rowsTotal > 0)
                        · {{ trans_choice(':count pool|:count pools', $rowsTotal, ['count' => $rowsTotal]) }}
                    @endif
                </p>
            </div>
        </div>

        <div class="dashboard-hero portfolio-hero">
            <p class="dashboard-hero__label">{{ __('Profit til today') }}</p>
            <p class="dashboard-hero__value">{{ $summary['profit_til_today'] }}</p>
            <p class="dashboard-hero__hint">
                @if ($rowsTotal > 0)
                    {{ trans_choice(':count pool|:count pools', $rowsTotal, ['count' => $rowsTotal]) }}
                @else
                    {{ __('No active pools yet') }}
                @endif
            </p>
        </div>

        <dl class="dashboard-stat-grid portfolio-summary-grid">
            <div class="dashboard-stat portfolio-stat portfolio-stat--capital">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7l8 4 8-4-8-4Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 12 8 4 8-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 8 4 8-4" />
                        </svg>
                    </span>
                    {{ __('Total capital') }}
                </dt>
                <dd>{{ $summary['total_capital'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--invested">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 6h14v12H5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14h6" />
                        </svg>
                    </span>
                    {{ __('Invested capital') }}
                </dt>
                <dd>{{ $summary['invested_capital'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--profit">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l5-5 4 4 7-8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h6v6" />
                        </svg>
                    </span>
                    {{ __('Total profit') }}
                </dt>
                <dd>{{ $summary['total_projected_profit'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--today portfolio-stat--desktop-only">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="5" width="16" height="15" rx="2" />
                            <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                        </svg>
                    </span>
                    {{ __('Profit til today') }}
                </dt>
                <dd>{{ $summary['profit_til_today'] }}</dd>
            </div>
        </dl>
    </section>

    @if ($rows->isEmpty())
        <div class="dashboard-stat-panel px-4 py-8 text-center">
            <p class="text-sm text-foreground-muted">{{ __('No active investments with a plan completion date yet.') }}</p>
        </div>
    @else
        <section class="dashboard-stat-panel dashboard-stat-panel--scroll min-w-0">
            <div class="dashboard-stat-panel__head">
                <div class="min-w-0">
                    <h3 class="dashboard-stat-panel__title">{{ __('Your investments') }}</h3>
                    @if ($hasMoreInvestments)
                        <p class="dashboard-stat-panel__meta tabular-nums">
                            {{ __(':shown of :total', ['shown' => $previewRows->count(), 'total' => $rowsTotal]) }}
                        </p>
                    @endif
                </div>
                @if ($hasMoreInvestments)
                    <a href="{{ $viewAllUrl }}" class="text-sm font-semibold text-primary hover:text-primary-hover">
                        {{ __('View all') }}
                    </a>
                @endif
            </div>
            <div class="p-3">
                @include('investor.investments.partials.daily-profit-rows', [
                    'rows' => $previewRows,
                    'viewRoute' => $viewRoute ?? null,
                ])
            </div>
        </section>
    @endif
</div>
