<x-guest-layout>
    <div class="mx-auto w-full max-w-6xl space-y-4">
        <section class="ui-card min-w-0 overflow-hidden">
            <div class="ui-card-header">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ __('Investment overview') }}</h1>
                        <div class="mt-2">
                            <x-user-identity :user="$user" :subtitle="$user->phone" size="md" />
                        </div>
                    </div>
                    <form method="POST" action="{{ route('phone-access.logout') }}" class="shrink-0">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Exit phone access') }}</x-secondary-button>
                    </form>
                </div>
            </div>
        </section>

        @include('investor.investments.partials.daily-profit-overview', [
            'dailyProfitSummary' => $dailyProfitSummary,
            'dailyProfitRows' => $dailyProfitRows,
            'dailyProfitRowsTotal' => $dailyProfitRowsTotal,
            'showPlatformStats' => true,
            'platformDailySummary' => $platformDailySummary,
            'viewRoute' => 'phone-access',
        ])
    </div>
</x-guest-layout>
