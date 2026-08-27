<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight">
                    {{ __('Pengaturan Akun & Profil') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">{{ __('Kelola informasi identitas, keamanan kata sandi, dan preferensi akun Anda') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

            {{-- User Profile Hero Banner Card --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-5 sm:p-8 text-white shadow-xl shadow-slate-900/20 border border-slate-800">
                {{-- Decorative gradient blob --}}
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-purple-600/20 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    {{-- User Avatar --}}
                    <div class="relative shrink-0">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-500 via-indigo-500 to-purple-500 p-0.5 shadow-lg shadow-blue-500/20">
                            <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center font-extrabold text-2xl text-blue-400 tracking-wider">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-slate-900 rounded-full" title="{{ __('Sesi Aktif') }}"></span>
                    </div>

                    {{-- User Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                            <h1 class="text-xl font-bold tracking-tight text-white">{{ Auth::user()->name }}</h1>
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-blue-300 border border-blue-500/30">
                                <svg class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('Administrator') }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-300 font-mono flex items-center justify-center sm:justify-start gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            {{ Auth::user()->email }}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-400 pt-3 border-t border-slate-800">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                Terdaftar: {{ Auth::user()->created_at?->format('d M Y') ?? 'N/A' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Inaktivitas 20m Auto-Lock
                            </span>
                        </div>
                    </div>

                    {{-- Quick Lock Session Button --}}
                    <div class="shrink-0 mt-4 sm:mt-0 w-full sm:w-auto">
                        <form method="POST" action="{{ route('lock.store') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 border border-amber-500/40 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                <span>{{ __('Kunci Sesi') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Grid Section Cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                {{-- Profile Info Card --}}
                <div class="p-5 sm:p-8 bg-white/90 backdrop-blur-md shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Password Change Card --}}
                <div class="p-5 sm:p-8 bg-white/90 backdrop-blur-md shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Danger Zone Card --}}
            <div class="p-5 sm:p-8 bg-rose-50/50 backdrop-blur-md shadow-lg shadow-rose-100/50 rounded-2xl border border-rose-200/80">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-app-layout>
