<form method="post" action="{{ route('admin.investments.store') }}">
    @csrf
    <input type="hidden" name="_form" value="create-investment">

    <div class="space-y-4">
        <div>
            <x-input-label for="create_title" :value="__('Title')" />
            <x-text-input id="create_title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_notes" :value="__('Notes')" />
            <textarea id="create_notes" name="notes" rows="3" class="ui-input mt-1 block w-full rounded-lg">{{ old('notes') }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_deed_completion_deadline" :value="__('Plan completion date')" />
            <x-text-input
                id="create_deed_completion_deadline"
                name="deed_completion_deadline"
                type="date"
                class="mt-1 block w-full"
                :value="old('deed_completion_deadline')"
                required
            />
            <p class="mt-1 text-xs text-foreground-muted">{{ __('Last day of the investment plan. Monthly profit accruals stop after this date and the pool is closed automatically.') }}</p>
            <x-input-error :messages="$errors->get('deed_completion_deadline')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_period_start" :value="__('First accrual month (optional)')" />
            <input
                id="create_period_start"
                name="period_start"
                type="month"
                class="ui-input mt-1 block w-full rounded-lg"
                value="{{ old('period_start') }}"
            />
            <p class="mt-1 text-xs text-foreground-muted">{{ __('Optional: first month this pool pays profit (defaults to the month you create this record).') }}</p>
            <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="create_default_monthly_rate_pct" :value="__('Default monthly rate %')" />
            <x-text-input id="create_default_monthly_rate_pct" name="default_monthly_rate_pct" type="text" class="mt-1 block w-full" value="{{ old('default_monthly_rate_pct', '1.5') }}" required />
            <x-input-error :messages="$errors->get('default_monthly_rate_pct')" class="mt-2" />
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
        <div class="rounded-lg border border-line bg-surface-secondary p-4 space-y-2">
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
