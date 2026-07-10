@php
    $viewRoute = $viewRoute ?? null;
    $showPoolTotals = $showPoolTotals ?? false;
    $serialPaginator = $serialPaginator ?? null;
@endphp

<div class="ui-glass-table-wrap overflow-x-auto">
    <table class="ui-table min-w-full text-xs">
        <thead>
            <tr>
                <x-table-serial-header />
                <th>{{ __('Investment') }}</th>
                <th class="hidden md:table-cell text-right">{{ __('Total') }}</th>
                <th class="text-right">{{ __('Profit') }}</th>
                <th class="hidden sm:table-cell text-right">{{ __('Til today') }}</th>
                <th class="hidden lg:table-cell text-right">{{ __('Per day') }}</th>
                <th class="w-0"><span class="sr-only">{{ __('Actions') }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $viewUrl = $viewRoute === 'phone-access'
                        ? route('phone-access.investments.show', $row['investment_id'])
                        : route('investments.show', $row['investment_id']);
                    $periodStart = \Carbon\Carbon::parse($row['start_date'])->translatedFormat('j M Y');
                    $periodEnd = \Carbon\Carbon::parse($row['end_date'])->translatedFormat('j M Y');
                @endphp
                <tr>
                    <x-table-serial-cell :paginator="$serialPaginator" :index="$loop->index" />
                    <td class="min-w-[10rem]">
                        <a href="{{ $viewUrl }}" class="ui-text-link">{{ $row['title'] }}</a>
                        <div class="mt-0.5 text-[10px] leading-snug text-foreground-muted">
                            <span class="tabular-nums">{{ __('Deed') }} {{ $row['deed_no'] }}</span>
                            <span class="mx-1" aria-hidden="true">·</span>
                            <span class="tabular-nums">{{ $periodStart }} – {{ $periodEnd }}</span>
                            <span class="mx-1 hidden sm:inline" aria-hidden="true">·</span>
                            <span class="hidden tabular-nums sm:inline">{{ __('Day :current/:total', ['current' => $row['days_elapsed'], 'total' => $row['plan_days']]) }}</span>
                        </div>
                        <div class="mt-0.5 text-[10px] tabular-nums text-foreground-muted sm:hidden">
                            {{ __('Til today') }}: {{ $row['profit_til_today'] }}
                        </div>
                    </td>
                    <td class="hidden text-right tabular-nums md:table-cell">
                        {{ $showPoolTotals ? $row['pool_total_amount'] : $row['total_amount'] }}
                    </td>
                    <td class="text-right tabular-nums font-medium text-success">
                        {{ $showPoolTotals ? $row['pool_profit_amount'] : $row['profit_amount'] }}
                    </td>
                    <td class="hidden text-right tabular-nums text-success sm:table-cell">{{ $row['profit_til_today'] }}</td>
                    <td class="hidden text-right tabular-nums text-foreground-muted lg:table-cell">{{ $row['daily_profit'] }}</td>
                    <td class="text-right">
                        <x-action-button :href="$viewUrl" variant="secondary" class="text-xs">{{ __('View') }}</x-action-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
