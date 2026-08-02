@if ($pools->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant px-4 py-10 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No pools match your search.') }}
            @else
                {{ __('No profit has been withdrawn from any pool yet.') }}
            @endif
        </p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Pool') }}</th>
                    <th class="text-right">{{ __('Total withdrawn') }}</th>
                    <th class="hidden sm:table-cell text-right">{{ __('Times withdrawn') }}</th>
                    <th class="hidden md:table-cell">{{ __('Last withdrawn') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pools as $pool)
                    <tr class="align-top">
                        <x-table-serial-cell :paginator="$pools" :index="$loop->index" />
                        <td>
                            <a href="{{ route('admin.investments.show', $pool) }}" class="ui-text-link font-medium">
                                {{ $pool->title }}
                            </a>
                            @if ($pool->deed_no)
                                <p class="mt-0.5 text-sm tabular-nums text-foreground-muted">{{ $pool->deed_no }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right tabular-nums font-semibold text-success">
                            {{ number_format((float) ($pool->total_withdrawn ?? 0), 2, '.', '') }}
                        </td>
                        <td class="hidden whitespace-nowrap text-right tabular-nums sm:table-cell">
                            {{ (int) ($pool->withdrawals_count ?? 0) }}
                        </td>
                        <td class="hidden whitespace-nowrap tabular-nums text-foreground-muted md:table-cell">
                            @if (! empty($pool->last_withdrawn_at))
                                {{ \Carbon\Carbon::parse($pool->last_withdrawn_at)->format('Y-m-d H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right">
                            <x-action-button
                                :href="route('admin.investments.show', $pool)"
                                variant="secondary"
                                class="text-sm"
                            >{{ __('View pool') }}</x-action-button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$pools"
            param="per_page"
            reset-page-key="page"
            :fetch-url="route('admin.profit-withdrawals.index')"
            target-id="profit-withdrawals-table-fragment"
        />
        <div class="ui-table-pagination">{{ $pools->links() }}</div>
    </div>
@endif
