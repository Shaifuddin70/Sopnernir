@if ($profitByMonth->isEmpty())
    <p class="text-sm text-gray-500">
        @if (request()->filled('profit_search'))
            {{ __('No rows match your search.') }}
        @else
            {{ __('No accrual payouts recorded for your account yet.') }}
        @endif
    </p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="py-2 pr-4">{{ __('Month') }}</th>
                    <th class="py-2 pr-4">{{ __('Investment') }}</th>
                    <th class="py-2 pr-4">{{ __('Recorded') }}</th>
                    <th class="py-2 text-right">{{ __('My profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profitByMonth as $row)
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-4 tabular-nums">{{ $row->period->month->translatedFormat('F Y') }}</td>
                        <td class="py-2 pr-4">{{ $row->period->investment->title }}</td>
                        <td class="py-2 pr-4 tabular-nums">{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2 text-right tabular-nums">{{ number_format((float) $row->profit_share, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-pagination-per-page
            :paginator="$profitByMonth"
            param="profit_per_page"
            reset-page-key="profit_page"
            :fetch-url="route('investments.index')"
            target-id="portfolio-profit-fragment"
            ajax-fragment="profit"
        />
        <div class="min-w-0 overflow-x-auto">{{ $profitByMonth->links() }}</div>
    </div>
@endif
