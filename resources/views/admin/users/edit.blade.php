<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Edit user') }}</h2>
            <x-action-button :href="route('admin.users.index')" variant="secondary" class="text-sm">
                {{ __('Back to list') }}
            </x-action-button>
        </div>
    </x-slot>

    <div class="space-y-4 pb-2">
        @if ($errors->has('active'))
            <p class="text-sm text-error">{{ $errors->first('active') }}</p>
        @endif

        <section class="ui-glass-panel min-w-0">
            <div class="border-b border-line bg-surface-variant px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-foreground">{{ $user->name }}</h3>
                        <p class="truncate text-sm text-foreground-muted">{{ $user->email }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($user->isAdmin())
                            <span class="ui-badge-brand">{{ __('Admin') }}</span>
                        @else
                            <span class="ui-badge-muted">{{ __('Investor') }}</span>
                        @endif
                        @if ($user->is_active)
                            <span class="ui-badge-success">{{ __('Active') }}</span>
                        @else
                            <span class="ui-badge-muted">{{ __('Inactive') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                @include('admin.users.partials.edit-form', compact('user'))
            </div>
        </section>
    </div>
</x-app-layout>
