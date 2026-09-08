<div x-data="fastConnectModal()" x-on:open-fast-connect.window="openModal()" x-on:keydown.escape.window="closeModal()"
    x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">

    {{-- Backdrop --}}
    <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-on:click="closeModal()"
        class="fixed inset-0 bg-slate-900/65 backdrop-blur-xs transition-opacity"></div>

    {{-- Modal Content --}}
    <div x-show="isOpen" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all w-full max-w-lg border border-slate-100 p-6 sm:p-7 z-10">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3.5">
                <span
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#00828c]/15 to-[#00585f]/20 text-[#00828c] shadow-xs shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Fast Connect VNC</h3>
                    <p class="text-xs text-slate-500 mt-0.5 leading-snug">Koneksi VNC langsung cukup dengan IP address &
                        password.</p>
                </div>
            </div>
            <button type="button" x-on:click="closeModal()"
                class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Error Banner --}}
        <template x-if="errorMessage">
            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800 shadow-2xs"
                x-transition.opacity>
                <svg class="h-5 w-5 shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div class="flex-1 font-medium leading-relaxed" x-text="errorMessage"></div>
                <button type="button" x-on:click="errorMessage = ''" class="text-rose-400 hover:text-rose-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        {{-- Form --}}
        <form x-on:submit.prevent="submitFastConnect()" class="mt-5 space-y-4">
            {{-- IP Address & Port Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {{-- IP Address --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        IP Address / Hostname <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" x-ref="ipInput" x-model="form.ip_address" required
                            placeholder="192.168.1.100"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                    </div>
                </div>

                {{-- Port --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Port VNC
                    </label>
                    <input type="number" x-model="form.vnc_port" placeholder="5900" min="1" max="65535"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                </div>
            </div>

            {{-- VNC Password --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Password VNC (Opsional)
                </label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" x-model="form.vnc_password"
                        placeholder="Password VNC (jika ada)"
                        class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                    <button type="button" x-on:click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                        <template x-if="!showPassword">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </template>
                        <template x-if="showPassword">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

            {{-- OS Selection Pills --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Sistem Operasi Target
                </label>
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" x-on:click="form.os_type = 'linux'"
                        :class="form.os_type === 'linux' ?
                            'bg-[#00828c]/10 border-[#00828c] text-[#00828c] font-bold shadow-2xs' :
                            'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                        class="flex items-center justify-center gap-2 px-3 py-2 border rounded-xl text-xs transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="12" cy="12" r="8.5" stroke="#E95420" stroke-width="2.2" />
                            <circle cx="12" cy="5.5" r="2" fill="#E95420" />
                            <circle cx="6.4" cy="15.25" r="2" fill="#E95420" />
                            <circle cx="17.6" cy="15.25" r="2" fill="#E95420" />
                        </svg>
                        <span>Linux</span>
                    </button>

                    <button type="button" x-on:click="form.os_type = 'windows'"
                        :class="form.os_type === 'windows' ?
                            'bg-[#00828c]/10 border-[#00828c] text-[#00828c] font-bold shadow-2xs' :
                            'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                        class="flex items-center justify-center gap-2 px-3 py-2 border rounded-xl text-xs transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path fill="#0078D4"
                                d="M3 5.55 10.6 4.5v7.05H3V5.55Zm8.75-1.19L21 3v8.55h-9.25V4.36ZM3 12.45h7.6v7.05L3 18.45v-6Zm8.75 0H21V21l-9.25-1.31v-7.24Z" />
                        </svg>
                        <span>Windows</span>
                    </button>
                </div>
            </div>

            {{-- Save to Devices Checkbox Toggle --}}
            <div class="pt-2 border-t border-slate-100">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" x-model="form.save_device"
                        class="rounded-md border-slate-300 text-[#00828c] shadow-xs focus:ring-[#00828c]">
                    <span class="text-xs font-semibold text-slate-700">Simpan juga ke daftar Perangkat (Device
                        List)</span>
                </label>

                {{-- Optional Device Name Input if Save Device is checked --}}
                <div x-show="form.save_device" x-transition.opacity class="mt-3">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        Nama Perangkat (Opsional)
                    </label>
                    <input type="text" x-model="form.device_name" placeholder="Misal: Server Kasir / PC Kantor"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:border-transparent transition">
                </div>
            </div>

            {{-- Footer / Action Buttons --}}
            <div class="mt-6 flex items-center justify-end gap-3 pt-3">
                <button type="button" x-on:click="closeModal()"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Batal
                </button>

                <button type="submit" :disabled="isConnecting || !form.ip_address.trim()"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#00828c] to-[#00585f] hover:from-[#006e76] hover:to-[#00474d] text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="isConnecting">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <template x-if="!isConnecting">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                    </template>
                    <span x-text="isConnecting ? 'Mengecek & Connecting...' : 'Sambungkan VNC'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function fastConnectModal() {
        return {
            isOpen: false,
            isConnecting: false,
            showPassword: false,
            errorMessage: '',
            form: {
                ip_address: '',
                vnc_port: 5900,
                vnc_password: '',
                os_type: 'linux',
                save_device: false,
                device_name: ''
            },
            openModal() {
                this.isOpen = true;
                this.errorMessage = '';
                this.isConnecting = false;
                this.$nextTick(() => {
                    if (this.$refs.ipInput) {
                        this.$refs.ipInput.focus();
                    }
                });
            },
            closeModal() {
                if (this.isConnecting) return;
                this.isOpen = false;
            },
            async submitFastConnect() {
                if (!this.form.ip_address.trim()) return;

                this.isConnecting = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('{{ route('vnc.fastConnect') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    if (response.ok && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        this.errorMessage = data.message ||
                            'Gagal membuka koneksi Fast Connect VNC. Periksa IP address dan port.';
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan jaringan saat mencoba menghubungkan ke VNC target.';
                } finally {
                    this.isConnecting = false;
                }
            }
        };
    }
</script>
