<div class="overflow-x-auto">
<table class="ui-table min-w-full">
    <thead>
        <tr>
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
            <th class="py-2 pr-3">{{ __('Users') }}</th>
            <th class="py-2 text-right">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            @php
                $poolDaily = app(\App\Services\InvestmentDailyProfitService::class)->poolProjection($inv);
            @endphp
            <tr class="align-top">
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
                    {{ $poolDaily ? number_format($poolDaily['profit_til_today'], 2, '.', '') : '—' }}
                </td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->participants_count }}</td>
                <td class="py-2.5 text-right">
                    <div class="flex flex-wrap items-center justify-end gap-2">
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
                <td colspan="12" class="py-4 text-sm text-foreground-muted">
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
