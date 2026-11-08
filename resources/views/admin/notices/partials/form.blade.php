@php
    /** @var \App\Models\Notice $notice */
    $isEdit = $notice->exists;
@endphp

<form
    id="notice-editor-form"
    method="post"
    action="{{ $isEdit ? route('admin.notices.update', $notice) : route('admin.notices.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
    data-notice-form
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input
            id="title"
            name="title"
            type="text"
            class="mt-1 block w-full"
            :value="old('title', $notice->title)"
            required
            maxlength="180"
            placeholder="{{ __('Notice title') }}"
        />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="body" :value="__('Content')" />
        <textarea name="body" id="body" class="sr-only" aria-hidden="true">{{ old('body', $notice->body) }}</textarea>
        <div
            id="notice-editor"
            class="notice-editor mt-1"
            data-quill-editor
            data-input="#body"
            data-placeholder="{{ __('Write the notice…') }}"
        ></div>
        <p class="mt-2 text-xs text-foreground-muted">{{ __('Use the toolbar to format headings, lists, links, and emphasis.') }}</p>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select
                id="status"
                name="status"
                class="ui-select mt-1 block w-full"
            >
                <option value="draft" @selected(old('status', $notice->status ?: 'draft') === 'draft')>{{ __('Draft') }}</option>
                <option value="published" @selected(old('status', $notice->status) === 'published')>{{ __('Published') }}</option>
            </select>
            <p class="mt-2 text-xs text-foreground-muted">{{ __('Publishing emails every active user and shows the notice on the board.') }}</p>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-start gap-2 cursor-pointer rounded-xl border border-line bg-surface-variant/40 px-4 py-3">
                <input
                    type="checkbox"
                    name="is_pinned"
                    value="1"
                    class="mt-1 rounded border-line bg-surface-secondary text-primary shadow-sm focus:ring-primary"
                    @checked(old('is_pinned', $notice->is_pinned))
                />
                <span>
                    <span class="block text-sm font-semibold text-foreground">{{ __('Pin to top') }}</span>
                    <span class="mt-0.5 block text-xs text-foreground-muted">{{ __('Pinned notices stay at the top of the board.') }}</span>
                </span>
            </label>
        </div>
    </div>

    <div>
        <x-input-label for="attachments" :value="__('Attachments')" />
        <input
            id="attachments"
            name="attachments[]"
            type="file"
            multiple
            class="mt-1 block w-full text-sm text-foreground file:me-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-on-primary"
        />
        <p class="mt-2 text-xs text-foreground-muted">{{ __('PDF, Office docs, images, text, or ZIP. Max 12 MB each, up to 10 files.') }}</p>
        <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
        <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
    </div>

    @if ($isEdit && $notice->attachments->isNotEmpty())
        <div class="rounded-2xl border border-line bg-surface-variant/40 p-4">
            <h3 class="text-sm font-semibold text-foreground">{{ __('Current attachments') }}</h3>
            <ul class="mt-3 space-y-2">
                @foreach ($notice->attachments as $attachment)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line bg-surface-card px-3 py-2">
                        <div class="min-w-0">
                            <a
                                href="{{ route('admin.notices.attachments.download', [$notice, $attachment]) }}"
                                class="truncate font-medium text-primary hover:underline"
                            >{{ $attachment->original_name }}</a>
                            <p class="text-xs text-foreground-muted">{{ $attachment->humanSize() }}</p>
                        </div>
                        <x-action-button
                            type="submit"
                            form="delete-attachment-{{ $attachment->id }}"
                            variant="secondary"
                            class="text-sm"
                            onclick="return confirm(@js(__('Remove this attachment?')))"
                        >
                            {{ __('Remove') }}
                        </x-action-button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
        <x-action-button type="submit" form="notice-editor-form">
            {{ $isEdit ? __('Save notice') : __('Create notice') }}
        </x-action-button>
        <x-action-button :href="route('admin.notices.index')" variant="secondary">{{ __('Cancel') }}</x-action-button>
    </div>
</form>

@if ($isEdit && $notice->attachments->isNotEmpty())
    @foreach ($notice->attachments as $attachment)
        <form
            id="delete-attachment-{{ $attachment->id }}"
            method="post"
            action="{{ route('admin.notices.attachments.destroy', [$notice, $attachment]) }}"
            class="hidden"
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
