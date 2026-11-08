<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Notice board') }}</h2>
            <div class="header-action-scroll">
                <x-action-button :href="route('notices.index')" variant="secondary">{{ __('View board') }}</x-action-button>
                <x-action-button :href="route('admin.notices.create')">{{ __('New notice') }}</x-action-button>
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
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="ui-select mt-1 block w-full">
                        <option value="">{{ __('All') }}</option>
                        <option value="published" @selected($status === 'published')>{{ __('Published') }}</option>
                        <option value="draft" @selected($status === 'draft')>{{ __('Draft') }}</option>
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
                <p class="notice-empty__title">{{ __('No notices yet.') }}</p>
                <p class="notice-empty__text">{{ __('Create a notice to share updates with every active user.') }}</p>
                <div class="mt-3">
                    <x-action-button :href="route('admin.notices.create')">{{ __('Create the first notice') }}</x-action-button>
                </div>
            </div>
        @else
            <section class="notice-card-grid" aria-label="{{ __('All notices') }}">
                @foreach ($notices as $notice)
                    <article class="notice-card notice-card--admin {{ $notice->isPublished() ? 'is-published' : 'is-draft' }} {{ $notice->is_pinned ? 'is-pinned' : '' }}">
                        <div class="notice-card__body">
                            <div class="notice-card__top">
                                <span class="notice-card__icon" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75h9A1.75 1.75 0 0 1 18.25 6.5v12.1l-3.1-1.85-3.15 1.85-3.15-1.85-3.1 1.85V6.5A1.75 1.75 0 0 1 7.5 4.75Z" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M9.25 9h5.5M9.25 12h5.5" />
                                    </svg>
                                </span>
                                <div class="notice-card__badges">
                                    <span class="notice-card__badge {{ $notice->isPublished() ? 'notice-card__badge--published' : 'notice-card__badge--draft' }}">
                                        {{ $notice->isPublished() ? __('Published') : __('Draft') }}
                                    </span>
                                    @if ($notice->is_pinned)
                                        <span class="notice-card__badge notice-card__badge--pin">{{ __('Pinned') }}</span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="notice-card__title">
                                <a href="{{ route('admin.notices.edit', $notice) }}" class="notice-card__title-link">{{ $notice->title }}</a>
                            </h3>

                            @if ($notice->excerpt(140) !== '')
                                <p class="notice-card__excerpt">{{ $notice->excerpt(140) }}</p>
                            @endif

                            <div class="notice-card__meta">
                                <span class="notice-card__meta-item">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8" />
                                        <path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M4 10h16" />
                                    </svg>
                                    {{ $notice->updated_at?->translatedFormat('j M Y, g:i A') ?? '—' }}
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
                                <div class="notice-card__actions">
                                    @if ($notice->isPublished())
                                        <a href="{{ route('notices.show', $notice) }}" class="notice-card__action">{{ __('View') }}</a>
                                    @endif
                                    <a href="{{ route('admin.notices.edit', $notice) }}" class="notice-card__action notice-card__action--primary">{{ __('Edit') }}</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="pt-2">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
