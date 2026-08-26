<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 tracking-tight">
                    {{ __('Edit Perangkat') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">{{ __('Perbarui informasi dan kredensial koneksi perangkat #:id', ['id' => $computer->id]) }}</p>
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

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-md overflow-hidden shadow-xl shadow-slate-200/50 sm:rounded-2xl border border-slate-200/80 p-8">
                <div class="mb-6 pb-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">{{ $computer->name }}</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ $computer->ip_address }}:{{ $computer->vnc_port }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider {{ $computer->os_type === 'windows' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
                        {{ ucfirst($computer->os_type) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('computers.update', $computer) }}">
                    @csrf
                    @method('PUT')
                    @include('computers._form', ['computer' => $computer, 'allTags' => $allTags ?? []])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
