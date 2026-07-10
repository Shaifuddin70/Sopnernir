<div class="overflow-x-auto">
<table class="ui-table min-w-full">
    <thead>
        <tr>
            <x-table-serial-header />
            <th>{{ __('Title') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Created') }}</th>
            <th class="text-right">{{ __('Capital') }}</th>
            <th class="text-right">{{ __('Profit') }}</th>
            <th class="text-right">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            <tr>
                <x-table-serial-cell :paginator="$investments" :index="$loop->index" />
                <td class="font-medium">
                    <a href="{{ route('investments.show', $inv) }}" class="ui-text-link">{{ $inv->title }}</a>
                </td>
                <td>
                    <span class="ui-badge-muted">{{ $inv->status }}</span>
                </td>
                <td class="tabular-nums text-foreground-muted">{{ $inv->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="text-right tabular-nums">{{ number_format((float) ($inv->my_contribution ?? 0), 2, '.', '') }}</td>
                <td class="text-right tabular-nums text-success">{{ number_format((float) ($inv->my_profit ?? 0), 2, '.', '') }}</td>
                <td class="text-right">
                    <x-action-button :href="route('investments.show', $inv)">{{ __('View') }}</x-action-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-4 text-sm text-foreground-muted">
                    @if (request()->filled('search'))
                        {{ __('No pools match your search.') }}
                    @else
                        {{ __('You are not tagged on any investment yet.') }}
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
@if ($investments->total() > 0)
    <p class="mt-3 text-sm text-foreground">
        <span class="font-medium">{{ __('Total profit (all your pools)') }}:</span>
        <span class="tabular-nums text-success">{{ number_format($portfolioProfitTotal, 2, '.', '') }}</span>
    </p>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$investments"
            param="pools_per_page"
            reset-page-key="page"
            :fetch-url="route('investments.index')"
            target-id="portfolio-pools-fragment"
            ajax-fragment="pools"
        />
        <div class="ui-table-pagination">{{ $investments->links() }}</div>
    </div>
@endif
