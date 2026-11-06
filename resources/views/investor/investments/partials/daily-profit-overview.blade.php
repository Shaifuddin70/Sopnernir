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

<div class="space-y-4">
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

    <section class="dashboard-stat-panel" aria-label="{{ __('Portfolio summary') }}">
        <div class="dashboard-stat-panel__head">
            <h3 class="dashboard-stat-panel__title">{{ __('Portfolio summary') }}</h3>
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="ui-chip tabular-nums">{{ __('As of :date', ['date' => $asOfLabel]) }}</span>
                @if ($rowsTotal > 0)
                    <span class="ui-chip">{{ trans_choice(':count pool|:count pools', $rowsTotal, ['count' => $rowsTotal]) }}</span>
                @endif
            </div>
        </div>
        <dl class="dashboard-stat-grid">
            <div class="dashboard-stat portfolio-stat portfolio-stat--capital">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7l8 4 8-4-8-4Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 12 8 4 8-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 8 4 8-4" />
                        </svg>
                    </span>
                    {{ __('Your capital') }}
                </dt>
                <dd>{{ $summary['total_capital'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--profit">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l5-5 4 4 7-8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h6v6" />
                        </svg>
                    </span>
                    {{ __('Total profit') }}
                </dt>
                <dd>{{ $summary['total_projected_profit'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--today">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="5" width="16" height="15" rx="2" />
                            <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                        </svg>
                    </span>
                    {{ __('Profit til today') }}
                </dt>
                <dd>{{ $summary['profit_til_today'] }}</dd>
            </div>
            <div class="dashboard-stat portfolio-stat portfolio-stat--withdrawn">
                <dt class="portfolio-stat__label">
                    <span class="portfolio-stat__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v10" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8 10 4 4 4-4" />
                            <path stroke-linecap="round" d="M5 19h14" />
                        </svg>
                    </span>
                    {{ __('Withdrawn') }}
                </dt>
                <dd>{{ $summary['total_withdrawn'] ?? '0.00' }}</dd>
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
                <h3 class="dashboard-stat-panel__title">{{ __('Your investments') }}</h3>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($hasMoreInvestments)
                        <span class="text-sm text-foreground-muted tabular-nums">
                            {{ __(':shown of :total', ['shown' => $previewRows->count(), 'total' => $rowsTotal]) }}
                        </span>
                    @endif
                    @if ($hasMoreInvestments)
                        <a href="{{ $viewAllUrl }}" class="text-sm font-semibold text-primary hover:text-primary-hover">
                            {{ __('View all') }}
                        </a>
                    @endif
                </div>
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
