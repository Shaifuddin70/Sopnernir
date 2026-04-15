<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add user') }}</h2>
            <x-action-button :href="route('admin.users.index')" variant="secondary"
                class="text-sm">{{ __('Back to list') }}</x-action-button>
        </div>
    </x-slot>


    <div class="mx-auto p-2 sm:p-8">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <form method="post" action="{{ route('admin.users.store') }}" enctype="multipart/form-data"
                class="max-w-6xl space-y-8">
                @csrf

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-start lg:gap-12">
                    <div class="lg:border-r lg:border-gray-200 lg:pe-8">
                        @include('partials.user-account-fields', ['user' => null])
                    </div>
                    <div>
                        @include('partials.user-nominee-fields-section', ['user' => null])
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 border-t border-gray-200 pt-8 lg:grid-cols-2 lg:gap-12">
                    <div class="space-y-4 lg:pe-8">
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password"
                                required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>
                    <div class="rounded-md border border-gray-200 p-4 space-y-4 lg:self-start">
                        <div class="space-y-2">
                            <x-input-label :value="__('Account status')" />
                            <input type="hidden" name="is_active" value="0" />
                            <label class="inline-flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1"
                                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(old('is_active', true)) />
                                <span
                                    class="text-sm text-gray-600">{{ __('When off, this user cannot sign in.') }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                        </div>
                        <div class="space-y-2">
                            <x-input-label :value="__('Administrator')" />
                            <label class="inline-flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="is_admin" value="1"
                                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @checked(old('is_admin')) />
                                <span
                                    class="text-sm text-gray-600">{{ __('Grant admin access (manage users, investments, and accruals). Admins can still be tagged on investments like any other user.') }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('is_admin')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button type="submit">{{ __('Create user') }}</x-primary-button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
