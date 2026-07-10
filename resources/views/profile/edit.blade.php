<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Profile') }}</h2>
    </x-slot>

    <div class="space-y-4 pb-2">
        <section class="ui-glass-panel mx-auto min-w-0 max-w-5xl">
            <div class="p-4 sm:p-5">
                @include('profile.partials.edit-form', ['user' => $user])
            </div>
        </section>
    </div>
</x-app-layout>
