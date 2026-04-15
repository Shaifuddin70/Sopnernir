<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit investment') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('admin.investments.update', $investment) }}">
                    @csrf
                    @method('patch')
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $investment->title)" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $investment->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="period_start" :value="__('First accrual month (optional)')" />
                            <input
                                id="period_start"
                                name="period_start"
                                type="month"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value="{{ old('period_start', optional($investment->period_start)?->format('Y-m')) }}"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Optional: first month this pool pays profit (defaults to the month this record was created).') }}</p>
                            <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="default_monthly_rate_pct" :value="__('Default monthly rate %')" />
                            <x-text-input id="default_monthly_rate_pct" name="default_monthly_rate_pct" type="text" class="mt-1 block w-full" :value="old('default_monthly_rate_pct', $investment->default_monthly_rate_pct)" required />
                            <x-input-error :messages="$errors->get('default_monthly_rate_pct')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach (['draft' => __('Draft'), 'active' => __('Active'), 'closed' => __('Closed')] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status', $investment->status) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                        <div class="rounded-md border border-gray-200 p-4 space-y-2">
                            <x-input-label :value="__('Listed for investors')" />
                            <input type="hidden" name="is_active" value="0" />
                            <label class="inline-flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $investment->is_active)) />
                                <span class="text-sm text-gray-600">{{ __('When off, this pool is hidden from investor portfolios and accruals are paused.') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <x-action-button :href="route('admin.investments.show', $investment)" variant="secondary" class="text-sm">{{ __('Cancel') }}</x-action-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
