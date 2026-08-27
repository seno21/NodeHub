<section>
    <div class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </div>
        <div>
            <h2 class="text-base font-bold text-slate-800">
                {{ __('Informasi Profil') }}
            </h2>
            <p class="text-xs text-slate-500">
                {{ __('Perbarui data diri dan alamat email utama akun Anda.') }}
            </p>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5 max-w-xl">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2 text-xs text-rose-500" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm" :value="old('username', $user->username)" required autocomplete="username" />
            <x-input-error class="mt-2 text-xs text-rose-500" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2 text-xs text-rose-500" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs">
                    <p class="font-medium">
                        {{ __('Alamat email Anda belum diverifikasi.') }}
                        <button form="send-verification" class="underline font-semibold hover:text-amber-900 focus:outline-none ml-1">
                            {{ __('Kirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 font-semibold text-emerald-700">
                            {{ __('Link verifikasi baru telah dikirimkan ke email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="auto_lock_timeout" :value="__('Durasi Auto-Lock Sesi (Inaktivitas)')" class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5" />
            <select id="auto_lock_timeout" name="auto_lock_timeout" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm">
                <option value="5" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 5)>5 {{ __('Menit') }}</option>
                <option value="10" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 10)>10 {{ __('Menit') }}</option>
                <option value="15" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 15)>15 {{ __('Menit') }}</option>
                <option value="20" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 20)>20 {{ __('Menit (Default)') }}</option>
                <option value="30" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 30)>30 {{ __('Menit') }}</option>
                <option value="60" @selected(old('auto_lock_timeout', $user->auto_lock_timeout ?? 20) == 60)>60 {{ __('Menit (1 Jam)') }}</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-500">
                {{ __('Sesi aplikasi akan otomatis dikunci jika tidak ada aktivitas pengguna sesuai durasi yang dipilih.') }}
            </p>
            <x-input-error class="mt-2 text-xs text-rose-500" :messages="$errors->get('auto_lock_timeout')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition shadow-md shadow-blue-500/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'profile-updated')
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
                    {{ __('Profil berhasil diperbarui.') }}
                </div>
            @endif
        </div>
    </form>
</section>
