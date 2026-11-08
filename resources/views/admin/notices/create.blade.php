<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-2">
            <h2 class="ui-page-header-title">{{ __('New notice') }}</h2>
            <div class="header-action-scroll">
                <x-action-button :href="route('admin.notices.index')" variant="secondary" class="text-sm">{{ __('Back to list') }}</x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="ui-card p-4 sm:p-8">
        @include('admin.notices.partials.form', ['notice' => $notice])
    </div>
</x-app-layout>
