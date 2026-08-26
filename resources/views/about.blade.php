<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="p-2 rounded-xl bg-blue-600/10 text-blue-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                </svg>
            </span>
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    {{ __('Tentang NodeHub (Centralized Infrastructure Control)') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('Sistem Manajemen Remote Desktop Portal') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- App Banner Card --}}
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider mb-3">
                        NodeHub Portal v1.2.0
                    </span>
                    <h3 class="text-2xl font-extrabold tracking-tight">NodeHub Remote Desktop Management Portal</h3>
                    <p class="mt-2 text-sm text-slate-300 leading-relaxed">
                        Aplikasi manajemen terpusat untuk mengendalikan perangkat display VNC secara langsung melalui browser HTML5 tanpa instalasi software tambahan.
                    </p>
                </div>
            </div>

            {{-- Technology Stack Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm mb-3">
                        PHP
                    </div>
                    <h4 class="font-bold text-sm text-gray-900">Laravel 11</h4>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">High performance PHP Web Framework dengan Blade views, Eloquent ORM, dan Artisan CLI.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm mb-3">
                        VNC
                    </div>
                    <h4 class="font-bold text-sm text-gray-900">noVNC & Websockify</h4>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Client VNC HTML5 murni yang terhubung via WebSocket proxy tunnel berkecepatan tinggi.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-sm mb-3">
                        🐳
                    </div>
                    <h4 class="font-bold text-sm text-gray-900">Docker Compose</h4>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Arsitektur multi-container terisolasi (`webvnc-app`, `webvnc-db`, `webvnc-bridge`).</p>
                </div>
            </div>

            {{-- Developer Credit Card --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-bold text-lg">
                        ❤️
                    </span>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">Made With Love</h4>
                        <p class="text-xs text-gray-500">Created by <strong class="text-gray-800">nzucode</strong></p>
                    </div>
                </div>

                <a href="https://seno21.github.io/" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition shadow-sm">
                    <span>seno21.github.io</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
