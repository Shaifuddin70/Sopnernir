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
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen ui-page-bg">
        @include('layouts.navigation')

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-64">
            <header class="ui-main-header sticky top-0 z-30 shrink-0">
                <div class="ui-page-container flex min-h-14 items-center justify-between gap-3">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-foreground-muted hover:bg-surface-variant hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary lg:hidden"
                            aria-expanded="false"
                            x-bind:aria-expanded="sidebarOpen"
                        >
                            <span class="sr-only">{{ __('Open navigation') }}</span>
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        @isset($header)
                            <div class="min-w-0 flex-1 [&_h2]:ui-page-header-title">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>

                    @include('layouts.auth-toolbar')
                </div>
            </header>

            <main class="ui-page-container flex-1 py-4 sm:py-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
