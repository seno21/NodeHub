<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">
                    {{ __('Tambah Perangkat Baru') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">{{ __('Daftarkan komputer atau server remote untuk pemantauan dan kontrol VNC') }}</p>
            </div>
            <a href="{{ route('computers.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                {{ __('Kembali') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80 p-5 sm:p-8">
                <div class="mb-6 pb-5 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ __('Formulir Tambah Device') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Isi detail perangkat di bawah ini dengan benar.') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('computers.store') }}">
                    @csrf
                    @include('computers._form', ['computer' => null, 'allTags' => $allTags ?? []])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
