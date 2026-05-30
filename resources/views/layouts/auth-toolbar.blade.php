@php
    $authUser = Auth::user();
@endphp

<div class="flex min-w-0 max-w-full items-center gap-2 sm:gap-3">
    <div class="hidden shrink-0 lg:block">
        <x-language-switcher />
    </div>
    <x-dropdown align="right" width="48" contentClasses="py-1 bg-surface-card border border-line">
        <x-slot name="trigger">
            <button
                type="button"
                class="inline-flex h-10 max-w-full min-w-0 items-center gap-2 rounded-lg border border-line bg-surface-card py-1 ps-1 pe-2 text-sm font-medium leading-4 text-foreground transition duration-150 ease-in-out hover:bg-surface-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface"
            >
                <span class="ui-user-identity__avatar ui-user-identity__avatar--compact">
                    <x-user-avatar :user="$authUser" />
                </span>
                <span class="sr-only">{{ $authUser->name }}</span>
                <span class="hidden max-w-[9rem] truncate md:inline lg:max-w-[12rem]">{{ $authUser->name }}</span>
                <span class="shrink-0 text-foreground-muted" aria-hidden="true">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">
                {{ __('Profile') }}
            </x-dropdown-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Log Out') }}
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</div>
