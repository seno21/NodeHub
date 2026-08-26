<x-guest-layout>
    <div class="mb-6 text-center sm:text-left">
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Buat Akun Baru ✨</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1.5">Daftarkan akun administrator untuk mengakses manajemen perangkat VNC.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ showPass: false }" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                {{ __('Nama Lengkap') }}
            </label>
            <input id="name"
                   name="name"
                   type="text"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name"
                   placeholder="John Doe"
                   class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Username -->
        <div>
            <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                {{ __('Username') }}
            </label>
            <input id="username"
                   name="username"
                   type="text"
                   value="{{ old('username') }}"
                   required
                   autocomplete="username"
                   placeholder="johndoe"
                   class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            <x-input-error :messages="$errors->get('username')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                {{ __('Alamat Email') }}
            </label>
            <input id="email"
                   name="email"
                   type="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="username"
                   placeholder="nama@email.com"
                   class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                {{ __('Password') }}
            </label>
            <input id="password"
                   name="password"
                   type="password"
                   required
                   autocomplete="new-password"
                   placeholder="Minimal 8 karakter"
                   class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                {{ __('Konfirmasi Password') }}
            </label>
            <input id="password_confirmation"
                   name="password_confirmation"
                   type="password"
                   required
                   autocomplete="new-password"
                   placeholder="Ulangi password"
                   class="w-full px-4 py-2.5 bg-slate-950/60 border border-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-2xl text-sm text-slate-100 placeholder-slate-500 transition duration-200" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-rose-400" />
        </div>

        <div class="pt-3">
            <button type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-bold text-sm rounded-2xl shadow-lg shadow-blue-600/30 hover:shadow-blue-500/40 active:scale-[0.99] transition duration-200 flex items-center justify-center gap-2">
                <span>{{ __('Daftar Sekarang') }}</span>
            </button>
        </div>

        <div class="text-center pt-3 border-t border-slate-800/80">
            <p class="text-xs text-slate-400">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-blue-400 hover:text-blue-300 transition hover:underline">
                    Masuk di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
