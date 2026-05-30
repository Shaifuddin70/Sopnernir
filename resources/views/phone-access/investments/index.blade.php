<x-guest-layout>
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-4 rounded-lg border border-line bg-surface-secondary p-4 text-sm text-foreground">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-base font-semibold text-foreground">{{ __('Investment overview') }}</h1>
                    <p class="text-xs text-foreground-muted">
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
            <div class="ui-card p-4">
                <p class="text-xs uppercase tracking-wide text-foreground-muted">{{ __('Total investments') }}</p>
                <p class="mt-1 text-xl font-semibold text-foreground tabular-nums">{{ $totalInvestmentCount }}</p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase tracking-wide text-foreground-muted">{{ __('Total profit') }}</p>
                <p class="mt-1 text-xl font-semibold text-foreground tabular-nums">
                    {{ number_format($portfolioProfitTotal, 2, '.', '') }}
                </p>
            </div>
            <div class="ui-card p-4">
                <p class="text-xs uppercase tracking-wide text-foreground-muted">{{ __('Balance') }}</p>
                <p class="mt-1 text-xl font-semibold text-foreground tabular-nums">
                    {{ number_format($balance, 2, '.', '') }}
                </p>
                <p class="mt-1 text-[11px] text-foreground-muted">
                    {{ __('Balance = tagged capital + total profit') }}
                </p>
            </div>
        </div>

        <div class="ui-card shadow-sm">
            <div class="border-b border-line bg-surface-secondary px-4 py-3">
                <h2 class="text-sm font-semibold text-foreground">{{ __('Your investment details') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="ui-table min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-xs uppercase tracking-wide text-foreground-muted">
                            <th class="px-4 py-3">{{ __('Pool') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Contribution') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Profit') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investments as $investment)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $investment->title }}</td>
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
                                <td colspan="4" class="px-4 py-6 text-center text-foreground-muted">
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
