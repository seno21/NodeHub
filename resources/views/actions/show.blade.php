<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('actions.index') }}" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        {{ __('Detail & Crosscheck Action') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Preview rincian perintah SSH dan perangkat target sebelum eksekusi') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2" x-data="{ showDeleteModal: false }">
                <a href="{{ route('actions.edit', $action) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl font-semibold text-xs text-slate-700 hover:bg-slate-200 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    {{ __('Edit Action') }}
                </a>

                <button type="button" x-on:click="showDeleteModal = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl font-semibold text-xs hover:bg-rose-100 transition shadow-2xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    {{ __('Hapus') }}
                </button>

                {{-- Modern Delete Confirmation Modal --}}
                <div x-show="showDeleteModal" x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
                    <div class="bg-white text-gray-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl relative text-center border border-rose-100"
                        x-on:click.outside="showDeleteModal = false">
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 ring-8 ring-rose-50/50 shadow-xs">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>

                        <h3 class="font-bold text-base text-gray-900 leading-snug">Hapus Action ini?</h3>
                        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                            Tindakan ini tidak dapat dibatalkan. Konfigurasi aksi <strong class="text-gray-800">"{{ $action->name }}"</strong> akan dihapus secara permanen.
                        </p>

                        <form method="POST" action="{{ route('actions.destroy', $action) }}" class="mt-6 flex items-center justify-center gap-2.5">
                            @csrf
                            @method('delete')
                            <button type="button" x-on:click="showDeleteModal = false"
                                class="w-1/2 py-2.5 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-md shadow-rose-500/20">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $actionJson = [
            'id' => $action->id,
            'name' => $action->name,
            'icon' => $action->icon ?: 'lucide:terminal',
            'command' => $action->command,
            'computer_ids' => $action->computers->pluck('id')->all(),
        ];
    @endphp

    <div class="py-8" x-data="showActionView({{ json_encode($actionJson) }})">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Action Info Card --}}
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs p-7">
                <div class="flex items-start gap-4 mb-6">
                    <span class="p-3.5 rounded-2xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center shrink-0">
                        <iconify-icon icon="{{ $action->icon ?: 'lucide:terminal' }}" class="text-3xl"></iconify-icon>
                    </span>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 leading-snug">{{ $action->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $action->description ?: 'Tidak ada deskripsi singkat' }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- SSH Command Box with crisp light text --}}
                    <div>
                        <span class="font-bold text-gray-500 uppercase tracking-wider text-[11px] block mb-2">
                            Perintah Exec SSH:
                        </span>
                        <div class="bg-[#0d1117] border border-slate-800 p-4 rounded-2xl font-mono text-xs overflow-x-auto flex items-center gap-3 shadow-inner">
                            <span class="text-emerald-500 font-bold select-none">$</span>
                            <code class="text-slate-100 font-semibold leading-relaxed">{{ $action->command }}</code>
                        </div>
                    </div>

                    {{-- Target Computers List Table --}}
                    <div>
                        <span class="font-bold text-gray-500 uppercase tracking-wider text-[11px] block mb-2">
                            Daftar Perangkat Target ({{ $action->computers->count() }} Perangkat):
                        </span>

                        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-2xs">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                        <th scope="col" class="px-5 py-3">Nama Perangkat</th>
                                        <th scope="col" class="px-5 py-3">Kredensial SSH</th>
                                        <th scope="col" class="px-5 py-3">Tag Perangkat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($action->computers as $comp)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="px-5 py-3.5 font-bold text-gray-900">
                                                {{ $comp->name }}
                                            </td>
                                            <td class="px-5 py-3.5 font-mono text-slate-700">
                                                <span class="bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                                    {{ $comp->ssh_user ?: 'xubuntu' }}@<span>{{ $comp->ip_address }}</span>:{{ $comp->ssh_port ?: 22 }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                @if ($comp->tags)
                                                    <div class="flex flex-wrap items-center gap-1">
                                                        @foreach (array_filter(array_map('trim', explode(',', $comp->tags))) as $tag)
                                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-semibold text-[10px]">
                                                                #{{ $tag }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Trigger Exec Button --}}
                <div class="pt-6 mt-6 border-t border-gray-100 flex items-center justify-end">
                    <button type="button" x-on:click="executeRemoteAction()"
                        x-bind:disabled="executing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-[#00828c] hover:bg-[#006e76] active:bg-[#00585f] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-md shadow-[#00828c]/20 disabled:opacity-50">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24" x-show="!executing">
                            <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                        </svg>
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="executing" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Jalankan Action Sekarang (Exec)</span>
                    </button>
                </div>
            </div>

            {{-- Realtime Terminal Log Console --}}
            <div class="bg-[#0d1117] rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-[#161b22] border-b border-slate-800 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-red-500"></span>
                        <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                        <span class="h-3 w-3 rounded-full bg-green-500"></span>
                        <span class="ml-2 font-mono text-xs text-gray-400 font-semibold">
                            Realtime Terminal Execution Log
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" x-on:click="exportTxt()" x-show="logs.length > 0" x-cloak
                            class="inline-flex items-center gap-1.5 text-xs text-emerald-400 hover:text-emerald-300 font-mono transition bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-800/80 px-3 py-1 rounded-lg">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            <span>Export .TXT</span>
                        </button>
                        <button type="button" x-on:click="exportMd()" x-show="logs.length > 0" x-cloak
                            class="inline-flex items-center gap-1.5 text-xs text-sky-400 hover:text-sky-300 font-mono transition bg-sky-950/60 hover:bg-sky-900/80 border border-sky-800/80 px-3 py-1 rounded-lg">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Export .MD</span>
                        </button>
                        <button type="button" x-on:click="logs = []" x-show="logs.length > 0" x-cloak
                            class="text-xs text-gray-400 hover:text-rose-400 font-mono transition px-2.5 py-1">
                            Clear Logs
                        </button>
                    </div>
                </div>

                <div id="show-terminal-container" class="p-4 h-96 sm:h-[450px] overflow-y-auto font-mono text-xs leading-6 space-y-1 bg-[#0d1117]">
                    <template x-for="(log, i) in logs" :key="'log-' + i">
                        <div class="flex items-start gap-2 border-b border-slate-900/50 pb-1">
                            <span class="text-slate-500 shrink-0 select-none text-[11px]" x-text="'[' + log.time + ']'"></span>
                            <span x-bind:class="log.cls" x-text="log.text" class="flex-1 whitespace-pre-wrap"></span>
                        </div>
                    </template>
                    <p x-show="logs.length === 0" class="text-gray-600 italic py-6 text-center">
                        Klik tombol "Jalankan Action Sekarang (Exec)" di atas untuk melihat log eksekusi realtime.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('showActionView', (action) => ({
                action: action,
                executing: false,
                logs: [],

                addLog(text, cls = 'text-gray-300') {
                    const time = new Date().toLocaleTimeString();
                    this.logs.push({ time, text, cls });
                    this.$nextTick(() => {
                        const el = document.getElementById('show-terminal-container');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },

                exportTxt() {
                    if (this.logs.length === 0) return;
                    const dateStamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
                    const content = this.logs.map(l => `[${l.time}] ${l.text}`).join('\n');
                    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `remote-action-${this.action.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-${dateStamp}.txt`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                },

                exportMd() {
                    if (this.logs.length === 0) return;
                    const dateStamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
                    const timeNow = new Date().toLocaleString();
                    let md = `# Remote Action Execution Log: ${this.action.name}\n\n`;
                    md += `- **Action Name:** ${this.action.name}\n`;
                    md += `- **Command:** \`${this.action.command}\`\n`;
                    md += `- **Waktu Export:** ${timeNow}\n`;
                    md += `- **Total Log Entries:** ${this.logs.length}\n\n`;
                    md += `## Terminal Output Log\n\n\`\`\`text\n`;
                    md += this.logs.map(l => `[${l.time}] ${l.text}`).join('\n');
                    md += `\n\`\`\`\n`;
                    const blob = new Blob([md], { type: 'text/markdown;charset=utf-8' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `remote-action-${this.action.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}-${dateStamp}.md`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                },

                async executeRemoteAction() {
                    this.executing = true;
                    this.addLog(`=== MEMULAI EKSEKUSI ACTION: "${this.action.name}" ===`, 'text-cyan-400 font-bold');
                    this.addLog(`[STEP 1/2] Membaca & Mengecek Koneksi SSH ke ${this.action.computer_ids.length} perangkat target...`, 'text-blue-400 font-bold');

                    try {
                        const response = await fetch(`/actions/${this.action.id}/execute`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                computer_ids: this.action.computer_ids
                            })
                        });

                        const data = await response.json();

                        if (data.results) {
                            Object.values(data.results).forEach(res => {
                                // Step 1: Pre-flight SSH Connection Check
                                if (res.ssh_check) {
                                    if (res.ssh_check.success) {
                                        this.addLog(`[SSH CHECK SUKSES] ${res.computer_name}: ${res.ssh_check.message}`, 'text-emerald-400 font-medium');
                                    } else {
                                        this.addLog(`[SSH CHECK GAGAL] ${res.computer_name}: ${res.ssh_check.message}`, 'text-rose-400 font-bold');
                                    }
                                }

                                // Step 2: Custom Script Execution (only runs if SSH check passed)
                                if (res.execution) {
                                    if (res.execution.success) {
                                        this.addLog(`[SCRIPT EXEC SUKSES] ${res.computer_name}: ${res.execution.message} -> ${res.execution.output}`, 'text-emerald-400 font-bold');
                                    } else {
                                        this.addLog(`[SCRIPT EXEC GAGAL] ${res.computer_name}: ${res.execution.message}`, 'text-rose-400 font-bold');
                                    }
                                }
                            });

                            this.addLog(`Hasil Eksekusi: ${data.success_count} SUKSES, ${data.fail_count} GAGAL (Total ${data.total} Perangkat).`, 'text-amber-400 font-bold');
                        }
                    } catch (e) {
                        this.addLog(`Error eksekusi: ${e.message}`, 'text-rose-500 font-bold');
                    } finally {
                        this.executing = false;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
