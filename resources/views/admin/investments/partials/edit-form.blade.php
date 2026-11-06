@php
    $closeUrl = $closeUrl ?? route('admin.investments.show', $investment);
    $invested = number_format((float) ($investment->total_invested_amount ?? 0), 2, '.', '');
@endphp

<form
    method="post"
    action="{{ route('admin.investments.update', $investment) }}"
    class="edit-investment"
    x-data="{
        profit: @js(old('total_profit_amount', $investment->total_profit_amount)),
        start: @js(old('period_start', optional($investment->period_start)?->format('Y-m-d'))),
        end: @js(old('deed_completion_deadline', optional($investment->deed_completion_deadline)?->format('Y-m-d'))),
        status: @js(old('status', $investment->status)),
        invested: @js($invested),
        parseAmount(value) {
            const amount = Number(String(value ?? '').replace(/,/g, ''));
            return Number.isFinite(amount) ? amount : 0;
        },
        parseDate(value) {
            if (! value || ! /^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return null;
            }
            const [year, month, day] = value.split('-').map(Number);
            const date = new Date(year, month - 1, day);
            date.setHours(0, 0, 0, 0);
            return date;
        },
        money(amount) {
            return this.parseAmount(amount).toFixed(2);
        },
        estimates() {
            const total = this.parseAmount(this.profit);
            const start = this.parseDate(this.start);
            const end = this.parseDate(this.end);
            if (! start || ! end || end < start || total <= 0) {
                return { month: '—', day: '—' };
            }
            const days = Math.max(1, Math.round((end - start) / 86400000));
            const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth()) + 1;
            return {
                month: months > 0 ? (total / months).toFixed(2) : '—',
                day: (total / days).toFixed(2),
            };
        },
    }"
>
    @csrf
    @method('patch')
    <input type="hidden" name="_form" value="edit-investment">
    <input type="hidden" name="_investment_id" value="{{ $investment->id }}">
    <input type="hidden" name="_return" value="{{ $return ?? 'show' }}">

    <div class="edit-investment__header">
        <div class="flex min-w-0 items-start gap-3">
            <span class="edit-investment__badge" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l5-5 3.5 3.5L19 7" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h5v5" />
                </svg>
            </span>
            <div class="min-w-0">
                <h3 class="text-lg font-bold text-[#16324f]">{{ __('Edit investment') }}</h3>
                <p class="mt-0.5 text-sm text-foreground-muted">{{ __('Update the investment details and plan settings.') }}</p>
            </div>
        </div>
        <a href="{{ $closeUrl }}" class="edit-investment__close" aria-label="{{ __('Close') }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <div class="edit-investment__body">
        <section class="edit-investment__section">
            <h4 class="edit-investment__section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h7.5L19 8v12.5H7z" />
                </svg>
                {{ __('Basic information') }}
            </h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="edit_title">
                        {{ __('Title') }} <span class="text-error">*</span>
                    </x-input-label>
                    <x-text-input id="edit_title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $investment->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="edit_deed_no">
                        {{ __('Deed number') }} <span class="text-error">*</span>
                    </x-input-label>
                    <x-text-input id="edit_deed_no" name="deed_no" type="text" class="mt-1 block w-full" :value="old('deed_no', $investment->deed_no)" required />
                    <x-input-error :messages="$errors->get('deed_no')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="edit_notes" :value="__('Notes')" />
                    <textarea id="edit_notes" name="notes" rows="2" class="ui-input mt-1 block w-full" placeholder="{{ __('Add notes (optional)…') }}">{{ old('notes', $investment->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <section class="edit-investment__section">
                <h4 class="edit-investment__section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7l8 4 8-4-8-4Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 12 8 4 8-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 8 4 8-4" />
                    </svg>
                    {{ __('Investment details') }}
                </h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <x-input-label for="edit_contribution_per_investor">
                            {{ __('Contribution each') }} <span class="text-error">*</span>
                        </x-input-label>
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
                        <x-input-label for="edit_total_profit_amount">
                            {{ __('Total profit') }} <span class="text-error">*</span>
                        </x-input-label>
                        <x-text-input
                            id="edit_total_profit_amount"
                            name="total_profit_amount"
                            type="text"
                            class="mt-1 block w-full"
                            x-model="profit"
                            required
                        />
                        <x-input-error :messages="$errors->get('total_profit_amount')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="edit_status">
                            {{ __('Status') }} <span class="text-error">*</span>
                        </x-input-label>
                        <div class="relative mt-1">
                            <span
                                class="pointer-events-none absolute left-3 top-1/2 h-2.5 w-2.5 -translate-y-1/2 rounded-full"
                                :class="status === 'active' ? 'bg-success' : (status === 'closed' ? 'bg-foreground-muted' : 'bg-warning')"
                                aria-hidden="true"
                            ></span>
                            <select id="edit_status" name="status" class="ui-select edit-investment__status block w-full text-sm" x-model="status">
                                @foreach (['draft' => __('Draft'), 'active' => __('Active'), 'closed' => __('Closed')] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>
            </section>

            <section class="edit-investment__estimates">
                <h4 class="edit-investment__section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="5" y="3" width="14" height="18" rx="2" />
                        <path stroke-linecap="round" d="M8 8h8M8 12h2M12 12h2M16 12h.01M8 16h2M12 16h2" />
                    </svg>
                    {{ __('Payout estimates') }}
                </h4>
                <div class="edit-investment__estimate-row">
                    <div>
                        <span class="edit-investment__estimate-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="5" width="16" height="15" rx="2" />
                                <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                            </svg>
                        </span>
                        <p>{{ __('Avg. per month') }}</p>
                        <strong x-text="estimates().month"></strong>
                    </div>
                    <div>
                        <span class="edit-investment__estimate-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7l8 4 8-4-8-4Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4 12 8 4 8-4" />
                            </svg>
                        </span>
                        <p>{{ __('Avg. per day') }}</p>
                        <strong x-text="estimates().day"></strong>
                    </div>
                </div>
                <p class="edit-investment__estimate-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="8" />
                        <path stroke-linecap="round" d="M12 11v5" />
                        <path stroke-linecap="round" d="M12 8h.01" />
                    </svg>
                    {{ __('Daily and monthly payouts are calculated automatically from this amount and the plan dates.') }}
                </p>
            </section>
        </div>

        <div class="edit-investment__totals">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" d="M5 19V9M10 19V5M15 19v-7M20 19V8" />
            </svg>
            <p>
                {{ __('Current totals') }}:
                <strong x-text="invested"></strong>
                {{ __('invested') }},
                <strong class="text-success" x-text="money(profit)"></strong>
                {{ __('planned profit') }}
            </p>
        </div>

        <section class="edit-investment__section">
            <h4 class="edit-investment__section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="4" y="5" width="16" height="15" rx="2" />
                    <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                </svg>
                {{ __('Investment period') }}
            </h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <x-input-label for="edit_period_start">
                        {{ __('Starting date') }} <span class="text-error">*</span>
                    </x-input-label>
                    <x-text-input id="edit_period_start" name="period_start" type="date" class="mt-1 block w-full" x-model="start" required />
                    <x-input-error :messages="$errors->get('period_start')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="edit_deed_completion_deadline">
                        {{ __('Ending date') }} <span class="text-error">*</span>
                    </x-input-label>
                    <x-text-input id="edit_deed_completion_deadline" name="deed_completion_deadline" type="date" class="mt-1 block w-full" x-model="end" required />
                    <x-input-error :messages="$errors->get('deed_completion_deadline')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="edit-investment__section">
            <h4 class="edit-investment__section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 6.5 12 6.5 21.5 12 21.5 12 18 17.5 12 17.5 2.5 12 2.5 12Z" />
                    <circle cx="12" cy="12" r="2.5" />
                </svg>
                {{ __('Listed for investors') }}
            </h4>
            <input type="hidden" name="is_active" value="0" />
            <label class="inline-flex cursor-pointer items-start gap-2">
                <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-line bg-white text-primary shadow-sm focus:ring-primary" @checked(old('is_active', $investment->is_active)) />
                <span class="text-sm text-foreground-muted">{{ __('When off, this pool is hidden from investor portfolios and accruals are paused.') }}</span>
            </label>
        </section>
    </div>

    <div class="edit-investment__footer">
        <x-action-button :href="$closeUrl" variant="secondary">{{ __('Cancel') }}</x-action-button>
        <x-primary-button>
            <svg class="me-1.5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 4h9l3 3v13H6z" />
                <path stroke-linecap="round" d="M9 4v4h6M8 14h8M8 17h5" />
            </svg>
            {{ __('Save') }}
        </x-primary-button>
    </div>
</form>
