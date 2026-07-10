<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('patch')

    @php($nominee = $user->nominee)

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
        <x-person-form-column
            class="lg:border-r lg:border-line lg:pe-8"
            :title="__('Account holder')"
            :image-url="$user->profileImageUrl()"
            :initial="$user->name ?: $user->email"
            input-name="image"
            input-id="profile_image"
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

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="rounded-lg border border-warning/30 bg-warning-muted px-3 py-2 text-sm text-foreground">
            {{ __('Your email address is unverified.') }}
            <button form="send-verification" type="submit" class="ms-1 font-medium text-primary underline hover:text-primary-hover">
                {{ __('Re-send verification email') }}
            </button>
            @if (session('status') === 'verification-link-sent')
                <p class="mt-1 text-xs text-success">{{ __('A new verification link has been sent.') }}</p>
            @endif
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-2 border-t border-line pt-4">
        <x-primary-button type="submit">{{ __('Save changes') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-success"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>

<form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-3 border-t border-line pt-5">
    @csrf
    @method('put')

    <h3 class="text-xs font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Password') }}</h3>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="update_password_current_password" :value="__('Current password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="update_password_password" :value="__('New password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm new password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-action-button type="submit" variant="secondary">{{ __('Update password') }}</x-action-button>
        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-success"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
