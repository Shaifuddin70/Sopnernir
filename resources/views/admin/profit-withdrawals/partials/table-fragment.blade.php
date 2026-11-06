@if ($batches->isEmpty())
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
    <div class="ui-glass-table-wrap overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('Pool') }}</th>
                    <th class="whitespace-nowrap">{{ __('Through date') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                    <th class="hidden sm:table-cell">{{ __('By') }}</th>
                    <th class="hidden md:table-cell">{{ __('When') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batches as $batch)
                    @php
                        $pool = $batch->withdrawals->count() === 1
                            ? $batch->withdrawals->first()?->investment
                            : null;
                    @endphp
                    <tr class="align-top">
                        <x-table-serial-cell :paginator="$batches" :index="$loop->index" />
                        <td>
                            <div class="space-y-1">
                                @forelse ($batch->withdrawals as $withdrawal)
                                    <div class="flex flex-wrap items-baseline gap-x-2">
                                        @if ($withdrawal->investment)
                                            <a href="{{ route('admin.investments.show', $withdrawal->investment) }}" class="ui-text-link font-medium">
                                                {{ $withdrawal->investment->title }}
                                            </a>
                                            @if ($withdrawal->investment->deed_no)
                                                <span class="text-sm tabular-nums text-foreground-muted">{{ $withdrawal->investment->deed_no }}</span>
                                            @endif
                                        @else
                                            <span class="text-foreground-muted">{{ __('Deleted investment') }}</span>
                                        @endif
                                        @if ($batch->withdrawals->count() > 1)
                                            <span class="tabular-nums text-sm font-semibold text-success">{{ number_format((float) $withdrawal->amount, 2, '.', '') }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-foreground-muted">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="whitespace-nowrap tabular-nums">{{ $batch->through_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums font-semibold text-success">
                            {{ number_format((float) $batch->amount, 2, '.', '') }}
                        </td>
                        <td class="hidden sm:table-cell">{{ $batch->withdrawnBy?->name ?? '—' }}</td>
                        <td class="hidden whitespace-nowrap tabular-nums text-foreground-muted md:table-cell">
                            {{ $batch->withdrawn_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="text-right">
                            <div class="table-row-actions flex flex-nowrap items-center justify-end gap-2">
                                @if ($pool)
                                    <x-action-button :href="route('admin.investments.show', $pool)" variant="secondary" class="whitespace-nowrap">
                                        {{ __('View pool') }}
                                    </x-action-button>
                                @endif
                                <x-action-button :href="route('admin.profit-withdrawals.show', $batch)" variant="secondary" class="whitespace-nowrap">
                                    {{ __('Open') }}
                                </x-action-button>
                                @include('admin.profit-withdrawals.partials.reverse-form', [
                                    'action' => route('admin.profit-withdrawals.destroy', $batch),
                                ])
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer">
        <x-pagination-per-page
            :paginator="$batches"
            param="per_page"
            reset-page-key="page"
            :fetch-url="route('admin.profit-withdrawals.index')"
            target-id="profit-withdrawals-table-fragment"
        />
        <div class="ui-table-pagination">{{ $batches->links() }}</div>
    </div>
@endif
