<x-guest-layout>

    <!-- Session Status -->
    <x-auth-session-status
        class="mb-5 p-3.5 bg-[#00828c]/10 border border-[#00828c]/20 text-[#00828c] rounded-2xl text-xs font-medium"
        :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPass: false }" class="space-y-5">
        @csrf

        <!-- Username / Email Address -->
        <div>
            <label for="login" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                {{ __('Username atau Email') }}
            </label>
            <div class="relative rounded-2xl">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                    autocomplete="username" placeholder="Username atau email"
                    class="w-full pl-11 pr-4 py-3 bg-slate-950/60 border border-slate-800 focus:border-[#00828c] focus:ring-2 focus:ring-[#00828c]/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2 text-xs text-rose-400" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    {{ __('Password') }}
                </label>
            </div>

            <div class="relative rounded-2xl">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <input id="password" name="password" :type="showPass ? 'text' : 'password'" required
                    autocomplete="current-password" placeholder="••••••••"
                    class="w-full pl-11 pr-11 py-3 bg-slate-950/60 border border-slate-800 focus:border-[#00828c] focus:ring-2 focus:ring-[#00828c]/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
                <button type="button" x-on:click="showPass = !showPass"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                    <svg x-show="!showPass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-400" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded-lg bg-slate-950 border-slate-800 text-[#00828c] focus:ring-[#00828c] focus:ring-offset-slate-900 transition">
                <span
                    class="ms-2.5 text-xs text-slate-400 group-hover:text-slate-300 transition">{{ __('Ingat saya di perangkat ini') }}</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit"
                class="w-full py-3.5 px-4 bg-gradient-to-r from-[#00828c] via-[#006e76] to-[#00585f] hover:from-[#00939e] hover:to-[#006e76] text-white font-bold text-sm rounded-2xl shadow-lg shadow-[#00828c]/30 hover:shadow-[#00828c]/40 active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2 group">
                <span>{{ __('Login Web UI') }}</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>

        @if (Route::has('register'))
            <div class="text-center pt-3 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    Made with <span class="text-rose-400">&hearts;</span> by
                    <a href="https://seno21.github.io/" target="_blank" rel="noopener noreferrer"
                        class="font-semibold text-white hover:text-teal-200 transition underline-offset-2">
                        nzucode
                    </a>
                </p>
            </div>
        @endif
    </form>
</x-guest-layout>
