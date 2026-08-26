<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-[#00828c]/10 text-[#00828c]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        {{ __('Remote Actions') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Manajemen otomatisasi perintah SSH & eksekusi aksi multi-perangkat') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('actions.create') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00828c] border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#006e76] active:bg-[#00585f] transition shadow-xs">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Create Action') }}
            </a>
        </div>
    </x-slot>

    @php
        $actionsJson = $actions->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'icon' => $a->icon ?: 'lucide:terminal',
                'description' => $a->description,
                'command' => $a->command,
                'computer_ids' => $a->computers->pluck('id')->all(),
                'computers_count' => $a->computers->count(),
            ];
        })->values();
    @endphp

    <div class="py-8" x-data="remoteActionDashboard({{ json_encode($actionsJson) }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash status --}}
            @if (session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Action Cards Output Grid --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                        {{ __('Daftar Remote Actions') }} (<span x-text="actions.length"></span>)
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" x-show="actions.length > 0">
                    <template x-for="act in actions" :key="act.id">
                        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 flex flex-col justify-between transition hover:shadow-md hover:border-[#00828c]/40">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="p-2.5 rounded-2xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center shrink-0">
                                            <iconify-icon :icon="act.icon || 'lucide:terminal'" class="text-2xl"></iconify-icon>
                                        </span>
                                        <div>
                                            <h4 class="font-bold text-base text-gray-900 leading-tight" x-text="act.name"></h4>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 mt-0.5">
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                                </svg>
                                                <span x-text="act.computers_count + ' Perangkat Target'"></span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Gear / Details Page link for previewing details & edit --}}
                                    <a :href="'/actions/' + act.id"
                                        class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-slate-100 transition" title="Pengaturan & Crosscheck Action">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                </div>

                                <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed mb-3" x-text="act.description || 'Tidak ada deskripsi'"></p>

                                <div class="bg-[#0d1117] p-2.5 rounded-xl border border-slate-800 font-mono text-[11px] text-slate-100 truncate mb-4">
                                    <span class="text-emerald-500 font-bold select-none">$ </span><span x-text="act.command"></span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                                <a :href="'/actions/' + act.id + '/edit'" class="text-[11px] text-slate-500 hover:text-[#00828c] font-semibold transition">
                                    Edit Action
                                </a>

                                <button type="button" x-on:click="executeRemoteAction(act)"
                                    x-bind:disabled="executingActionId === act.id"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00828c] hover:bg-[#006e76] active:bg-[#00585f] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-xs disabled:opacity-50">
                                    <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24" x-show="executingActionId !== act.id">
                                        <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                    </svg>
                                    <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="executingActionId === act.id" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <span>Exec</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty state --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-8 text-center" x-show="actions.length === 0">
                    <span class="p-3 rounded-full bg-[#00828c]/10 text-[#00828c] inline-block mb-3">
                        <iconify-icon icon="lucide:terminal" class="text-3xl"></iconify-icon>
                    </span>
                    <h3 class="font-bold text-base text-gray-900 mb-1">Belum Ada Remote Action</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mb-4">
                        Buat aksi remote baru untuk menjalankan perintah SSH seperti refresh browser, restart aplikasi, atau reboot perangkat secara otomatis.
                    </p>
                    <a href="{{ route('actions.create') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00828c] text-white text-xs font-semibold rounded-xl hover:bg-[#006e76] transition">
                        + Buat Action Pertama
                    </a>
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

                <div id="terminal-container" class="p-4 h-96 sm:h-[450px] overflow-y-auto font-mono text-xs leading-6 space-y-1 bg-[#0d1117]">
                    <template x-for="(log, i) in logs" :key="'log-' + i">
                        <div class="flex items-start gap-2 border-b border-slate-900/50 pb-1">
                            <span class="text-slate-500 shrink-0 select-none text-[11px]" x-text="'[' + log.time + ']'"></span>
                            <span x-bind:class="log.cls" x-text="log.text" class="flex-1 whitespace-pre-wrap"></span>
                        </div>
                    </template>
                    <p x-show="logs.length === 0" class="text-gray-600 italic py-6 text-center">
                        Belum ada aksi yang dieksekusi. Klik tombol "Exec" pada salah satu card action di atas untuk memulai eksekusi.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('remoteActionDashboard', (actions) => ({
                actions: actions,
                executingActionId: null,
                logs: [],

                addLog(text, cls = 'text-gray-300') {
                    const time = new Date().toLocaleTimeString();
                    this.logs.push({ time, text, cls });
                    this.$nextTick(() => {
                        const el = document.getElementById('terminal-container');
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
                    link.download = `remote-execution-log-${dateStamp}.txt`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                },

                exportMd() {
                    if (this.logs.length === 0) return;
                    const dateStamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
                    const timeNow = new Date().toLocaleString();
                    let md = `# Remote Execution Log Report\n\n`;
                    md += `- **Waktu Export:** ${timeNow}\n`;
                    md += `- **Total Log Entries:** ${this.logs.length}\n\n`;
                    md += `## Terminal Output Log\n\n\`\`\`text\n`;
                    md += this.logs.map(l => `[${l.time}] ${l.text}`).join('\n');
                    md += `\n\`\`\`\n`;
                    const blob = new Blob([md], { type: 'text/markdown;charset=utf-8' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `remote-execution-log-${dateStamp}.md`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                },

                async executeRemoteAction(act) {
                    this.executingActionId = act.id;
                    this.addLog(`=== MEMULAI EKSEKUSI ACTION: "${act.name}" ===`, 'text-cyan-400 font-bold');
                    this.addLog(`[STEP 1/2] Membaca & Mengecek Koneksi SSH ke ${act.computers_count} perangkat target...`, 'text-blue-400 font-bold');

                    try {
                        const response = await fetch(`/actions/${act.id}/execute`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                computer_ids: act.computer_ids
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
                        this.executingActionId = null;
                    }
                }
            }));
        });
    </script>
</x-app-layout>
