<section>
    <div class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <div>
            <h2 class="text-base font-bold text-slate-800">
                {{ __('Ubah Kata Sandi') }}
            </h2>
            <p class="text-xs text-slate-500">
                {{ __('Gunakan kata sandi yang panjang dan acak untuk menjaga keamanan akun.') }}
            </p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5 max-w-xl">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm" autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs text-rose-500" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm" autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs text-rose-500" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm" autocomplete="new-password" placeholder="Ulangi kata sandi baru" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs text-rose-500" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-md shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                {{ __('Perbarui Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-semibold bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200"
                >
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('Password berhasil diperbarui.') }}
                </div>
            @endif
        </div>
    </form>
</section>
