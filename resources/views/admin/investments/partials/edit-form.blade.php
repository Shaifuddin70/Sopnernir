<form method="post" action="{{ route('admin.investments.update', $investment) }}">
    @csrf
    @method('patch')
    <input type="hidden" name="_form" value="edit-investment">
    <input type="hidden" name="_investment_id" value="{{ $investment->id }}">
    <input type="hidden" name="_return" value="{{ $return ?? 'show' }}">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="edit_title" :value="__('Title')" />
            <x-text-input id="edit_title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $investment->title)" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="edit_notes" :value="__('Notes')" />
            <textarea id="edit_notes" name="notes" rows="3" class="ui-input mt-1 block w-full rounded-lg">{{ old('notes', $investment->notes) }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="edit_deed_no" :value="__('Deed number')" />
            <x-text-input id="edit_deed_no" name="deed_no" type="text" class="mt-1 block w-full" :value="old('deed_no', $investment->deed_no)" required />
            <x-input-error :messages="$errors->get('deed_no')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="edit_status" :value="__('Status')" />
            <select id="edit_status" name="status" class="ui-select mt-1 block w-full text-sm">
                @foreach (['draft' => __('Draft'), 'active' => __('Active'), 'closed' => __('Closed')] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $investment->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="edit_contribution_per_investor" :value="__('Contribution each')" />
            <x-text-input
                id="edit_contribution_per_investor"
                name="contribution_per_investor"
                type="text"
                class="mt-1 block w-full"
                :value="old('contribution_per_investor', $investment->contribution_per_investor)"
                required
            />
            <x-input-error :messages="$errors->get('contribution_per_investor')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="edit_total_profit_amount" :value="__('Total profit')" />
            <x-text-input
                id="edit_total_profit_amount"
                name="total_profit_amount"
                type="text"
                class="mt-1 block w-full"
                :value="old('total_profit_amount', $investment->total_profit_amount)"
                required
            />
            <p class="mt-1 text-sm text-foreground-muted">{{ __('Daily and monthly payouts are calculated automatically from this amount and the plan dates.') }}</p>
            @if ($investment->averageMonthlyProfitAmount() !== null || $investment->dailyPoolProfitFromTotal() !== null)
                <p class="mt-1 text-sm text-foreground-muted">
                    @if ($investment->averageMonthlyProfitAmount() !== null)
                        {{ __('Avg. :amount / month', ['amount' => number_format($investment->averageMonthlyProfitAmount(), 2, '.', '')]) }}
                    @endif
                    @if ($investment->dailyPoolProfitFromTotal() !== null)
                        <span class="mx-1" aria-hidden="true">·</span>
                        {{ __(':amount / day', ['amount' => number_format($investment->dailyPoolProfitFromTotal(), 2, '.', '')]) }}
                    @endif
                </p>
            @endif
            <x-input-error :messages="$errors->get('total_profit_amount')" class="mt-2" />
        </div>
        @if ($investment->participants_count ?? $investment->participants()->count())
            <div class="sm:col-span-2 rounded-lg border border-line bg-surface-secondary px-4 py-3 text-sm text-foreground-muted">
                {{ __('Current totals') }}:
                <span class="tabular-nums font-medium text-foreground">{{ number_format((float) ($investment->total_invested_amount ?? 0), 2, '.', '') }}</span>
                {{ __('invested') }},
                <span class="tabular-nums font-medium text-success">{{ number_format((float) ($investment->total_profit_amount ?? 0), 2, '.', '') }}</span>
                {{ __('planned profit') }}
            </div>
        @endif
        <div>
            <x-input-label for="edit_period_start" :value="__('Starting date')" />
            <x-text-input
                id="edit_period_start"
                name="period_start"
                type="date"
                class="mt-1 block w-full"
                :value="old('period_start', optional($investment->period_start)?->format('Y-m-d'))"
                required
            />
            <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="edit_deed_completion_deadline" :value="__('Ending date')" />
            <x-text-input
                id="edit_deed_completion_deadline"
                name="deed_completion_deadline"
                type="date"
                class="mt-1 block w-full"
                :value="old('deed_completion_deadline', optional($investment->deed_completion_deadline)?->format('Y-m-d'))"
                required
            />
            <x-input-error :messages="$errors->get('deed_completion_deadline')" class="mt-2" />
        </div>
        <div class="sm:col-span-2 rounded-lg border border-line bg-surface-secondary p-4 space-y-2">
            <x-input-label :value="__('Listed for investors')" />
            <input type="hidden" name="is_active" value="0" />
            <label class="inline-flex cursor-pointer items-start gap-2">
                <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-line bg-surface-card text-primary shadow-sm focus:ring-primary" @checked(old('is_active', $investment->is_active)) />
                <span class="text-sm text-foreground-muted">{{ __('When off, this pool is hidden from investor portfolios and accruals are paused.') }}</span>
            </label>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-line pt-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (($return ?? 'show') === 'index')
            <x-action-button :href="route('admin.investments.index')" variant="secondary">{{ __('Cancel') }}</x-action-button>
        @else
            <x-action-button :href="route('admin.investments.show', $investment)" variant="secondary">{{ __('Cancel') }}</x-action-button>
        @endif
    </div>
</form>
