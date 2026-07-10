<div class="relative shrink-0 lg:w-0 lg:flex-shrink-0 lg:overflow-visible">
    {{-- Overlay when mobile menu open --}}
    <div x-show="sidebarOpen" x-transition.opacity x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/40 lg:hidden" aria-hidden="true"></div>

    {{-- Sidebar --}}
    <aside class="flex min-h-0 flex-1 flex-col lg:flex-none">
        <div @keydown.escape.window="sidebarOpen = false" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="ui-sidebar fixed inset-y-0 left-0 z-50 flex w-64 max-w-[85vw] flex-col transition-transform duration-200 ease-in-out lg:inset-y-0 lg:left-0 lg:z-40 lg:max-w-none lg:w-64 lg:!translate-x-0">
            <div class="ui-sidebar-brand">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center" @click="sidebarOpen = false">
                    <x-application-logo class="min-w-0" />
                </a>
                <button type="button" @click="sidebarOpen = false"
                    class="rounded-lg p-2 text-foreground-muted hover:bg-surface-card hover:text-foreground lg:hidden">
                    <span class="sr-only">{{ __('Close navigation') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="ui-sidebar-nav" aria-label="{{ __('Main') }}">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 11.25L12 4l9 7.25M5.25 10.5V20h13.5v-9.5" />
                        </svg>
                        <span>{{ __('Dashboard') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('investments.index')" :active="request()->routeIs('investments.*')" @click="sidebarOpen = false">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 7.5A1.5 1.5 0 014.5 6h15A1.5 1.5 0 0121 7.5v9A1.5 1.5 0 0119.5 18h-15A1.5 1.5 0 013 16.5v-9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5h18" />
                        </svg>
                        <span>{{ __('Portfolio') }}</span>
                    </span>
                </x-responsive-nav-link>
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15 19.5a6.75 6.75 0 00-12 0M9 12a3.75 3.75 0 100-7.5A3.75 3.75 0 009 12zm10.5 7.5v-1.5a4.5 4.5 0 00-4.5-4.5h-1.125" />
                            </svg>
                            <span>{{ __('Users') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.investments.index')" :active="request()->routeIs('admin.investments.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4.5 6.75h15m-15 5.25h15m-15 5.25h9.75M4.5 4.5h15A1.5 1.5 0 0121 6v12a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 18V6a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                            <span>{{ __('Investments') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.monthly-payments.index')" :active="request()->routeIs('admin.monthly-payments.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 7V3m8 4V3M4.5 9.75h15M6 5.25h12A1.5 1.5 0 0119.5 6.75v12A1.5 1.5 0 0118 21H6A1.5 1.5 0 014.5 19.5v-12A1.5 1.5 0 016 5.25z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14.25l2 2 4-4" />
                            </svg>
                            <span>{{ __('Monthly payments') }}</span>
                        </span>
                    </x-responsive-nav-link>
                @endif
            </nav>

            <div class="shrink-0 border-t border-line py-4 ui-shell-padding lg:hidden">
                <p class="mb-2 text-sm font-medium uppercase tracking-wide text-foreground-muted">{{ __('Language') }}</p>
                <x-language-switcher />
            </div>

        </div>
    </aside>
</div>
