<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('All investments') }}</h2>
            <x-action-button :href="route('investments.index')" variant="secondary" class="text-sm">
                {{ __('Back to portfolio') }}
            </x-action-button>
        </div>
    </x-slot>

    <div class="portfolio-page space-y-4 pb-2">
        @if ($investmentRows->isEmpty())
            <div class="dashboard-stat-panel px-4 py-8 text-center">
                <p class="text-sm text-foreground-muted">{{ __('No active investments with a plan completion date yet.') }}</p>
            </div>
        @else
            <section class="dashboard-stat-panel min-w-0">
                <div class="dashboard-stat-panel__head">
                    <h3 class="dashboard-stat-panel__title">{{ __('All pools') }}</h3>
                    <span class="ui-chip">{{ trans_choice(':count pool|:count pools', $investmentRows->total(), ['count' => $investmentRows->total()]) }}</span>
                </div>
                <div class="p-3">
                    @include('investor.investments.partials.daily-profit-rows', [
                        'rows' => $investmentRows,
                        'showPoolTotals' => true,
                        'serialPaginator' => $investmentRows,
                    ])

                    <div class="ui-table-footer mt-2">
                        <x-pagination-per-page
                            :paginator="$investmentRows"
                            param="per_page"
                            :fetch-url="route('investments.all')"
                            target-id=""
                        />
                        <div class="ui-table-pagination">{{ $investmentRows->withQueryString()->links() }}</div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
