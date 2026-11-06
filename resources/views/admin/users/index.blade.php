<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Users') }}</h2>
            <div class="header-action-scroll">
                <a href="{{ route('admin.users.create') }}">
                    <x-primary-button type="button">{{ __('Add user') }}</x-primary-button>
                </a>
                <a href="{{ route('admin.users.import') }}">
                    <x-secondary-button type="button">{{ __('Import users') }}</x-secondary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="users-page space-y-4">
        @if ($errors->has('active'))
            <p class="text-sm text-error">{{ $errors->first('active') }}</p>
        @endif

        <section class="dashboard-stat-panel" aria-label="{{ __('Users summary') }}">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Summary') }}</h3>
            </div>
            <dl class="dashboard-stat-grid">
                <div class="dashboard-stat dashboard-stat--capital">
                    <dt>{{ __('Users') }}</dt>
                    <dd>{{ $userSummary['total'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--profit">
                    <dt>{{ __('Active') }}</dt>
                    <dd>{{ $userSummary['active'] }}</dd>
                </div>
                <div class="dashboard-stat">
                    <dt>{{ __('Inactive') }}</dt>
                    <dd>{{ $userSummary['inactive'] }}</dd>
                </div>
                <div class="dashboard-stat dashboard-stat--capital">
                    <dt>{{ __('Admins') }}</dt>
                    <dd>{{ $userSummary['admins'] }}</dd>
                </div>
            </dl>
        </section>

        <x-ajax-table-region :fetch-url="route('admin.users.index')" target-id="users-table-fragment" class="dashboard-stat-panel min-w-0">
            <div class="dashboard-stat-panel__head">
                <h3 class="dashboard-stat-panel__title">{{ __('Users list') }}</h3>
                <x-table-search
                    :fetch-url="route('admin.users.index')"
                    target-id="users-table-fragment"
                    :placeholder="__('Search by name, email, phone, or nominee…')"
                    class="!mb-0 w-full sm:w-80"
                />
            </div>
            <div class="p-3 pt-1">
                <div id="users-table-fragment">
                    @include('admin.users.partials.table-fragment', compact('users'))
                </div>
            </div>
        </x-ajax-table-region>
    </div>
</x-app-layout>
