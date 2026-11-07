@php($nominee = $user->nominee)

<x-app-layout>
    <x-slot name="header">
        <h2 class="ui-page-header-title">{{ __('Profile') }}</h2>
    </x-slot>

    <div class="profile-page space-y-3 pb-2 sm:space-y-4">
        @if (session('status') === 'profile-updated')
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2500)"
                class="rounded-2xl border border-success/30 bg-success-muted px-4 py-3 text-sm text-foreground"
            >
                {{ __('Saved.') }}
            </div>
        @endif

        <section class="dashboard-stat-panel profile-hero" aria-label="{{ __('Profile') }}">
            <div class="profile-hero__inner">
                <div class="profile-hero__avatar">
                    <x-user-avatar :user="$user" size="2xl" class="profile-hero__photo ring-2 ring-white/70 dark:ring-white/15" />
                </div>
                <div class="profile-hero__copy min-w-0">
                    <h3 class="profile-hero__name">{{ $user->name }}</h3>
                    @if ($user->email)
                        <p class="profile-hero__meta truncate">{{ $user->email }}</p>
                    @endif
                    @if ($user->phone)
                        <p class="profile-hero__meta tabular-nums">{{ $user->phone }}</p>
                    @endif
                    <div class="profile-hero__actions">
                        <x-action-button
                            href="/profile/edit"
                            class="profile-edit-btn w-full sm:w-auto"
                        >
                            {{ __('Edit profile') }}
                        </x-action-button>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-stat-panel profile-section" aria-labelledby="profile-account-heading">
            <div class="dashboard-stat-panel__head">
                <div class="min-w-0">
                    <h3 id="profile-account-heading" class="dashboard-stat-panel__title">{{ __('Account holder') }}</h3>
                    <p class="dashboard-stat-panel__meta">{{ __('Your personal details and photo') }}</p>
                </div>
            </div>
            <dl class="profile-dl">
                <div class="profile-dl__row">
                    <dt class="profile-dl__label">{{ __('Name') }}</dt>
                    <dd class="profile-dl__value">{{ $user->name ?: '—' }}</dd>
                </div>
                <div class="profile-dl__row">
                    <dt class="profile-dl__label">{{ __('Phone') }}</dt>
                    <dd class="profile-dl__value tabular-nums">{{ $user->phone ?: '—' }}</dd>
                </div>
                <div class="profile-dl__row">
                    <dt class="profile-dl__label">{{ __('Email') }}</dt>
                    <dd class="profile-dl__value break-all">{{ $user->email ?: '—' }}</dd>
                </div>
                <div class="profile-dl__row">
                    <dt class="profile-dl__label">{{ __('NID number') }}</dt>
                    <dd class="profile-dl__value tabular-nums">{{ $user->nid_number ?: '—' }}</dd>
                </div>
                <div class="profile-dl__row profile-dl__row--block">
                    <dt class="profile-dl__label">{{ __('Address') }}</dt>
                    <dd class="profile-dl__value">{{ $user->address ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="dashboard-stat-panel profile-section" aria-labelledby="profile-nominee-heading">
            <div class="dashboard-stat-panel__head">
                <div class="profile-section-heading min-w-0">
                    @if ($nominee?->profileImageUrl() || $nominee?->name)
                        <x-user-avatar
                            :name="$nominee?->name"
                            :image-url="$nominee?->profileImageUrl()"
                            size="lg"
                            class="profile-section-heading__avatar"
                        />
                    @endif
                    <div class="min-w-0">
                        <h3 id="profile-nominee-heading" class="dashboard-stat-panel__title">{{ __('Nominee') }}</h3>
                        <p class="dashboard-stat-panel__meta">{{ __('Emergency contact for this account') }}</p>
                    </div>
                </div>
            </div>
            @if ($nominee)
                <dl class="profile-dl">
                    <div class="profile-dl__row">
                        <dt class="profile-dl__label">{{ __('Nominee name') }}</dt>
                        <dd class="profile-dl__value">{{ $nominee->name ?: '—' }}</dd>
                    </div>
                    <div class="profile-dl__row">
                        <dt class="profile-dl__label">{{ __('Nominee phone') }}</dt>
                        <dd class="profile-dl__value tabular-nums">{{ $nominee->phone ?: '—' }}</dd>
                    </div>
                    <div class="profile-dl__row">
                        <dt class="profile-dl__label">{{ __('Nominee email') }}</dt>
                        <dd class="profile-dl__value break-all">{{ $nominee->email ?: '—' }}</dd>
                    </div>
                    <div class="profile-dl__row">
                        <dt class="profile-dl__label">{{ __('Nominee NID number') }}</dt>
                        <dd class="profile-dl__value tabular-nums">{{ $nominee->nid_number ?: '—' }}</dd>
                    </div>
                    <div class="profile-dl__row profile-dl__row--block">
                        <dt class="profile-dl__label">{{ __('Nominee address') }}</dt>
                        <dd class="profile-dl__value">{{ $nominee->address ?: '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="profile-empty">{{ __('No nominee on file.') }}</p>
            @endif
        </section>

        <section class="dashboard-stat-panel profile-section" aria-labelledby="profile-security-heading">
            <div class="dashboard-stat-panel__head">
                <div class="min-w-0">
                    <h3 id="profile-security-heading" class="dashboard-stat-panel__title">{{ __('Password') }}</h3>
                    <p class="dashboard-stat-panel__meta">{{ __('Update your account password') }}</p>
                </div>
            </div>
            <div class="profile-section__body">
                <x-action-button
                    href="/profile/edit#password"
                    variant="secondary"
                    class="w-full sm:w-auto"
                >
                    {{ __('Change password') }}
                </x-action-button>
            </div>
        </section>
    </div>
</x-app-layout>
