@php
    $today = now()->toDateString();
@endphp

<x-modal name="withdraw-profit" focusable maxWidth="lg">
    <form
        method="post"
        class="space-y-0"
        data-withdraw-profit-form
        action="#"
    >
        @csrf
        <div class="flex items-center justify-between border-b border-line px-6 py-4">
            <div>
                <h3 class="text-lg font-semibold text-foreground" data-withdraw-modal-title>{{ __('Withdraw profit') }}</h3>
                <p class="mt-0.5 text-sm text-foreground-muted" data-withdraw-modal-subtitle></p>
            </div>
            <button
                type="button"
                class="rounded-lg p-1.5 text-foreground-muted transition hover:bg-surface-secondary hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                @click="$dispatch('close-modal', 'withdraw-profit')"
                aria-label="{{ __('Close') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-4 px-6 py-4">
            <div>
                <x-input-label for="withdraw_through_date" :value="__('Through date')" />
                <x-text-input
                    id="withdraw_through_date"
                    name="through_date"
                    type="date"
                    class="mt-1 block w-full"
                    max="{{ $today }}"
                    value="{{ $today }}"
                    required
                    data-withdraw-through-date
                />
                <p class="mt-1 text-sm text-foreground-muted">
                    {{ __('Withdraws available profit accrued through this date (profit til date minus already withdrawn).') }}
                </p>
            </div>

            <div class="rounded-xl border border-line bg-surface-variant px-4 py-3 text-sm" data-withdraw-preview>
                <p class="text-foreground-muted">{{ __('Choose a date to preview the withdrawable amount.') }}</p>
            </div>

            <div>
                <x-input-label for="withdraw_notes" :value="__('Notes (optional)')" />
                <textarea
                    id="withdraw_notes"
                    name="notes"
                    rows="2"
                    class="mt-1 block w-full rounded-lg border-line bg-surface-card shadow-sm focus:border-primary focus:ring-primary"
                    data-withdraw-notes
                ></textarea>
            </div>

            <p class="hidden text-sm text-error" data-withdraw-error></p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line px-6 py-4">
            <x-secondary-button type="button" @click="$dispatch('close-modal', 'withdraw-profit')">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-primary-button type="submit" data-withdraw-submit disabled>
                {{ __('Confirm withdraw') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>
