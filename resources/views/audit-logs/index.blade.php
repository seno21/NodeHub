<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" x-data>
            <div class="flex items-center gap-3">
                <span class="p-2.5 rounded-xl bg-[#00828c]/10 text-[#00828c] shrink-0 shadow-2xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        {{ __('Audit Logs') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Riwayat seluruh aktivitas sistem dengan retensi otomatis :days hari', ['days' => $retentionDays]) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button type="button" @click="$dispatch('open-prune-modal')"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200/80 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700 text-gray-700 rounded-xl font-semibold text-xs transition shadow-2xs">
                    <svg class="h-4 w-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>{{ __('Bersihkan Log > :days Hari', ['days' => $retentionDays]) }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8"
         x-data="{ selectedLog: null, modalOpen: false, pruneModalOpen: false }"
         x-on:open-prune-modal.window="pruneModalOpen = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Status Notification Banner -->
            @if (session('status'))
                <div class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 text-xs font-semibold text-emerald-950 shadow-2xs"
                     x-data="{ show: true }" x-show="show" x-transition.opacity.duration.400ms x-init="setTimeout(() => show = false, 7000)">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        <span>{{ session('status') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center font-bold shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Total Audit Log</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalLogsCount) }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Log Hari Ini</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($todayLogsCount) }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Masa Retensi</p>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mt-0.5">{{ $retentionDays }} Hari</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-gray-500">Data Tertua</p>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-800 mt-0.5 truncate">
                            {{ $oldestLogDate instanceof \DateTimeInterface ? $oldestLogDate->format('d M Y') : ($oldestLogDate ? \Illuminate\Support\Carbon::parse($oldestLogDate)->format('d M Y') : '-') }}
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-2xs">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                    <!-- Search input -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Cari Kata Kunci') }}</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari deskripsi, event, IP, pengguna..."
                                   class="w-full rounded-xl border-gray-200 text-xs sm:text-sm pl-9 pr-3 py-2 focus:border-[#00828c] focus:ring-[#00828c]">
                            <svg class="h-4 w-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Category filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Kategori Event') }}</label>
                        <select name="category" class="w-full rounded-xl border-gray-200 text-xs sm:text-sm py-2 focus:border-[#00828c] focus:ring-[#00828c]">
                            <option value="all">{{ __('Semua Kategori') }}</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Pengguna') }}</label>
                        <select name="user_id" class="w-full rounded-xl border-gray-200 text-xs sm:text-sm py-2 focus:border-[#00828c] focus:ring-[#00828c]">
                            <option value="">{{ __('Semua Pengguna') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="flex-1 bg-[#00828c] hover:bg-[#006e76] text-white text-xs sm:text-sm font-semibold py-2 px-4 rounded-xl transition shadow-2xs flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>{{ __('Filter') }}</span>
                        </button>
                        @if(request()->anyFilled(['search', 'category', 'user_id', 'date_from', 'date_to']))
                            <a href="{{ route('audit-logs.index') }}"
                               class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
                               title="Reset Filter">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Mobile View Cards (< 640px) -->
            <div class="block sm:hidden space-y-3">
                @forelse($logs as $log)
                    @php
                        $badgeColor = match(explode('.', $log->event)[0] ?? '') {
                            'auth' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'vnc' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'computer' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'action' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'tag' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                        };
                    @endphp
                    <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeColor }}">
                                {{ $log->event }}
                            </span>
                            <span class="text-[11px] text-gray-400 font-medium">
                                {{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }}
                            </span>
                        </div>

                        <p class="text-xs font-semibold text-gray-800 leading-snug">
                            {{ $log->description }}
                        </p>

                        <div class="flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <span class="h-5 w-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-[10px]">
                                    {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                </span>
                                <span>{{ $log->user_name ?? 'System' }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="font-mono text-gray-400">{{ $log->ip_address ?? '-' }}</span>
                                @if(!empty($log->properties))
                                    <button type="button"
                                            @click="selectedLog = {{ json_encode($log) }}; modalOpen = true"
                                            class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-[#00828c] bg-teal-50 hover:bg-teal-100 transition border border-teal-200/60">
                                        JSON
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-8 text-center text-gray-400 border border-gray-200">
                        <svg class="h-10 w-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xs font-medium">{{ __('Tidak ada audit log yang ditemukan.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Desktop & Tablet Data Table (>= 640px) -->
            <div class="hidden sm:block bg-white rounded-2xl border border-gray-200/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3.5">Waktu</th>
                                <th class="px-5 py-3.5">Pengguna</th>
                                <th class="px-5 py-3.5">Event</th>
                                <th class="px-5 py-3.5">Deskripsi Aktivitas</th>
                                <th class="px-5 py-3.5">IP Address</th>
                                <th class="px-5 py-3.5 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-medium text-gray-500">
                                        {{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="h-7 w-7 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                            </span>
                                            <span class="font-medium text-gray-800 text-xs">{{ $log->user_name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @php
                                            $badgeColor = match(explode('.', $log->event)[0] ?? '') {
                                                'auth' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                'vnc' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'computer' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'action' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'tag' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                default => 'bg-gray-100 text-gray-800 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeColor }}">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 font-medium text-gray-700 text-xs max-w-md truncate" title="{{ $log->description }}">
                                        {{ $log->description }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-500">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        @if(!empty($log->properties))
                                            <button type="button"
                                                    @click="selectedLog = {{ json_encode($log) }}; modalOpen = true"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-[#00828c] bg-teal-50 hover:bg-teal-100 transition border border-teal-200/60">
                                                {{ __('Payload JSON') }}
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 font-mono">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                        <svg class="h-10 w-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm font-medium">{{ __('Tidak ada audit log yang ditemukan.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

            @if($logs->hasPages())
                <div class="block sm:hidden pt-2">
                    {{ $logs->links() }}
                </div>
            @endif

            <!-- Modal Konfirmasi Pruning Audit Log (Modern Custom Modal) -->
            <div x-show="pruneModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4" aria-labelledby="prune-modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 pb-20 text-center sm:p-0">
                    <div x-show="pruneModalOpen" x-transition.opacity @click="pruneModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

                    <div x-show="pruneModalOpen" x-transition.scale
                         class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle max-w-md w-full border border-gray-200 z-10 p-6 space-y-4">
                        
                        <div class="flex items-start gap-4">
                            <span class="h-11 w-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="prune-modal-title">
                                    Konfirmasi Bersihkan Log Lama
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                    Apakah Anda yakin ingin menghapus log yang berusia lebih dari <strong class="text-gray-800">{{ $retentionDays }} hari</strong>? Log dalam batas retensi akan tetap tersimpan secara aman.
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="pruneModalOpen = false"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition">
                                Batal
                            </button>
                            <form action="{{ route('audit-logs.prune') }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                                    Ya, Bersihkan Log
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Touch-Friendly Payload JSON Modal -->
            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 pb-20 text-center sm:p-0">
                    <div x-show="modalOpen" x-transition.opacity @click="modalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

                    <div x-show="modalOpen" x-transition.scale
                         class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-8 sm:align-middle max-w-lg w-full border border-gray-200 z-10">
                        <div class="bg-slate-900 px-5 py-3.5 flex items-center justify-between border-b border-slate-800">
                            <div class="flex items-center gap-2 text-white">
                                <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                <h3 class="text-xs sm:text-sm font-bold" id="modal-title">
                                    Detail JSON - <span x-text="selectedLog?.event"></span>
                                </h3>
                            </div>
                            <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-white transition p-1">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-4 sm:p-5 bg-slate-950 text-emerald-400 font-mono text-[11px] sm:text-xs overflow-x-auto max-h-[60vh]">
                            <pre x-text="JSON.stringify(selectedLog?.properties, null, 2)"></pre>
                        </div>

                        <div class="bg-slate-50 px-5 py-3 border-t border-gray-200 flex justify-end">
                            <button type="button" @click="modalOpen = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-semibold rounded-xl transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
