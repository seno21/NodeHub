<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <a href="{{ route('computers.index') }}"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 bg-[#00828c] border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition ease-in-out duration-150 shadow-xs w-full sm:w-auto">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                {{ __('Manage Devices') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="deviceStats('{{ route('computers.status') }}')">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm"
                    x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms x-init="setTimeout(() => show = false, 4000)">
                    <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Device stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">

                {{-- Total --}}
                <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-gray-200/80 p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">
                            {{ __('Total Devices') }}</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#00828c]/10 text-[#00828c]">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 tabular-nums">{{ $total }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $windowsCount }} Windows · {{ $linuxCount }} Linux</p>
                </div>

                {{-- Online --}}
                <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-gray-200/80 p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ __('Online') }}</p>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-emerald-600 tabular-nums" x-text="loading ? '…' : online"></p>
                    <p class="mt-1 text-xs text-gray-400">{{ __('Reachable right now') }}</p>
                </div>

                {{-- Offline --}}
                <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-gray-200/80 p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ __('Offline') }}
                        </p>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-rose-500 tabular-nums" x-text="loading ? '…' : offline"></p>
                    <p class="mt-1 text-xs text-gray-400">{{ __('Not responding') }}</p>
                </div>

                {{-- OS breakdown --}}
                <div
                    class="bg-white overflow-hidden shadow-xs rounded-2xl border border-gray-200/80 p-4 sm:p-5 flex flex-col">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ __('By OS') }}</p>
                    <div class="mt-3 space-y-2 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#0078D4"
                                        d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z" />
                                </svg>
                                Windows
                            </span>
                            <span class="font-bold text-gray-900 tabular-nums">{{ $windowsCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="8.5" fill="none" stroke="#E95420"
                                        stroke-width="2.2" />
                                    <circle cx="12" cy="5.5" r="2" fill="#E95420" />
                                    <circle cx="6.4" cy="15.25" r="2" fill="#E95420" />
                                    <circle cx="17.6" cy="15.25" r="2" fill="#E95420" />
                                </svg>
                                Linux
                            </span>
                            <span class="font-bold text-gray-900 tabular-nums">{{ $linuxCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($total === 0)
                {{-- Empty state --}}
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-200">
                    <div class="p-14 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#00828c]/10 text-[#00828c]">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-gray-900">
                            {{ __('No devices registered yet') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Add your first computer to start remote access.') }}
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('computers.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-[#00828c] hover:bg-[#006e76] rounded-xl text-xs font-semibold uppercase tracking-widest text-white transition">
                                {{ __('Add Device') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
