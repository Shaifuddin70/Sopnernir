<x-guest-layout>
    <div class="mx-auto w-full max-w-4xl space-y-4">
        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <h1 class="text-base font-semibold text-gray-900">{{ $investment->title }}</h1>
                <p class="text-xs text-gray-600">
                    {{ __('Phone access for :name', ['name' => $user->name]) }}
                </p>
            </div>
            <x-action-button :href="route('phone-access.investments.index')">{{ __('Back') }}</x-action-button>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm text-sm">
            <p><span class="font-medium">{{ __('Status') }}:</span> {{ $investment->status }}</p>
            <p><span class="font-medium">{{ __('My contribution') }}:</span>
                {{ number_format((float) ($myParticipant?->contribution_amount ?? 0), 2, '.', '') }}</p>
            <p><span class="font-medium">{{ __('My total profit') }}:</span>
                {{ number_format($myTotalProfit, 2, '.', '') }}</p>
            <p><span class="font-medium">{{ __('My balance on this pool') }}:</span>
                {{ number_format((float) ($myParticipant?->contribution_amount ?? 0) + $myTotalProfit, 2, '.', '') }}</p>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('My profit by month') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs uppercase tracking-wide text-gray-500">
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
                                <td colspan="2" class="px-4 py-6 text-center text-gray-500">{{ __('No profit history yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-guest-layout>
