<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shopnonir') }}</title>

    <x-favicon />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-status-banner />
    <div class="phone-access-page min-h-screen">
        <div class="phone-access-page__backdrop" aria-hidden="true"></div>

        <header class="phone-access-page__header">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <x-application-logo class="min-w-0" />
                <x-language-switcher />
            </div>
        </header>

        <main class="phone-access-page__main mx-auto w-full max-w-6xl px-4 py-4 sm:px-6 sm:py-6">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
