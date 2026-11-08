<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-3">
            <h2 class="ui-page-header-title">{{ __('Notice') }}</h2>
            <div class="header-action-scroll flex flex-wrap items-center gap-2">
                <x-action-button :href="route('notices.index')" variant="secondary" class="text-sm">{{ __('Back to board') }}</x-action-button>
                @if ($canManage)
                    <x-action-button :href="route('admin.notices.edit', $notice)" variant="secondary" class="text-sm">{{ __('Edit') }}</x-action-button>
                @endif
            </div>
        </div>
    </x-slot>

    <article class="ui-card overflow-hidden">
        <header class="border-b border-line px-4 py-5 sm:px-8">
            <div class="flex flex-wrap items-center gap-2">
                @if ($notice->is_pinned)
                    <span class="rounded-full bg-primary-muted px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-primary">{{ __('Pinned') }}</span>
                @endif
                @unless ($notice->isPublished())
                    <span class="rounded-full bg-warning-muted px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-warning">{{ __('Draft') }}</span>
                @endunless
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ $notice->title }}</h1>
            <p class="mt-2 text-sm text-foreground-muted">
                {{ $notice->author?->name ?? __('Admin') }}
                @if ($notice->published_at)
                    · {{ $notice->published_at->translatedFormat('j M Y, g:i A') }}
                @endif
            </p>
        </header>

        <div class="notice-content px-4 py-6 sm:px-8">
            {!! $notice->body !!}
        </div>

        @if ($notice->attachments->isNotEmpty())
            <section class="border-t border-line px-4 py-5 sm:px-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-foreground-muted">{{ __('Attachments') }}</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($notice->attachments as $attachment)
                        <li>
                            <a
                                href="{{ route('notices.attachments.download', [$notice, $attachment]) }}"
                                class="flex items-center justify-between gap-3 rounded-xl border border-line bg-surface-variant/50 px-4 py-3 transition hover:border-primary/40"
                            >
                                <span class="min-w-0 truncate font-medium text-foreground">{{ $attachment->original_name }}</span>
                                <span class="shrink-0 text-xs text-foreground-muted">{{ $attachment->humanSize() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
</x-app-layout>
