<x-app-layout>
    <x-slot name="header">
        <div class="ui-header-heading flex min-w-0 flex-wrap items-center justify-between gap-2">
            <h2 class="ui-page-header-title">{{ __('Edit notice') }}</h2>
            <div class="header-action-scroll flex flex-wrap items-center gap-2">
                @if ($notice->isPublished())
                    <x-action-button :href="route('notices.show', $notice)" variant="secondary" class="text-sm">{{ __('View') }}</x-action-button>
                    <form method="post" action="{{ route('admin.notices.resend', $notice) }}" onsubmit="return confirm(@js(__('Resend this notice email to all users?')))">
                        @csrf
                        <x-action-button type="submit" variant="secondary" class="text-sm">{{ __('Resend email') }}</x-action-button>
                    </form>
                    <form method="post" action="{{ route('admin.notices.unpublish', $notice) }}">
                        @csrf
                        <x-action-button type="submit" variant="secondary" class="text-sm">{{ __('Unpublish') }}</x-action-button>
                    </form>
                @else
                    <form method="post" action="{{ route('admin.notices.publish', $notice) }}">
                        @csrf
                        <x-action-button type="submit" class="text-sm">{{ __('Publish & email') }}</x-action-button>
                    </form>
                @endif
                <form method="post" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm(@js(__('Delete this notice permanently?')))">
                    @csrf
                    @method('DELETE')
                    <x-action-button type="submit" variant="secondary" class="text-sm">{{ __('Delete') }}</x-action-button>
                </form>
                <x-action-button :href="route('admin.notices.index')" variant="secondary" class="text-sm">{{ __('Back to list') }}</x-action-button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="rounded-2xl border border-line bg-surface-variant/50 px-4 py-3 text-sm text-foreground-muted">
            @if ($notice->isPublished())
                {{ __('Published') }}: {{ $notice->published_at?->translatedFormat('j M Y, g:i A') ?? '—' }}
                @if ($notice->emailed_at)
                    · {{ __('Last emailed') }}: {{ $notice->emailed_at->translatedFormat('j M Y, g:i A') }}
                @endif
            @else
                {{ __('This notice is a draft. Publish it to show it on the board and email all users.') }}
            @endif
        </div>

        <div class="ui-card p-4 sm:p-8">
            @include('admin.notices.partials.form', ['notice' => $notice])
        </div>
    </div>
</x-app-layout>
