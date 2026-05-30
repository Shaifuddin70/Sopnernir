<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2>{{ __('Users') }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.create') }}">
                    <x-primary-button type="button">{{ __('Add user') }}</x-primary-button>
                </a>
                <a href="{{ route('admin.users.import') }}">
                    <x-secondary-button type="button">{{ __('Import users') }}</x-secondary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        @if ($errors->has('active'))
            <p class="mb-4 text-sm text-error">{{ $errors->first('active') }}</p>
        @endif

        <section class="ui-card">
            <div class="ui-card-header">
                <p class="ui-card-header-title">{{ __('Users list') }}</p>
                <p class="ui-card-header-subtitle">{{ __('Search by name, email, phone, or nominee') }}</p>
            </div>
            <div class="p-4 sm:p-6">
                <x-ajax-table-region :fetch-url="route('admin.users.index')" target-id="users-table-fragment">
                    <x-table-search :fetch-url="route('admin.users.index')" target-id="users-table-fragment" :placeholder="__('Search by name, email, phone, or nominee…')" />
                    <div id="users-table-fragment">
                        @include('admin.users.partials.table-fragment', compact('users'))
                    </div>
                </x-ajax-table-region>
            </div>
        </section>
    </div>

</x-app-layout>
