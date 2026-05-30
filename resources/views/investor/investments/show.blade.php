<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">{{ $investment->title }}</h2>
            <x-action-button :href="route('investments.index')" class="text-sm">{{ __('Back to portfolio') }}</x-action-button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="ui-card shadow-sm sm:rounded-lg p-6 text-sm text-foreground space-y-1">
            <p><span class="font-medium">{{ __('Status') }}:</span> {{ $investment->status }}</p>
            @if ($investment->deed_completion_deadline)
                <p>
                    <span class="font-medium">{{ __('Plan completion date') }}:</span>
                    {{ $investment->deed_completion_deadline->translatedFormat('j F Y') }}
                    @if ($investment->hasPlanCompleted())
                        <span class="text-red-700 font-medium">({{ __('plan completed') }})</span>
                    @endif
                </p>
            @endif
            <p><span class="font-medium">{{ __('My contribution') }}:</span>
                {{ $myParticipant?->contribution_amount ?? '—' }}</p>
            <p><span class="font-medium">{{ __('Default monthly rate') }}:</span>
                {{ $investment->default_monthly_rate_pct }}%</p>
            <p><span class="font-medium">{{ __('My total profit on this pool') }}:</span>
                {{ number_format((float) $myTotalProfit, 2, '.', '') }}</p>
            <p class="mt-2 text-xs text-foreground-muted">
                {{ __('Each month, pool profit is split among everyone tagged on the pool based on their contribution.') }}
            </p>
        </div>

        <div class="ui-card shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-2">{{ __('My profit by month (this pool)') }}</h3>
            <p class="text-sm text-foreground-muted mb-4">{{ __('Your share each month on this pool.') }}</p>
            <x-ajax-table-region :fetch-url="route('investments.show', $investment)" target-id="pool-profit-fragment" ajax-fragment="pool_profit">
                <x-table-search :fetch-url="route('investments.show', $investment)" target-id="pool-profit-fragment" ajax-fragment="pool_profit"
                    :placeholder="__('Search by month or profit amount…')" />
                <div id="pool-profit-fragment">
                    @include(
                        'investor.investments.partials.show-profit-fragment',
                        compact('investment', 'myProfitByMonth'))
                </div>
            </x-ajax-table-region>
        </div>

        <div class="ui-card shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-4">{{ __('Documents') }}</h3>
            <ul class="divide-y border rounded-md text-sm">
                @forelse ($investment->documents as $doc)
                    <li class="flex justify-between items-center px-4 py-2">
                        <span>{{ $doc->original_name }}</span>
                        <x-action-button :href="route('investments.documents.download', [$investment, $doc])">{{ __('Download') }}</x-action-button>
                    </li>
                @empty
                    <li class="px-4 py-3 text-foreground-muted">{{ __('No documents uploaded.') }}</li>
                @endforelse
            </ul>
        </div>

        <div class="ui-card shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-foreground mb-4">{{ __('Co-investors') }}</h3>
            @if ($investment->participants->isEmpty())
                <p class="text-sm text-foreground-muted">{{ __('No participants on this pool yet.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="ui-table min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="py-2 pr-4">{{ __('Name') }}</th>
                                <th class="py-2 text-right">{{ __('Contribution') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($investment->participants as $p)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 pr-4 font-medium text-foreground">{{ $p->user->name }}</td>
                                    <td class="py-2 text-right tabular-nums">{{ $p->contribution_amount }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</x-app-layout>
