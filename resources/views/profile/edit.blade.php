<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl space-y-6">
        {{-- Same shell as admin users create/edit: one full-width white card for account | nominee + save --}}
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-6xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-app-layout>
