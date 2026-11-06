<x-mail::layout>
{{-- Header --}}
<x-slot:header>
@php
    $logoPath = public_path('images/logo.jpg');
    $logoUrl = asset('images/logo.jpg');

    if (isset($message) && is_object($message) && method_exists($message, 'embed') && is_file($logoPath)) {
        try {
            $logoUrl = $message->embed($logoPath);
        } catch (Throwable) {
            $logoUrl = asset('images/logo.jpg');
        }
    }
@endphp
<x-mail::header :url="config('app.url')" :logo="$logoUrl">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} [{{ config('app.name') }}]({{ config('app.url') }}). {{ __('All rights reserved.') }}

{{ __('This is an automated message from :app.', ['app' => config('app.name')]) }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
