<x-phone-access-layout>
    <div class="space-y-4">
        <section class="phone-access-hero ui-card min-w-0">
            <div class="ui-card-header">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="ui-card-header-title">{{ __('Investment overview') }}</h1>
                        <div class="mt-2">
                            <x-user-identity :user="$user" :subtitle="$user->phone" size="md" />
                        </div>
                    </div>
                    <form method="POST" action="{{ route('phone-access.logout') }}" class="shrink-0">
                        @csrf
                        <x-secondary-button type="submit" class="w-full sm:w-auto">{{ __('Exit phone access') }}</x-secondary-button>
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
</x-phone-access-layout>
