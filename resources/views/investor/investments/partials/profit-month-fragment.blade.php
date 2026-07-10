@if ($profitByMonth->isEmpty())
    <div class="rounded-lg border border-dashed border-line bg-surface-variant px-3 py-6 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('profit_search'))
                {{ __('No rows match your search.') }}
            @else
                {{ __('No accrual payouts recorded yet.') }}
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
                    <th>{{ __('Pool') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Recorded') }}</th>
                    <th class="text-right">{{ __('Profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profitByMonth as $row)
                    <tr>
                        <x-table-serial-cell :paginator="$profitByMonth" :index="$loop->index" />
                        <td class="whitespace-nowrap tabular-nums text-foreground-muted">{{ $row->period->month->translatedFormat('M Y') }}</td>
                        <td class="font-medium">
                            <a href="{{ route('investments.show', $row->period->investment) }}" class="ui-text-link">
                                {{ $row->period->investment->title }}
                            </a>
                        </td>
                        <td class="hidden tabular-nums text-foreground-muted sm:table-cell">{{ $row->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-right tabular-nums font-semibold text-success">{{ number_format((float) $row->profit_share, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer mt-2">
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
