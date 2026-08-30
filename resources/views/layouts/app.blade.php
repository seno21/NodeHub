<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auto-lock-timeout" content="{{ Auth::user()?->auto_lock_timeout ?? 20 }}">
    <meta name="theme-color" content="#003e43">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
</head>

<body class="font-sans antialiased text-slate-900 bg-slate-50/50 selection:bg-[#00828c] selection:text-white">
    <div class="min-h-screen flex bg-gray-100/90" x-data="{ sidebarOpen: false }">
        @include('layouts.navigation')

        <!-- Content area -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen lg:pl-64">
            @include('layouts.topbar')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-gray-200/80 sticky top-16 z-20 shadow-2xs">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 pb-20 lg:pb-6">
                {{ $slot }}
            </main>

            @include('layouts.bottom-nav')
        </div>
    </div>
</body>

</html>
