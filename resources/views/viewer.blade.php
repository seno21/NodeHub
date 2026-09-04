<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ __('NodeHub - Remote View') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/vnc-viewer.js'])

    <style>
        html,
        body {
            height: 100%;
            background: #000;
            overflow: hidden;
            touch-action: manipulation;
        }

        #screen-container, #screen, #screen canvas {
            touch-action: pan-x pan-y pinch-zoom;
        }

        body.local-cursor-active #screen canvas,
        body.local-cursor-active #screen {
            cursor: default !important;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div id="viewer-root" data-token="{{ $token }}" data-ticket-url="{{ route('viewer.ticket', $token) }}"
        class="fixed inset-0 bg-black flex flex-col overflow-hidden">

        <!-- Outer Control Bar Header (Situated completely outside remote screen canvas) -->
        <header id="toolbar"
            class="z-40 w-full bg-zinc-900/95 border-b border-white/10 px-2 sm:px-4 py-1.5 backdrop-blur-md shadow-xl flex items-center justify-between flex-none transition-all duration-200">

            <!-- Left Section: Back Button & Device Status -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('computers.index') }}" id="btn-back" title="{{ __('Kembali ke Daftar Devices') }}"
                    class="toolbar-btn flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold bg-white/5 hover:bg-white/10 rounded-lg border border-white/10 transition text-gray-200 hover:text-white">
                    <svg class="h-4 w-4 text-[#00828c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('Kembali') }}</span>
                </a>

                <span class="h-4 w-px bg-white/15"></span>

                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <div id="device-info-header" class="flex items-center gap-1.5 sm:gap-2">
                        <span id="device-name-display" class="text-xs font-bold text-gray-100 tracking-wide truncate max-w-[120px] sm:max-w-[200px]">NodeHub Remote</span>
                        <span id="device-ip-badge" class="hidden text-[11px] font-mono font-medium text-emerald-300 bg-emerald-950/80 border border-emerald-500/40 px-2 py-0.5 rounded-md shadow-xs"></span>
                    </div>
                </div>

                <span id="conn-status" class="ms-1 me-1 select-none text-xs font-medium text-slate-300 truncate max-w-[150px] sm:max-w-xs"></span>
            </div>

            <!-- Right Section: Always Visible Remote Controller Actions -->
            <div class="flex items-center gap-1 sm:gap-1.5">
                {{-- Quick control keys --}}
                <div class="relative">
                    <button type="button" id="btn-quick-keys" title="{{ __('Quick Control Keys') }}"
                        class="flex h-8 items-center gap-1 rounded-lg px-2 text-gray-300 transition hover:bg-white/10 hover:text-white text-xs font-semibold border border-white/5 bg-white/5">
                        <svg class="h-4 w-4 text-[#00828c]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 7.5h10.5a2.25 2.25 0 0 1 2.25 2.25v4.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 14.25v-4.5A2.25 2.25 0 0 1 6.75 7.5Zm.75 3v.008H7.5v-.008H7.5Zm2.25 0v.008h-.008v-.008H9.75Zm2.25 0v.008h-.008v-.008H12Zm2.25 0v.008h-.008v-.008h-.008Zm2.25 0v.008h-.008v-.008h-.008Zm-6.75 2.25v.008h-.008v-.008h.008Zm2.25 0v.008h-.008v-.008h.008Zm2.25 0v.008h-.008v-.008h.008Z" />
                        </svg>
                        <span>Keys</span>
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div id="quick-keys-panel"
                        class="hidden absolute right-0 top-10 z-50 w-48 sm:w-52 max-w-[calc(100vw-32px)] rounded-xl border border-white/15 bg-zinc-900/95 backdrop-blur-md p-2 shadow-2xl">
                        <p class="px-2 pb-1.5 pt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            {{ __('Quick Control Keys') }}
                        </p>
                        <div class="space-y-0.5">
                            <button type="button" id="qk-ctrl-alt-del"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                <span class="font-semibold text-rose-400">Ctrl + Alt + Del</span>
                            </button>
                            <button type="button" id="qk-win-key"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                {{ __('Win Key / Super') }}
                            </button>
                            <button type="button" id="qk-alt-tab"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                {{ __('Alt + Tab') }}
                            </button>
                            <button type="button" id="qk-ctrl-esc"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                {{ __('Ctrl + Esc') }}
                            </button>
                            <button type="button" id="qk-f5"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                {{ __('F5 (Refresh Window)') }}
                            </button>
                            <button type="button" id="qk-ctrl-alt-backspace"
                                class="menu-item hover:bg-[#00828c]/20 hover:text-white">
                                {{ __('Ctrl + Alt + Backspace') }}
                            </button>
                        </div>
                    </div>
                </div>

                <span class="h-5 w-px bg-white/15"></span>

                {{-- Clipboard / Send Text --}}
                <div class="relative">
                    <button type="button" id="btn-clipboard" title="{{ __('Kirim Clipboard / Teks ke Remote') }}" class="toolbar-btn">
                        <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                        </svg>
                    </button>

                    <div id="clipboard-panel"
                        class="hidden absolute right-0 top-10 z-50 w-64 sm:w-72 max-w-[calc(100vw-32px)] rounded-xl border border-white/15 bg-zinc-900/95 backdrop-blur-md p-3 shadow-2xl">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            {{ __('Kirim Teks / Clipboard ke Remote') }}
                        </p>
                        <textarea id="clipboard-text-input" rows="3"
                            class="w-full rounded-lg border border-white/10 bg-zinc-800 p-2 text-xs text-gray-100 placeholder-gray-500 focus:border-[#00828c] focus:outline-none focus:ring-1 focus:ring-[#00828c]"
                            placeholder="{{ __('Tempel / ketik teks dari komputer Anda di sini...') }}"></textarea>
                        <div class="mt-2 flex gap-1.5">
                            <button type="button" id="btn-send-clipboard"
                                class="flex-1 rounded-lg bg-[#00828c] py-1.5 text-xs font-bold text-white hover:bg-[#006e76] transition">
                                {{ __('Kirim ke Clipboard VNC') }}
                            </button>
                            <button type="button" id="btn-type-clipboard"
                                class="rounded-lg bg-white/10 px-2.5 py-1.5 text-xs font-semibold text-gray-300 hover:bg-white/20 transition"
                                title="{{ __('Ketik karakter demi karakter langsung ke layar remote') }}">
                                {{ __('Ketik Teks') }}
                            </button>
                        </div>
                    </div>
                </div>

                <span class="h-5 w-px bg-white/15"></span>

                {{-- View-Only Toggle --}}
                <button type="button" id="btn-view-only" title="{{ __('Toggle View-Only Mode') }}" class="toolbar-btn">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>

                {{-- Screenshot --}}
                <button type="button" id="btn-screenshot" title="{{ __('Take Screenshot') }}" class="toolbar-btn">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                </button>

                {{-- Fullscreen --}}
                <button type="button" id="btn-fullscreen" title="{{ __('Toggle Fullscreen Mode') }}"
                    class="toolbar-btn">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </button>

                <span class="h-5 w-px bg-white/15"></span>

                {{-- Display Settings --}}
                <div class="relative">
                    <button type="button" id="btn-settings" title="{{ __('Settings') }}" class="toolbar-btn">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.111-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>

                    <div id="settings-panel"
                        class="hidden absolute right-0 top-10 z-50 w-52 sm:w-56 max-w-[calc(100vw-32px)] rounded-xl border border-white/15 bg-zinc-900/95 backdrop-blur-md p-3 shadow-2xl">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            {{ __('Display Settings') }}
                        </p>
                        <label class="flex cursor-pointer items-center justify-between py-1 text-[13px] text-gray-200">
                            {{ __('Scale to fit window') }}
                            <input type="checkbox" id="chk-scale" checked
                                class="h-4 w-4 rounded border-gray-600 bg-zinc-800 text-[#00828c] focus:ring-[#00828c]" />
                        </label>
                        <label class="flex cursor-pointer items-center justify-between py-1 text-[13px] text-gray-200">
                            {{ __('Local cursor') }}
                            <input type="checkbox" id="chk-cursor" checked
                                class="h-4 w-4 rounded border-gray-600 bg-zinc-800 text-[#00828c] focus:ring-[#00828c]" />
                        </label>
                    </div>
                </div>

                {{-- Toggle Bar Collapse --}}
                <button type="button" id="btn-toggle-bar" title="{{ __('Sembunyikan Bar Kontrol') }}" class="toolbar-btn">
                    <svg class="h-4 w-4 text-slate-400 hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Floating Restore Top Bar Button (Shown when bar is collapsed) -->
        <button type="button" id="floating-show-bar" title="{{ __('Tampilkan Bar Kontrol') }}"
            class="hidden fixed top-2 right-3 z-50 p-1.5 rounded-lg bg-zinc-900/80 hover:bg-zinc-800 text-gray-300 hover:text-white backdrop-blur-md border border-white/10 shadow-lg transition">
            <svg class="h-4 w-4 text-[#00828c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <!-- Remote Screen Container -->
        <div id="screen-container" class="relative flex-1 w-full overflow-hidden bg-black">
            <!-- Remote screen canvas -->
            <div id="screen" class="absolute inset-0 w-full h-full"></div>

            <!-- Virtual Mobile Pointer Cursor -->
            <div id="virtual-cursor" class="pointer-events-none absolute z-[60] hidden lg:hidden transition-opacity duration-75 transform -translate-x-1 -translate-y-1">
                <svg class="h-6 w-6 text-cyan-400 drop-shadow-[0_2px_8px_rgba(0,0,0,0.9)]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 3l7 18 3-7 7-3L3 3z" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
                <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                </span>
            </div>
        </div>

        <!-- Native Android keyboard trigger input -->
        <input type="text" id="mobile-keyboard-input" class="fixed bottom-0 left-0 w-px h-px opacity-0 z-0 lg:hidden" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" />

        <!-- Floating Mobile Dock Bar (Mobile Only - Icon Only Layout) -->
        <div id="mobile-dock" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 hidden lg:hidden items-center gap-2 rounded-2xl border border-white/20 bg-zinc-900/90 px-3 py-2 backdrop-blur-md shadow-2xl transition-all duration-200">
            <!-- Left Click -->
            <button type="button" id="mb-left-click" title="Klik Kiri" class="flex items-center justify-center p-2 rounded-xl bg-[#00828c] hover:bg-[#006e76] text-white active:scale-95 transition shadow-lg shadow-[#00828c]/30">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286.678z" />
                </svg>
            </button>

            <!-- Double Click -->
            <button type="button" id="mb-double-click" title="Klik 2x (Buka File/Folder)" class="flex items-center justify-center px-2 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-cyan-400 font-black text-xs active:scale-95 transition border border-white/10">
                2x
            </button>

            <!-- Right Click -->
            <button type="button" id="mb-right-click" title="Klik Kanan" class="flex items-center justify-center p-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 border border-amber-500/40 active:scale-95 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 00-6 6v3" />
                </svg>
            </button>

            <!-- Zoom Toggle Button -->
            <button type="button" id="mb-zoom" title="Perbesar Layar / Zoom (1x / 1.5x / 2x)" class="flex items-center justify-center p-2 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-400 border border-sky-500/40 active:scale-95 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                </svg>
            </button>

            <!-- Keyboard Toggle & Exit Button -->
            <button type="button" id="mb-keyboard" title="Buka / Tutup Keyboard Android" class="flex items-center justify-center p-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/40 active:scale-95 transition">
                <svg id="mb-keyboard-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>

        {{-- Overlay: connecting --}}
        <div id="overlay-connecting" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <div class="flex flex-col items-center gap-7 px-6">
                <div class="relative h-20 w-20">
                    <span class="absolute inset-0 animate-ping rounded-full border border-blue-500/30"></span>
                    <span class="absolute inset-2 animate-pulse rounded-full border border-blue-500/40"></span>
                    <span class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-9 w-9 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                    </span>
                </div>

                <div class="text-center">
                    <p class="text-sm font-semibold tracking-wide text-gray-100">
                        {{ __('Establishing remote session') }}
                    </p>
                    <p id="stage-text" class="mt-1.5 h-4 text-xs text-blue-300/80"></p>
                </div>

                <div class="h-1 w-52 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full w-1/3 rounded-full bg-gradient-to-r from-blue-600 via-blue-400 to-blue-600"
                        style="animation: slide 1.4s ease-in-out infinite;"></div>
                </div>
            </div>
        </div>

        {{-- Overlay: VNC password prompt --}}
        <div id="overlay-password" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <form id="password-form"
                class="w-full max-w-sm rounded-2xl border border-white/10 bg-zinc-900 p-6 sm:p-8 text-center shadow-2xl mx-auto">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <p class="mt-4 text-sm font-semibold tracking-wide text-gray-100">
                    {{ __('VNC Authentication') }}
                </p>
                <p class="mt-1 text-xs leading-relaxed text-gray-400">
                    {{ __('The remote server is asking for a password. Enter the VNC password of this device.') }}
                </p>
                <input id="vnc-password-input" type="password" autocomplete="off"
                    placeholder="{{ __('VNC password') }}"
                    class="mt-5 block w-full rounded-lg border border-white/10 bg-zinc-800 px-3 py-2.5 text-sm text-gray-100 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                <p id="password-error" class="mt-2 hidden text-xs text-red-400 font-semibold"></p>

                <div class="mt-5 flex flex-col gap-2">
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#00828c] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#006e76] shadow-lg shadow-[#00828c]/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        {{ __('Unlock Session') }}
                    </button>

                    <a id="btn-password-back" href="{{ route('computers.index') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-semibold text-gray-300 transition hover:bg-white/20 hover:text-white border border-white/10">
                        <svg class="h-4 w-4 text-[#00828c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        {{ __('Kembali ke Daftar Devices') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Overlay: disconnected / error --}}
        <div id="overlay-disconnected" class="fixed inset-0 z-50 hidden flex flex-col items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <div
                class="w-full max-w-sm rounded-2xl border border-white/10 bg-zinc-900 p-6 sm:p-8 text-center shadow-2xl mx-auto">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                    <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
                <p id="disc-text" class="mt-4 text-sm leading-relaxed text-gray-300"></p>
                <a id="disc-btn" href="{{ route('computers.index') }}"
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#00828c] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#006e76]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span id="disc-btn-text">{{ __('Kembali ke Daftar Devices') }}</span>
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes slide {
            0% {
                transform: translateX(-110%);
            }

            100% {
                transform: translateX(320%);
            }
        }
    </style>
</body>

</html>
