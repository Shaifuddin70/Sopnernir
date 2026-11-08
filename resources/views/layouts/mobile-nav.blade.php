@php
    $isAdmin = Auth::user()->isAdmin();
    $moreActive = $isAdmin && (
        request()->routeIs('payments.*')
        || request()->routeIs('admin.users.*')
        || request()->routeIs('admin.investments.*')
        || request()->routeIs('admin.profit-withdrawals.*')
        || request()->routeIs('admin.monthly-payments.*')
        || request()->routeIs('admin.special-installments.*')
        || request()->routeIs('admin.notices.*')
    );
@endphp

<div
    x-data="{ moreOpen: false }"
    class="ui-mobile-tabbar lg:hidden"
    @keydown.escape.window="moreOpen = false"
>
    @if ($isAdmin)
    <div
        x-show="moreOpen"
        x-cloak
        x-transition.opacity
        class="ui-mobile-more-backdrop"
        @click="moreOpen = false"
    ></div>

    <div
        x-show="moreOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-3 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-3 opacity-0"
        class="ui-mobile-more"
    >
        <p class="ui-mobile-more__label">{{ __('More') }}</p>
        <nav class="ui-mobile-more__list" aria-label="{{ __('More') }}">
            <a href="{{ route('payments.index') }}" class="ui-mobile-more__link {{ request()->routeIs('payments.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="6.25" width="18" height="11.5" rx="2" stroke-width="1.8" />
                    <circle cx="12" cy="12" r="2.15" stroke-width="1.8" />
                    <path stroke-linecap="round" stroke-width="1.8" d="M6.25 9.25h.01M17.75 14.75h.01" />
                </svg>
                <span>{{ __('My Installments') }}</span>
            </a>
                <a href="{{ route('admin.users.index') }}" class="ui-mobile-more__link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.25v-.6A3.4 3.4 0 0 0 11.6 15.25H7.4A3.4 3.4 0 0 0 4 18.65v.6" />
                        <circle cx="9.5" cy="8.25" r="2.6" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 19.25v-.55a3 3 0 0 0-2.25-2.9" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.1 5.7a2.35 2.35 0 0 1 0 4.55" />
                    </svg>
                    <span>{{ __('Users') }}</span>
                </a>
                <a href="{{ route('admin.investments.index') }}" class="ui-mobile-more__link {{ request()->routeIs('admin.investments.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.25h16" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.25 19.25V11.5h3.5v7.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.25 19.25V7.25h3.5v12" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 19.25V4.75h3.5v14.5" />
                    </svg>
                    <span>{{ __('Investments') }}</span>
                </a>
                <a href="{{ route('admin.profit-withdrawals.index') }}" class="ui-mobile-more__link {{ request()->routeIs('admin.profit-withdrawals.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v9.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m7.75 10.25 4.25 4.25 4.25-4.25" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 15.25v3.25A1.75 1.75 0 0 0 6.5 20.25h11a1.75 1.75 0 0 0 1.75-1.75v-3.25" />
                    </svg>
                    <span>{{ __('Withdrawals') }}</span>
                </a>
                <a href="{{ route('admin.monthly-payments.index') }}" class="ui-mobile-more__link {{ request()->routeIs('admin.monthly-payments.*') || request()->routeIs('admin.special-installments.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 3.75v3M16 3.75v3" />
                        <rect x="4.25" y="5.75" width="15.5" height="14.5" rx="2" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-width="1.8" d="M4.25 10h15.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 14.6 1.7 1.7 3.6-3.8" />
                    </svg>
                    <span>{{ __('Monthly Installment') }}</span>
                </a>
                <a href="{{ route('admin.notices.index') }}" class="ui-mobile-more__link {{ request()->routeIs('admin.notices.*') ? 'is-active' : '' }}" @click="moreOpen = false">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75h9A1.75 1.75 0 0 1 18.25 6.5v12.1l-3.1-1.85-3.15 1.85-3.15-1.85-3.1 1.85V6.5A1.75 1.75 0 0 1 7.5 4.75Z" />
                        <path stroke-linecap="round" stroke-width="1.8" d="M9.25 9h5.5M9.25 12h5.5" />
                    </svg>
                    <span>{{ __('Manage notices') }}</span>
                </a>
        </nav>
        <div class="ui-mobile-more__locale">
            <p class="ui-mobile-more__label">{{ __('Language') }}</p>
            <x-language-switcher />
        </div>
    </div>
    @endif

    <nav class="ui-mobile-tabbar__bar" aria-label="{{ __('Main') }}">
        <a href="{{ route('dashboard') }}" class="ui-mobile-tab ui-mobile-tab--home {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 3l9 7.5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 9.75V20.25h13.5V9.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 20.25v-6h4.5v6" />
            </svg>
            <span>{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('investments.index') }}" class="ui-mobile-tab ui-mobile-tab--portfolio {{ request()->routeIs('investments.*') ? 'is-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25H12V3.75Z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 3.9a8.25 8.25 0 0 1 5.85 5.85h-5.85V3.9Z" />
            </svg>
            <span>{{ __('Portfolio') }}</span>
        </a>
        <a href="{{ route('notices.index') }}" class="ui-mobile-tab ui-mobile-tab--notices {{ request()->routeIs('notices.*') ? 'is-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75h9A1.75 1.75 0 0 1 18.25 6.5v12.1l-3.1-1.85-3.15 1.85-3.15-1.85-3.1 1.85V6.5A1.75 1.75 0 0 1 7.5 4.75Z" />
                <path stroke-linecap="round" stroke-width="1.8" d="M9.25 9h5.5M9.25 12h5.5" />
            </svg>
            <span class="relative">
                {{ __('Notices') }}
                @if (($noticeUnreadCount ?? 0) > 0)
                    <span class="absolute -end-2 -top-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[9px] font-bold text-on-primary">{{ $noticeUnreadCount > 9 ? '9+' : $noticeUnreadCount }}</span>
                @endif
            </span>
        </a>
        @unless ($isAdmin)
            <a href="{{ route('payments.index') }}" class="ui-mobile-tab ui-mobile-tab--payments {{ request()->routeIs('payments.*') ? 'is-active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="6.25" width="18" height="11.5" rx="2" stroke-width="1.8" />
                    <circle cx="12" cy="12" r="2.15" stroke-width="1.8" />
                    <path stroke-linecap="round" stroke-width="1.8" d="M6.25 9.25h.01M17.75 14.75h.01" />
                </svg>
                <span>{{ __('My Installments') }}</span>
            </a>
        @endunless
        <a href="/profile" class="ui-mobile-tab ui-mobile-tab--profile {{ request()->is('profile', 'profile/*') ? 'is-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8.25" r="3.1" stroke-width="1.8" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 19.25a6.75 6.75 0 0 1 13.5 0" />
            </svg>
            <span>{{ __('Profile') }}</span>
        </a>
        @if ($isAdmin)
        <button
            type="button"
            class="ui-mobile-tab ui-mobile-tab--more {{ $moreActive ? 'is-active' : '' }}"
            @click="moreOpen = !moreOpen"
            :aria-expanded="moreOpen"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="4" y="4" width="6.5" height="6.5" rx="1.4" stroke-width="1.8" />
                <rect x="13.5" y="4" width="6.5" height="6.5" rx="1.4" stroke-width="1.8" />
                <rect x="4" y="13.5" width="6.5" height="6.5" rx="1.4" stroke-width="1.8" />
                <rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.4" stroke-width="1.8" />
            </svg>
            <span>{{ __('More') }}</span>
        </button>
        @endif
    </nav>
</div>
