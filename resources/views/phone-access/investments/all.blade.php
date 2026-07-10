<x-phone-access-layout>
    <div class="space-y-4">
        <section class="phone-access-hero ui-card min-w-0">
            <div class="ui-card-header">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ __('All investments') }}</h1>
                        <p class="mt-1 text-sm text-foreground-muted">{{ __('Every pool you are tagged on, newest first.') }}</p>
                    </div>
                    <x-action-button :href="route('phone-access.investments.index')" variant="secondary" class="w-full shrink-0 sm:w-auto">
                        {{ __('Back to overview') }}
                    </x-action-button>
                </div>
            </div>
        </section>

        @if ($investmentRows->isEmpty())
            <div class="dashboard-stat-panel px-4 py-8 text-center">
                <p class="text-sm text-foreground-muted">{{ __('No active investments with a plan completion date yet.') }}</p>
            </div>
        @else
            <section class="dashboard-stat-panel dashboard-stat-panel--scroll min-w-0 p-3 sm:p-4">
                @include('investor.investments.partials.daily-profit-rows', [
                    'rows' => $investmentRows,
                    'viewRoute' => 'phone-access',
                    'showPoolTotals' => true,
                    'serialPaginator' => $investmentRows,
                ])

                <div class="ui-table-footer mt-4">
                    <x-pagination-per-page
                        :paginator="$investmentRows"
                        param="per_page"
                        :fetch-url="route('phone-access.investments.all')"
                        target-id=""
                    />
                    <div class="ui-table-pagination">{{ $investmentRows->withQueryString()->links() }}</div>
                </div>
            </section>
        @endif
    </div>
</x-phone-access-layout>
