@php
    $viewRoute = $viewRoute ?? null;
    $showPoolTotals = $showPoolTotals ?? false;
    $serialPaginator = $serialPaginator ?? null;
    $isPhoneAccess = $viewRoute === 'phone-access';
@endphp

<div class="portfolio-investment-cards space-y-2 md:hidden">
    @foreach ($rows as $row)
        @php
            $viewUrl = $isPhoneAccess
                ? route('phone-access.investments.show', $row['investment_id'])
                : route('investments.show', $row['investment_id']);
            $periodStart = \Carbon\Carbon::parse($row['start_date'])->translatedFormat('j M Y');
            $periodEnd = \Carbon\Carbon::parse($row['end_date'])->translatedFormat('j M Y');
            $rank = $serialPaginator
                ? ($serialPaginator->firstItem() + $loop->index)
                : ($loop->iteration);
        @endphp
        <article class="portfolio-investment-card">
            <div class="flex items-start gap-3">
                <span class="portfolio-investment-card__rank tabular-nums">{{ $rank }}</span>
                <div class="min-w-0 flex-1">
                    <a href="{{ $viewUrl }}" class="ui-text-link text-[0.95rem] font-semibold leading-snug">{{ $row['title'] }}</a>
                    <p class="mt-1 text-xs text-foreground-muted">
                        {{ __('Deed') }} {{ $row['deed_no'] }}
                    </p>
                    <p class="mt-0.5 text-xs tabular-nums text-foreground-muted">
                        {{ $periodStart }} – {{ $periodEnd }}
                        · {{ __('Day :current/:total', ['current' => $row['days_elapsed'], 'total' => $row['plan_days']]) }}
                    </p>
                </div>
                <a href="{{ $viewUrl }}" class="portfolio-investment-card__link shrink-0">{{ __('View') }}</a>
            </div>
            <dl class="portfolio-investment-card__metrics">
                <div>
                    <dt>{{ __('Capital') }}</dt>
                    <dd class="tabular-nums">{{ $showPoolTotals ? $row['pool_total_amount'] : $row['total_amount'] }}</dd>
                </div>
                <div>
                    <dt>{{ __('Profit til today') }}</dt>
                    <dd class="tabular-nums text-success">{{ $row['profit_til_today'] }}</dd>
                </div>
                <div>
                    <dt>{{ __('Total profit') }}</dt>
                    <dd class="tabular-nums text-success">{{ $showPoolTotals ? $row['pool_profit_amount'] : $row['profit_amount'] }}</dd>
                </div>
                <div>
                    <dt>{{ __('Withdrawn') }}</dt>
                    <dd class="tabular-nums">{{ $showPoolTotals ? ($row['pool_withdrawn'] ?? '0.00') : ($row['withdrawn'] ?? '0.00') }}</dd>
                </div>
            </dl>
        </article>
    @endforeach
</div>

<div class="ui-glass-table-wrap hidden overflow-x-auto md:block">
    <table class="ui-table min-w-full">
        <thead>
            <tr>
                <x-table-serial-header />
                <th>{{ __('Investment') }}</th>
                <th class="hidden md:table-cell text-right">{{ __('Total') }}</th>
                <th class="text-right">{{ __('Profit') }}</th>
                <th class="text-right">{{ __('Profit til today') }}</th>
                <th class="hidden lg:table-cell text-right">{{ __('Withdrawn') }}</th>
                <th class="hidden xl:table-cell text-right">{{ __('Per day') }}</th>
                <th class="w-0 text-right">{{ __('Action') }}</th>
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
                        <div class="table-row-actions flex flex-nowrap items-center justify-end gap-2">
                            <x-action-button :href="$viewUrl" variant="secondary">{{ __('View') }}</x-action-button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
