<x-guest-layout>
    <div class="mx-auto w-full max-w-6xl space-y-4">
        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ __('All investments') }}</h1>
                        <p class="mt-1 text-sm text-foreground-muted">{{ __('Every pool you are tagged on, newest first.') }}</p>
                    </div>
                    <x-action-button :href="route('phone-access.investments.index')" variant="secondary" class="shrink-0">
                        {{ __('Back to overview') }}
                    </x-action-button>
                </div>
            </div>
        </section>

        @if ($investmentRows->isEmpty())
            <div class="ui-glass-panel p-6 text-center">
                <p class="text-sm text-foreground-muted">{{ __('No active investments with a plan completion date yet.') }}</p>
            </div>
        @else
            @include('investor.investments.partials.daily-profit-rows', [
                'rows' => $investmentRows,
                'viewRoute' => 'phone-access',
                'showPoolTotals' => true,
                'serialPaginator' => $investmentRows,
            ])

            <div class="ui-table-footer">
                <x-pagination-per-page
                    :paginator="$investmentRows"
                    param="per_page"
                    :fetch-url="route('phone-access.investments.all')"
                    target-id=""
                />
                <div class="ui-table-pagination">{{ $investmentRows->withQueryString()->links() }}</div>
            </div>
        @endif
    </div>
</x-guest-layout>
