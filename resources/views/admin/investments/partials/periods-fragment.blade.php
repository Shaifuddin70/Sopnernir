@if ($periods->isEmpty())
    <p class="text-sm text-foreground-muted">
        @if (request()->filled('periods_search'))
            {{ __('No accrual rows match your search.') }}
        @else
            {{ __('No accrual months posted for this pool yet.') }}
        @endif
    </p>
@else
    <div class="overflow-x-auto">
        <table class="ui-table min-w-full text-sm">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Month') }}</th>
                    <th class="text-right">{{ __('Pool profit') }}</th>
                    <th class="text-right">{{ __('Principal') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($periods as $period)
                    <tr>
                        <x-table-serial-cell :paginator="$periods" :index="$loop->index" />
                        <td class="py-2 pr-4 tabular-nums">{{ $period->month->translatedFormat('F Y') }}</td>
                        <td class="py-2 pr-4 text-right tabular-nums">{{ $period->profit_amount }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $period->principal_snapshot }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$periods"
            param="periods_per_page"
            reset-page-key="periods_page"
            :fetch-url="route('admin.investments.show', $investment)"
            target-id="periods-table-fragment"
            ajax-fragment="periods"
        />
        <div class="ui-table-pagination">{{ $periods->links() }}</div>
    </div>
@endif
