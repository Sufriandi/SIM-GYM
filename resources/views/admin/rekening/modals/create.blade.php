{{-- resources/views/admin/rekening/modals/create.blade.php --}}
<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false" @keydown.escape.window="openCreate = false">

    <div class="relative w-full rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        :class="createTab === 'rekening' ? 'max-w-3xl' : 'max-w-4xl'">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Data Rekening</h2>
                <p class="text-sm text-text-muted mt-0.5">Pilih tambah Rekening atau QRIS.</p>
            </div>

            <button type="button" class="rounded-full p-2 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">

            {{-- TABS (segmented) --}}
            <div class="mb-5">
                <div class="inline-flex items-center rounded-full border border-brand-borderSoft bg-brand-shell p-1">
                    <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold transition"
                        :class="createTab === 'rekening'
                            ?
                            'bg-brand-surface-50 text-text-main shadow-sm ring-1 ring-brand-borderSoft' :
                            'text-text-muted hover:text-text-main'"
                        @click="createTab='rekening'">
                        Rekening
                    </button>

                    <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold transition"
                        :class="createTab === 'qris'
                            ?
                            'bg-brand-surface-50 text-text-main shadow-sm' :
                            'text-text-muted hover:text-text-main'"
                        @click="createTab='qris'">
                        QRIS
                    </button>
                </div>
            </div>

            {{-- CREATE REKENING --}}
            <div x-show="createTab==='rekening'">
                <form action="{{ route('admin.info-rekening.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama Bank</label>
                                <input name="nama_bank" type="text" required value="{{ old('nama_bank') }}"
                                    placeholder="Contoh: BRI / Mandiri"
                                    class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Nomor
                                    Rekening</label>
                                <input name="nomor_rekening" type="text" required value="{{ old('nomor_rekening') }}"
                                    placeholder="Contoh: 1234567890"
                                    class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Atas Nama</label>
                                <input name="nama_pemilik" type="text" required value="{{ old('nama_pemilik') }}"
                                    placeholder="Contoh: BETA GYM"
                                    class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3">
                        <x-ui.button-secondary type="button" @click="openCreate = false">
                            Batal
                        </x-ui.button-secondary>

                        <x-ui.button-primary type="submit">
                            Simpan Rekening
                        </x-ui.button-primary>
                    </div>
                </form>
            </div>

            {{-- CREATE QRIS --}}
            <div x-show="createTab==='qris'" x-cloak x-data="{
                qrisPreviewUrl: null,
                setQrisPreview(event) {
                    const file = event.target.files?.[0];
                    this.qrisPreviewUrl = file ? URL.createObjectURL(file) : null;
                }
            }"
                x-effect="
                    if (!openCreate || createTab !== 'qris') qrisPreviewUrl = null;
                ">
                <form action="{{ route('admin.info-qris.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- PREVIEW --}}
                        <div class="md:col-span-1">
                            <div
                                class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                <div
                                    class="w-32 h-32 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50
                                            flex items-center justify-center overflow-hidden">
                                    <template x-if="qrisPreviewUrl">
                                        <img :src="qrisPreviewUrl" alt="Preview QRIS"
                                            class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!qrisPreviewUrl">
                                        <span class="text-[11px] text-text-muted">Preview QRIS</span>
                                    </template>
                                </div>

                                <p class="text-[11px] text-text-muted text-center">
                                    Pilih gambar untuk melihat preview QRIS.
                                </p>
                            </div>
                        </div>

                        {{-- FORM --}}
                        <div class="md:col-span-2">
                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5 space-y-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama
                                        QRIS</label>
                                    <input name="nama_qris" type="text" required value="{{ old('nama_qris') }}"
                                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-text-muted mb-1">
                                        Keterangan (opsional)
                                    </label>
                                    <textarea name="keterangan" rows="3"
                                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">{{ old('keterangan') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-text-muted mb-1">Gambar
                                        QRIS</label>
                                    <input name="gambar" type="file" accept="image/*" required
                                        @change="setQrisPreview($event)"
                                        class="block w-full text-sm text-text-main
                                               file:mr-4 file:py-2 file:px-4
                                               file:rounded-full file:border-0
                                               file:text-sm file:font-semibold
                                               file:bg-gold-600 file:text-white
                                               hover:file:bg-gold-700">
                                    <p class="text-[11px] text-text-muted mt-1">Maks 2MB, format JPG/PNG/WebP.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3">
                        <x-ui.button-secondary type="button" @click="openCreate = false">
                            Batal
                        </x-ui.button-secondary>

                        <x-ui.button-primary type="submit">
                            Simpan QRIS
                        </x-ui.button-primary>
                    </div>
                </form>
            </div>

            <style>
                [x-cloak] {
                    display: none !important;
                }

                .custom-scrollbar::-webkit-scrollbar {
                    height: 6px;
                    width: 6px;
                }

                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #F5E6D6;
                    border-radius: 999px;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #D4A757;
                    border-radius: 999px;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #A67C39;
                }
            </style>
        </div>
    </div>
</div>
