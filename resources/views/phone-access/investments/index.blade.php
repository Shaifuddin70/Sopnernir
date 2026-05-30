<x-guest-layout>
    <div class="mx-auto w-full max-w-5xl space-y-4">
        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ __('Investment overview') }}</h1>
                        <div class="mt-2">
                            <x-user-identity :user="$user" :subtitle="$user->phone" size="md" />
                        </div>
                    </div>
                    <form method="POST" action="{{ route('phone-access.logout') }}" class="shrink-0">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Exit phone access') }}</x-secondary-button>
                    </form>
                </div>
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <h2 class="ui-card-header-title">{{ __('Summary') }}</h2>
            </div>
            <div class="space-y-4 p-3 text-sm sm:p-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-foreground-muted">
                        {{ __('Platform total amount') }}
                    </p>
                    <p class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-foreground">
                        {{ $platform['total_amount'] }}
                    </p>
                    <p class="mt-1 text-xs text-foreground-muted">
                        {{ __('Total tagged capital plus profit distributed across all active pools on the platform.') }}
                    </p>
                </div>

                <dl class="grid gap-2 border-t border-line pt-4 sm:grid-cols-2">
                    <div>
                        <dt class="inline text-foreground-muted">{{ __('Your investments') }}:</dt>
                        <dd class="ms-1 inline font-semibold tabular-nums text-foreground">{{ $totalInvestmentCount }}</dd>
                    </div>
                    <div>
                        <dt class="inline text-foreground-muted">{{ __('Your tagged capital') }}:</dt>
                        <dd class="ms-1 inline font-semibold tabular-nums text-primary">
                            {{ number_format($totalTaggedCapital, 2, '.', '') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="inline text-foreground-muted">{{ __('Your profit received') }}:</dt>
                        <dd class="ms-1 inline font-semibold tabular-nums text-success">
                            {{ number_format($portfolioProfitTotal, 2, '.', '') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="inline text-foreground-muted">{{ __('Your total amount') }}:</dt>
                        <dd class="ms-1 inline font-semibold tabular-nums text-foreground">
                            {{ number_format($totalAmount, 2, '.', '') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <h2 class="ui-card-header-title">{{ __('Your investment details') }}</h2>
            </div>
            <div class="p-3 sm:p-4">
                <div class="overflow-x-auto rounded-lg border border-line">
                    <table class="ui-table min-w-full text-xs sm:text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Pool') }}</th>
                                <th class="text-right">{{ __('Contribution') }}</th>
                                <th class="text-right">{{ __('Profit') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($investments as $investment)
                                <tr>
                                    <td class="font-medium">{{ $investment->title }}</td>
                                    <td class="text-right tabular-nums">
                                        {{ number_format((float) ($investment->my_contribution ?? 0), 2, '.', '') }}
                                    </td>
                                    <td class="text-right tabular-nums text-success">
                                        {{ number_format((float) ($investment->my_profit ?? 0), 2, '.', '') }}
                                    </td>
                                    <td class="text-right">
                                        <x-action-button :href="route('phone-access.investments.show', $investment)" variant="secondary" class="text-xs">
                                            {{ __('View') }}
                                        </x-action-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-foreground-muted">
                                        {{ __('No active investments found for this phone number.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
