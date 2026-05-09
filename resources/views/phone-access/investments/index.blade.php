<x-guest-layout>
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-4 rounded-lg border border-indigo-100 bg-indigo-50/70 p-4 text-sm text-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">{{ __('Investment overview') }}</h1>
                    <p class="text-xs text-gray-600">
                        {{ __('Phone access for :name (:phone)', ['name' => $user->name, 'phone' => $user->phone]) }}
                    </p>
                </div>
                <form method="POST" action="{{ route('phone-access.logout') }}">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Exit phone access') }}</x-secondary-button>
                </form>
            </div>
        </div>

        <div class="mb-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total investments') }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 tabular-nums">{{ $totalInvestmentCount }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total profit') }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 tabular-nums">
                    {{ number_format($portfolioProfitTotal, 2, '.', '') }}
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Balance') }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 tabular-nums">
                    {{ number_format($balance, 2, '.', '') }}
                </p>
                <p class="mt-1 text-[11px] text-gray-500">
                    {{ __('Balance = tagged capital + total profit') }}
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">{{ __('Your investment details') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">{{ __('Pool') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Contribution') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Profit') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investments as $investment)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $investment->title }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ number_format((float) ($investment->my_contribution ?? 0), 2, '.', '') }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums">
                                    {{ number_format((float) ($investment->my_profit ?? 0), 2, '.', '') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-action-button :href="route('phone-access.investments.show', $investment)">{{ __('View') }}</x-action-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    {{ __('No active investments found for this phone number.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-guest-layout>
