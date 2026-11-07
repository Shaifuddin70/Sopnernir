@php
    $locale = app()->getLocale();
@endphp

<div {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full border border-white/70 bg-white/70 p-0.5 text-sm shadow-sm backdrop-blur-md dark:border-white/10 dark:bg-white/10',
]) }}>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="en">
        <button
            type="submit"
            class="rounded-full px-3 py-1 transition {{ $locale === 'en' ? 'bg-primary font-medium text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-variant hover:text-foreground' }}"
        >
            English
        </button>
    </form>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="bn">
        <button
            type="submit"
            class="rounded-full px-3 py-1 transition {{ $locale === 'bn' ? 'bg-primary font-medium text-on-primary shadow-sm' : 'text-foreground-muted hover:bg-surface-variant hover:text-foreground' }}"
        >
            বাংলা
        </button>
    </form>
</div>
