@if ($myProfitByMonth->isEmpty())
    <div class="rounded-lg border border-dashed border-line bg-surface-variant px-3 py-6 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No rows match your search.') }}
            @else
                {{ __('No monthly payouts recorded yet.') }}
            @endif
        </p>
    </div>
@else
    <div class="ui-glass-table-wrap overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Month') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Recorded') }}</th>
                    <th class="text-right">{{ __('Profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($myProfitByMonth as $row)
                    <tr>
                        <x-table-serial-cell :paginator="$myProfitByMonth" :index="$loop->index" />
                        <td class="tabular-nums text-foreground-muted">{{ $row->period->month->translatedFormat('M Y') }}</td>
                        <td class="hidden tabular-nums text-foreground-muted sm:table-cell">{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-right tabular-nums font-semibold text-success">{{ $row->profit_share }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer mt-2">
        <x-pagination-per-page
            :paginator="$myProfitByMonth"
            param="pool_profit_per_page"
            reset-page-key="profit_page"
            :fetch-url="route('investments.show', $investment)"
            target-id="pool-profit-fragment"
            ajax-fragment="pool_profit"
        />
        <div class="ui-table-pagination">{{ $myProfitByMonth->links() }}</div>
    </div>
@endif
