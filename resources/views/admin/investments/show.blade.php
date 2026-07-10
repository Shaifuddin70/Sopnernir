<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-foreground leading-tight">{{ $investment->title }}</h2>
            <div class="flex flex-wrap gap-2">
                <x-action-button :href="route('admin.investments.edit', [$investment, 'return' => 'show'])" variant="secondary">{{ __('Edit') }}</x-action-button>
                <x-action-button :href="route('admin.investments.index')" variant="secondary">{{ __('List') }}</x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @php
            $poolStatus = $investment->status;
            $statusBadge = match ($poolStatus) {
                \App\Models\Investment::STATUS_ACTIVE => 'ui-badge-success',
                \App\Models\Investment::STATUS_CLOSED => 'ui-badge-warning',
                default => 'ui-badge-muted',
            };
            $statusLabel = match ($poolStatus) {
                \App\Models\Investment::STATUS_ACTIVE => __('Active'),
                \App\Models\Investment::STATUS_CLOSED => __('Closed'),
                default => __('Draft'),
            };
            $listedBadge = $investment->is_active ? 'ui-badge-brand' : 'ui-badge-muted';
            $planCompleted = $investment->hasPlanCompleted();
            $daysLeft = $investment->planCompletionDaysRemaining();
            $dailyProfit = $investment->dailyPoolProfitFromTotal();
        @endphp

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                        <h3 class="ui-card-header-title">{{ __('Pool overview') }}</h3>
                        <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                        <span class="{{ $listedBadge }}">{{ $investment->is_active ? __('Listed') : __('Hidden') }}</span>
                        @if ($investment->deed_completion_deadline)
                            @if ($planCompleted)
                                <span class="ui-badge-error">{{ __('Plan completed') }}</span>
                            @elseif ($daysLeft === 0)
                                <span class="ui-badge-warning">{{ __('Ends today') }}</span>
                            @elseif ($daysLeft !== null && $daysLeft <= 30)
                                <span class="ui-badge-warning">{{ __(':count days left', ['count' => $daysLeft]) }}</span>
                            @endif
                        @endif
                    </div>
                    <form method="post" action="{{ route('admin.investments.active', $investment) }}" class="shrink-0">
                        @csrf
                        @method('patch')
                        @if ($investment->is_active)
                            <x-secondary-button type="submit" class="text-sm">{{ __('Set inactive') }}</x-secondary-button>
                        @else
                            <x-primary-button type="submit" class="text-sm">{{ __('Set active') }}</x-primary-button>
                        @endif
                    </form>
                </div>
            </div>

            <div class="space-y-4 p-3 sm:p-4">
                <dl class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                    <div class="ui-stat-tile rounded-lg p-3 sm:p-4">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Tagged investors') }}</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-foreground sm:text-xl">
                            {{ $investment->participants_count ?? 0 }}
                        </dd>
                    </div>
                    <div class="ui-stat-tile-capital rounded-lg p-3 sm:p-4">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Total invested') }}</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-foreground sm:text-xl">
                            {{ $investment->total_invested_amount !== null ? number_format((float) $investment->total_invested_amount, 2, '.', '') : '—' }}
                        </dd>
                    </div>
                    <div class="ui-stat-tile-capital rounded-lg p-3 sm:p-4">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Tagged capital') }}</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-foreground sm:text-xl">
                            {{ number_format((float) ($investment->participants_sum_contribution_amount ?? 0), 2, '.', '') }}
                        </dd>
                    </div>
                    <div class="ui-stat-tile-profit rounded-lg p-3 sm:p-4">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Planned profit') }}</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-success sm:text-xl">
                            {{ $investment->total_profit_amount !== null ? number_format((float) $investment->total_profit_amount, 2, '.', '') : '—' }}
                        </dd>
                        @if ($dailyProfit)
                            <dd class="mt-1 text-sm tabular-nums text-foreground-muted">
                                {{ __(':amount / day', ['amount' => number_format($dailyProfit, 2, '.', '')]) }}
                            </dd>
                        @endif
                        @if ($investment->averageMonthlyProfitAmount() !== null)
                            <dd class="mt-0.5 text-sm tabular-nums text-foreground-muted">
                                {{ __('Avg. :amount / month', ['amount' => number_format($investment->averageMonthlyProfitAmount(), 2, '.', '')]) }}
                            </dd>
                        @endif
                    </div>
                    <div class="ui-stat-tile-profit rounded-lg p-3 sm:p-4">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Posted profit') }}</dt>
                        <dd class="mt-1 text-lg font-bold tabular-nums text-success sm:text-xl">
                            {{ number_format((float) ($investment->periods_sum_profit_amount ?? 0), 2, '.', '') }}
                        </dd>
                    </div>
                </dl>

                <dl class="grid gap-3 border-t border-line pt-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Deed number') }}</dt>
                        <dd class="mt-0.5 font-medium tabular-nums text-foreground">{{ $investment->deed_no ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Contribution each') }}</dt>
                        <dd class="mt-0.5 font-medium tabular-nums text-foreground">
                            {{ $investment->contribution_per_investor !== null ? number_format((float) $investment->contribution_per_investor, 2, '.', '') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Starting date') }}</dt>
                        <dd class="mt-0.5 font-medium text-foreground">{{ $investment->period_start?->translatedFormat('j M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Tagged from payment month') }}</dt>
                        <dd class="mt-0.5 font-medium text-foreground">{{ $investment->tagged_payment_month?->translatedFormat('F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Ending date') }}</dt>
                        <dd class="mt-0.5 font-medium text-foreground">
                            {{ $investment->deed_completion_deadline?->translatedFormat('j M Y') ?? '—' }}
                        </dd>
                    </div>
                </dl>

                @if ($investment->notes)
                    <p class="border-t border-line pt-3 text-sm text-foreground whitespace-pre-wrap">{{ $investment->notes }}</p>
                @endif
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <h3 class="ui-card-header-title">{{ __('Tagged investors') }}</h3>
            </div>
            <div class="space-y-4 p-3 sm:p-4">
                @include('admin.investments.partials.tag-investors-form')

                <x-ajax-table-region :fetch-url="route('admin.investments.show', $investment)" target-id="participants-table-fragment" ajax-fragment="participants">
                    <x-table-search
                        :fetch-url="route('admin.investments.show', $investment)"
                        target-id="participants-table-fragment"
                        param="participants_search"
                        ajax-fragment="participants"
                        :placeholder="__('Search investors…')"
                    />
                    <div id="participants-table-fragment">
                        @include('admin.investments.partials.participants-fragment', compact('investment', 'participants'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="ui-card-header-title">{{ __('Monthly accruals') }}</h3>
                    @can('accrue', $investment)
                        @if ($investment->status === 'active' && $investment->is_active)
                            <form method="post" action="{{ route('admin.investments.accruals.fill-missing', $investment) }}">
                                @csrf
                                <x-secondary-button type="submit" class="text-sm">{{ __('Fill missing months') }}</x-secondary-button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
            <div class="p-3 sm:p-4">
                @if ($errors->has('accrual'))
                    <p class="mb-3 text-sm text-red-600">{{ $errors->first('accrual') }}</p>
                @endif
                <x-ajax-table-region :fetch-url="route('admin.investments.show', $investment)" target-id="periods-table-fragment" ajax-fragment="periods">
                    <x-table-search
                        :fetch-url="route('admin.investments.show', $investment)"
                        target-id="periods-table-fragment"
                        param="periods_search"
                        ajax-fragment="periods"
                        :placeholder="__('Search months…')"
                    />
                    <div id="periods-table-fragment">
                        @include('admin.investments.partials.periods-fragment', compact('investment', 'periods'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="ui-card-header-title">{{ __('Documents') }}</h3>
                    <form method="post" action="{{ route('admin.investments.documents.store', $investment) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="max-w-[14rem] text-sm" required />
                        <x-primary-button type="submit" class="text-sm">{{ __('Upload') }}</x-primary-button>
                    </form>
                </div>
            </div>
            <div class="p-3 sm:p-4">
                <x-input-error :messages="$errors->get('file')" class="mb-3" />
                @if ($investment->documents->isEmpty())
                    <p class="text-sm text-foreground-muted">{{ __('No documents yet.') }}</p>
                @else
                    <ul class="divide-y divide-line rounded-lg border border-line text-sm">
                        @foreach ($investment->documents as $doc)
                            <li class="flex flex-wrap items-center justify-between gap-2 px-3 py-2">
                                <span class="min-w-0 truncate">{{ $doc->original_name }}</span>
                                <div class="flex shrink-0 gap-2">
                                    <x-action-button :href="route('admin.investments.documents.download', [$investment, $doc])" class="text-sm">{{ __('Download') }}</x-action-button>
                                    <form method="post" action="{{ route('admin.investments.documents.destroy', [$investment, $doc]) }}" onsubmit="return confirm('{{ __('Remove this file?') }}');">
                                        @csrf
                                        @method('delete')
                                        <x-action-button variant="danger" class="text-sm">{{ __('Remove') }}</x-action-button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>

    @if ($editingInvestment)
        <x-modal name="edit-investment" :show="true" focusable maxWidth="3xl">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h3 class="text-lg font-semibold text-foreground">{{ __('Edit investment') }}</h3>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-foreground-muted transition hover:bg-surface-secondary hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                    @click="window.location.href = '{{ route('admin.investments.show', $investment) }}'"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[calc(100vh-5rem)] overflow-y-auto px-6 py-4">
                @include('admin.investments.partials.edit-form', ['investment' => $editingInvestment, 'return' => 'show'])
            </div>
        </x-modal>
    @endif
</x-app-layout>
