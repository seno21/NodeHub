<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Devices') }}
            </h2>

            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <button type="button" x-on:click="$dispatch('trigger-open-import-modal')"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 rounded-xl font-semibold text-xs text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#00828c] transition shadow-xs">
                    <svg class="h-4 w-4 text-[#00828c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span>Import / Restore</span>
                </button>

                <button type="button" x-on:click="$dispatch('trigger-open-export-modal')"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 rounded-xl font-semibold text-xs text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#00828c] transition shadow-xs">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Export / Backup</span>
                </button>

                <a href="{{ route('computers.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#00828c] border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition shadow-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Add Device') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="deviceBoard({{ json_encode($allDevices) }})"
        x-on:trigger-open-export-modal.window="openExportModal()"
        x-on:trigger-open-import-modal.window="openImportModal()"
        x-on:trigger-check-all-connections.window="checkAllConnections('{{ route('computers.status') }}', false)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm"
                    x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <ul class="space-y-1 flex-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" x-on:click="show = false" class="text-red-400 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700 shadow-sm"
                    x-data="{ show: true }" x-show="show" x-transition.opacity.duration.500ms x-init="setTimeout(() => show = false, 4000)">
                    <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <!-- Batch Connectivity Check Results Notification Banner -->
            <template x-if="batchSummary">
                <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm text-emerald-950 shadow-sm"
                    x-transition.opacity.duration.300ms>
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 border border-emerald-200/80 text-emerald-700 shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v6.525a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Z" />
                            </svg>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-sm text-emerald-950">Hasil Cek Koneksi Massal</h4>
                                <span
                                    class="text-xs text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full font-mono border border-emerald-200/60"
                                    x-text="batchSummary.time"></span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                <span
                                    class="inline-flex items-center gap-1.5 bg-emerald-600 text-white font-bold px-2.5 py-0.5 rounded-full shadow-xs">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                                    <span x-text="batchSummary.online + ' Online'"></span>
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 bg-rose-600 text-white font-bold px-2.5 py-0.5 rounded-full shadow-xs"
                                    x-show="batchSummary.offline > 0">
                                    <span x-text="batchSummary.offline + ' Offline'"></span>
                                </span>
                                <span class="text-emerald-800 font-medium" x-show="batchSummary.offline === 0">
                                    — Semua perangkat merespon jaringan!
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-2.5 w-full sm:w-auto justify-end border-t sm:border-t-0 border-emerald-200/60 pt-2 sm:pt-0 shrink-0">
                        <button type="button" x-on:click="checkAllConnections('{{ route('computers.status') }}', true)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-800 hover:text-emerald-950 hover:bg-emerald-100 px-3 py-1.5 rounded-lg border border-emerald-200/70 bg-white/60 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            Lihat Log Terminal
                        </button>
                        <button type="button" x-on:click="dismissBatchSummary()"
                            class="rounded-lg p-1.5 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-900 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Search & Tag Filter Bar -->
            <div class="mb-6 bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 w-full">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery"
                            placeholder="{{ __('Cari device, lokasi, IP address, deskripsi...') }}"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                    </div>

                    <!-- Tag Filter Dropdown -->
                    @if (isset($allTags) && $allTags->isNotEmpty())
                        <div class="w-full sm:w-48 shrink-0">
                            <select x-model="selectedTag"
                                class="w-full py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                                <option value="">{{ __('Semua Tag') }}</option>
                                @foreach ($allTags as $tagItem)
                                    <option value="{{ $tagItem->id }}">
                                        {{ $tagItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- OS Filter Dropdown -->
                    <div class="w-full sm:w-40 shrink-0">
                        <select x-model="selectedOs"
                            class="w-full py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-xl text-sm text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                            <option value="">{{ __('Semua OS') }}</option>
                            <option value="windows">Windows</option>
                            <option value="linux">Linux</option>
                        </select>
                    </div>

                    <!-- Action: Cek Semua Koneksi Button -->
                    <div class="w-full sm:w-auto shrink-0">
                        <button type="button"
                            x-on:click="checkAllConnections('{{ route('computers.status') }}', false)"
                            x-bind:disabled="checkingAll"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold uppercase tracking-wider transition shadow-xs disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4 animate-spin" x-show="checkingAll" fill="none"
                                viewBox="0 0 24 24" x-cloak>
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg class="h-4 w-4" x-show="!checkingAll" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v6.525a2.25 2.25 0 0 1-2.25-2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Z" />
                            </svg>
                            <span
                                x-text="checkingAll ? '{{ __('Memeriksa...') }}' : '{{ __('Ping All') }}'">{{ __('Ping All') }}</span>
                        </button>
                    </div>

                    <!-- Reset Button -->
                    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0"
                        x-show="searchQuery || selectedTag || selectedOs" x-cloak>
                        <button type="button" x-on:click="resetFilters()"
                            class="inline-flex items-center justify-center gap-1 px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition"
                            title="{{ __('Reset Filter') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            <span>{{ __('Reset') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State when 0 filtered devices -->
            <div x-show="filteredDevices.length === 0"
                class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200" x-cloak>
                <div class="p-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                        <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">
                        {{ __('Tidak ada perangkat yang ditemukan') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Coba gunakan kata kunci lain atau hapus filter pencarian.') }}
                    </p>
                    <div class="mt-6">
                        <button type="button" x-on:click="resetFilters()"
                            class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold uppercase tracking-widest transition">
                            {{ __('Reset Filter') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- List Container with Alpine Instant Filter -->
            <div x-show="filteredDevices.length > 0">
                {{-- Mobile card list (visible on screens < md) --}}
                <div class="space-y-3.5 md:hidden">
                    <template x-for="comp in paginatedDevices" :key="comp.id">
                        <div
                            class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-4 space-y-3 transition hover:shadow-md hover:border-[#00828c]/40">
                            <!-- Header: Status & OS -->
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="inline-flex items-center gap-2 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200/60">
                                    <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                                        :class="statusClass(comp.id)"></span>
                                    <span class="text-xs font-semibold text-gray-700"
                                        x-text="statusLabel(comp.id)"></span>
                                </span>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider shrink-0"
                                    :class="comp.os_type === 'windows' ? 'bg-blue-50 text-blue-700 border border-blue-100' :
                                        'bg-orange-50 text-orange-700 border border-orange-100'">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <template x-if="comp.os_type === 'windows'">
                                            <path fill="#0078D4"
                                                d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z" />
                                        </template>
                                        <template x-if="comp.os_type !== 'windows'">
                                            <g>
                                                <circle cx="12" cy="12" r="8.5" fill="none"
                                                    stroke="#E95420" stroke-width="2.2" />
                                                <circle cx="12" cy="5.5" r="2" fill="#E95420" />
                                                <circle cx="6.4" cy="15.25" r="2" fill="#E95420" />
                                                <circle cx="17.6" cy="15.25" r="2" fill="#E95420" />
                                            </g>
                                        </template>
                                    </svg>
                                    <span x-text="comp.os_type.charAt(0).toUpperCase() + comp.os_type.slice(1)"></span>
                                </span>
                            </div>

                            <!-- Body: Device Name & Details -->
                            <div>
                                <h3 class="font-bold text-base text-gray-900 leading-snug" x-text="comp.name"></h3>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700"
                                        x-text="comp.ip_address + ':' + comp.vnc_port"></span>

                                    <!-- SSH Badge in Address Section -->
                                    <template x-if="isSshOpen(comp) && comp.has_ssh">
                                        <span
                                            class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            title="SSH Open & Password Terkonfigurasi">
                                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                            SSH
                                        </span>
                                    </template>
                                    <template x-if="isSshOpen(comp) && !comp.has_ssh">
                                        <span
                                            class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200/80 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            title="Port SSH Open (Password Belum Konfigurasi)">
                                            <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                            SSH
                                        </span>
                                    </template>
                                    <template x-if="!isSshOpen(comp)">
                                        <span
                                            class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-full text-[10px] font-medium"
                                            title="SSH Closed / Unreachable">
                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            No SSH
                                        </span>
                                    </template>

                                    <template x-if="comp.location">
                                        <span
                                            class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-slate-600">
                                            <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                            </svg>
                                            <span x-text="comp.location"></span>
                                        </span>
                                    </template>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1"
                                    x-show="comp.tags_relation && comp.tags_relation.length > 0">
                                    <template x-for="tagItem in comp.tags_relation" :key="tagItem.id">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium"
                                            :style="'background-color: ' + (tagItem.color ? tagItem.color + '20' : '#e2e8f0') +
                                            '; color: ' + (tagItem.color || '#475569') + '; border: 1px solid ' + (
                                                tagItem.color ? tagItem.color + '40' : '#cbd5e1')"
                                            x-text="tagItem.name"></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Actions Footer -->
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                                <form :action="'/computers/' + comp.id + '/connect'" method="POST"
                                    :data-name="comp.name" x-on:submit.prevent="connect($event, comp.id)"
                                    class="flex-1">
                                    <input type="hidden" name="_token" :value="csrfToken">
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#00828c] border border-transparent rounded-xl text-xs font-bold uppercase tracking-wider text-white hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none transition shadow-xs disabled:opacity-60 disabled:cursor-not-allowed"
                                        :disabled="pendingId !== null || connecting">
                                        <svg class="h-3.5 w-3.5 animate-spin" x-show="isButtonLoading(comp.id)"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2"
                                            x-show="!isButtonLoading(comp.id)">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                        </svg>
                                        <span>Connect</span>
                                    </button>
                                </form>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" title="Detail Perangkat & Informasional"
                                        class="rounded-xl p-2.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 bg-slate-100 transition"
                                        x-on:click.prevent="openDetailModal(comp)">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                        </svg>
                                    </button>

                                    <button type="button" title="Cek Diagnosa Ping & Port"
                                        class="rounded-xl p-2.5 text-gray-500 hover:text-[#00828c] hover:bg-[#00828c]/10 bg-slate-100 transition"
                                        x-on:click.prevent="ping(comp.id, comp.ip_address, comp.vnc_port, '/computers/' + comp.id + '/ping')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m8.25 7.5 .415-.207a.75.75 0 0 1 1.085.67V10.5m6-3-.415-.207a.75.75 0 0 0-1.085.67V10.5M6.75 16.5h.008v.008h-.008v-.008Zm2.25 0h.008v.008H9v-.008Zm2.25 0h.008v.008H12v-.008Zm2.25 0h.008v.008h-.008v-.008ZM4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75Z" />
                                        </svg>
                                    </button>

                                    <button type="button" title="Duplicate / Salin Perangkat"
                                        class="rounded-xl p-2.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 bg-slate-100 transition"
                                        x-on:click.prevent="openDuplicateModal(comp)">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 0 1 2.25 20.625V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v9.25c0 .621-.504 1.125-1.125 1.125Z" />
                                        </svg>
                                    </button>

                                    <a :href="'/computers/' + comp.id + '/edit'"
                                        class="rounded-xl p-2.5 text-gray-500 hover:text-[#00828c] hover:bg-[#00828c]/10 bg-slate-100 transition"
                                        title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>

                                    <button type="button"
                                        class="rounded-xl p-2.5 text-gray-500 hover:text-red-600 hover:bg-red-50 bg-slate-100 transition"
                                        x-on:click.prevent="$dispatch('open-modal', 'confirm-computer-deletion-' + comp.id)"
                                        title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Desktop table view (visible on screens >= md) --}}
                <div
                    class="hidden md:block bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr
                                class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-widest text-gray-500">
                                <th scope="col" class="px-5 py-3">{{ __('Status') }}</th>
                                <th scope="col" class="px-5 py-3">{{ __('Device') }}</th>
                                <th scope="col" class="px-5 py-3 hidden md:table-cell">{{ __('Location') }}</th>
                                <th scope="col" class="px-5 py-3 hidden md:table-cell">{{ __('Address') }}</th>
                                <th scope="col" class="px-5 py-3 hidden lg:table-cell">{{ __('OS') }}</th>
                                <th scope="col" class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="comp in paginatedDevices" :key="comp.id">
                                <tr class="group transition hover:bg-blue-50/40">
                                    {{-- Status --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full transition-colors duration-300"
                                                :class="statusClass(comp.id)"></span>
                                            <span class="text-xs font-medium text-gray-500 min-w-[52px]"
                                                x-text="statusLabel(comp.id)"></span>
                                        </span>
                                    </td>

                                    {{-- Name & Tags --}}
                                    <td class="px-5 py-3.5">
                                        <p class="font-semibold text-gray-900 leading-snug" x-text="comp.name"></p>
                                        <p class="text-xs text-gray-500 md:hidden mt-0.5"
                                            x-text="comp.ip_address + ':' + comp.vnc_port"></p>
                                        <template x-if="comp.location">
                                            <span
                                                class="inline-flex items-center gap-1 text-[11px] text-gray-600 md:hidden mt-1 bg-slate-100 px-2 py-0.5 rounded-md">
                                                <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                <span x-text="comp.location"></span>
                                            </span>
                                        </template>
                                        <div class="flex flex-wrap gap-1 mt-1"
                                            x-show="comp.tags_relation && comp.tags_relation.length > 0">
                                            <template x-for="tagItem in comp.tags_relation" :key="tagItem.id">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium"
                                                    :style="'background-color: ' + (tagItem.color ? tagItem.color + '20' :
                                                        '#e2e8f0') + '; color: ' + (tagItem.color || '#475569') +
                                                    '; border: 1px solid ' + (tagItem.color ? tagItem.color + '40' :
                                                        '#cbd5e1')"
                                                    x-text="tagItem.name"></span>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Location --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap hidden md:table-cell">
                                        <template x-if="comp.location">
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs text-slate-700 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/60">
                                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                <span class="truncate max-w-[140px]" x-text="comp.location"></span>
                                            </span>
                                        </template>
                                        <template x-if="!comp.location">
                                            <span class="text-xs text-gray-400 font-normal">-</span>
                                        </template>
                                    </td>

                                    {{-- Address & SSH Badge --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap hidden md:table-cell">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-gray-600"
                                                x-text="comp.ip_address + ':' + comp.vnc_port"></span>
                                            <!-- SSH Badge -->
                                            <template x-if="isSshOpen(comp) && comp.has_ssh">
                                                <span
                                                    class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0"
                                                    title="SSH Open & Password Terkonfigurasi">
                                                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                    </svg>
                                                    SSH
                                                </span>
                                            </template>
                                            <template x-if="isSshOpen(comp) && !comp.has_ssh">
                                                <span
                                                    class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200/80 px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0"
                                                    title="Port SSH Open (Password Belum Konfigurasi)">
                                                    <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                    </svg>
                                                    SSH
                                                </span>
                                            </template>
                                            <template x-if="!isSshOpen(comp)">
                                                <span
                                                    class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0"
                                                    title="SSH Closed / Unreachable">
                                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    No SSH
                                                </span>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- OS --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap hidden lg:table-cell">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide"
                                            :class="comp.os_type === 'windows' ? 'bg-blue-50 text-blue-700' :
                                                'bg-orange-50 text-orange-700'">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <template x-if="comp.os_type === 'windows'">
                                                    <path fill="#0078D4"
                                                        d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z" />
                                                </template>
                                                <template x-if="comp.os_type !== 'windows'">
                                                    <g>
                                                        <circle cx="12" cy="12" r="8.5" fill="none"
                                                            stroke="#E95420" stroke-width="2.2" />
                                                        <circle cx="12" cy="5.5" r="2" fill="#E95420" />
                                                        <circle cx="6.4" cy="15.25" r="2" fill="#E95420" />
                                                        <circle cx="17.6" cy="15.25" r="2" fill="#E95420" />
                                                    </g>
                                                </template>
                                            </svg>
                                            <span
                                                x-text="comp.os_type.charAt(0).toUpperCase() + comp.os_type.slice(1)"></span>
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" title="Detail Perangkat & Informasional"
                                                class="rounded-md p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition"
                                                x-on:click.prevent="openDetailModal(comp)">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                </svg>
                                            </button>

                                            <form :action="'/computers/' + comp.id + '/connect'" method="POST"
                                                :data-name="comp.name" x-on:submit.prevent="connect($event, comp.id)">
                                                <input type="hidden" name="_token" :value="csrfToken">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#00828c] border border-transparent rounded-md text-[11px] font-semibold uppercase tracking-widest text-white hover:bg-[#006e76] active:bg-[#00585f] focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                                                    :disabled="pendingId !== null || connecting">
                                                    <svg class="h-3 w-3 animate-spin"
                                                        x-show="isButtonLoading(comp.id)" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2"
                                                        x-show="!isButtonLoading(comp.id)">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                                    </svg>
                                                    <span>Connect</span>
                                                </button>
                                            </form>

                                            <button type="button" title="Cek Diagnosa Ping & Port"
                                                class="rounded-md p-1.5 text-gray-400 hover:text-[#00828c] hover:bg-[#00828c]/10 transition"
                                                x-on:click.prevent="ping(comp.id, comp.ip_address, comp.vnc_port, '/computers/' + comp.id + '/ping')">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m8.25 7.5 .415-.207a.75.75 0 0 1 1.085.67V10.5m6-3-.415-.207a.75.75 0 0 0-1.085.67V10.5M6.75 16.5h.008v.008h-.008v-.008Zm2.25 0h.008v.008H9v-.008Zm2.25 0h.008v.008H12v-.008Zm2.25 0h.008v.008h-.008v-.008ZM4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75Z" />
                                                </svg>
                                            </button>

                                            <button type="button" title="Duplicate / Salin Perangkat"
                                                class="rounded-md p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition"
                                                x-on:click.prevent="openDuplicateModal(comp)">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 0 1 2.25 20.625V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v9.25c0 .621-.504 1.125-1.125 1.125Z" />
                                                </svg>
                                            </button>

                                            <a :href="'/computers/' + comp.id + '/edit'"
                                                class="rounded-md p-1.5 text-gray-400 hover:text-[#00828c] hover:bg-[#00828c]/10 transition"
                                                title="Edit">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>

                                            <button type="button"
                                                class="rounded-md p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                                x-on:click.prevent="$dispatch('open-modal', 'confirm-computer-deletion-' + comp.id)"
                                                title="Delete">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Component Bar -->
                <div x-show="filteredDevices.length > 0"
                    class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                    <!-- Info & Per-Page Selector -->
                    <div
                        class="flex items-center gap-3 text-xs text-gray-600 w-full sm:w-auto justify-between sm:justify-start">
                        <span>
                            {{ __('Menampilkan') }}
                            <span class="font-bold text-gray-900" x-text="showingStart"></span>
                            –
                            <span class="font-bold text-gray-900" x-text="showingEnd"></span>
                            {{ __('dari') }}
                            <span class="font-bold text-gray-900" x-text="filteredDevices.length"></span>
                            {{ __('perangkat') }}
                        </span>

                        <div class="flex items-center gap-1.5 ml-2">
                            <span class="text-gray-300 hidden sm:inline">|</span>
                            <span class="text-gray-500 hidden sm:inline">{{ __('Per Halaman:') }}</span>
                            <select x-model.number="perPage"
                                class="py-1 pl-2 pr-7 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Navigation Controls -->
                    <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                        <!-- Prev Button -->
                        <button type="button" x-on:click="prevPage()" :disabled="currentPage === 1"
                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-gray-700 hover:bg-slate-50 active:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition text-xs font-semibold shadow-xs"
                            title="{{ __('Halaman Sebelumnya') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            <span class="hidden sm:inline">{{ __('Sebelumnya') }}</span>
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex items-center gap-1">
                            <template x-for="p in paginationPages" :key="p">
                                <button type="button" x-on:click="goToPage(p)"
                                    class="h-8 min-w-[32px] px-2 rounded-xl text-xs font-bold transition flex items-center justify-center"
                                    :class="currentPage === p ?
                                        'bg-[#00828c] text-white shadow-xs' :
                                        'bg-white border border-slate-200 text-gray-700 hover:bg-slate-100'"
                                    x-text="p">
                                </button>
                            </template>
                        </div>

                        <!-- Next Button -->
                        <button type="button" x-on:click="nextPage()" :disabled="currentPage === totalPages"
                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-gray-700 hover:bg-slate-50 active:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition text-xs font-semibold shadow-xs"
                            title="{{ __('Halaman Selanjutnya') }}">
                            <span class="hidden sm:inline">{{ __('Selanjutnya') }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Connection error (centered) --}}
        <div x-show="boardError" x-cloak
            class="fixed inset-0 z-[80] flex items-center justify-center p-4 pointer-events-none"
            x-transition.opacity.duration.200ms>
            <div class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border border-red-200 bg-white p-5 shadow-2xl"
                x-transition.scale.origin.center.duration.200ms>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ __('Connection Failed') }}</p>
                    <p class="mt-1 text-sm break-words text-gray-600" x-text="boardError"></p>
                </div>
                <button type="button" x-on:click="boardError = ''"
                    class="rounded-md p-1 text-gray-400 transition hover:text-gray-600 hover:bg-gray-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Ping result popup --}}
        <div x-show="term.open" x-cloak
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            x-on:keydown.escape.window="closeTerminal()" x-transition.opacity.duration.200ms>
            <div class="w-full max-w-xl overflow-hidden rounded-xl border border-slate-700 bg-[#0d1117] shadow-2xl"
                x-transition.scale.origin.center.duration.200ms>
                <div class="flex items-center gap-2 border-b border-white/5 bg-[#161b22] px-4 py-3">
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                    <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    <p class="ml-2 font-mono text-xs text-gray-400">
                        nodehub-ping — <span x-text="term.title"></span>
                    </p>
                    <button type="button" x-on:click="closeTerminal()"
                        class="ml-auto text-gray-500 transition hover:text-gray-200">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div x-ref="termBody" class="h-56 overflow-y-auto p-4 font-mono text-[13px] leading-6">
                    <template x-for="(line, index) in term.lines" :key="'line-' + index">
                        <p x-bind:class="line.cls" x-text="line.text"></p>
                    </template>
                    <p x-show="term.running" class="text-emerald-400 animate-pulse">▌</p>
                </div>
            </div>
        </div>

        {{-- Connect loading overlay --}}
        <div x-show="connecting" x-cloak
            class="fixed inset-0 z-[60] flex flex-col items-center justify-center gap-6 bg-gray-900/80 backdrop-blur-sm">
            <div class="relative h-16 w-16">
                <span class="absolute inset-0 rounded-full border-4 border-blue-200/30"></span>
                <span
                    class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-500 animate-spin"></span>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-white">Connecting to <span x-text="targetName"></span>...</p>
                <p class="mt-1 text-xs text-blue-200/70">Establishing secure remote session</p>
            </div>
        </div>

        {{-- Device Detail Modal --}}
        <div x-show="detailModalOpen" x-cloak
            class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm overflow-y-auto"
            x-on:keydown.escape.window="closeDetailModal()" x-transition.opacity.duration.200ms>
            <template x-if="selectedDevice">
                <div class="w-full max-w-xl max-h-[calc(100dvh-1.5rem)] sm:max-h-[calc(100dvh-2rem)] flex flex-col my-auto overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200"
                    x-transition.scale.origin.center.duration.200ms x-on:click.outside="closeDetailModal()">

                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6 sm:py-4 shrink-0">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-[#00828c]/10 text-[#00828c] border border-[#00828c]/20 shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-base text-slate-900 leading-tight"
                                    x-text="selectedDevice.name"></h3>
                                <p class="text-xs text-slate-500">Detail Spesifikasi & Informasi Perangkat</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="closeDetailModal()"
                            class="rounded-lg p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 min-h-0">
                        <!-- OS & Status Summary -->
                        <div
                            class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full" :class="statusClass(selectedDevice.id)"></span>
                                <span class="text-xs font-semibold text-slate-700"
                                    x-text="statusLabel(selectedDevice.id)"></span>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider"
                                :class="selectedDevice.os_type === 'windows' ?
                                    'bg-blue-50 text-blue-700 border border-blue-100' :
                                    'bg-orange-50 text-orange-700 border border-orange-100'">
                                <span x-text="selectedDevice.os_type.toUpperCase()"></span>
                            </span>
                        </div>

                        <!-- Technical Info Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">IP
                                    Address</span>
                                <p class="font-mono text-xs font-semibold text-slate-800 mt-0.5"
                                    x-text="selectedDevice.ip_address"></p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Port
                                    VNC</span>
                                <p class="font-mono text-xs font-semibold text-slate-800 mt-0.5"
                                    x-text="selectedDevice.vnc_port"></p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Port
                                    SSH</span>
                                <p class="font-mono text-xs font-semibold text-slate-800 mt-0.5"
                                    x-text="selectedDevice.ssh_port || 22"></p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">User
                                    SSH</span>
                                <p class="font-mono text-xs font-semibold text-slate-800 mt-0.5"
                                    x-text="selectedDevice.ssh_user || 'xubuntu'"></p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lokasi</span>
                                <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate"
                                    x-text="selectedDevice.location || '-'"></p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dibuat
                                    Pada</span>
                                <p class="text-xs font-semibold text-slate-800 mt-0.5"
                                    x-text="selectedDevice.created_at || '-'"></p>
                            </div>
                        </div>

                        <!-- SSH Port & Password Status Cards -->
                        <div class="space-y-3">
                            <!-- Port SSH Listen Status -->
                            <template x-if="isSshOpen(selectedDevice)">
                                <div
                                    class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-900 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs">✓</span>
                                        <div>
                                            <h4 class="font-bold text-xs text-emerald-900">Port SSH <span
                                                    x-text="selectedDevice.ssh_port || 22"></span> Open & Listening
                                            </h4>
                                            <p class="text-[11px] text-emerald-700">Port SSH aktif dan menerima koneksi
                                                jaringan TCP.</p>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200/60 text-emerald-800 uppercase">Port
                                        Open</span>
                                </div>
                            </template>

                            <template x-if="!isSshOpen(selectedDevice)">
                                <div
                                    class="p-3.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-200 text-slate-600 font-bold text-xs">✕</span>
                                        <div>
                                            <h4 class="font-bold text-xs text-slate-800">Port SSH <span
                                                    x-text="selectedDevice.ssh_port || 22"></span> Closed / Unreachable
                                            </h4>
                                            <p class="text-[11px] text-slate-500">Port SSH tidak merespon atau layanan
                                                SSH pada target mati.</p>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600 uppercase">Port
                                        Closed</span>
                                </div>
                            </template>

                            <!-- Password SSH Configuration Alert Card -->
                            <template x-if="!selectedDevice.has_ssh">
                                <div
                                    class="p-4 rounded-xl bg-amber-50/90 border border-amber-300/80 text-amber-900 flex items-start gap-3 shadow-xs">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 border border-amber-200 text-amber-700">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM10.29 3.86l-8.6 14.8A1.5 1.5 0 003 21h18a1.5 1.5 0 001.29-2.34l-8.6-14.8a1.5 1.5 0 00-2.58 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-xs uppercase tracking-wider text-amber-900">Password
                                            SSH Belum Konfigurasi</h4>
                                        <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                                            Password SSH belum diisi untuk perangkat ini. Fitur Remote Action (eksekusi
                                            perintah/script remote) memerlukan password SSH.
                                        </p>
                                        <a :href="'/computers/' + selectedDevice.id + '/edit'"
                                            class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            Konfigurasi SSH Sekarang
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Description & Tags -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Deskripsi /
                                Catatan</span>
                            <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line"
                                x-text="selectedDevice.description || 'Tidak ada deskripsi.'"></p>
                        </div>

                        <div x-show="selectedDevice.tags_relation && selectedDevice.tags_relation.length > 0">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Tags</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="tagItem in selectedDevice.tags_relation" :key="tagItem.id">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold"
                                        :style="'background-color: ' + (tagItem.color ? tagItem.color + '20' : '#e2e8f0') +
                                        '; color: ' + (tagItem.color || '#475569') + '; border: 1px solid ' + (tagItem
                                            .color ? tagItem.color + '40' : '#cbd5e1')"
                                        x-text="tagItem.name"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6 sm:py-3.5 shrink-0">
                        <button type="button" x-on:click="closeDetailModal()"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition">
                            Tutup
                        </button>
                        <button type="button" x-on:click="closeDetailModal(); openDuplicateModal(selectedDevice)"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 0 1 2.25 20.625V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v9.25c0 .621-.504 1.125-1.125 1.125Z" />
                            </svg>
                            Duplicate Device
                        </button>
                        <a :href="'/computers/' + selectedDevice.id + '/edit'"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                            Edit Device
                        </a>
                    </div>
                </div>
            </template>
        </div>

        {{-- Duplicate Device Modal --}}
        <div x-show="duplicateModalOpen" x-cloak
            class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm overflow-y-auto"
            x-on:keydown.escape.window="closeDuplicateModal()" x-transition.opacity.duration.200ms>
            <template x-if="duplicateDevice">
                <div class="w-full max-w-xl max-h-[calc(100dvh-1.5rem)] sm:max-h-[calc(100dvh-2rem)] flex flex-col my-auto overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200"
                    x-transition.scale.origin.center.duration.200ms x-on:click.outside="closeDuplicateModal()">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6 sm:py-4 shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 shrink-0 font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 0 1 2.25 20.625V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v9.25c0 .621-.504 1.125-1.125 1.125Z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-bold text-base text-slate-900 leading-tight">Duplicate / Salin Device</h3>
                                <p class="text-xs text-slate-500">Menyalin dari: <span class="font-semibold text-slate-700" x-text="duplicateDevice.name"></span> (<span class="font-mono" x-text="duplicateDevice.ip_address"></span>)</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="closeDuplicateModal()"
                            class="rounded-lg p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Form Body -->
                    <form method="POST" action="{{ route('computers.store') }}" class="flex flex-col flex-1 min-h-0">
                        @csrf
                        <input type="hidden" name="duplicate_from_id" :value="duplicateForm.duplicate_from_id">

                        <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                            <!-- Helper Banner -->
                            <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-900 text-xs flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <div>
                                    <span class="font-bold">Info Duplikasi:</span> Kredensial VNC/SSH, port, OS, lokasi, dan tag disalin otomatis. Silakan masukkan nama dan IP address baru.
                                </div>
                            </div>

                            <!-- Name & IP Inputs -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="dup_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Nama Perangkat Baru <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="dup_name" name="name" x-model="duplicateForm.name" required
                                        class="w-full rounded-xl border-slate-300 text-sm shadow-xs focus:border-[#00828c] focus:ring-[#00828c]"
                                        placeholder="Nama Perangkat">
                                </div>

                                <div>
                                    <label for="dup_ip" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Alamat IP Baru <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="dup_ip" name="ip_address" x-model="duplicateForm.ip_address" required
                                        class="w-full rounded-xl border-slate-300 text-sm font-mono shadow-xs focus:border-[#00828c] focus:ring-[#00828c]"
                                        placeholder="192.168.1.100">
                                </div>
                            </div>

                            <!-- OS & Location -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="dup_os" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Sistem Operasi
                                    </label>
                                    <select id="dup_os" name="os_type" x-model="duplicateForm.os_type"
                                        class="w-full rounded-xl border-slate-300 text-sm shadow-xs focus:border-[#00828c] focus:ring-[#00828c]">
                                        <option value="windows">Windows</option>
                                        <option value="linux">Linux</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="dup_location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Lokasi
                                    </label>
                                    <input type="text" id="dup_location" name="location" x-model="duplicateForm.location"
                                        class="w-full rounded-xl border-slate-300 text-sm shadow-xs focus:border-[#00828c] focus:ring-[#00828c]"
                                        placeholder="Lab / Ruangan">
                                </div>
                            </div>

                            <!-- Ports & SSH User -->
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label for="dup_vnc_port" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Port VNC
                                    </label>
                                    <input type="number" id="dup_vnc_port" name="vnc_port" x-model="duplicateForm.vnc_port" required min="1" max="65535"
                                        class="w-full rounded-xl border-slate-300 text-xs font-mono shadow-xs focus:border-[#00828c] focus:ring-[#00828c]">
                                </div>
                                <div>
                                    <label for="dup_ssh_port" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Port SSH
                                    </label>
                                    <input type="number" id="dup_ssh_port" name="ssh_port" x-model="duplicateForm.ssh_port" min="1" max="65535"
                                        class="w-full rounded-xl border-slate-300 text-xs font-mono shadow-xs focus:border-[#00828c] focus:ring-[#00828c]">
                                </div>
                                <div>
                                    <label for="dup_ssh_user" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        SSH User
                                    </label>
                                    <input type="text" id="dup_ssh_user" name="ssh_user" x-model="duplicateForm.ssh_user"
                                        class="w-full rounded-xl border-slate-300 text-xs font-mono shadow-xs focus:border-[#00828c] focus:ring-[#00828c]">
                                </div>
                            </div>

                            <!-- Tags selection -->
                            @if (isset($allTags) && $allTags->isNotEmpty())
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Tag Perangkat <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                        @foreach ($allTags as $tagItem)
                                            <label class="inline-flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:border-[#00828c]/50 transition cursor-pointer text-xs">
                                                <input type="checkbox" name="tag_ids[]" value="{{ $tagItem->id }}"
                                                    x-model="duplicateForm.tag_ids"
                                                    class="rounded border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                                <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $tagItem->color ?: '#00828c' }}"></span>
                                                <span class="truncate font-medium text-slate-700">#{{ $tagItem->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Credentials Copy Checkboxes -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="copy_vnc_password" value="1" x-model="duplicateForm.copy_vnc_password"
                                        class="rounded border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                    <span class="text-xs font-semibold text-slate-800">Salin Password VNC dari perangkat asal</span>
                                </label>
                                <br>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="copy_ssh_password" value="1" x-model="duplicateForm.copy_ssh_password"
                                        class="rounded border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                    <span class="text-xs font-semibold text-slate-800">Salin Password SSH dari perangkat asal</span>
                                </label>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="dup_desc" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Deskripsi / Catatan (Opsional)
                                </label>
                                <textarea id="dup_desc" name="description" x-model="duplicateForm.description" rows="2"
                                    class="w-full rounded-xl border-slate-300 text-xs shadow-xs focus:border-[#00828c] focus:ring-[#00828c]"
                                    placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-6 sm:py-3.5 shrink-0">
                            <a :href="'/computers/create?duplicate_from=' + duplicateForm.duplicate_from_id"
                                class="text-xs font-semibold text-[#00828c] hover:underline flex items-center gap-1">
                                Buka Form Lengkap →
                            </a>

                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="closeDuplicateModal()"
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Simpan Duplicate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </template>
        </div>

        {{-- Export / Backup Devices Modal --}}
        <div x-show="exportModalOpen" x-cloak
            class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm overflow-y-auto"
            x-on:keydown.escape.window="closeExportModal()" x-transition.opacity.duration.200ms>
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200"
                x-transition.scale.origin.center.duration.200ms x-on:click.outside="closeExportModal()">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3.5 sm:px-6 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 font-bold shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-base text-slate-900 leading-tight">Export & Backup List Device</h3>
                            <p class="text-xs text-slate-500">Download data perangkat untuk backup / migrasi portal</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeExportModal()"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="GET" action="{{ route('computers.export') }}" x-on:submit="closeExportModal()" class="p-4 sm:p-6 space-y-4">
                    <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-950 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 1 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <div>
                            <span class="font-bold">Info Backup:</span> File backup (JSON/CSV) ini berisi seluruh daftar perangkat, IP, port, lokasi, deskripsi, tag, dan kredensial VNC/SSH.
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Format File</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col p-3.5 rounded-xl border-2 border-slate-200 bg-white hover:border-[#00828c] cursor-pointer transition">
                                <input type="radio" name="format" value="json" checked class="sr-only peer">
                                <span class="text-xs font-bold text-slate-900 peer-checked:text-[#00828c]">JSON Backup (.json)</span>
                                <span class="text-[11px] text-slate-500 mt-1">Sangat Direkomendasikan untuk Migrasi Portal NodeHub</span>
                            </label>
                            <label class="relative flex flex-col p-3.5 rounded-xl border-2 border-slate-200 bg-white hover:border-[#00828c] cursor-pointer transition">
                                <input type="radio" name="format" value="csv" class="sr-only peer">
                                <span class="text-xs font-bold text-slate-900 peer-checked:text-[#00828c]">CSV Spreadsheet (.csv)</span>
                                <span class="text-[11px] text-slate-500 mt-1">Untuk dibuka di Microsoft Excel atau Google Sheets</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="include_passwords" value="1" checked
                                class="rounded border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                            <span class="text-xs font-semibold text-slate-800">Sertakan Password VNC & SSH dalam Backup</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mt-1 ml-6">
                            Jika dicentang, password VNC/SSH akan ikut disimpan di file backup agar migrasi ke server baru bisa langsung terhubung tanpa memasukkan password kembali.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" x-on:click="closeExportModal()"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download Backup File
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Import / Restore Devices Modal --}}
        <div x-show="importModalOpen" x-cloak
            class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm overflow-y-auto"
            x-on:keydown.escape.window="closeImportModal()" x-transition.opacity.duration.200ms>
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200"
                x-transition.scale.origin.center.duration.200ms x-on:click.outside="closeImportModal()">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3.5 sm:px-6 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00828c]/10 text-[#00828c] border border-[#00828c]/20 font-bold shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-base text-slate-900 leading-tight">Restore & Import List Device</h3>
                            <p class="text-xs text-slate-500">Upload file backup JSON/CSV untuk import masal</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeImportModal()"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('computers.import') }}" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4">
                    @csrf

                    <div>
                        <label for="backup_file_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pilih File Backup (.json / .csv) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="backup_file_input" name="backup_file" required accept=".json,.csv,.txt"
                            class="w-full text-xs text-slate-700 border border-slate-300 rounded-xl cursor-pointer bg-slate-50 p-2 focus:outline-none focus:border-[#00828c]">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Mendukung file JSON backup hasil export NodeHub atau file spreadsheet CSV.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Penanganan Duplikat (Berdasarkan IP Address)</label>
                        <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="duplicate_action" value="skip" checked
                                    class="mt-0.5 rounded-full border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Lewati Duplikat (Recommended)</span>
                                    <p class="text-[11px] text-slate-500">Jika IP Address sudah terdaftar, perangkat tersebut tidak akan diubah.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="duplicate_action" value="update"
                                    class="mt-0.5 rounded-full border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Perbarui Data Perangkat Ada</span>
                                    <p class="text-[11px] text-slate-500">Jika IP Address cocok, perbarui nama, port, lokasi, dan password dari file backup.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="radio" name="duplicate_action" value="add"
                                    class="mt-0.5 rounded-full border-slate-300 text-[#00828c] focus:ring-[#00828c]">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Tambahkan Semua Sebagai Baru</span>
                                    <p class="text-[11px] text-slate-500">Import semua perangkat tanpa mengecek IP duplikat.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" x-on:click="closeImportModal()"
                            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00828c] hover:bg-[#006e76] text-white font-bold text-xs shadow-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Mulai Restore / Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($allDevices as $computer)
        <x-modal name="confirm-computer-deletion-{{ is_array($computer) ? $computer['id'] : $computer->id }}"
            maxWidth="md" :show="false" focusable>
            <form method="post"
                action="{{ route('computers.destroy', is_array($computer) ? $computer['id'] : $computer->id) }}"
                class="p-6">
                @csrf
                @method('delete')

                <div class="flex items-start gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 shadow-xs">
                        <svg class="h-5.5 w-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-bold text-gray-900 leading-snug">
                            {{ __('Konfirmasi Hapus Perangkat') }}
                        </h2>
                        <p class="mt-0.5 text-xs text-rose-600 font-medium">
                            {{ __('Tindakan ini permanen dan tidak dapat dibatalkan.') }}
                        </p>
                    </div>
                </div>

                {{-- Device Card Preview --}}
                <div class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                </svg>
                            </span>
                            <span
                                class="font-semibold text-sm text-slate-900 truncate">{{ is_array($computer) ? $computer['name'] : $computer->name }}</span>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shrink-0 {{ (is_array($computer) ? $computer['os_type'] : $computer->os_type) === 'windows' ? 'bg-blue-100/80 text-blue-700' : 'bg-orange-100/80 text-orange-700' }}">
                            {{ ucfirst(is_array($computer) ? $computer['os_type'] : $computer->os_type) }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-200/60">
                        <span
                            class="font-mono text-slate-600">{{ is_array($computer) ? $computer['ip_address'] : $computer->ip_address }}:{{ is_array($computer) ? $computer['vnc_port'] : $computer->vnc_port }}</span>
                        @if (is_array($computer) ? $computer['location'] : $computer->location)
                            <span
                                class="truncate max-w-[150px] text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200 text-[11px]">
                                {{ is_array($computer) ? $computer['location'] : $computer->location }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
                    <button type="button" x-on:click="$dispatch('close')"
                        class="w-full sm:w-auto px-4 py-2.5 sm:py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-100 font-semibold text-xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-slate-300 text-center">
                        {{ __('Batal') }}
                    </button>

                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-xl text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 font-semibold text-xs shadow-xs shadow-rose-200 transition duration-150 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        {{ __('Ya, Hapus Device') }}
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-app-layout>
