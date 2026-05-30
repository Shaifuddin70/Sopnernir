@php
    $locale = app()->getLocale();
@endphp

<div {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-lg border border-line bg-surface-secondary p-0.5 text-sm shadow-sm',
]) }}>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="en">
        <button
            type="submit"
            class="rounded-md px-2.5 py-1 transition {{ $locale === 'en' ? 'bg-primary font-medium text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-variant hover:text-foreground' }}"
        >
            English
        </button>
    </form>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="bn">
        <button
            type="submit"
            class="rounded-md px-2.5 py-1 transition {{ $locale === 'bn' ? 'bg-primary font-medium text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-variant hover:text-foreground' }}"
        >
            বাংলা
        </button>
    </form>
</div>
