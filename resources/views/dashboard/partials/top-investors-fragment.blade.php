@if ($topInvestors->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant px-4 py-10 text-center">
        <div
            class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full border border-line bg-surface-card text-foreground-muted"
            aria-hidden="true"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                />
            </svg>
        </div>
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No investors match your search.') }}
            @else
                {{ __('No accrual data yet. Run accruals on active investments to see rankings.') }}
            @endif
        </p>
    </div>
@else
    <div class="ui-glass-table-wrap overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Investor') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Last payout month') }}</th>
                    <th class="text-right">{{ __('Tagged capital') }}</th>
                    <th class="text-right">{{ __('Total profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topInvestors as $row)
                    <tr @class(['bg-primary-muted/40' => $row->user_id === auth()->id()])>
                        <x-table-serial-cell :paginator="$topInvestors" :index="$loop->index" />
                        <td>
                            <x-user-identity
                                :name="$row->name"
                                :image-url="$row->profile_image_url"
                                :subtitle="$row->email"
                            />
                        </td>
                        <td class="hidden whitespace-nowrap tabular-nums text-foreground-muted sm:table-cell">
                            {{ $row->last_payout_month ? \Carbon\Carbon::parse($row->last_payout_month)->translatedFormat('F Y') : '—' }}
                        </td>
                        <td class="whitespace-nowrap text-right tabular-nums">{{ $row->total_contribution }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums font-semibold text-success">{{ $row->total_profit }}</td>
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
