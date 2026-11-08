<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Monthly Installment') }}</h2>
    </x-slot>

    <div class="dashboard-page space-y-5 pb-2">
        @php
            $previousMonth = $month->copy()->subMonth()->format('Y-m');
            $nextMonth = $month->copy()->addMonth()->format('Y-m');
            $monthNavQuery = array_filter([
                'search' => request('search'),
            ]);
        @endphp
        <section class="ui-glass-panel min-w-0">
            <div class="border-b border-line bg-surface-variant px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center gap-3">
                    <form method="get" action="{{ route('admin.monthly-payments.index') }}" class="shrink-0">
                        <div class="flex items-center gap-2">
                            <x-action-button
                                variant="secondary"
                                class="!h-10 !w-10 shrink-0 !p-0"
                                :href="route('admin.monthly-payments.index', array_merge(['month' => $previousMonth], $monthNavQuery))"
                                :title="__('Previous month')"
                                :aria-label="__('Previous month')"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </x-action-button>
                            <input
                                id="month"
                                name="month"
                                type="month"
                                value="{{ $month->format('Y-m') }}"
                                aria-label="{{ __('Month') }}"
                                class="ui-input !mt-0 !h-10 !min-h-0 w-[11.5rem]"
                                onchange="this.form.submit()"
                            >
                            <x-action-button
                                variant="secondary"
                                class="!h-10 !w-10 shrink-0 !p-0"
                                :href="route('admin.monthly-payments.index', array_merge(['month' => $nextMonth], $monthNavQuery))"
                                :title="__('Next month')"
                                :aria-label="__('Next month')"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </x-action-button>
                        </div>
                        @if (request()->filled('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>

                    <form method="post" action="{{ route('admin.monthly-payments.amount') }}" class="flex shrink-0 items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                        @if (request()->filled('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <label for="default_amount" class="shrink-0 text-sm font-medium text-foreground-muted">{{ __('Default amount') }}</label>
                        <input
                            id="default_amount"
                            name="amount"
                            type="text"
                            inputmode="decimal"
                            value="{{ old('amount', $defaultAmount) }}"
                            required
                            class="ui-input !mt-0 !h-10 !min-h-0 w-28"
                        >
                        <x-action-button type="submit" class="shrink-0">{{ __('Save') }}</x-action-button>
                    </form>
                    <x-input-error :messages="$errors->get('amount')" class="basis-full" />

                    <div class="ms-auto flex flex-wrap items-center justify-end gap-2">
                        <div id="monthly-payments-summary">
                            @include('admin.monthly-payments.partials.summary', compact(
                                'month',
                                'summaryPaid',
                                'summaryUnpaid',
                                'summaryTotal',
                            ))
                        </div>
                        <x-action-button
                            class="shrink-0"
                            variant="secondary"
                            :href="route('admin.special-installments.index')"
                        >
                            {{ __('Special installment') }}
                        </x-action-button>
                        <x-action-button
                            class="shrink-0"
                            :href="route('admin.investments.index', ['new' => 1, 'payment_month' => $month->format('Y-m')])"
                            :title="__('Create an investment and tag investors who paid for :month', ['month' => $month->translatedFormat('F Y')])"
                        >
                            {{ __('Create investment') }}
                        </x-action-button>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <x-ajax-table-region
                    :fetch-url="route('admin.monthly-payments.index', ['month' => $month->format('Y-m')])"
                    target-id="monthly-payments-table-fragment"
                >
                    <x-table-search
                        :fetch-url="route('admin.monthly-payments.index', ['month' => $month->format('Y-m')])"
                        target-id="monthly-payments-table-fragment"
                        :placeholder="__('Search by investor name, email, or phone…')"
                    />
                    <div id="monthly-payments-table-fragment">
                        @include('admin.monthly-payments.partials.table-fragment', compact(
                            'rows',
                            'month',
                            'filteredCount',
                            'summaryTotal',
                        ))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
