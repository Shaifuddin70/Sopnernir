<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Profit withdrawals') }}</h2>
            <div class="header-action-scroll">
                <x-action-button :href="route('admin.investments.index')" variant="secondary" class="text-sm">
                    {{ __('Investments') }}
                </x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <section class="dashboard-stat-panel" aria-label="{{ __('Withdrawals summary') }}">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Totals') }}</h3>
            </div>
            <dl class="dashboard-stat-grid">
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Total withdrawn') }}</dt>
                    <dd>{{ number_format($summaryTotalAmount, 2, '.', '') }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Pools with withdrawals') }}</dt>
                    <dd>{{ $summaryPoolCount }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Withdraw actions') }}</dt>
                    <dd>{{ $summaryCount }}</dd>
                </div>
            </dl>
        </section>

        <div class="ui-card">
            <div class="ui-card-header">
                <p class="ui-card-header-title">{{ __('Withdrawals') }}</p>
            </div>
            <div class="p-3 text-foreground sm:p-4">
                <x-ajax-table-region :fetch-url="route('admin.profit-withdrawals.index')" target-id="profit-withdrawals-table-fragment">
                    <x-table-search
                        :fetch-url="route('admin.profit-withdrawals.index')"
                        target-id="profit-withdrawals-table-fragment"
                        :placeholder="__('Search by pool title or deed number…')"
                    />
                    <div id="profit-withdrawals-table-fragment">
                        @include('admin.profit-withdrawals.partials.table-fragment', compact('batches'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>
    </div>
</x-app-layout>
