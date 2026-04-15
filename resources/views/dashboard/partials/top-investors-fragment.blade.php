@if ($topInvestors->isEmpty())
    <div
        class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center shadow-inner"
    >
        <div
            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 ring-1 ring-gray-200/80"
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
        <p class="text-sm font-medium text-gray-700">
            @if (request()->filled('search'))
                {{ __('No investors match your search.') }}
            @else
                {{ __('No accrual data yet. Run accruals on active investments to see rankings.') }}
            @endif
        </p>
    </div>
@else
    <div class="overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/95">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            #
                        </th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Investor') }}
                        </th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Last payout month') }}
                        </th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Tagged capital') }}
                        </th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Total profit') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($topInvestors as $row)
                        <tr
                            class="{{ $row->user_id === auth()->id() ? 'bg-indigo-50/70 ring-1 ring-inset ring-indigo-100/80' : 'hover:bg-gray-50/80' }} transition-colors"
                        >
                            <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-gray-500">
                                {{ $topInvestors->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-medium text-gray-900">{{ $row->name }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500">{{ $row->email }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-gray-700">
                                {{ $row->last_payout_month ? \Carbon\Carbon::parse($row->last_payout_month)->translatedFormat('F Y') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-right tabular-nums text-gray-900">
                                {{ $row->total_contribution }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-right tabular-nums font-semibold text-gray-900">
                                {{ $row->total_profit }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <x-pagination-per-page
            :paginator="$topInvestors"
            param="top_investors_per_page"
            reset-page-key="top_investors_page"
            :fetch-url="route('dashboard')"
            target-id="dashboard-top-investors-fragment"
            ajax-fragment="top_investors"
        />
        <div class="min-w-0 overflow-x-auto">{{ $topInvestors->links() }}</div>
    </div>
@endif
