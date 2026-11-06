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
                    class="rounded-full p-2 text-foreground-muted hover:bg-surface-card hover:text-foreground lg:hidden">
                    <span class="sr-only">{{ __('Close navigation') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="ui-sidebar-nav" aria-label="{{ __('Main') }}">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 3l9 7.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 9.75V20.25h13.5V9.75" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 20.25v-6h4.5v6" />
                        </svg>
                        <span>{{ __('Dashboard') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('investments.index')" :active="request()->routeIs('investments.*')" @click="sidebarOpen = false">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25H12V3.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 3.9a8.25 8.25 0 0 1 5.85 5.85h-5.85V3.9Z" />
                        </svg>
                        <span>{{ __('Portfolio') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" @click="sidebarOpen = false">
                    <span class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="6.25" width="18" height="11.5" rx="2" stroke-width="1.8" />
                            <circle cx="12" cy="12" r="2.15" stroke-width="1.8" />
                            <path stroke-linecap="round" stroke-width="1.8" d="M6.25 9.25h.01M17.75 14.75h.01" />
                        </svg>
                        <span>{{ __('Payments') }}</span>
                    </span>
                </x-responsive-nav-link>
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.25v-.6A3.4 3.4 0 0 0 11.6 15.25H7.4A3.4 3.4 0 0 0 4 18.65v.6" />
                                <circle cx="9.5" cy="8.25" r="2.6" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 19.25v-.55a3 3 0 0 0-2.25-2.9" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.1 5.7a2.35 2.35 0 0 1 0 4.55" />
                            </svg>
                            <span>{{ __('Users') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.investments.index')" :active="request()->routeIs('admin.investments.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.25h16" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.25 19.25V11.5h3.5v7.75" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.25 19.25V7.25h3.5v12" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 19.25V4.75h3.5v14.5" />
                            </svg>
                            <span>{{ __('Investments') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.profit-withdrawals.index')" :active="request()->routeIs('admin.profit-withdrawals.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v9.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m7.75 10.25 4.25 4.25 4.25-4.25" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 15.25v3.25A1.75 1.75 0 0 0 6.5 20.25h11a1.75 1.75 0 0 0 1.75-1.75v-3.25" />
                            </svg>
                            <span>{{ __('Withdrawals') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.monthly-payments.index')" :active="request()->routeIs('admin.monthly-payments.*') || request()->routeIs('admin.special-installments.*')" @click="sidebarOpen = false">
                        <span class="flex items-center gap-2.5">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3.75v3M16 3.75v3" />
                                <rect x="4.25" y="5.75" width="15.5" height="14.5" rx="2" stroke-width="1.8" />
                                <path stroke-linecap="round" stroke-width="1.8" d="M4.25 10h15.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 14.6 1.7 1.7 3.6-3.8" />
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
