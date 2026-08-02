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
    <div
        class="hidden flex flex-wrap items-end justify-between gap-3 rounded-xl border border-primary/20 bg-primary-muted/40 px-3 py-3 sm:px-4"
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
                    class="rounded-lg border-line bg-surface-card text-sm shadow-sm focus:border-primary focus:ring-primary"
                    data-bulk-through-date
                >
            </div>
        </div>
        <x-action-button type="button" data-bulk-withdraw-open>{{ __('Withdraw selected') }}</x-action-button>
    </div>

    <div class="overflow-x-auto">
    <table class="ui-table min-w-full">
        <thead>
            <tr>
                <th class="w-10 py-2 pr-2 text-center">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                        data-withdraw-check-all
                        aria-label="{{ __('Select all') }}"
                        title="{{ __('Select all') }}"
                    >
                </th>
                <x-table-serial-header />
                <th class="py-2 pr-3">{{ __('Title') }}</th>
                <th class="py-2 pr-3">{{ __('Status') }}</th>
                <th class="py-2 pr-3">{{ __('Listed') }}</th>
                <th class="py-2 pr-3">{{ __('Deed number') }}</th>
                <th class="py-2 pr-3">{{ __('Starts') }}</th>
                <th class="py-2 pr-3">{{ __('Ends') }}</th>
                <th class="py-2 pr-3 text-right">{{ __('Total amount') }}</th>
                <th class="py-2 pr-3 text-right">{{ __('Profit amount') }}</th>
                <th class="py-2 pr-3 text-right">{{ __('Profit til today') }}</th>
                <th class="py-2 pr-3 text-right">{{ __('Withdrawn') }}</th>
                <th class="py-2 pr-3">{{ __('Users') }}</th>
                <th class="py-2 text-right">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($investments as $inv)
            @php
                $poolDaily = app(\App\Services\InvestmentDailyProfitService::class)->poolProjection($inv);
                $profitTilToday = $poolDaily ? (float) $poolDaily['profit_til_today'] : 0.0;
                $withdrawn = (float) ($inv->profit_withdrawn_total ?? ($poolDaily['withdrawn'] ?? 0));
            @endphp
            <tr class="align-top" data-withdraw-row data-investment-id="{{ $inv->id }}">
                <td class="py-2.5 pr-2 text-center">
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                        value="{{ $inv->id }}"
                        data-withdraw-check
                        aria-label="{{ __('Select :title', ['title' => $inv->title]) }}"
                    >
                </td>
                <x-table-serial-cell :paginator="$investments" :index="$loop->index" />
                <td class="py-2.5 pr-3">
                    <p class="font-medium">
                        <a href="{{ route('admin.investments.show', $inv) }}" class="ui-text-link">{{ $inv->title }}</a>
                    </p>
                    @if ($inv->notes)
                        <p class="mt-0.5 line-clamp-1 text-sm text-foreground-muted">{{ $inv->notes }}</p>
                    @endif
                </td>
                <td class="py-2.5 pr-3">
                    @php
                        $statusBadge = match ($inv->status) {
                            \App\Models\Investment::STATUS_ACTIVE => 'ui-badge-success',
                            \App\Models\Investment::STATUS_CLOSED => 'ui-badge-warning',
                            default => 'ui-badge-muted',
                        };
                    @endphp
                    <span class="{{ $statusBadge }}">{{ $inv->status }}</span>
                </td>
                <td class="py-2.5 pr-3">
                    @if ($inv->is_active)
                        <span class="ui-badge-success">{{ __('Active') }}</span>
                    @else
                        <span class="ui-badge-muted">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="py-2.5 pr-3 tabular-nums text-foreground-muted">{{ $inv->deed_no ?? '—' }}</td>
                <td class="py-2.5 pr-3 tabular-nums text-foreground-muted">{{ $inv->period_start?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2.5 pr-3 tabular-nums text-foreground-muted">
                    @if ($inv->deed_completion_deadline)
                        <span @class([
                            'font-medium' => $inv->hasPlanCompleted(),
                            'text-error' => $inv->hasPlanCompleted(),
                        ])>{{ $inv->deed_completion_deadline->format('Y-m-d') }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="py-2.5 pr-3 text-right tabular-nums">
                    {{ $inv->total_invested_amount !== null ? number_format((float) $inv->total_invested_amount, 2, '.', '') : '—' }}
                </td>
                <td class="py-2.5 pr-3 text-right tabular-nums text-success">
                    {{ $inv->total_profit_amount !== null ? number_format((float) $inv->total_profit_amount, 2, '.', '') : '—' }}
                </td>
                <td class="py-2.5 pr-3 text-right tabular-nums font-medium text-success">
                    {{ $poolDaily ? number_format($profitTilToday, 2, '.', '') : '—' }}
                </td>
                <td class="py-2.5 pr-3 text-right tabular-nums text-foreground-muted">
                    {{ number_format($withdrawn, 2, '.', '') }}
                </td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->participants_count }}</td>
                <td class="py-2.5 text-right">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-action-button
                            type="button"
                            data-row-withdraw-open
                            data-investment-id="{{ $inv->id }}"
                            data-investment-title="{{ $inv->title }}"
                        >{{ __('Withdraw') }}</x-action-button>
                        <x-action-button :href="route('admin.investments.show', $inv)">{{ __('Manage') }}</x-action-button>
                        <x-action-button :href="route('admin.investments.edit', $inv)" variant="secondary">{{ __('Edit') }}</x-action-button>
                        <form method="post" action="{{ route('admin.investments.active', $inv) }}" class="inline">
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
        @empty
            <tr>
                <td colspan="14" class="py-4 text-sm text-foreground-muted">
                    @if (request()->filled('search'))
                        {{ __('No investments match your search.') }}
                    @else
                        {{ __('No investments yet.') }}
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$investments"
            param="per_page"
            reset-page-key="page"
            :fetch-url="route('admin.investments.index')"
            target-id="investments-table-fragment"
        />
        <div class="ui-table-pagination">{{ $investments->links() }}</div>
    </div>
</div>
