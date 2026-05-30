<x-guest-layout>
    <div class="mx-auto w-full max-w-4xl space-y-4">
        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 ui-card p-4 shadow-sm">
            <div>
                <h1 class="text-base font-semibold text-foreground">{{ $investment->title }}</h1>
                <p class="text-xs text-foreground-muted">
                    {{ __('Phone access for :name', ['name' => $user->name]) }}
                </p>
            </div>
            <x-action-button :href="route('phone-access.investments.index')">{{ __('Back') }}</x-action-button>
        </div>

        <div class="rounded-lg border border-gray-200 ui-card p-4 shadow-sm text-sm">
            <p><span class="font-medium">{{ __('Status') }}:</span> {{ $investment->status }}</p>
            @if ($investment->deed_completion_deadline)
                <p>
                    <span class="font-medium">{{ __('Plan completion date') }}:</span>
                    {{ $investment->deed_completion_deadline->translatedFormat('j F Y') }}
                </p>
            @endif
            <p><span class="font-medium">{{ __('My contribution') }}:</span>
                {{ number_format((float) ($myParticipant?->contribution_amount ?? 0), 2, '.', '') }}</p>
            <p><span class="font-medium">{{ __('My total profit') }}:</span>
                {{ number_format($myTotalProfit, 2, '.', '') }}</p>
            <p><span class="font-medium">{{ __('My balance on this pool') }}:</span>
                {{ number_format((float) ($myParticipant?->contribution_amount ?? 0) + $myTotalProfit, 2, '.', '') }}</p>
        </div>

        <div class="ui-card shadow-sm">
            <div class="border-b border-line bg-surface-secondary px-4 py-3">
                <h2 class="text-sm font-semibold text-foreground">{{ __('My profit by month') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="ui-table min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs uppercase tracking-wide text-foreground-muted">
                            <th class="px-4 py-3">{{ __('Month') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Profit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($myProfitByMonth as $row)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->period->month)->format('M Y') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ number_format((float) $row->profit_share, 2, '.', '') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('No profit history yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-guest-layout>
