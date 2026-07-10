<x-phone-access-layout>
    <div class="space-y-4">
        <section class="phone-access-hero ui-card min-w-0">
            <div class="ui-card-header">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ $investment->title }}</h1>
                        <div class="mt-2">
                            <x-user-identity :user="$user" size="md" />
                        </div>
                    </div>
                    <x-action-button :href="route('phone-access.investments.index')" variant="secondary" class="w-full shrink-0 sm:w-auto">
                        {{ __('Back') }}
                    </x-action-button>
                </div>
            </div>
        </section>

        <section class="dashboard-stat-panel min-w-0">
            <div class="dashboard-stat-panel__head">
                <h2 class="dashboard-stat-panel__title">{{ __('Pool summary') }}</h2>
            </div>
            <dl class="dashboard-stat-grid sm:grid-cols-2 lg:grid-cols-3">
                <div class="dashboard-stat">
                    <dt>{{ __('Status') }}</dt>
                    <dd class="text-base sm:text-lg">{{ $investment->status }}</dd>
                </div>
                @if ($investment->deed_completion_deadline)
                    <div class="dashboard-stat">
                        <dt>{{ __('Plan ends') }}</dt>
                        <dd class="text-base sm:text-lg">{{ $investment->deed_completion_deadline->translatedFormat('j M Y') }}</dd>
                    </div>
                @endif
                <div class="dashboard-stat dashboard-stat--capital">
                    <dt>{{ __('My contribution') }}</dt>
                    <dd>{{ number_format((float) ($myParticipant?->contribution_amount ?? 0), 2, '.', '') }}</dd>
                </div>
                @if ($myDailyProfit)
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Projected profit') }}</dt>
                        <dd>{{ $myDailyProfit['projected_profit'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Profit til today') }}</dt>
                        <dd>{{ $myDailyProfit['profit_til_today'] }}</dd>
                    </div>
                    <div class="dashboard-stat dashboard-stat--profit">
                        <dt>{{ __('Daily profit') }}</dt>
                        <dd>{{ $myDailyProfit['daily_profit'] }}</dd>
                    </div>
                @endif
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Posted profit') }}</dt>
                    <dd>{{ number_format($myTotalProfit, 2, '.', '') }}</dd>
                </div>
                <div class="dashboard-stat sm:col-span-2 lg:col-span-3">
                    <dt>{{ __('My balance on this pool') }}</dt>
                    <dd>{{ number_format((float) ($myParticipant?->contribution_amount ?? 0) + $myTotalProfit, 2, '.', '') }}</dd>
                </div>
            </dl>
        </section>

        <section class="dashboard-stat-panel dashboard-stat-panel--scroll min-w-0">
            <div class="dashboard-stat-panel__head">
                <h2 class="dashboard-stat-panel__title">{{ __('My profit by month') }}</h2>
            </div>
            <div class="p-3 sm:p-4">
                <div class="ui-glass-table-wrap overflow-x-auto">
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
                                    <td class="text-right tabular-nums font-medium text-success">
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
</x-phone-access-layout>
