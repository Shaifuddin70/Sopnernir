@if ($batches->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant px-4 py-8 text-center">
        <p class="text-sm text-foreground-muted">{{ __('No withdraw actions yet.') }}</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Through date') }}</th>
                    <th class="text-right">{{ __('Total this withdraw') }}</th>
                    <th>{{ __('Pools') }}</th>
                    <th class="hidden sm:table-cell">{{ __('By') }}</th>
                    <th class="hidden lg:table-cell">{{ __('When') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            @foreach ($batches as $batch)
                <tbody x-data="{ open: false }" class="border-t border-line">
                    <tr class="align-top">
                        <x-table-serial-cell :index="$loop->index" />
                        <td class="whitespace-nowrap tabular-nums">{{ $batch->through_date?->format('Y-m-d') }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums font-semibold text-success">
                            {{ number_format((float) $batch->amount, 2, '.', '') }}
                        </td>
                        <td class="tabular-nums">
                            <button
                                type="button"
                                class="ui-text-link text-left font-medium"
                                @click="open = !open"
                                :aria-expanded="open.toString()"
                            >
                                {{ trans_choice(':count pool|:count pools', $batch->pools_count, ['count' => $batch->pools_count]) }}
                            </button>
                        </td>
                        <td class="hidden sm:table-cell">{{ $batch->withdrawnBy?->name ?? '—' }}</td>
                        <td class="hidden whitespace-nowrap tabular-nums text-foreground-muted lg:table-cell">
                            {{ $batch->withdrawn_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-line bg-surface-secondary px-3 py-1.5 text-sm font-medium text-primary transition hover:bg-primary-muted focus:outline-none focus:ring-2 focus:ring-primary"
                                    @click="open = !open"
                                >
                                    <span x-text="open ? '{{ __('Hide') }}' : '{{ __('Details') }}'"></span>
                                </button>
                                <x-action-button
                                    :href="route('admin.profit-withdrawals.show', $batch)"
                                    variant="secondary"
                                    class="text-sm"
                                >{{ __('Open') }}</x-action-button>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak class="bg-surface-variant/60">
                        <td colspan="7" class="px-3 py-3 sm:px-4">
                            <div class="overflow-x-auto rounded-lg border border-line bg-surface-card">
                                <table class="ui-table min-w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Pool') }}</th>
                                            <th class="text-right">{{ __('Withdrawn from pool') }}</th>
                                            <th class="text-right">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($batch->withdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    @if ($withdrawal->investment)
                                                        <a href="{{ route('admin.investments.show', $withdrawal->investment) }}" class="ui-text-link font-medium">
                                                            {{ $withdrawal->investment->title }}
                                                        </a>
                                                    @else
                                                        <span class="text-foreground-muted">{{ __('Deleted investment') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-right tabular-nums font-semibold text-success">
                                                    {{ number_format((float) $withdrawal->amount, 2, '.', '') }}
                                                </td>
                                                <td class="text-right">
                                                    @if ($withdrawal->investment)
                                                        <x-action-button
                                                            :href="route('admin.investments.show', $withdrawal->investment)"
                                                            variant="secondary"
                                                            class="text-sm"
                                                        >{{ __('View pool') }}</x-action-button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforeach
        </table>
    </div>
@endif
