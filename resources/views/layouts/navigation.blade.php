<div class="relative shrink-0 lg:w-0 lg:flex-shrink-0 lg:overflow-visible">
    {{-- Mobile top bar --}}
    <header
        class="fixed inset-x-0 top-0 z-40 flex h-14 shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 lg:hidden">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <button type="button" @click="sidebarOpen = true"
                class="inline-flex shrink-0 items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                aria-expanded="false" x-bind:aria-expanded="sidebarOpen">
                <span class="sr-only">{{ __('Open navigation') }}</span>
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2 text-gray-900">
                <x-application-logo class="block h-8 w-auto shrink-0 fill-current" />
            </a>
        </div>
        @include('layouts.auth-toolbar')
    </header>

    {{-- Overlay when mobile menu open --}}
    <div x-show="sidebarOpen" x-transition.opacity x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-900/40 lg:hidden" aria-hidden="true"></div>

    {{-- Sidebar: mobile slide-over; lg fixed to viewport (main scrolls under pl-64) --}}
    <aside class="flex min-h-0 flex-1 flex-col lg:flex-none">
        <div @keydown.escape.window="sidebarOpen = false" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-64 max-w-[85vw] flex-col border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-in-out lg:inset-y-0 lg:left-0 lg:z-40 lg:max-w-none lg:w-64 lg:!translate-x-0 lg:border-r lg:border-gray-200 lg:bg-white lg:shadow-none">
            <div
                class="flex h-14 shrink-0 items-center justify-between border-b border-gray-100 px-4 lg:h-auto lg:py-4">
                <a href="{{ route('dashboard') }}" class="hidden items-center lg:flex" @click="sidebarOpen = false">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
                <span class="text-sm font-semibold text-gray-900 lg:hidden">{{ config('app.name') }}</span>
                <button type="button" @click="sidebarOpen = false"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden">
                    <span class="sr-only">{{ __('Close navigation') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="{{ __('Main') }}">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('investments.index')" :active="request()->routeIs('investments.*')" @click="sidebarOpen = false">
                    {{ __('Portfolio') }}
                </x-responsive-nav-link>
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" @click="sidebarOpen = false">
                        {{ __('Users') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.investments.index')" :active="request()->routeIs('admin.investments.*')" @click="sidebarOpen = false">
                        {{ __('Investments') }}
                    </x-responsive-nav-link>
                @endif
            </nav>

            <div class="shrink-0 border-t border-gray-100 px-3 py-4 lg:hidden">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Language') }}</p>
                <x-language-switcher />
            </div>

        </div>
    </aside>
</div>
