<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Monthly payments') }}</h2>
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
            <div class="border-b border-line bg-surface-variant px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <form method="get" action="{{ route('admin.monthly-payments.index') }}" class="shrink-0">
                        <div class="flex items-center gap-2">
                            <x-action-button
                                variant="secondary"
                                class="!h-11 !w-11 shrink-0 !p-0"
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
                                class="ui-input !mt-0 w-full min-w-[11rem] sm:min-w-[12rem]"
                                onchange="this.form.submit()"
                            >
                            <x-action-button
                                variant="secondary"
                                class="!h-11 !w-11 shrink-0 !p-0"
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

                    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end lg:gap-4">
                        @include('admin.monthly-payments.partials.summary', compact(
                            'month',
                            'summaryPaid',
                            'summaryUnpaid',
                            'summaryTotal',
                        ))
                        <x-action-button
                            class="!h-11 w-full shrink-0 justify-center sm:w-auto"
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
