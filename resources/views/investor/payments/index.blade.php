@php
    $lines = collect();

    foreach ($months as $month) {
        $lines->push([
            'sort' => $month['paid_at'],
            'title' => $month['month']->translatedFormat('F Y'),
            'kind' => 'monthly',
            'amount' => $month['amount'],
            'paid_at' => $month['paid_at'],
        ]);
    }

    foreach ($specials as $special) {
        $lines->push([
            'sort' => $special->paid_at,
            'title' => $special->installment->title,
            'kind' => 'special',
            'amount' => $special->installment->amount,
            'paid_at' => $special->paid_at,
        ]);
    }

    $lines = $lines->sortByDesc(fn (array $line) => $line['sort']?->getTimestamp() ?? 0)->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Payments') }}</h2>
    </x-slot>

    <div class="payments-page space-y-4 pb-2">
        <section class="dashboard-stat-panel" aria-label="{{ __('Payments summary') }}">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Summary') }}</h3>
                @if ($latestPaidAt)
                    <span class="ui-chip tabular-nums">{{ __('Paid on') }} {{ $latestPaidAt->translatedFormat('j M Y') }}</span>
                @endif
            </div>
            <dl class="dashboard-stat-grid">
                <div class="dashboard-stat portfolio-stat portfolio-stat--capital">
                    <dt class="portfolio-stat__label">
                        <span class="portfolio-stat__icon" aria-hidden="true">
                            <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="5" width="16" height="15" rx="2" />
                                <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                            </svg>
                        </span>
                        {{ __('Paid months') }}
                    </dt>
                    <dd>{{ $paidMonths }}</dd>
                </div>
                <div class="dashboard-stat portfolio-stat portfolio-stat--profit">
                    <dt class="portfolio-stat__label">
                        <span class="portfolio-stat__icon" aria-hidden="true">
                            <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5c0-1.5-1.8-2.5-4.5-2.5S7.5 6 7.5 7.5 9.3 10 12 10s4.5 1 4.5 2.5S14.7 15 12 15s-4.5-1-4.5-2.5" />
                            </svg>
                        </span>
                        {{ __('Amount') }}
                    </dt>
                    <dd>{{ $paidTotal }}</dd>
                </div>
                <div class="dashboard-stat portfolio-stat portfolio-stat--today">
                    <dt class="portfolio-stat__label">
                        <span class="portfolio-stat__icon" aria-hidden="true">
                            <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7l8 4 8-4-8-4Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4 12 8 4 8-4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 8 4 8-4" />
                            </svg>
                        </span>
                        {{ __('Pools') }}
                    </dt>
                    <dd>{{ $poolCount }}</dd>
                </div>
                <div class="dashboard-stat portfolio-stat portfolio-stat--withdrawn">
                    <dt class="portfolio-stat__label">
                        <span class="portfolio-stat__icon" aria-hidden="true">
                            <svg class="h-4 w-4" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="8" />
                                <path stroke-linecap="round" d="M12 8v4l3 2" />
                            </svg>
                        </span>
                        {{ __('Latest') }}
                    </dt>
                    <dd class="!text-base sm:!text-lg">{{ $latestMonth?->translatedFormat('M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="dashboard-stat-panel min-w-0">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Your payments') }}</h3>
                @if ($lines->isNotEmpty())
                    <span class="ui-chip tabular-nums">{{ $lines->count() }}</span>
                @endif
            </div>
            <div class="p-3 pt-1">
                @if ($lines->isEmpty())
                    <div class="rounded-xl border border-dashed border-line bg-surface-variant/50 px-3 py-10 text-center">
                        <p class="text-sm text-foreground-muted">{{ __('No payments yet.') }}</p>
                    </div>
                @else
                    <div class="ui-glass-table-wrap overflow-x-auto">
                        <table class="ui-table min-w-full">
                            <thead>
                                <tr>
                                    <x-table-serial-header />
                                    <th>{{ __('Payment') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                    <th>{{ __('Paid on') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $line)
                                    <tr>
                                        <x-table-serial-cell :index="$loop->index" />
                                        <td>
                                            <div class="font-medium text-foreground">{{ $line['title'] }}</div>
                                        </td>
                                        <td>
                                            @if ($line['kind'] === 'special')
                                                <span class="ui-badge-brand">{{ __('One-time') }}</span>
                                            @else
                                                <span class="ui-badge-muted">{{ __('Monthly') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right tabular-nums font-semibold text-success">
                                            {{ $line['amount'] !== null ? number_format((float) $line['amount'], 2) : '—' }}
                                        </td>
                                        <td class="whitespace-nowrap tabular-nums text-foreground-muted">{{ $line['paid_at']?->translatedFormat('j M Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
