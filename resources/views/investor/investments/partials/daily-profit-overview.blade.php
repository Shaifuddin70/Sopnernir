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
                    <dt>{{ __('Members') }}</dt>
                    <dd>{{ $platform['member_count'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Per person') }}</dt>
                    <dd>{{ $platform['per_person_profit_til_today'] }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Total til today') }}</dt>
                    <dd>{{ $platform['total_til_today'] }}</dd>
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
            <div class="dashboard-stat dashboard-stat--capital">
                <dt>{{ __('Your capital') }}</dt>
                <dd>{{ $summary['total_capital'] }}</dd>
            </div>
            <div class="dashboard-stat dashboard-stat--profit">
                <dt>{{ __('Total profit') }}</dt>
                <dd>{{ $summary['total_projected_profit'] }}</dd>
            </div>
            <div class="dashboard-stat dashboard-stat--profit">
                <dt>{{ __('Profit til today') }}</dt>
                <dd>{{ $summary['profit_til_today'] }}</dd>
            </div>
            <div class="dashboard-stat">
                <dt>{{ __('Total til today') }}</dt>
                <dd>{{ $summary['total_til_today'] }}</dd>
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
                        <x-action-button :href="$viewAllUrl" variant="secondary" class="text-sm">
                            {{ __('View all') }}
                        </x-action-button>
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
