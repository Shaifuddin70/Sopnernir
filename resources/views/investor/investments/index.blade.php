<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My investments') }}</h2>
    </x-slot>

    <div class="mx-auto space-y-6 p-2 sm:p-8">
        @if ($investments->total() > 0 || $totalTaggedCapital > 0 || $portfolioProfitTotal > 0)
            <div class="rounded-lg border border-indigo-100 bg-indigo-50/60 p-5 text-sm text-gray-800 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Your totals across all pools') }}</h3>
                <dl class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Tagged capital') }}
                        </dt>
                        <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900">
                            {{ number_format($totalTaggedCapital, 2, '.', '') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            {{ __('Your profit received') }}</dt>
                        <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900">
                            {{ number_format($portfolioProfitTotal, 2, '.', '') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            {{ __('Return on tagged capital') }}</dt>
                        <dd class="mt-1 text-lg font-semibold tabular-nums text-gray-900">
                            @if ($returnOnTaggedCapitalPct !== null)
                                {{ $returnOnTaggedCapitalPct }}%
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-gray-600">
                    {{ __('Profit is your share each month; capital is your total contributions on pools you are on.') }}
                </p>
                <p class="mt-2 text-xs">
                    <x-action-button :href="route('dashboard')"
                        class="text-sm font-medium">{{ __('Back to dashboard') }}</x-action-button>
                </p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 overflow-x-auto">
                <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-pools-fragment" ajax-fragment="pools">
                    <x-table-search :fetch-url="route('investments.index')" target-id="portfolio-pools-fragment" ajax-fragment="pools"
                        :placeholder="__('Search pools by title…')" />
                    <div id="portfolio-pools-fragment">
                        @include(
                            'investor.investments.partials.pools-fragment',
                            compact('investments', 'portfolioProfitTotal'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>

        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Profit by month') }}</h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Every tagged investor receives a share each month on each pool they are on, from the pool’s first month through the current month.') }}
                </p>
                <x-ajax-table-region :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" ajax-fragment="profit">
                    <x-table-search :fetch-url="route('investments.index')" target-id="portfolio-profit-fragment" param="profit_search"
                        ajax-fragment="profit" :placeholder="__('Search by pool title, month, or amount…')" />
                    <div id="portfolio-profit-fragment">
                        @include(
                            'investor.investments.partials.profit-month-fragment',
                            compact('profitByMonth'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>
    </div>

</x-app-layout>
