<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit user') }}: {{ $user->name }}</h2>
            <x-action-button :href="route('admin.users.index')" variant="secondary"
                class="text-sm">{{ __('Back to list') }}</x-action-button>
        </div>
    </x-slot>

    <div class="mx-auto p-2 sm:p-8">
        @if (session('status'))
            <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
        @endif
        @if ($errors->has('active'))
            <p class="mb-4 text-sm text-red-600">{{ $errors->first('active') }}</p>
        @endif

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
                class="max-w-6xl space-y-8">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-start lg:gap-12">
                    <div class="lg:border-r lg:border-gray-200 lg:pe-8">
                        @include('partials.user-account-fields', ['user' => $user])
                    </div>
                    <div>
                        @include('partials.user-nominee-fields-section', ['user' => $user])
                    </div>
                </div>

                <div class="rounded-md border border-gray-200 p-4 space-y-2">
                    <x-input-label :value="__('Account status')" />
                    @if ($user->id === auth()->id())
                        <p class="text-sm text-gray-600">
                            {{ __('Your account is always active while you are signed in. Use another admin to deactivate this user if needed.') }}
                        </p>
                        <input type="hidden" name="is_active" value="1" />
                    @else
                        <input type="hidden" name="is_active" value="0" />
                        <label class="inline-flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                @checked(old('is_active', $user->is_active)) />
                            <span class="text-sm text-gray-600">{{ __('When off, this user cannot sign in.') }}</span>
                        </label>
                    @endif
                    <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                </div>

                <div class="rounded-md border border-gray-200 p-4 space-y-2">
                    <x-input-label :value="__('Administrator')" />
                    <label class="inline-flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="is_admin" value="1"
                            class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('is_admin', $user->isAdmin())) />
                        <span
                            class="text-sm text-gray-600">{{ __('Grant admin access (manage users, investments, and accruals). Admins can still be tagged on investments like any other user.') }}</span>
                    </label>
                    <x-input-error :messages="$errors->get('is_admin')" class="mt-1" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
