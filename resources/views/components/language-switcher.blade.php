@php
    $locale = app()->getLocale();
@endphp

<div {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-md border border-gray-200 bg-white p-0.5 text-sm shadow-sm',
]) }}>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="en">
        <button
            type="submit"
            class="rounded px-2 py-1 transition {{ $locale === 'en' ? 'bg-indigo-100 font-medium text-indigo-900' : 'text-gray-600 hover:bg-gray-50' }}"
        >
            English
        </button>
    </form>
    <form method="POST" action="{{ route('locale.update') }}" class="inline">
        @csrf
        <input type="hidden" name="locale" value="bn">
        <button
            type="submit"
            class="rounded px-2 py-1 transition {{ $locale === 'bn' ? 'bg-indigo-100 font-medium text-indigo-900' : 'text-gray-600 hover:bg-gray-50' }}"
        >
            বাংলা
        </button>
    </form>
</div>
