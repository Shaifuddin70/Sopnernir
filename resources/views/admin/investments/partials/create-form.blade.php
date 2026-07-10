<form method="post" action="{{ route('admin.investments.store') }}">
    @csrf
    <input type="hidden" name="_form" value="create-investment">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="create_title" :value="__('Title')" />
            <x-text-input id="create_title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="create_notes" :value="__('Notes')" />
            <textarea id="create_notes" name="notes" rows="3" class="ui-input mt-1 block w-full rounded-lg">{{ old('notes') }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_deed_no" :value="__('Deed number')" />
            <x-text-input id="create_deed_no" name="deed_no" type="text" class="mt-1 block w-full" :value="old('deed_no')" required />
            <x-input-error :messages="$errors->get('deed_no')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_status" :value="__('Status')" />
            <select id="create_status" name="status" class="ui-select mt-1 block w-full text-sm">
                @foreach (['draft' => __('Draft'), 'active' => __('Active'), 'closed' => __('Closed')] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', 'draft') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_contribution_per_investor" :value="__('Contribution each')" />
            <x-text-input id="create_contribution_per_investor" name="contribution_per_investor" type="text" class="mt-1 block w-full" :value="old('contribution_per_investor')" required />
            <p class="mt-1 text-sm text-foreground-muted">{{ __('Applied to each investor when they are tagged on this pool.') }}</p>
            <x-input-error :messages="$errors->get('contribution_per_investor')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_total_profit_amount" :value="__('Total profit')" />
            <x-text-input id="create_total_profit_amount" name="total_profit_amount" type="text" class="mt-1 block w-full" :value="old('total_profit_amount')" required />
            <p class="mt-1 text-sm text-foreground-muted">{{ __('Planned profit for the full plan. Daily and monthly payouts are calculated automatically from this amount and the plan dates.') }}</p>
            <x-input-error :messages="$errors->get('total_profit_amount')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_period_start" :value="__('Starting date')" />
            <x-text-input
                id="create_period_start"
                name="period_start"
                type="date"
                class="mt-1 block w-full"
                :value="old('period_start')"
                required
            />
            <p class="mt-1 text-sm text-foreground-muted">{{ __('First day of the investment plan.') }}</p>
            <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_deed_completion_deadline" :value="__('Ending date')" />
            <x-text-input
                id="create_deed_completion_deadline"
                name="deed_completion_deadline"
                type="date"
                class="mt-1 block w-full"
                :value="old('deed_completion_deadline')"
                required
            />
            <p class="mt-1 text-sm text-foreground-muted">{{ __('Last day of the plan.') }}</p>
            <x-input-error :messages="$errors->get('deed_completion_deadline')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="create_payment_month" :value="__('Payment month for tagging')" />
            <input
                id="create_payment_month"
                name="payment_month"
                type="month"
                value="{{ old('payment_month', $defaultPaymentMonth ?? now()->format('Y-m')) }}"
                class="ui-input mt-1 block w-full"
                required
            />
            <p class="mt-1 text-sm text-foreground-muted">
                {{ __('Only investors marked paid for this month on Monthly payments will be tagged automatically.') }}
            </p>
            <x-input-error :messages="$errors->get('payment_month')" class="mt-2" />
        </div>
        <div class="sm:col-span-2 rounded-lg border border-line bg-surface-secondary p-4 space-y-2">
            <x-input-label :value="__('Listed for investors')" />
            <input type="hidden" name="is_active" value="0" />
            <label class="inline-flex cursor-pointer items-start gap-2">
                <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-line bg-surface-card text-primary shadow-sm focus:ring-primary" @checked(old('is_active', true)) />
                <span class="text-sm text-foreground-muted">{{ __('When off, this pool is hidden from investor portfolios and accruals are paused.') }}</span>
            </label>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-line pt-4">
        <x-primary-button>{{ __('Create') }}</x-primary-button>
        <x-action-button type="button" variant="secondary" @click="$dispatch('close-modal', 'create-investment')">
            {{ __('Cancel') }}
        </x-action-button>
    </div>
</form>
