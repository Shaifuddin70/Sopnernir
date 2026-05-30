@if ($profitByMonth->isEmpty())
    <p class="text-sm text-foreground-muted">
        @if (request()->filled('profit_search'))
            {{ __('No rows match your search.') }}
        @else
            {{ __('No accrual payouts recorded for your account yet.') }}
        @endif
    </p>
@else
    <div class="overflow-x-auto">
        <table class="ui-table min-w-full text-xs sm:text-sm">
            <thead>
                <tr>
                    <th>{{ __('Month') }}</th>
                    <th>{{ __('Investment') }}</th>
                    <th>{{ __('Recorded') }}</th>
                    <th class="text-right">{{ __('My profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profitByMonth as $row)
                    <tr>
                        <td class="tabular-nums text-foreground-muted">{{ $row->period->month->translatedFormat('F Y') }}</td>
                        <td class="font-medium">{{ $row->period->investment->title }}</td>
                        <td class="tabular-nums text-foreground-muted">{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-right tabular-nums font-medium text-success">{{ number_format((float) $row->profit_share, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$profitByMonth"
            param="profit_per_page"
            reset-page-key="profit_page"
            :fetch-url="route('investments.index')"
            target-id="portfolio-profit-fragment"
            ajax-fragment="profit"
        />
        <div class="ui-table-pagination">{{ $profitByMonth->links() }}</div>
    </div>
@endif
