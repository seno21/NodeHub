<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('actions.index') }}"
                    class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        {{ __('Create Remote Action') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Konfigurasi nama, ikon, perintah SSH, dan pilih perangkat target') }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $computersJson = $computers
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'ip_address' => $c->ip_address,
                    'os_type' => $c->os_type,
                    'tags' => array_values(array_filter(array_map('trim', explode(',', $c->tags ?? '')))),
                ];
            })
            ->values();
    @endphp

    <div class="py-8" x-data="createActionForm({{ json_encode($computersJson) }})">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs p-8">

                <form method="POST" action="{{ route('actions.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Left Column: Action Details & Command --}}
                        <div class="space-y-5">
                            <h3
                                class="font-bold text-sm uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-2">
                                1. Informasi & Perintah Action
                            </h3>

                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Nama Action')" />
                                <x-text-input id="name" name="name" type="text" value="{{ old('name') }}"
                                    required class="mt-1.5 block w-full rounded-xl text-xs py-2.5"
                                    placeholder="Refresh Firefox Displays / Restart Database VM" />
                                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                            </div>

                            {{-- Iconify Logo Input --}}
                            <div>
                                <x-input-label for="icon" :value="__('Logo / Iconify Name (Paste dari Iconify)')" />
                                <div class="flex items-center gap-2 mt-1.5">
                                    <div
                                        class="p-2.5 rounded-xl border border-gray-200 bg-slate-50 flex items-center justify-center shrink-0 w-11 h-11 shadow-xs">
                                        <iconify-icon :icon="iconInput || 'lucide:terminal'"
                                            class="text-2xl text-[#00828c]"></iconify-icon>
                                    </div>
                                    <x-text-input id="icon" name="icon" type="text" x-model="iconInput"
                                        required class="flex-1 rounded-xl text-xs font-mono py-2.5"
                                        placeholder="lucide:refresh-cw, tabler:reload, mdi:power, lucide:rocket" />
                                </div>
                                <p class="mt-1.5 text-[11px] text-gray-500">
                                    Cari & paste nama ikon dari <a href="https://icon-sets.iconify.design/"
                                        target="_blank" class="text-[#00828c] underline font-semibold">Iconify</a>
                                    (<code>lucide:refresh-cw</code>, <code>tabler:reload</code>,
                                    <code>mdi:rocket-launch</code>).
                                </p>
                                <x-input-error :messages="$errors->get('icon')" class="mt-1.5" />
                            </div>

                            {{-- Description --}}
                            <div>
                                <x-input-label for="description" :value="__('Deskripsi Singkat')" />
                                <x-text-input id="description" name="description" type="text"
                                    value="{{ old('description') }}"
                                    class="mt-1.5 block w-full rounded-xl text-xs py-2.5"
                                    placeholder="Keterangan singkat kegunaan aksi..." />
                                <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                            </div>

                            {{-- Command --}}
                            <div>
                                <x-input-label for="command" :value="__('Perintah yang Akan Dieksekusi (Shell Command)')" />
                                <textarea id="command" name="command" rows="3" required
                                    class="mt-1.5 block w-full rounded-xl border-gray-200 text-xs font-mono focus:border-blue-500 focus:ring-blue-500 shadow-xs"
                                    placeholder="DISPLAY=:0 xdotool key F5">{{ old('command', 'DISPLAY=:0 xdotool key F5') }}</textarea>
                                <x-input-error :messages="$errors->get('command')" class="mt-1.5" />
                            </div>
                        </div>

                        {{-- Right Column: Searchable Multiple Device Selection --}}
                        <div class="space-y-4 flex flex-col">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <h3 class="font-bold text-sm uppercase tracking-wider text-gray-400">
                                    2. Pilih Perangkat Target
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button type="button" x-on:click="selectAllFilteredDevices()"
                                        class="text-[11px] font-semibold text-[#00828c] hover:underline">
                                        Pilih Semua
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" x-on:click="clearDeviceSelection()"
                                        class="text-[11px] text-gray-500 hover:underline">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            {{-- Search Filter Input --}}
                            <input type="text" x-model="searchQuery"
                                placeholder="Cari nama perangkat, IP, atau tag..."
                                class="w-full rounded-xl border-gray-200 text-xs py-2 focus:border-[#00828c] focus:ring-[#00828c] shadow-xs" />

                            {{-- Device Checkboxes List --}}
                            <div
                                class="flex-1 min-h-[260px] max-h-[340px] overflow-y-auto p-3 rounded-2xl border border-gray-200 bg-slate-50/70 space-y-2">
                                <template x-for="comp in filteredDevices" :key="comp.id">
                                    <label
                                        class="flex items-center justify-between p-2.5 rounded-xl hover:bg-white border border-transparent hover:border-gray-200 transition cursor-pointer text-xs">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="computer_ids[]" :value="comp.id"
                                                x-model="selectedIds"
                                                class="h-4 w-4 rounded border-gray-300 text-[#00828c] focus:ring-[#00828c]" />
                                            <div>
                                                <span class="font-bold text-gray-900" x-text="comp.name"></span>
                                                <span class="text-gray-400 font-mono text-[11px] ml-1"
                                                    x-text="'(' + comp.ip_address + ')'"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <template x-for="t in comp.tags" :key="t">
                                                <span
                                                    class="px-2 py-0.5 rounded-md text-[10px] bg-slate-200 text-slate-700 font-semibold"
                                                    x-text="'#' + t"></span>
                                            </template>
                                        </div>
                                    </label>
                                </template>

                                <p x-show="filteredDevices.length === 0"
                                    class="text-xs text-gray-400 italic text-center py-8">
                                    Tidak ada perangkat yang cocok dengan pencarian.
                                </p>
                            </div>

                            <p class="text-[11px] text-gray-500 font-medium">
                                Terpilih: <strong class="text-[#00828c] font-bold" x-text="selectedIds.length"></strong>
                                dari <span x-text="allDevices.length"></span> perangkat.
                            </p>
                            <x-input-error :messages="$errors->get('computer_ids')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Form Footer --}}
                    <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('actions.index') }}"
                            class="px-5 py-2.5 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                            {{ __('Batal') }}
                        </a>

                        <button type="submit"
                            class="px-6 py-2.5 bg-[#00828c] text-white font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-[#006e76] transition shadow-md shadow-[#00828c]/20 disabled:opacity-50"
                            x-bind:disabled="selectedIds.length === 0">
                            {{ __('Simpan Action') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('createActionForm', (allDevices) => ({
                allDevices: allDevices,
                iconInput: '{{ old('icon', 'lucide:refresh-cw') }}',
                selectedIds: allDevices.map(d => d.id),
                searchQuery: '',

                get filteredDevices() {
                    if (!this.searchQuery.trim()) {
                        return this.allDevices;
                    }
                    const q = this.searchQuery.toLowerCase();
                    return this.allDevices.filter(d => {
                        const matchName = d.name.toLowerCase().includes(q);
                        const matchIp = d.ip_address.toLowerCase().includes(q);
                        const matchTag = d.tags.some(t => t.toLowerCase().includes(q));
                        return matchName || matchIp || matchTag;
                    });
                },

                selectAllFilteredDevices() {
                    const ids = this.filteredDevices.map(d => d.id);
                    this.selectedIds = Array.from(new Set([...this.selectedIds, ...ids]));
                },

                clearDeviceSelection() {
                    this.selectedIds = [];
                }
            }));
        });
    </script>
</x-app-layout>
