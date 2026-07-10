@props(['user' => null])

@php
    $nominee = $user?->nominee;
@endphp

<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-md font-medium text-foreground">{{ __('Nominee') }}</h3>
        <p class="mt-1 text-sm text-foreground-muted">{{ __('Person to contact if something happens to your account.') }}</p>
    </div>

    <div>
        <x-input-label for="nominee_name" :value="__('Nominee name')" />
        <x-text-input id="nominee_name" name="nominee[name]" type="text" class="mt-1 block w-full" :value="old('nominee.name', $nominee?->name)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nominee.name')" />
    </div>

    <div>
        <x-input-label for="nominee_email" :value="__('Nominee email')" />
        <x-text-input id="nominee_email" name="nominee[email]" type="email" class="mt-1 block w-full" :value="old('nominee.email', $nominee?->email)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nominee.email')" />
    </div>

    <div>
        <x-input-label for="nominee_phone" :value="__('Nominee phone')" />
        <x-text-input id="nominee_phone" name="nominee[phone]" type="text" class="mt-1 block w-full" :value="old('nominee.phone', $nominee?->phone)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nominee.phone')" />
    </div>

    <div>
        <x-input-label for="nominee_nid_number" :value="__('Nominee NID number')" />
        <x-text-input id="nominee_nid_number" name="nominee[nid_number]" type="text" class="mt-1 block w-full" :value="old('nominee.nid_number', $nominee?->nid_number)" required />
        <x-input-error class="mt-2" :messages="$errors->get('nominee.nid_number')" />
    </div>

    <div>
        <x-input-label for="nominee_address" :value="__('Nominee address')" />
        <textarea id="nominee_address" name="nominee[address]" rows="3" class="mt-1 block w-full ui-input rounded-lg" required>{{ old('nominee.address', $nominee?->address) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('nominee.address')" />
    </div>

    <div>
        <x-input-label for="nominee_image" :value="__('Nominee photo')" />
        @if ($nominee && $nominee->profileImageUrl())
            <div class="mt-2 mb-2">
                <img src="{{ $nominee->profileImageUrl() }}" alt="" class="h-20 w-20 rounded-md object-cover border border-gray-200" />
            </div>
        @endif
        <input id="nominee_image" name="nominee[image]" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-foreground-muted file:mr-4 file:rounded-md file:border-0 file:bg-primary-muted file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/25" />
        @if ($user)
            <p class="mt-1 text-sm text-foreground-muted">{{ __('JPEG, PNG or WebP, max 2 MB. Leave empty to keep current photo.') }}</p>
        @else
            <p class="mt-1 text-sm text-foreground-muted">{{ __('JPEG, PNG or WebP, max 2 MB. Optional.') }}</p>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('nominee.image')" />
    </div>
</div>
