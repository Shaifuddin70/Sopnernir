<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="ui-page-header-title">{{ __('Notice board') }}</h2>
                @if ($unreadCount > 0)
                    <p class="mt-1 text-sm text-foreground-muted">
                        {{ trans_choice(':count unread notice|:count unread notices', $unreadCount, ['count' => $unreadCount]) }}
                    </p>
                @endif
            </div>
            <div class="header-action-scroll">
                @if ($canManage)
                    <x-action-button :href="route('admin.notices.index')" variant="secondary">{{ __('Manage') }}</x-action-button>
                    <x-action-button :href="route('admin.notices.create')">{{ __('New notice') }}</x-action-button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="notices-page space-y-4 pb-2">
        <section class="dashboard-stat-panel">
            <form method="get" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:p-5">
                <div class="min-w-0 flex-1">
                    <x-input-label for="q" :value="__('Search')" />
                    <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$search" placeholder="{{ __('Search notices…') }}" />
                </div>
                <div class="sm:w-44">
                    <x-input-label for="filter" :value="__('Filter')" />
                    <select id="filter" name="filter" class="ui-select mt-1 block w-full">
                        <option value="">{{ __('All') }}</option>
                        <option value="unread" @selected($filter === 'unread')>{{ __('Unread') }}</option>
                        <option value="pinned" @selected($filter === 'pinned')>{{ __('Pinned') }}</option>
                    </select>
                </div>
                <x-action-button type="submit" variant="secondary">{{ __('Filter') }}</x-action-button>
            </form>
        </section>

        @if ($notices->isEmpty())
            <div class="notice-empty">
                <div class="notice-empty__icon" aria-hidden="true">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75h9A1.75 1.75 0 0 1 18.25 6.5v12.1l-3.1-1.85-3.15 1.85-3.15-1.85-3.1 1.85V6.5A1.75 1.75 0 0 1 7.5 4.75Z" />
                        <path stroke-linecap="round" stroke-width="1.8" d="M9.25 9h5.5M9.25 12h5.5" />
                    </svg>
                </div>
                <p class="notice-empty__title">{{ __('No notices to show.') }}</p>
                <p class="notice-empty__text">{{ __('New notices from the admin will appear here.') }}</p>
            </div>
        @else
            <section class="notice-card-grid" aria-label="{{ __('Notice board') }}">
                @foreach ($notices as $notice)
                    <article class="notice-card {{ $notice->is_read ? 'is-read' : 'is-unread' }} {{ $notice->is_pinned ? 'is-pinned' : '' }}">
                        <a href="{{ route('notices.show', $notice) }}" class="notice-card__link">
                            <div class="notice-card__top">
                                <span class="notice-card__icon" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75h9A1.75 1.75 0 0 1 18.25 6.5v12.1l-3.1-1.85-3.15 1.85-3.15-1.85-3.1 1.85V6.5A1.75 1.75 0 0 1 7.5 4.75Z" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M9.25 9h5.5M9.25 12h5.5" />
                                    </svg>
                                </span>
                                <div class="notice-card__badges">
                                    @unless ($notice->is_read)
                                        <span class="notice-card__badge notice-card__badge--new">{{ __('New') }}</span>
                                    @endunless
                                    @if ($notice->is_pinned)
                                        <span class="notice-card__badge notice-card__badge--pin">{{ __('Pinned') }}</span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="notice-card__title">{{ $notice->title }}</h3>

                            @if ($notice->excerpt(140) !== '')
                                <p class="notice-card__excerpt">{{ $notice->excerpt(140) }}</p>
                            @endif

                            <div class="notice-card__meta">
                                <span class="notice-card__meta-item">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16" />
                                    </svg>
                                    {{ $notice->published_at?->translatedFormat('j M Y') ?? '—' }}
                                </span>
                                @if ($notice->attachments_count)
                                    <span class="notice-card__meta-item">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15.2 7.4-6.4 6.4a2.3 2.3 0 0 0 3.25 3.25l7.05-7.05a3.7 3.7 0 0 0-5.23-5.23L6.5 12.15" />
                                        </svg>
                                        {{ trans_choice(':count attachment|:count attachments', $notice->attachments_count, ['count' => $notice->attachments_count]) }}
                                    </span>
                                @endif
                            </div>

                            <div class="notice-card__footer">
                                <span class="notice-card__author">{{ $notice->author?->name ?? __('Admin') }}</span>
                                <span class="notice-card__cta">{{ __('Open') }} →</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </section>

            <div class="pt-2">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
