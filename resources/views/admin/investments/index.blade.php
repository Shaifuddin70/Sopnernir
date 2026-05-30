@php
    $openCreateInvestmentModal = request()->boolean('new')
        || (old('_form') === 'create-investment' && $errors->isNotEmpty());
    $openEditInvestmentModal = $editingInvestment !== null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2>{{ __('Investments') }}</h2>
            <x-primary-button type="button" @click="$dispatch('open-modal', 'create-investment')">
                {{ __('New investment') }}
            </x-primary-button>
        </div>
    </x-slot>

    <div class="space-y-3">
        <div class="ui-card">
            <div class="ui-card-header">
                <p class="ui-card-header-title">{{ __('Investments list') }}</p>
                <p class="ui-card-header-subtitle">{{ __('Compact view with quick actions') }}</p>
            </div>
            <div class="p-3 text-foreground sm:p-4">
                <x-ajax-table-region :fetch-url="route('admin.investments.index')" target-id="investments-table-fragment">
                    <x-table-search :fetch-url="route('admin.investments.index')" target-id="investments-table-fragment" :placeholder="__('Search by title, notes, or status…')" />
                    <div id="investments-table-fragment">
                        @include('admin.investments.partials.table-fragment', compact('investments'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>
    </div>

    <x-modal name="create-investment" :show="$openCreateInvestmentModal" focusable maxWidth="2xl">
        <div class="flex items-center justify-between border-b border-line px-6 py-4">
            <h3 class="text-lg font-semibold text-foreground">{{ __('New investment') }}</h3>
            <button
                type="button"
                class="rounded-lg p-1.5 text-foreground-muted transition hover:bg-surface-secondary hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                @click="$dispatch('close-modal', 'create-investment')"
                aria-label="{{ __('Close') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="max-h-[calc(100vh-5rem)] overflow-y-auto px-6 py-4">
            @include('admin.investments.partials.create-form')
        </div>
    </x-modal>

    @if ($editingInvestment)
        <x-modal name="edit-investment" :show="$openEditInvestmentModal" focusable maxWidth="2xl">
            <div class="flex items-center justify-between border-b border-line px-6 py-4">
                <h3 class="text-lg font-semibold text-foreground">{{ __('Edit investment') }}</h3>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-foreground-muted transition hover:bg-surface-secondary hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                    onclick="window.location.href = '{{ route('admin.investments.index') }}'"
                    aria-label="{{ __('Close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[calc(100vh-5rem)] overflow-y-auto px-6 py-4">
                @include('admin.investments.partials.edit-form', ['investment' => $editingInvestment, 'return' => 'index'])
            </div>
        </x-modal>
    @endif
</x-app-layout>
