<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Withdrawal details') }}</h2>
            <x-action-button :href="route('admin.profit-withdrawals.index')" variant="secondary" class="text-sm">
                {{ __('All withdrawals') }}
            </x-action-button>
        </div>
    </x-slot>

    <div class="space-y-4">
        <section class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-header-title">{{ __('Summary') }}</h3>
            </div>
            <div class="p-3 sm:p-4">
                <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Through date') }}</dt>
                        <dd class="mt-0.5 font-medium tabular-nums">{{ $batch->through_date?->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Total this withdraw') }}</dt>
                        <dd class="mt-0.5 font-semibold tabular-nums text-success">{{ number_format((float) $batch->amount, 2, '.', '') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Pools') }}</dt>
                        <dd class="mt-0.5 font-medium tabular-nums">{{ $batch->pools_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('By') }}</dt>
                        <dd class="mt-0.5 font-medium">{{ $batch->withdrawnBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Withdrawn at') }}</dt>
                        <dd class="mt-0.5 font-medium tabular-nums">{{ $batch->withdrawn_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2 lg:col-span-3">
                        <dt class="text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Notes') }}</dt>
                        <dd class="mt-0.5 whitespace-pre-wrap">{{ $batch->notes ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-header-title">{{ __('Pools in this withdrawal') }}</h3>
            </div>
            <div class="p-3 sm:p-4">
                <div class="overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                <x-table-serial-header />
                                <th>{{ __('Investment') }}</th>
                                <th class="text-right">{{ __('Withdrawn from pool') }}</th>
                                <th class="text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batch->withdrawals as $withdrawal)
                                <tr>
                                    <x-table-serial-cell :index="$loop->index" />
                                    <td>
                                        @if ($withdrawal->investment)
                                            <a href="{{ route('admin.investments.show', $withdrawal->investment) }}" class="ui-text-link font-medium">
                                                {{ $withdrawal->investment->title }}
                                            </a>
                                            @if ($withdrawal->investment->deed_no)
                                                <p class="mt-0.5 text-sm tabular-nums text-foreground-muted">{{ $withdrawal->investment->deed_no }}</p>
                                            @endif
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
            </div>
        </section>
    </div>
</x-app-layout>
