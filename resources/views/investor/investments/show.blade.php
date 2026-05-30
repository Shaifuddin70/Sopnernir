<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">{{ $investment->title }}</h2>
            <x-action-button :href="route('investments.index')" class="text-sm">{{ __('Back to portfolio') }}</x-action-button>
        </div>
    </x-slot>

    <div class="space-y-4">
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

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.75fr)_minmax(0,1fr)] lg:items-start">
            <section class="ui-card min-w-0 overflow-hidden">
                <div class="ui-card-header">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="ui-card-header-title">{{ __('Pool overview') }}</h3>
                            <p class="ui-card-header-subtitle">{{ __('Your position and key terms for this pool.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                            @if ($investment->deed_completion_deadline)
                                @if ($planCompleted)
                                    <span class="ui-badge-warning">{{ __('Plan completed') }}</span>
                                @elseif ($daysLeft !== null && $daysLeft <= 30)
                                    <span class="ui-badge-warning">
                                        @if ($daysLeft === 0)
                                            {{ __('Ends today') }}
                                        @else
                                            {{ __(':count days left', ['count' => $daysLeft]) }}
                                        @endif
                                    </span>
                                @else
                                    <span class="ui-badge-brand">{{ __('In progress') }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-3 sm:p-4">
                    <dl @class([
                        'grid gap-3 grid-cols-1 sm:grid-cols-2',
                        'lg:grid-cols-4' => $investment->deed_completion_deadline,
                        'lg:grid-cols-3' => ! $investment->deed_completion_deadline,
                    ])>
                    <div class="ui-stat-tile-capital p-4 sm:p-5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                            {{ __('My contribution') }}
                        </dt>
                        <dd class="mt-2 text-xl font-bold tabular-nums tracking-tight text-foreground lg:text-2xl">
                            @if ($myParticipant)
                                {{ number_format($contribution, 2, '.', '') }}
                            @else
                                —
                            @endif
                        </dd>
                        <p class="mt-2 text-xs text-foreground-muted">
                            {{ __('Capital you have tagged on this pool.') }}
                        </p>
                    </div>

                    <div class="ui-stat-tile-profit p-4 sm:p-5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                            {{ __('My total profit') }}
                        </dt>
                        <dd class="mt-2 text-xl font-bold tabular-nums tracking-tight text-success lg:text-2xl">
                            {{ number_format((float) $myTotalProfit, 2, '.', '') }}
                        </dd>
                        @if ($returnPct !== null)
                            <p class="mt-2 text-xs font-medium text-success">
                                {{ __('Return on contribution: :pct%', ['pct' => $returnPct]) }}
                            </p>
                        @else
                            <p class="mt-2 text-xs text-foreground-muted">
                                {{ __('Profit earned on this pool so far.') }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-line bg-surface-secondary p-4 sm:p-5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                            {{ __('Default monthly rate') }}
                        </dt>
                        <dd class="mt-2 flex items-center gap-2">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-muted text-primary ring-1 ring-primary/20"
                                aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </span>
                            <span class="text-xl font-bold tabular-nums text-foreground">
                                {{ $investment->default_monthly_rate_pct }}%
                            </span>
                        </dd>
                    </div>

                    @if ($investment->deed_completion_deadline)
                        <div @class([
                            'rounded-lg border p-4 sm:p-5',
                            'border-error/30 bg-error-muted' => $planCompleted,
                            'border-warning/30 bg-warning-muted' => ! $planCompleted && $daysLeft !== null && $daysLeft <= 30,
                            'border-primary/20 bg-primary-muted' => ! $planCompleted && ($daysLeft === null || $daysLeft > 30),
                        ])>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">
                                {{ __('Plan completion date') }}
                            </dt>
                            <dd @class([
                                'mt-2 text-base font-semibold tabular-nums',
                                'text-error' => $planCompleted,
                                'text-warning' => ! $planCompleted && $daysLeft !== null && $daysLeft <= 30,
                                'text-primary' => ! $planCompleted && ($daysLeft === null || $daysLeft > 30),
                            ])>
                                {{ $investment->deed_completion_deadline->translatedFormat('j F Y') }}
                            </dd>
                            <p class="mt-1 text-xs text-foreground-muted">
                                @if ($planCompleted)
                                    {{ __('This pool has finished its planned term.') }}
                                @elseif ($daysLeft !== null)
                                    @if ($daysLeft === 0)
                                        {{ __('Plan ends today.') }}
                                    @else
                                        {{ __(':count days remaining on the plan.', ['count' => $daysLeft]) }}
                                    @endif
                                @endif
                            </p>
                        </div>
                    @endif
                </dl>

                    <p class="border-t border-line pt-4 text-xs leading-relaxed text-foreground-muted">
                        {{ __('Each month, pool profit is split among everyone tagged on the pool based on their contribution.') }}
                    </p>
                </div>
            </section>

            <section class="ui-card min-w-0">
                <div class="ui-card-header">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-muted text-primary ring-1 ring-primary/20"
                            aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="ui-card-header-title">{{ __('Documents') }}</h3>
                            <p class="ui-card-header-subtitle">{{ __('Files shared for this pool.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 sm:p-4">
                    <ul class="divide-y divide-line overflow-hidden rounded-lg border border-line bg-surface-secondary text-sm">
                        @forelse ($investment->documents as $doc)
                            <li class="flex items-center justify-between gap-2 bg-surface-card px-3 py-2.5 transition hover:bg-surface-variant">
                                <span class="min-w-0 truncate font-medium text-foreground">{{ $doc->original_name }}</span>
                                <x-action-button :href="route('investments.documents.download', [$investment, $doc])" variant="secondary" class="shrink-0 text-xs">
                                    {{ __('Download') }}
                                </x-action-button>
                            </li>
                        @empty
                            <li class="px-3 py-8 text-center text-foreground-muted">
                                <svg class="mx-auto h-8 w-8 text-line" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm">{{ __('No documents uploaded.') }}</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2 lg:items-start">
            <section class="ui-card min-w-0">
                <div class="ui-card-header">
                    <h3 class="ui-card-header-title">{{ __('My profit by month (this pool)') }}</h3>
                    <p class="ui-card-header-subtitle">{{ __('Your share each month on this pool.') }}</p>
                </div>
                <div class="p-3 sm:p-4">
                    <x-ajax-table-region :fetch-url="route('investments.show', $investment)" target-id="pool-profit-fragment" ajax-fragment="pool_profit">
                        <x-table-search :fetch-url="route('investments.show', $investment)" target-id="pool-profit-fragment" ajax-fragment="pool_profit"
                            :placeholder="__('Search by month or profit amount…')" />
                        <div id="pool-profit-fragment">
                            @include(
                                'investor.investments.partials.show-profit-fragment',
                                compact('investment', 'myProfitByMonth'))
                        </div>
                    </x-ajax-table-region>
                </div>
            </section>

            <section class="ui-card min-w-0">
                <div class="ui-card-header">
                    <h3 class="ui-card-header-title">{{ __('Co-investors') }}</h3>
                </div>
                <div class="p-3 sm:p-4">
                    @if ($investment->participants->isEmpty())
                        <p class="text-sm text-foreground-muted">{{ __('No participants on this pool yet.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="ui-table min-w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="py-2 pr-4">{{ __('Name') }}</th>
                                        <th class="py-2 text-right">{{ __('Contribution') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($investment->participants as $p)
                                        <tr>
                                            <td class="py-2 pr-4 font-medium text-foreground">{{ $p->user->name }}</td>
                                            <td class="py-2 text-right tabular-nums">{{ $p->contribution_amount }}</td>
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
