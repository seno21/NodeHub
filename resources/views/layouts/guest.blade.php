<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Handons Portal') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body
    class="h-full font-sans text-slate-100 antialiased selection:bg-[#00828c] selection:text-white relative overflow-x-hidden bg-[#090d14]">
    {{-- Ambient background glows with theme color #00828c --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-[#00828c]/20 rounded-full blur-[140px]"></div>
        <div class="absolute top-1/2 -right-40 w-[550px] h-[550px] bg-[#006e76]/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 left-1/3 w-[500px] h-[500px] bg-[#004e54]/15 rounded-full blur-[140px]"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-20">
        </div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col justify-between items-center p-4 sm:p-6 lg:p-8">
        {{-- Main Content Card --}}
        <div class="w-full max-w-md my-auto">
            <div
                class="bg-slate-900/80 backdrop-blur-2xl border border-slate-800/80 shadow-2xl shadow-black/80 rounded-3xl p-6 sm:p-8 relative overflow-hidden">
                {{-- Decorative top border highlight --}}
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#00828c] via-[#00a3b0] to-[#00585f]">
                </div>

                {{ $slot }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="py-6 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} Handons Remote Access Management System. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
