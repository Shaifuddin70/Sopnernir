<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <div class="flex min-w-0 flex-1 flex-col pt-14 lg:pt-0 lg:pl-64">
            <div
                class="hidden border-b border-gray-200 bg-white lg:fixed lg:left-64 lg:right-0 lg:top-0 lg:z-30 lg:block">
                <div class="mx-auto flex max-w-7xl items-center justify-end px-4 py-2 sm:px-6 lg:px-8">
                    @include('layouts.auth-toolbar')
                </div>
            </div>
            <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:pt-14">
                @isset($header)
                    <header class="border-b border-gray-200 bg-white shadow-sm">
                        <div class="mx-auto space-y-6 p-1 sm:p-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</body>

</html>
