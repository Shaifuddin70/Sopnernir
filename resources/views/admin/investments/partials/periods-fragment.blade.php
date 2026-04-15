@if ($periods->isEmpty())
    <p class="text-sm text-gray-500">
        @if (request()->filled('periods_search'))
            {{ __('No accrual rows match your search.') }}
        @else
            {{ __('No accrual months posted for this pool yet.') }}
        @endif
    </p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="py-2 pr-4">{{ __('Month') }}</th>
                    <th class="py-2 pr-4">{{ __('Recorded') }}</th>
                    <th class="py-2 pr-4 text-right">{{ __('Pool profit') }}</th>
                    <th class="py-2 text-right">{{ __('Principal at accrual') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($periods as $period)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-4 tabular-nums">{{ $period->month->translatedFormat('F Y') }}</td>
                        <td class="py-2 pr-4 tabular-nums">{{ $period->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2 pr-4 text-right tabular-nums">{{ $period->profit_amount }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $period->principal_snapshot }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-pagination-per-page
            :paginator="$periods"
            param="periods_per_page"
            reset-page-key="periods_page"
            :fetch-url="route('admin.investments.show', $investment)"
            target-id="periods-table-fragment"
            ajax-fragment="periods"
        />
        <div class="min-w-0 overflow-x-auto">{{ $periods->links() }}</div>
    </div>
@endif
