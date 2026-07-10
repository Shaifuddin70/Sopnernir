<x-guest-layout>
    <div class="mx-auto w-full max-w-4xl space-y-4">
        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ $investment->title }}</h1>
                        <div class="mt-2">
                            <x-user-identity :user="$user" size="md" />
                        </div>
                    </div>
                    <x-action-button :href="route('phone-access.investments.index')" variant="secondary" class="shrink-0 text-sm">
                        {{ __('Back') }}
                    </x-action-button>
                </div>
            </div>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <h2 class="ui-card-header-title">{{ __('Pool summary') }}</h2>
            </div>
            <dl class="grid gap-3 p-3 text-sm sm:grid-cols-2 sm:p-4">
                <div>
                    <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Status') }}</dt>
                    <dd class="mt-1 font-medium text-foreground">{{ $investment->status }}</dd>
                </div>
                @if ($investment->deed_completion_deadline)
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Plan completion date') }}</dt>
                        <dd class="mt-1 font-medium text-foreground">
                            {{ $investment->deed_completion_deadline->translatedFormat('j F Y') }}
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('My contribution') }}</dt>
                    <dd class="mt-1 font-semibold tabular-nums text-foreground">
                        {{ number_format((float) ($myParticipant?->contribution_amount ?? 0), 2, '.', '') }}
                    </dd>
                </div>
                @if ($myDailyProfit)
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Projected total profit') }}</dt>
                        <dd class="mt-1 font-semibold tabular-nums text-success">{{ $myDailyProfit['projected_profit'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Profit til today') }}</dt>
                        <dd class="mt-1 font-semibold tabular-nums text-success">{{ $myDailyProfit['profit_til_today'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Daily profit') }}</dt>
                        <dd class="mt-1 font-semibold tabular-nums text-success">{{ $myDailyProfit['daily_profit'] }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Posted profit') }}</dt>
                    <dd class="mt-1 font-semibold tabular-nums text-success">
                        {{ number_format($myTotalProfit, 2, '.', '') }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('My balance on this pool') }}</dt>
                    <dd class="mt-1 font-semibold tabular-nums text-foreground">
                        {{ number_format((float) ($myParticipant?->contribution_amount ?? 0) + $myTotalProfit, 2, '.', '') }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <h2 class="ui-card-header-title">{{ __('My profit by month') }}</h2>
            </div>
            <div class="p-3 sm:p-4">
                <div class="overflow-x-auto rounded-lg border border-line">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                <x-table-serial-header />
                                <th>{{ __('Month') }}</th>
                                <th class="text-right">{{ __('Profit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myProfitByMonth as $row)
                                <tr>
                                    <x-table-serial-cell :index="$loop->index" />
                                    <td class="tabular-nums">{{ \Carbon\Carbon::parse($row->period->month)->translatedFormat('F Y') }}</td>
                                    <td class="text-right tabular-nums text-success">
                                        {{ number_format((float) $row->profit_share, 2, '.', '') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-foreground-muted">
                                        {{ __('No profit history yet.') }}
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
