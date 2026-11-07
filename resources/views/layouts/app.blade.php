<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shopnernir') }}</title>

    <x-favicon />
    <x-theme-init />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <x-status-banner />
    <div class="flex min-h-screen ui-page-bg">
        @include('layouts.navigation')

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-64">
            <header class="ui-main-header sticky top-0 z-30 shrink-0">
                <div class="ui-page-container ui-header-bar">
                    <div class="ui-header-main flex min-w-0 flex-1 items-center gap-3">
                        @isset($header)
                            <div class="ui-header-slot min-w-0 flex-1 [&_h2]:ui-page-header-title">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>

                    <div class="ui-header-profile">
                        @include('layouts.auth-toolbar')
                    </div>
                </div>
            </header>

            <main class="ui-page-container ui-app-main flex-1 py-4 sm:py-6">
                {{ $slot }}
            </main>
        </div>

        @include('layouts.mobile-nav')
    </div>
</body>

</html>
