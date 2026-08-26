<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-[#00828c]/10 text-[#00828c]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.568 3.15c.677-.07 1.354-.07 2.032 0A12.753 12.753 0 0 1 20.85 12.33a12.753 12.753 0 0 1-9.25 9.25c-.678.07-1.355.07-2.033 0a12.753 12.753 0 0 1-9.25-9.25c-.07-.678-.07-1.355 0-2.033a12.753 12.753 0 0 1 9.25-9.25ZM9.568 3.15l-1.026 3.078M12.33 20.85l1.026-3.078M20.85 12.33l-3.078 1.026M3.15 9.568l3.078-1.026" />
                    </svg>
                </span>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        {{ __('Manajemen Tags Perangkat') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Kelola tag/kategori perangkat untuk kemudahan pengelompokkan dan pencarian remote action') }}
                    </p>
                </div>
            </div>

            <button type="button" x-data x-on:click="$dispatch('open-create-tag-modal')"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#00828c] border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#006e76] active:bg-[#00585f] transition shadow-xs">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Create Tag') }}
            </button>
        </div>
    </x-slot>

    <div class="py-8" x-data="tagDashboard()" x-on:open-create-tag-modal.window="openCreateModal()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash status --}}
            @if (session('status'))
                <div
                    class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Tags Table --}}
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                        {{ __('Daftar Tag Perangkat') }} ({{ $tags->count() }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead>
                            <tr
                                class="bg-slate-50 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th scope="col" class="px-6 py-3.5">Nama Tag</th>
                                <th scope="col" class="px-6 py-3.5">Deskripsi</th>
                                <th scope="col" class="px-6 py-3.5 text-center">Jumlah Perangkat Target</th>
                                <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tags as $tagItem)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <span class="h-3 w-3 rounded-full shrink-0 shadow-xs"
                                                style="background-color: {{ $tagItem->color ?: '#00828c' }}"></span>
                                            <span class="font-bold text-sm text-gray-900">#{{ $tagItem->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $tagItem->description ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                            {{ $tagItem->computers_count }} Perangkat
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                x-on:click="openEditModal({{ json_encode($tagItem) }})"
                                                class="px-3 py-1.5 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200 transition">
                                                Edit
                                            </button>
                                            <button type="button"
                                                x-on:click="confirmDeleteTag({{ json_encode($tagItem) }})"
                                                class="px-3 py-1.5 bg-rose-50 text-rose-600 font-semibold rounded-xl hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-gray-400 text-xs italic">
                                        Belum ada tag yang dibuat. Klik tombol "+ Create Tag" di atas untuk menambahkan
                                        tag baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CREATE / EDIT TAG MODAL FORM --}}
        <div x-show="showFormModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            style="display: none;">
            <div class="bg-white text-gray-900 rounded-3xl max-w-md w-full p-7 shadow-2xl relative text-left"
                x-on:click.outside="closeFormModal()">

                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-2xl bg-[#00828c]/10 text-[#00828c] flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.568 3.15c.677-.07 1.354-.07 2.032 0A12.753 12.753 0 0 1 20.85 12.33a12.753 12.753 0 0 1-9.25 9.25c-.678.07-1.355.07-2.033 0a12.753 12.753 0 0 1-9.25-9.25c-.07-.678-.07-1.355 0-2.033a12.753 12.753 0 0 1 9.25-9.25Z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900"
                                x-text="isEditing ? 'Edit Tag Perangkat' : 'Create Tag Baru'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">Konfigurasi nama tag dan deskripsi</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeFormModal()"
                        class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" x-bind:action="isEditing ? '/tags/' + form.id : '{{ route('tags.store') }}'"
                    class="space-y-4">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <x-input-label for="tag_name" :value="__('Nama Tag')" />
                        <x-text-input id="tag_name" name="name" type="text" x-model="form.name" required
                            class="mt-1.5 block w-full rounded-xl text-xs py-2.5"
                            placeholder="Kasir, Display Utama, Ubuntu" />
                    </div>

                    <div>
                        <x-input-label for="tag_color" :value="__('Warna Aksen Tag')" />
                        <div class="flex items-center gap-3 mt-1.5">
                            <input id="tag_color" name="color" type="color" x-model="form.color"
                                class="h-10 w-14 rounded-xl border border-gray-200 cursor-pointer p-1" />
                            <x-text-input type="text" x-model="form.color"
                                class="flex-1 rounded-xl text-xs font-mono py-2.5" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="tag_description" :value="__('Deskripsi Singkat (Opsional)')" />
                        <x-text-input id="tag_description" name="description" type="text"
                            x-model="form.description" class="mt-1.5 block w-full rounded-xl text-xs py-2.5"
                            placeholder="Perangkat pada area kasir utama..." />
                    </div>

                    <div class="pt-5 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" x-on:click="closeFormModal()"
                            class="px-5 py-2.5 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#00828c] text-white font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-[#006e76] transition shadow-md shadow-[#00828c]/20">
                            <span x-text="isEditing ? 'Update Tag' : 'Simpan Tag'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODERN DELETE CONFIRMATION MODAL --}}
        <div x-show="showDeleteModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
            style="display: none;">
            <div class="bg-white text-gray-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl relative text-center border border-rose-100"
                x-on:click.outside="showDeleteModal = false">
                <div
                    class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 ring-8 ring-rose-50/50 shadow-xs">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </div>

                <h3 class="font-bold text-base text-gray-900 leading-snug">Hapus Tag ini?</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Tag <strong class="text-gray-800" x-text="selectedTag ? '#' + selectedTag.name : ''"></strong>
                    akan dihapus. Perangkat terkait tidak akan terhapus.
                </p>

                <form method="POST" x-bind:action="selectedTag ? '/tags/' + selectedTag.id : ''"
                    class="mt-6 flex items-center justify-center gap-2.5">
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tagDashboard', () => ({
                showFormModal: false,
                showDeleteModal: false,
                isEditing: false,
                selectedTag: null,
                form: {
                    id: null,
                    name: '',
                    color: '#00828c',
                    description: '',
                },

                openCreateModal() {
                    this.isEditing = false;
                    this.form = {
                        id: null,
                        name: '',
                        color: '#00828c',
                        description: '',
                    };
                    this.showFormModal = true;
                },

                openEditModal(tag) {
                    this.isEditing = true;
                    this.form = {
                        id: tag.id,
                        name: tag.name,
                        color: tag.color || '#00828c',
                        description: tag.description || '',
                    };
                    this.showFormModal = true;
                },

                closeFormModal() {
                    this.showFormModal = false;
                },

                confirmDeleteTag(tag) {
                    this.selectedTag = tag;
                    this.showDeleteModal = true;
                }
            }));
        });
    </script>
</x-app-layout>
