<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ $investment->title }}</h2>
            <x-action-button :href="route('investments.index')" variant="secondary" class="text-sm">{{ __('Back to portfolio') }}</x-action-button>
        </div>
    </x-slot>

    @php
        $contribution = (float) ($myParticipant?->contribution_amount ?? 0);
        $returnPct = $contribution > 0
            ? number_format(((float) $myTotalProfit / $contribution) * 100, 2, '.', '')
            : null;
        $statusBadge = match ($investment->status) {
            \App\Models\Investment::STATUS_ACTIVE => 'ui-badge-success',
            \App\Models\Investment::STATUS_CLOSED => 'ui-badge-warning',
            default => 'ui-badge-muted',
        };
        $statusLabel = match ($investment->status) {
            \App\Models\Investment::STATUS_ACTIVE => __('Active'),
            \App\Models\Investment::STATUS_CLOSED => __('Closed'),
            default => ucfirst($investment->status),
        };
        $planCompleted = $investment->hasPlanCompleted();
        $daysLeft = $investment->planCompletionDaysRemaining();
    @endphp

    <div class="portfolio-page space-y-4 pb-2">
        <div class="grid gap-4 lg:grid-cols-12">
            <section class="dashboard-stat-panel min-w-0 lg:col-span-8">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Your position') }}</h3>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                        @if ($returnPct !== null)
                            <span class="ui-chip tabular-nums text-success">{{ __('Return :pct%', ['pct' => $returnPct]) }}</span>
                        @endif
                        @if ($investment->participants->isNotEmpty())
                            <span class="ui-chip">{{ trans_choice(':count investor|:count investors', $investment->participants->count(), ['count' => $investment->participants->count()]) }}</span>
                        @endif
                    </div>
                </div>
                <dl class="dashboard-stat-grid sm:grid-cols-3">
                    <div class="dashboard-stat dashboard-stat--capital">
                        <dt>{{ __('My contribution') }}</dt>
                        <dd>
                            @if ($myParticipant)
                                {{ number_format($contribution, 2, '.', '') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ $myDailyProfit ? __('Projected profit') : __('My total profit') }}</dt>
                        <dd>
                            @if ($myDailyProfit)
                                {{ $myDailyProfit['projected_profit'] }}
                            @else
                                {{ number_format((float) $myTotalProfit, 2, '.', '') }}
                            @endif
                        </dd>
                        @if ($myDailyProfit)
                            <p>{{ __('+:amount/day', ['amount' => $myDailyProfit['daily_profit']]) }}</p>
                        @endif
                    </div>
                    @if ($myDailyProfit)
                        <div class="dashboard-stat dashboard-stat--profit">
                            <dt>{{ __('Profit til today') }}</dt>
                            <dd>{{ $myDailyProfit['profit_til_today'] }}</dd>
                        </div>
                    @endif
                    @if ($myDailyProfit && $investment->usesTotalProfitPlan())
                        <div class="dashboard-stat">
                            <dt>{{ __('Avg. monthly profit') }}</dt>
                            <dd class="text-success">
                                @if ($investment->averageMonthlyProfitAmount() !== null)
                                    {{ number_format($investment->averageMonthlyProfitAmount(), 2, '.', '') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if ($investment->deed_completion_deadline)
                        <div @class([
                            'dashboard-stat',
                            'border-error/30 bg-error-muted' => $planCompleted,
                            'border-warning/30 bg-warning-muted' => ! $planCompleted && $daysLeft !== null && $daysLeft <= 30,
                        ])>
                            <dt>{{ __('Plan ends') }}</dt>
                            <dd @class([
                                'text-error' => $planCompleted,
                                'text-warning' => ! $planCompleted && $daysLeft !== null && $daysLeft <= 30,
                            ])>
                                {{ $investment->deed_completion_deadline->translatedFormat('j M Y') }}
                            </dd>
                            @if ($planCompleted)
                                <p>{{ __('Completed') }}</p>
                            @elseif ($daysLeft !== null)
                                <p>{{ $daysLeft === 0 ? __('Ends today') : __(':count days left', ['count' => $daysLeft]) }}</p>
                            @endif
                        </div>
                    @endif
                </dl>
            </section>

            <section class="dashboard-stat-panel min-w-0 lg:col-span-4">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Documents') }}</h3>
                    <span class="text-sm tabular-nums text-foreground-muted">{{ $investment->documents->count() }}</span>
                </div>
                <ul class="divide-y divide-line text-sm">
                    @forelse ($investment->documents as $doc)
                        <li class="flex items-center justify-between gap-2 px-3 py-2">
                            <span class="min-w-0 truncate font-medium text-foreground">{{ $doc->original_name }}</span>
                            <x-action-button :href="route('investments.documents.download', [$investment, $doc])" variant="secondary" class="shrink-0">
                                {{ __('Get') }}
                            </x-action-button>
                        </li>
                    @empty
                        <li class="px-3 py-6 text-center text-foreground-muted">{{ __('No documents.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="dashboard-stat-panel min-w-0">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Profit by month') }}</h3>
                </div>
                <div class="p-3">
                    <x-ajax-table-region :fetch-url="route('investments.show', $investment)" target-id="pool-profit-fragment" ajax-fragment="pool_profit">
                        <x-table-search
                            :fetch-url="route('investments.show', $investment)"
                            target-id="pool-profit-fragment"
                            ajax-fragment="pool_profit"
                            :placeholder="__('Search month or amount…')"
                        />
                        <div id="pool-profit-fragment">
                            @include('investor.investments.partials.show-profit-fragment', compact('investment', 'myProfitByMonth'))
                        </div>
                    </x-ajax-table-region>
                </div>
            </section>

            <section class="dashboard-stat-panel min-w-0">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('Co-investors') }}</h3>
                </div>
                <div class="p-3">
                    @if ($investment->participants->isEmpty())
                        <p class="text-sm text-foreground-muted">{{ __('No participants yet.') }}</p>
                    @else
                        <div class="ui-glass-table-wrap overflow-x-auto">
                            <table class="ui-table min-w-full">
                                <thead>
                                    <tr>
                                        <x-table-serial-header />
                                        <th>{{ __('Name') }}</th>
                                        <th class="text-right">{{ __('Contribution') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($investment->participants as $p)
                                        <tr @class(['bg-primary-muted/30' => $p->user_id === auth()->id()])>
                                            <x-table-serial-cell :index="$loop->index" />
                                            <td>
                                                <x-user-identity :user="$p->user" />
                                            </td>
                                            <td class="text-right tabular-nums">{{ $p->contribution_amount }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
