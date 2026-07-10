@props([
    'title',
    'imageUrl' => null,
    'initial' => '?',
    'inputName',
    'inputId',
    'errorKey',
])

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <div class="flex flex-col items-center border-b border-line pb-4 text-center">
        <label
            class="person-photo-upload group relative block h-20 w-20 cursor-pointer overflow-hidden rounded-full border-2 border-line shadow-elevation-1 focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 sm:h-24 sm:w-24"
            aria-label="{{ __('Choose profile photo') }}"
        >
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt=""
                    class="h-full w-full object-cover"
                />
            @else
                <span
                    class="flex h-full w-full items-center justify-center bg-primary-muted text-2xl font-semibold text-primary sm:text-3xl"
                    aria-hidden="true"
                >{{ strtoupper(\Illuminate\Support\Str::substr($initial, 0, 1)) }}</span>
            @endif

            <span
                class="absolute inset-0 flex items-center justify-center bg-foreground/60 opacity-0 transition-opacity duration-150 group-hover:opacity-100 group-focus-within:opacity-100"
                aria-hidden="true"
            >
                <span class="px-2 text-center text-sm font-semibold leading-tight text-white sm:text-sm">
                    {{ __('Choose file') }}
                </span>
            </span>

            <input
                id="{{ $inputId }}"
                name="{{ $inputName }}"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="sr-only"
            />
        </label>

        <h3 class="mt-3 text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ $title }}</h3>
        <x-input-error class="mt-1" :messages="$errors->get($errorKey)" />
    </div>

    <div class="mt-4 space-y-3">
        {{ $slot }}
    </div>
</div>
