<form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('put')

    @php($nominee = $user->nominee)

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
        <x-person-form-column
            class="lg:border-r lg:border-line lg:pe-8"
            :title="__('Account holder')"
            :image-url="$user->profileImageUrl()"
            :initial="$user->name ?: $user->email"
            input-name="image"
            input-id="image"
            error-key="image"
        >
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" required autocomplete="tel" />
                <x-input-error class="mt-1" :messages="$errors->get('phone')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="nid_number" :value="__('NID number')" />
                <x-text-input id="nid_number" name="nid_number" type="text" class="mt-1 block w-full" :value="old('nid_number', $user->nid_number)" required />
                <x-input-error class="mt-1" :messages="$errors->get('nid_number')" />
            </div>
            <div>
                <x-input-label for="address" :value="__('Address')" />
                <textarea id="address" name="address" rows="2" class="ui-input mt-1 block w-full rounded-lg" required>{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('address')" />
            </div>
        </x-person-form-column>

        <x-person-form-column
            :title="__('Nominee')"
            :image-url="$nominee?->profileImageUrl()"
            :initial="$nominee?->name ?: '?'"
            input-name="nominee[image]"
            input-id="nominee_image"
            error-key="nominee.image"
        >
            <div>
                <x-input-label for="nominee_name" :value="__('Nominee name')" />
                <x-text-input id="nominee_name" name="nominee[name]" type="text" class="mt-1 block w-full" :value="old('nominee.name', $nominee?->name)" required />
                <x-input-error class="mt-1" :messages="$errors->get('nominee.name')" />
            </div>
            <div>
                <x-input-label for="nominee_phone" :value="__('Nominee phone')" />
                <x-text-input id="nominee_phone" name="nominee[phone]" type="text" class="mt-1 block w-full" :value="old('nominee.phone', $nominee?->phone)" required />
                <x-input-error class="mt-1" :messages="$errors->get('nominee.phone')" />
            </div>
            <div>
                <x-input-label for="nominee_email" :value="__('Nominee email')" />
                <x-text-input id="nominee_email" name="nominee[email]" type="email" class="mt-1 block w-full" :value="old('nominee.email', $nominee?->email)" required />
                <x-input-error class="mt-1" :messages="$errors->get('nominee.email')" />
            </div>
            <div>
                <x-input-label for="nominee_nid_number" :value="__('Nominee NID number')" />
                <x-text-input id="nominee_nid_number" name="nominee[nid_number]" type="text" class="mt-1 block w-full" :value="old('nominee.nid_number', $nominee?->nid_number)" required />
                <x-input-error class="mt-1" :messages="$errors->get('nominee.nid_number')" />
            </div>
            <div>
                <x-input-label for="nominee_address" :value="__('Nominee address')" />
                <textarea id="nominee_address" name="nominee[address]" rows="2" class="ui-input mt-1 block w-full rounded-lg" required>{{ old('nominee.address', $nominee?->address) }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('nominee.address')" />
            </div>
        </x-person-form-column>
    </div>

    <div class="rounded-lg border border-line bg-surface-variant px-3 py-3">
        <div class="flex flex-wrap gap-x-6 gap-y-3">
            <div class="min-w-[12rem] flex-1">
                <x-input-label :value="__('Account status')" />
                @if ($user->id === auth()->id())
                    <p class="mt-1 text-xs text-foreground-muted">{{ __('Cannot deactivate your own account.') }}</p>
                    <input type="hidden" name="is_active" value="1" />
                @else
                    <input type="hidden" name="is_active" value="0" />
                    <label class="mt-1 inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-line text-primary focus:ring-primary" @checked(old('is_active', $user->is_active)) />
                        <span class="text-sm text-foreground">{{ __('Active') }}</span>
                    </label>
                @endif
                <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
            </div>
            <div class="min-w-[12rem] flex-1">
                <x-input-label :value="__('Administrator')" />
                <label class="mt-1 inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="is_admin" value="1" class="rounded border-line text-primary focus:ring-primary" @checked(old('is_admin', $user->isAdmin())) />
                    <span class="text-sm text-foreground">{{ __('Admin access') }}</span>
                </label>
                <x-input-error :messages="$errors->get('is_admin')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-line pt-4">
        <x-primary-button type="submit">{{ __('Save changes') }}</x-primary-button>
        <x-action-button :href="route('admin.users.index')" variant="secondary">{{ __('Cancel') }}</x-action-button>
    </div>
</form>

<form method="post" action="{{ route('admin.users.password.reset', $user) }}" class="mt-5 space-y-3 border-t border-line pt-5">
    @csrf
    @method('patch')

    <h3 class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Reset password') }}</h3>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <x-input-label for="reset_password" :value="__('New password')" />
            <x-text-input id="reset_password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="reset_password_confirmation" :value="__('Confirm new password')" />
            <x-text-input id="reset_password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>
    </div>

    <x-action-button type="submit" variant="secondary">{{ __('Update password') }}</x-action-button>
</form>
