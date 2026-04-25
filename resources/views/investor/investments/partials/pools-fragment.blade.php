<div class="overflow-x-auto">
<table class="min-w-full text-xs sm:text-sm">
    <thead>
        <tr class="border-b border-gray-200 bg-gray-50 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">
            <th class="py-2 pr-3">{{ __('Title') }}</th>
            <th class="py-2 pr-3">{{ __('Status') }}</th>
            <th class="py-2 pr-3">{{ __('Created') }}</th>
            <th class="py-2 pr-3 text-right">{{ __('Capital') }}</th>
            <th class="py-2 pr-3 text-right">{{ __('Profit') }}</th>
            <th class="py-2 text-right">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            <tr class="border-b border-gray-100">
                <td class="py-2.5 pr-3 font-medium text-gray-900">{{ $inv->title }}</td>
                <td class="py-2.5 pr-3">
                    <span class="inline-flex rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $inv->status }}</span>
                </td>
                <td class="py-2.5 pr-3 tabular-nums text-gray-600">{{ $inv->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2.5 pr-3 text-right tabular-nums">{{ number_format((float) ($inv->my_contribution ?? 0), 2, '.', '') }}</td>
                <td class="py-2.5 pr-3 text-right tabular-nums">{{ number_format((float) ($inv->my_profit ?? 0), 2, '.', '') }}</td>
                <td class="py-2.5 text-right">
                    <x-action-button :href="route('investments.show', $inv)">{{ __('View') }}</x-action-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-4 text-sm text-gray-500">
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
    <p class="mt-3 text-sm text-gray-700">
        <span class="font-medium">{{ __('Total profit (all your pools)') }}:</span>
        <span class="tabular-nums">{{ number_format($portfolioProfitTotal, 2, '.', '') }}</span>
    </p>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-pagination-per-page
            :paginator="$investments"
            param="pools_per_page"
            reset-page-key="page"
            :fetch-url="route('investments.index')"
            target-id="portfolio-pools-fragment"
            ajax-fragment="pools"
        />
        <div class="min-w-0 overflow-x-auto">{{ $investments->links() }}</div>
    </div>
@endif
