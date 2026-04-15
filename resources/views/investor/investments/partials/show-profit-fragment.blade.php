@if ($myProfitByMonth->isEmpty())
    <p class="text-sm text-gray-500">
        @if (request()->filled('search'))
            {{ __('No rows match your search.') }}
        @else
            {{ __('No monthly payouts recorded for you on this pool yet.') }}
        @endif
    </p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="py-2 pr-4">{{ __('Month') }}</th>
                    <th class="py-2 pr-4">{{ __('Recorded') }}</th>
                    <th class="py-2 text-right">{{ __('My profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($myProfitByMonth as $row)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-4 tabular-nums">{{ $row->period->month->translatedFormat('F Y') }}</td>
                        <td class="py-2 pr-4 tabular-nums">{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $row->profit_share }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-pagination-per-page
            :paginator="$myProfitByMonth"
            param="pool_profit_per_page"
            reset-page-key="profit_page"
            :fetch-url="route('investments.show', $investment)"
            target-id="pool-profit-fragment"
            ajax-fragment="pool_profit"
        />
        <div class="min-w-0 overflow-x-auto">{{ $myProfitByMonth->links() }}</div>
    </div>
@endif
