<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shopnonir') }}</title>

    <x-favicon />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-status-banner />
    <div class="ui-auth-shell flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
        <div class="mb-6 flex w-full max-w-md justify-end">
            <x-language-switcher />
        </div>
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
