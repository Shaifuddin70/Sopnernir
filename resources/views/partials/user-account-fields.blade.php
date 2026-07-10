@props(['user' => null])

<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-md font-medium text-foreground">{{ __('Account holder') }}</h3>
        <p class="mt-1 text-sm text-foreground-muted">{{ __('Your personal details and NID.') }}</p>
    </div>

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user?->name)" required autocomplete="name" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user?->phone)" required autocomplete="tel" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required autocomplete="username" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="nid_number" :value="__('NID number')" />
        <x-text-input id="nid_number" name="nid_number" type="text" class="mt-1 block w-full" :value="old('nid_number', $user?->nid_number)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nid_number')" />
    </div>

    <div>
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" rows="3" class="mt-1 block w-full ui-input rounded-lg" required>{{ old('address', $user?->address) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>

    <div>
        <x-input-label for="image" :value="__('Profile photo')" />
        @if ($user && $user->profileImageUrl())
            <div class="mt-2 mb-2">
                <img src="{{ $user->profileImageUrl() }}" alt="" class="h-20 w-20 rounded-md object-cover border border-gray-200" />
            </div>
        @endif
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-foreground-muted file:mr-4 file:rounded-md file:border-0 file:bg-primary-muted file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/25" />
        @if ($user)
            <p class="mt-1 text-sm text-foreground-muted">{{ __('JPEG, PNG or WebP, max 2 MB. Leave empty to keep current photo.') }}</p>
        @else
            <p class="mt-1 text-sm text-foreground-muted">{{ __('JPEG, PNG or WebP, max 2 MB. Optional.') }}</p>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('image')" />
    </div>
</div>
