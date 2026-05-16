@php
    $authUser = Auth::user();
    $avatarInitial = strtoupper(\Illuminate\Support\Str::substr($authUser->name ?: $authUser->email ?: '?', 0, 1));
@endphp

<div class="flex min-w-0 max-w-full items-center gap-2 sm:gap-3">
    <div class="hidden shrink-0 lg:block">
        <x-language-switcher />
    </div>
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button
                type="button"
                class="inline-flex h-10 max-w-full min-w-0 items-center gap-2 rounded-md border border-transparent py-1 ps-1 pe-2 text-sm font-medium leading-4 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                @if ($authUser->profileImageUrl())
                    <img
                        src="{{ $authUser->profileImageUrl() }}"
                        alt=""
                        class="h-8 w-8 shrink-0 rounded-full object-cover ring-1 ring-gray-200"
                    />
                @else
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200"
                        aria-hidden="true"
                    >{{ $avatarInitial }}</span>
                @endif
                <span class="sr-only">{{ $authUser->name }}</span>
                <span class="hidden max-w-[9rem] truncate md:inline lg:max-w-[12rem]">{{ $authUser->name }}</span>
                <span class="shrink-0 text-gray-400" aria-hidden="true">
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
