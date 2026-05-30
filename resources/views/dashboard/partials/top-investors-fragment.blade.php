@if ($topInvestors->isEmpty())
    <div
        class="rounded-xl border border-dashed border-line bg-surface-secondary px-6 py-12 text-center"
    >
        <div
            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-surface-card text-foreground-muted ring-1 ring-line"
            aria-hidden="true"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
            </svg>
        </div>
        <p class="text-sm font-medium text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No investors match your search.') }}
            @else
                {{ __('No accrual data yet. Run accruals on active investments to see rankings.') }}
            @endif
        </p>
    </div>
@else
    <div class="overflow-x-auto rounded-xl border border-line">
        <table class="ui-table min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3.5">#</th>
                    <th class="px-4 py-3.5">{{ __('Investor') }}</th>
                    <th class="px-4 py-3.5">{{ __('Last payout month') }}</th>
                    <th class="px-4 py-3.5 text-right">{{ __('Tagged capital') }}</th>
                    <th class="px-4 py-3.5 text-right">{{ __('Total profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topInvestors as $row)
                    <tr
                        class="{{ $row->user_id === auth()->id() ? 'bg-primary-muted ring-1 ring-inset ring-primary/30' : '' }}"
                    >
                        <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-foreground-muted">
                            {{ $topInvestors->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-medium text-foreground">{{ $row->name }}</span>
                            <span class="mt-0.5 block text-xs text-foreground-muted">{{ $row->email }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-foreground">
                            {{ $row->last_payout_month ? \Carbon\Carbon::parse($row->last_payout_month)->translatedFormat('F Y') : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right tabular-nums text-foreground">
                            {{ $row->total_contribution }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 text-right tabular-nums font-semibold text-success">
                            {{ $row->total_profit }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$topInvestors"
            param="top_investors_per_page"
            reset-page-key="top_investors_page"
            :fetch-url="route('dashboard')"
            target-id="dashboard-top-investors-fragment"
            ajax-fragment="top_investors"
        />
        <div class="ui-table-pagination">{{ $topInvestors->links() }}</div>
    </div>
@endif
