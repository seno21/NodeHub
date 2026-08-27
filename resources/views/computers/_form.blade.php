<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="name" :value="__('Nama Perangkat (Device Name)')" />
            <x-text-input id="name" name="name" type="text"
                class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm"
                :value="old('name', $computer?->name)" required autofocus placeholder="{{ __('Kasir') }}" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="ip_address" :value="__('Alamat IP (IP Address)')" />
            <x-text-input id="ip_address" name="ip_address" type="text"
                class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm font-mono"
                :value="old('ip_address', $computer?->ip_address)" required placeholder="192.168.1.100" />
            <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <x-input-label for="vnc_port" :value="__('Port VNC')" />
            <x-text-input id="vnc_port" name="vnc_port" type="number" min="1" max="65535"
                class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm font-mono"
                :value="old('vnc_port', $computer?->vnc_port ?? 5900)" required />
            <x-input-error :messages="$errors->get('vnc_port')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="os_type" :value="__('Sistem Operasi (OS Type)')" />
            <select id="os_type" name="os_type"
                class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm">
                @foreach (App\Models\Computer::OS_TYPES as $os)
                    <option value="{{ $os }}" @selected(old('os_type', $computer?->os_type ?? 'linux') === $os)>
                        {{ ucfirst($os) }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('os_type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="location" :value="__('Lokasi (Location)')" />
            <x-text-input id="location" name="location" type="text"
                class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm"
                :value="old('location', $computer?->location)" placeholder="{{ __('Lab B / Lantai 1') }}" />
            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>
    </div>

    {{-- Tag Picker from Tags Table --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1.5">
                <x-input-label for="tag_ids" :value="__('Pilih Tag Perangkat')" />
                <span class="text-xs font-bold text-red-500">* (Wajib)</span>
            </div>
            <a href="{{ route('tags.index') }}" target="_blank"
                class="text-xs font-semibold text-[#00828c] hover:underline flex items-center gap-1">
                + Kelola Master Tag
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>
        </div>

        @php
            $tagsList = Illuminate\Support\Facades\Schema::hasTable('tags')
                ? $allTags ?? App\Models\Tag::query()->orderBy('name')->get()
                : collect();
            $selectedTagIds = old(
                'tag_ids',
                $computer && Illuminate\Support\Facades\Schema::hasTable('tags')
                    ? $computer->tagsRelation->pluck('id')->all()
                    : [],
            );
        @endphp

        @if ($tagsList->count() > 0)
            <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 bg-slate-50/80 p-4 rounded-2xl border border-slate-200">
                @foreach ($tagsList as $tagItem)
                    <label
                        class="inline-flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 bg-white hover:border-[#00828c]/50 transition cursor-pointer group shadow-2xs">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tagItem->id }}" @checked(in_array($tagItem->id, $selectedTagIds))
                            class="rounded border-gray-300 text-[#00828c] focus:ring-[#00828c]">
                        <span class="h-2.5 w-2.5 rounded-full shrink-0"
                            style="background-color: {{ $tagItem->color ?: '#00828c' }}"></span>
                        <span
                            class="text-xs font-semibold text-gray-700 group-hover:text-gray-900">#{{ $tagItem->name }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <div
                class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between">
                <span>Belum ada Master Tag di database.</span>
                <a href="{{ route('tags.index') }}" class="font-bold text-[#00828c] hover:underline">+ Buat Tag
                    Pertama</a>
            </div>
        @endif
        <x-input-error :messages="$errors->get('tag_ids')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Deskripsi / Catatan (Description)')" />
        <textarea id="description" name="description" rows="3"
            class="mt-1.5 block w-full border-gray-200 focus:border-[#00828c] focus:ring-[#00828c] rounded-xl shadow-sm text-sm placeholder-gray-400"
            placeholder="{{ __('Catatan spesifikasi, keperluan, atau informasi tambahan perangkat...') }}">{{ old('description', $computer?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="vnc_password" :value="__('Password VNC (opsional, untuk auto-login)')" />
        <x-text-input id="vnc_password" name="vnc_password" type="password"
            class="mt-1.5 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm"
            placeholder="{{ $computer?->vnc_password ? '••••••••' : '' }}" autocomplete="new-password" />
        @if ($computer?->vnc_password)
            <p class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1.5 font-medium">
                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('Password VNC sudah tersimpan. Biarkan kosong jika tidak ingin mengubahnya.') }}
            </p>
        @else
            <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                {{ __('Belum ada password VNC tersimpan. Kosongkan jika server VNC target tidak menggunakan autentikasi.') }}
            </p>
        @endif
        <x-input-error :messages="$errors->get('vnc_password')" class="mt-2" />
    </div>

    {{-- SSH Credentials --}}
    <div class="border-t border-gray-200 pt-6 mt-6">
        <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-lg bg-slate-100 text-slate-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 7.5h10.5a2.25 2.25 0 0 1 2.25 2.25v4.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 14.25v-4.5A2.25 2.25 0 0 1 6.75 7.5Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Kredensial SSH (Untuk Remote Action)') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Diperlukan untuk eksekusi perintah SSH remote jarak jauh.') }}
                    </p>
                </div>
            </div>
            @if ($computer?->exists)
                @if ($computer->ssh_password)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0112 2.714z" />
                        </svg>
                        {{ __('SSH Terkonfigurasi') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-800 border border-amber-500/30 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM10.29 3.86l-8.6 14.8A1.5 1.5 0 003 21h18a1.5 1.5 0 001.29-2.34l-8.6-14.8a1.5 1.5 0 00-2.58 0z" />
                        </svg>
                        {{ __('SSH Belum Konfigurasi') }}
                    </span>
                @endif
            @endif
        </div>

        <div class="space-y-4 bg-slate-50/70 p-4 rounded-2xl border border-slate-200/80">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="ssh_user" :value="__('SSH Username')" />
                    <x-text-input id="ssh_user" name="ssh_user" type="text"
                        class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm font-mono"
                        :value="old('ssh_user', $computer?->ssh_user ?? 'xubuntu')" placeholder="xubuntu" />
                    <x-input-error :messages="$errors->get('ssh_user')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="ssh_port" :value="__('SSH Port')" />
                    <x-text-input id="ssh_port" name="ssh_port" type="number" min="1" max="65535"
                        class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm font-mono"
                        :value="old('ssh_port', $computer?->ssh_port ?? 22)" />
                    <x-input-error :messages="$errors->get('ssh_port')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="ssh_password" :value="__('Password SSH')" />
                <x-text-input id="ssh_password" name="ssh_password" type="password"
                    class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#00828c] focus:ring-[#00828c] text-sm"
                    placeholder="{{ $computer?->ssh_password ? '••••••••' : '' }}" autocomplete="new-password" />
                @if ($computer?->ssh_password)
                    <p class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1.5 font-medium">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Password SSH sudah tersimpan. Biarkan kosong jika tidak ingin mengubah password.') }}
                    </p>
                @else
                    <div class="mt-3 p-3.5 rounded-xl bg-gradient-to-r from-amber-500/10 via-amber-50 to-amber-100/50 border border-amber-300/60 text-amber-900 shadow-xs flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 border border-amber-200 text-amber-700">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM10.29 3.86l-8.6 14.8A1.5 1.5 0 003 21h18a1.5 1.5 0 001.29-2.34l-8.6-14.8a1.5 1.5 0 00-2.58 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-amber-900">{{ __('Password SSH Belum Konfigurasi') }}</h4>
                            <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                                {{ __('Password SSH untuk perangkat ini belum pernah diisi. Silakan masukkan password SSH agar fitur Remote Action (eksekusi perintah remote) dapat digunakan.') }}
                            </p>
                        </div>
                    </div>
                @endif
                <x-input-error :messages="$errors->get('ssh_password')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('computers.index') }}"
            class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#00828c] focus:ring-offset-2 transition shadow-sm">
            {{ __('Batal') }}
        </a>

        <x-primary-button class="py-2.5 px-6 rounded-xl bg-[#00828c] hover:bg-[#006e76]">
            {{ $submitText ?? __('Simpan Perangkat') }}
        </x-primary-button>
    </div>
</div>
