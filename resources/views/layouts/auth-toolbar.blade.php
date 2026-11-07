@php
    $authUser = Auth::user();
@endphp

<div class="flex min-w-0 max-w-full items-center gap-2 sm:gap-3">
    <x-theme-toggle class="shrink-0" />
    <div @class(['shrink-0', 'hidden sm:block' => ! $authUser->isAdmin(), 'hidden lg:block' => $authUser->isAdmin()])>
        <x-language-switcher />
    </div>
    <x-dropdown align="right" width="full" contentClasses="overflow-hidden rounded-2xl border border-line bg-surface-card py-1.5 shadow-[0_12px_32px_rgb(15_23_42/0.12)] dark:shadow-[0_12px_32px_rgb(0_0_0/0.45)]">
        <x-slot name="trigger">
            <button
                type="button"
                class="inline-flex h-10 max-w-full min-w-0 items-center gap-2 rounded-full border border-white/70 bg-white/80 py-1 ps-1 pe-3 text-sm font-medium leading-4 text-foreground shadow-sm backdrop-blur-md transition duration-150 ease-in-out hover:bg-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15"
            >
                <span class="ui-user-identity__avatar ui-user-identity__avatar--compact">
                    <x-user-avatar :user="$authUser" />
                </span>
                <span class="max-w-[7.5rem] truncate sm:max-w-[9rem] lg:max-w-[12rem]">{{ $authUser->name }}</span>
                <span class="shrink-0 text-foreground-muted" aria-hidden="true">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>
        </x-slot>

        <x-slot name="content">
            @unless ($authUser->isAdmin())
                <div class="px-3 py-2 sm:hidden">
                    <x-language-switcher />
                </div>
            @endunless
            <x-dropdown-link href="/profile">
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
