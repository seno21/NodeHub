<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#003e43">

    <title>{{ __('Lock Screen') }} - {{ config('app.name', 'NodeHub') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="h-full font-sans antialiased text-slate-100 bg-[#002f33] selection:bg-[#00828c] selection:text-white flex items-center justify-center p-4">
    <!-- Animated background accents -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#00828c]/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl animate-pulse"
            style="animation-delay: 2s;"></div>
    </div>

    <!-- Lock Card Container -->
    <div class="relative z-10 w-full max-w-md" x-data="{ showPassword: false, submitting: false }">
        <div
            class="bg-white/10 backdrop-blur-xl border border-white/15 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/40 text-center space-y-6">
            <!-- User Avatar & Lock Badge -->
            <div class="relative inline-block mx-auto">
                <span
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-[#00828c] to-[#00585f] text-2xl font-bold text-white uppercase shadow-inner border-2 border-white/20">
                    {{ Auth::user()->initials() }}
                </span>
                <span
                    class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-amber-950 border-2 border-[#002f33] shadow-md"
                    title="Sesi Terkunci">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
            </div>

            <!-- User Name & Title -->
            <div>
                <h2 class="text-lg font-bold text-white leading-snug">{{ Auth::user()->name }}</h2>
                <div
                    class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-200 border border-amber-500/30">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ __('Sesi Terkunci (Inaktivitas ') . (Auth::user()->auto_lock_timeout ?? 20) . __(' Menit)') }}</span>
                </div>
            </div>

            <!-- Unlock Form -->
            <form method="POST" action="{{ route('lock.unlock') }}" class="space-y-4" x-on:submit="submitting = true">
                @csrf

                <div>
                    <label for="password" class="sr-only">{{ __('Password') }}</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                            autofocus placeholder="{{ __('Masukkan Password Anda...') }}"
                            class="w-full px-4 py-3 bg-black/20 border border-white/20 rounded-2xl text-sm text-white placeholder-slate-400 focus:bg-black/30 focus:border-[#00828c] focus:outline-none focus:ring-2 focus:ring-[#00828c]/50 transition pr-10">

                        <button type="button" x-on:click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" x-show="!showPassword">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" x-show="showPassword" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>

                    @if ($errors->has('password'))
                        <p class="mt-2 text-xs font-semibold text-rose-300 flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            {{ $errors->first('password') }}
                        </p>
                    @endif
                </div>

                <button type="submit" :disabled="submitting"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-[#00828c] border border-transparent rounded-2xl font-bold text-xs text-white uppercase tracking-wider hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 focus:ring-offset-[#002f33] transition shadow-lg shadow-[#00828c]/30 disabled:opacity-60">
                    <svg class="h-4 w-4 animate-spin" x-show="submitting" x-cloak fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        x-show="!submitting">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <span>{{ __('Buka Kunci Sesi') }}</span>
                </button>
            </form>

            <!-- Logout Link -->
            <div class="pt-2 border-t border-white/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-xs text-teal-200/70 hover:text-white font-medium transition underline-offset-4 hover:underline">
                        {{ __('Bukan Anda? Log Out dan Ganti Akun') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
