<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Edit profile') }}</h2>
    </x-slot>

    <div class="profile-page space-y-3 pb-2 sm:space-y-4">
        @include('profile.partials.edit-form', ['user' => $user])
    </div>
</x-app-layout>
