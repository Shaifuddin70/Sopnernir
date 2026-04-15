<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 p-2 sm:space-y-10 sm:p-8">
        {{-- Welcome --}}
        <div
            class="rounded-2xl border border-gray-200 bg-white px-5 py-8 shadow-sm ring-1 ring-gray-900/5 sm:px-8 sm:py-10"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        {{ config('app.name') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                        {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-gray-600">
                        {{ auth()->user()->email }}
                    </p>
                </div>
                <div class="shrink-0">
                    <x-action-button
                        :href="route('investments.index')"
                        variant="secondary"
                        class="px-4 py-2.5 text-sm font-semibold"
                    >{{ __('Open portfolio') }}</x-action-button>
                </div>
            </div>
        </div>

        @if ($platform !== null)
            <section
                class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/5"
                aria-labelledby="platform-overview-heading"
            >
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 sm:px-6">
                    <h2 id="platform-overview-heading" class="text-base font-semibold text-gray-900">
                        {{ __('Platform overview') }}
                    </h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        {{ __('Participant rows: :n', ['n' => $platform['participant_rows']]) }}
                    </p>
                </div>
                <div class="grid gap-px bg-gray-100 sm:grid-cols-3">
                    <article class="bg-white p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Investments') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-gray-900">
                                    {{ $platform['investments_total'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100"
                                aria-hidden="true"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.75"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-gray-600">
                            {{ __('Active: :count', ['count' => $platform['investments_active']]) }}
                        </p>
                    </article>
                    <article class="bg-white p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Total tagged capital') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-gray-900">
                                    {{ $platform['total_contributions'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                                aria-hidden="true"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.75"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-gray-600">{{ __('Sum of all participant contributions.') }}</p>
                    </article>
                    <article class="bg-white p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Total profit distributed') }}
                                </p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-gray-900">
                                    {{ $platform['total_profit_distributed'] }}
                                </p>
                            </div>
                            <span
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-700 ring-1 ring-violet-100"
                                aria-hidden="true"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.75"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    />
                                </svg>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-gray-600">
                            {{ __(':count accrual month(s)', ['count' => $platform['accrual_periods']]) }}
                        </p>
                    </article>
                </div>
            </section>
        @endif

        <section
            class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/5"
            aria-labelledby="portfolio-heading"
        >
            <div class="flex flex-col gap-4 border-b border-gray-100 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 id="portfolio-heading" class="text-base font-semibold text-gray-900">
                        {{ __('Your portfolio') }}
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-gray-500">
                        @if ($personal['return_on_tagged_capital_pct'] !== null)
                            <span class="tabular-nums font-medium text-gray-700">{{ __('Return') }}:
                                {{ $personal['return_on_tagged_capital_pct'] }}%</span>
                            <span class="mx-2 text-gray-300" aria-hidden="true">·</span>
                        @endif
                        {{ __('Profit is your share each month; capital is your total contributions on pools you are on.') }}
                    </p>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-xl border border-gray-100 bg-gray-50/50 p-5 ring-1 ring-gray-900/5 transition hover:border-gray-200 hover:bg-white"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('Your investments') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-gray-900">
                            {{ $personal['investments_count'] }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-emerald-100/80 bg-emerald-50/40 p-5 ring-1 ring-emerald-900/5 transition hover:border-emerald-200/80 hover:bg-emerald-50/60"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800/80">
                            {{ __('Your tagged capital') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-emerald-950">
                            {{ $personal['total_contribution'] }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-indigo-100/80 bg-indigo-50/40 p-5 ring-1 ring-indigo-900/5 transition hover:border-indigo-200/80 hover:bg-indigo-50/60"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-800/80">
                            {{ __('Your profit received') }}
                        </p>
                        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-indigo-950">
                            {{ $personal['total_profit'] }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/5"
            aria-labelledby="top-investors-heading"
        >
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 sm:px-6">
                <h2 id="top-investors-heading" class="text-base font-semibold text-gray-900">
                    {{ __('Top investors') }}
                </h2>
                @if ($topInvestorsScope === 'global')
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Ranked by total profit share from all accruals.') }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Ranked by profit share on investments you are tagged on.') }}
                    </p>
                @endif
            </div>
            <div class="p-5 sm:p-6">
                <x-ajax-table-region
                    :fetch-url="route('dashboard')"
                    target-id="dashboard-top-investors-fragment"
                    ajax-fragment="top_investors"
                >
                    <x-table-search
                        class="mb-4"
                        :fetch-url="route('dashboard')"
                        target-id="dashboard-top-investors-fragment"
                        ajax-fragment="top_investors"
                        :placeholder="__('Search investors by name or email…')"
                    />
                    <div id="dashboard-top-investors-fragment">
                        @include('dashboard.partials.top-investors-fragment', compact('topInvestors'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>
</x-app-layout>
