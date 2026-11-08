<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Special installments') }}</h2>
            <div class="header-action-scroll">
                <x-action-button :href="route('admin.monthly-payments.index')" variant="secondary">
                    {{ __('Monthly Installment') }}
                </x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="payments-page space-y-4 pb-2">
        <section class="dashboard-stat-panel">
            <div class="dashboard-stat-panel__head">
                <div>
                    <h3 class="dashboard-stat-panel__title">{{ __('New special installment') }}</h3>
                    <p class="mt-1 text-sm text-foreground-muted">{{ __('Collect an extra amount outside the monthly installment.') }}</p>
                </div>
            </div>
            <form method="post" action="{{ route('admin.special-installments.store') }}" class="grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_10rem_auto] sm:items-end sm:p-5">
                @csrf
                <div>
                    <x-input-label for="special_title" :value="__('Title')" />
                    <x-text-input id="special_title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required placeholder="{{ __('Eid collection') }}" />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="special_amount" :value="__('Amount')" />
                    <x-text-input id="special_amount" name="amount" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>
                <x-action-button type="submit" class="w-full sm:w-auto">{{ __('Create') }}</x-action-button>
            </form>
        </section>

        <section class="dashboard-stat-panel min-w-0">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Special installments') }}</h3>
            </div>
            <div class="space-y-3 p-4 pt-3 sm:p-5">
                @if ($installments->isEmpty())
                    <div class="rounded-2xl border border-dashed border-line bg-surface-variant/50 px-4 py-14 text-center">
                        <p class="text-sm text-foreground-muted">{{ __('No special installments yet.') }}</p>
                    </div>
                @else
                    @foreach ($installments as $installment)
                        <article class="flex flex-col gap-3 rounded-2xl border border-line bg-surface-card/80 px-4 py-4 shadow-elevation-1 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div class="min-w-0">
                                <h4 class="truncate text-base font-semibold text-foreground">{{ $installment->title }}</h4>
                                <p class="mt-1 text-sm text-foreground-muted">
                                    {{ __('Paid') }}
                                    <span class="tabular-nums">{{ $installment->paid_count }}</span>
                                    / {{ $activeUsers }}
                                    · {{ $installment->created_at?->translatedFormat('j M Y') }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between gap-3 sm:justify-end">
                                <p class="text-xl font-bold tabular-nums text-success">{{ number_format((float) $installment->amount, 2) }}</p>
                                <div class="table-row-actions flex shrink-0 flex-wrap items-center justify-end gap-2">
                                    <x-action-button
                                        :href="route('admin.investments.index', ['new' => 1, 'special_installment' => $installment->id])"
                                        variant="secondary"
                                    >
                                        {{ __('Create investment') }}
                                    </x-action-button>
                                    <x-action-button :href="route('admin.special-installments.show', $installment)" variant="secondary">
                                        {{ __('Manage') }}
                                    </x-action-button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
