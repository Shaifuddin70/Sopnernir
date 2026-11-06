<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ $specialInstallment->title }}</h2>
            <div class="header-action-scroll">
                <x-action-button :href="route('admin.special-installments.index')" variant="secondary">
                    {{ __('Special installments') }}
                </x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-page space-y-5 pb-2">
        <section class="ui-glass-panel min-w-0">
            <div class="flex flex-col gap-4 border-b border-line bg-surface-variant px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p class="text-sm font-medium text-foreground-muted">{{ __('One-time') }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-[#1f8a4c]">{{ number_format((float) $specialInstallment->amount, 2) }}</p>
                </div>
                <div id="monthly-payments-summary">
                    @include('admin.special-installments.partials.summary', compact(
                        'summaryPaid',
                        'summaryUnpaid',
                        'summaryTotal',
                    ))
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <x-ajax-table-region
                    :fetch-url="route('admin.special-installments.show', $specialInstallment)"
                    target-id="monthly-payments-table-fragment"
                >
                    <x-table-search
                        :fetch-url="route('admin.special-installments.show', $specialInstallment)"
                        target-id="monthly-payments-table-fragment"
                        :placeholder="__('Search by investor name, email, or phone…')"
                    />
                    <div id="monthly-payments-table-fragment">
                        @include('admin.special-installments.partials.table-fragment', compact(
                            'rows',
                            'specialInstallment',
                            'filteredCount',
                            'summaryTotal',
                        ))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
