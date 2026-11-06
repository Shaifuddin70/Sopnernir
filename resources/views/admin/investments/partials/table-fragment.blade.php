@php
    $today = now()->toDateString();
@endphp
<div
    class="space-y-3"
    data-profit-withdrawals
    data-preview-url="{{ route('admin.investments.profit-withdrawals.preview') }}"
    data-bulk-url="{{ route('admin.investments.profit-withdrawals.bulk') }}"
    data-store-url-template="{{ route('admin.investments.profit-withdrawals.store', ['investment' => '__ID__']) }}"
    data-label-selected="{{ __('Selected: :count') }}"
    data-label-preview-error="{{ __('Could not calculate withdrawable profit.') }}"
    data-label-none="{{ __('No withdrawable profit for the selection.') }}"
>
    @if ($investments->isEmpty())
        <div class="rounded-xl border border-dashed border-[#e3eaf2] bg-white/50 px-3 py-10 text-center">
            <p class="text-sm text-foreground-muted">
                @if (request()->filled('search'))
                    {{ __('No investments match your search.') }}
                @else
                    {{ __('No investments yet.') }}
                @endif
            </p>
        </div>
    @else
        <div
            class="hidden flex flex-wrap items-end justify-between gap-3 rounded-2xl border border-[#d6e6f8] bg-[#eef5ff] px-3 py-3 sm:px-4"
            data-withdraw-bulk-bar
        >
            <div class="flex flex-wrap items-end gap-3">
                <p class="text-sm font-medium text-foreground" data-withdraw-selected-count>{{ __('Selected: 0') }}</p>
                <div>
                    <label class="mb-1 block text-xs font-medium text-foreground-muted" for="bulk-withdraw-through-date">{{ __('Through date') }}</label>
                    <input
                        id="bulk-withdraw-through-date"
                        type="date"
                        max="{{ $today }}"
                        value="{{ $today }}"
                        class="ui-input !mt-0 h-10 w-auto"
                        data-bulk-through-date
                    >
                </div>
            </div>
            <x-action-button type="button" data-bulk-withdraw-open>{{ __('Withdraw selected') }}</x-action-button>
        </div>

        <div class="ui-glass-table-wrap overflow-x-auto">
            <table class="ui-table min-w-full">
                <thead>
                    <tr>
                        <th class="w-10 text-center">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                data-withdraw-check-all
                                aria-label="{{ __('Select all') }}"
                                title="{{ __('Select all') }}"
                            >
                        </th>
                        <x-table-serial-header />
                        <th>{{ __('Investment') }}</th>
                        <th class="text-right">{{ __('Total') }}</th>
                        <th class="text-right">{{ __('Profit') }}</th>
                        <th class="text-right">{{ __('Profit til today') }}</th>
                        <th class="text-right">{{ __('Withdrawn') }}</th>
                        <th class="text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($investments as $inv)
                        @php
                            $poolDaily = app(\App\Services\InvestmentDailyProfitService::class)->poolProjection($inv);
                            $profitTilToday = $poolDaily ? (float) $poolDaily['profit_til_today'] : 0.0;
                            $withdrawn = (float) ($inv->profit_withdrawn_total ?? ($poolDaily['withdrawn'] ?? 0));
                            $periodStart = $inv->period_start?->translatedFormat('j M Y');
                            $periodEnd = $inv->deed_completion_deadline?->translatedFormat('j M Y');
                            $statusLabel = match ($inv->status) {
                                \App\Models\Investment::STATUS_ACTIVE => __('Active'),
                                \App\Models\Investment::STATUS_CLOSED => __('Closed'),
                                default => __('Draft'),
                            };
                            $statusBadge = match ($inv->status) {
                                \App\Models\Investment::STATUS_ACTIVE => 'ui-badge-success',
                                \App\Models\Investment::STATUS_CLOSED => 'ui-badge-warning',
                                default => 'ui-badge-muted',
                            };
                        @endphp
                        <tr data-withdraw-row data-investment-id="{{ $inv->id }}">
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                    value="{{ $inv->id }}"
                                    data-withdraw-check
                                    aria-label="{{ __('Select :title', ['title' => $inv->title]) }}"
                                >
                            </td>
                            <x-table-serial-cell :paginator="$investments" :index="$loop->index" />
                            <td class="min-w-[16rem]">
                                <a href="{{ route('admin.investments.show', $inv) }}" class="ui-text-link">{{ $inv->title }}</a>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                                    @if ($inv->is_active)
                                        <span class="ui-badge-brand">{{ __('Listed') }}</span>
                                    @else
                                        <span class="ui-badge-muted">{{ __('Unlisted') }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm leading-snug text-foreground-muted">
                                    @if ($inv->deed_no)
                                        <span class="tabular-nums">{{ __('Deed') }} {{ $inv->deed_no }}</span>
                                        <span class="mx-1" aria-hidden="true">·</span>
                                    @endif
                                    @if ($periodStart || $periodEnd)
                                        <span class="tabular-nums">{{ $periodStart ?? '—' }} – {{ $periodEnd ?? '—' }}</span>
                                        <span class="mx-1" aria-hidden="true">·</span>
                                    @endif
                                    <span>{{ trans_choice(':count person|:count people', $inv->participants_count, ['count' => $inv->participants_count]) }}</span>
                                </p>
                                @if ($inv->notes)
                                    <p class="mt-0.5 line-clamp-1 text-sm text-foreground-muted">{{ $inv->notes }}</p>
                                @endif
                            </td>
                            <td class="text-right tabular-nums font-medium">
                                {{ $inv->total_invested_amount !== null ? number_format((float) $inv->total_invested_amount, 2, '.', '') : '—' }}
                            </td>
                            <td class="text-right tabular-nums font-semibold text-success">
                                {{ $inv->total_profit_amount !== null ? number_format((float) $inv->total_profit_amount, 2, '.', '') : '—' }}
                            </td>
                            <td class="text-right tabular-nums font-semibold text-success">
                                {{ $poolDaily ? number_format($profitTilToday, 2, '.', '') : '—' }}
                            </td>
                            <td class="text-right tabular-nums text-foreground-muted">
                                {{ number_format($withdrawn, 2, '.', '') }}
                            </td>
                            <td class="text-right">
                                <div class="table-row-actions investment-row-actions flex flex-nowrap items-center justify-end gap-2">
                                    <x-action-button
                                        type="button"
                                        data-row-withdraw-open
                                        data-investment-id="{{ $inv->id }}"
                                        data-investment-title="{{ $inv->title }}"
                                    >{{ __('Withdraw') }}</x-action-button>
                                    <x-action-button :href="route('admin.investments.show', $inv)" variant="secondary">{{ __('Manage') }}</x-action-button>
                                    <x-action-button :href="route('admin.investments.edit', $inv)" variant="secondary">{{ __('Edit') }}</x-action-button>
                                    <form method="post" action="{{ route('admin.investments.active', $inv) }}" class="inline-flex">
                                        @csrf
                                        @method('patch')
                                        @if ($inv->is_active)
                                            <x-action-button variant="secondary">{{ __('Deactivate') }}</x-action-button>
                                        @else
                                            <x-action-button>{{ __('Activate') }}</x-action-button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ui-table-footer mt-2">
            <x-pagination-per-page
                :paginator="$investments"
                param="per_page"
                reset-page-key="page"
                :fetch-url="route('admin.investments.index')"
                target-id="investments-table-fragment"
            />
            <div class="ui-table-pagination">{{ $investments->links() }}</div>
        </div>
    @endif
</div>
