@php
    $viewRoute = $viewRoute ?? null;
    $showPoolTotals = $showPoolTotals ?? false;
    $serialPaginator = $serialPaginator ?? null;
    $isPhoneAccess = $viewRoute === 'phone-access';
@endphp

@if ($isPhoneAccess)
    <div class="space-y-3 md:hidden">
        @foreach ($rows as $row)
            @php
                $viewUrl = route('phone-access.investments.show', $row['investment_id']);
                $periodStart = \Carbon\Carbon::parse($row['start_date'])->translatedFormat('j M Y');
                $periodEnd = \Carbon\Carbon::parse($row['end_date'])->translatedFormat('j M Y');
            @endphp
            <article class="phone-access-investment-card">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="{{ $viewUrl }}" class="ui-text-link text-base">{{ $row['title'] }}</a>
                        <p class="mt-1 text-sm text-foreground-muted">
                            {{ __('Deed') }} {{ $row['deed_no'] }}
                        </p>
                        <p class="mt-0.5 text-sm tabular-nums text-foreground-muted">
                            {{ $periodStart }} – {{ $periodEnd }}
                        </p>
                        <p class="mt-0.5 text-sm tabular-nums text-foreground-muted">
                            {{ __('Day :current/:total', ['current' => $row['days_elapsed'], 'total' => $row['plan_days']]) }}
                        </p>
                    </div>
                    <x-action-button :href="$viewUrl" variant="secondary" class="shrink-0 text-sm">{{ __('View') }}</x-action-button>
                </div>
                <dl class="phone-access-investment-card__metrics">
                    <div class="phone-access-investment-card__metric">
                        <dt>{{ __('Total') }}</dt>
                        <dd>{{ $showPoolTotals ? $row['pool_total_amount'] : $row['total_amount'] }}</dd>
                    </div>
                    <div class="phone-access-investment-card__metric phone-access-investment-card__metric--profit">
                        <dt>{{ __('Profit') }}</dt>
                        <dd>{{ $showPoolTotals ? $row['pool_profit_amount'] : $row['profit_amount'] }}</dd>
                    </div>
                    <div class="phone-access-investment-card__metric phone-access-investment-card__metric--profit sm:col-span-2">
                        <dt>{{ __('Profit til today') }}</dt>
                        <dd>{{ $row['profit_til_today'] }}</dd>
                    </div>
                    <div class="phone-access-investment-card__metric sm:col-span-2">
                        <dt>{{ __('Withdrawn') }}</dt>
                        <dd>{{ $showPoolTotals ? ($row['pool_withdrawn'] ?? '0.00') : ($row['withdrawn'] ?? '0.00') }}</dd>
                    </div>
                </dl>
            </article>
        @endforeach
    </div>
@endif

<div @class(['ui-glass-table-wrap overflow-x-auto', 'hidden md:block' => $isPhoneAccess])>
    <table class="ui-table min-w-full @if ($isPhoneAccess) min-w-[40rem] @endif">
        <thead>
            <tr>
                <x-table-serial-header />
                <th>{{ __('Investment') }}</th>
                <th class="hidden md:table-cell text-right">{{ __('Total') }}</th>
                <th class="text-right">{{ __('Profit') }}</th>
                <th class="text-right">{{ __('Profit til today') }}</th>
                <th class="hidden lg:table-cell text-right">{{ __('Withdrawn') }}</th>
                <th class="hidden xl:table-cell text-right">{{ __('Per day') }}</th>
                <th class="w-0"><span class="sr-only">{{ __('Actions') }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $viewUrl = $isPhoneAccess
                        ? route('phone-access.investments.show', $row['investment_id'])
                        : route('investments.show', $row['investment_id']);
                    $periodStart = \Carbon\Carbon::parse($row['start_date'])->translatedFormat('j M Y');
                    $periodEnd = \Carbon\Carbon::parse($row['end_date'])->translatedFormat('j M Y');
                @endphp
                <tr>
                    <x-table-serial-cell :paginator="$serialPaginator" :index="$loop->index" />
                    <td class="min-w-[10rem]">
                        <a href="{{ $viewUrl }}" class="ui-text-link">{{ $row['title'] }}</a>
                        <div class="mt-0.5 text-sm leading-snug text-foreground-muted">
                            <span class="tabular-nums">{{ __('Deed') }} {{ $row['deed_no'] }}</span>
                            <span class="mx-1" aria-hidden="true">·</span>
                            <span class="tabular-nums">{{ $periodStart }} – {{ $periodEnd }}</span>
                            <span class="mx-1 hidden sm:inline" aria-hidden="true">·</span>
                            <span class="hidden tabular-nums sm:inline">{{ __('Day :current/:total', ['current' => $row['days_elapsed'], 'total' => $row['plan_days']]) }}</span>
                        </div>
                    </td>
                    <td class="hidden text-right tabular-nums md:table-cell">
                        {{ $showPoolTotals ? $row['pool_total_amount'] : $row['total_amount'] }}
                    </td>
                    <td class="text-right tabular-nums font-medium text-success">
                        {{ $showPoolTotals ? $row['pool_profit_amount'] : $row['profit_amount'] }}
                    </td>
                    <td class="text-right tabular-nums font-medium text-success">{{ $row['profit_til_today'] }}</td>
                    <td class="hidden text-right tabular-nums text-foreground-muted lg:table-cell">
                        {{ $showPoolTotals ? ($row['pool_withdrawn'] ?? '0.00') : ($row['withdrawn'] ?? '0.00') }}
                    </td>
                    <td class="hidden text-right tabular-nums text-foreground-muted xl:table-cell">{{ $row['daily_profit'] }}</td>
                    <td class="text-right">
                        <x-action-button :href="$viewUrl" variant="secondary" class="text-sm">{{ __('View') }}</x-action-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
