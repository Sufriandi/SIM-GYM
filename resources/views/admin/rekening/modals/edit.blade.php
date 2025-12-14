{{-- resources/views/admin/rekening/modals/edit.blade.php --}}

{{-- EDIT REKENING --}}
<div x-show="openEditRekening" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEditRekening = false" @keydown.escape.window="openEditRekening = false">
    <div
        class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Rekening</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui data rekening bank.</p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditRekening = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" :action="editRekening ? `{{ url('/admin/info-rekening') }}/${editRekening.id}` : '#'"
                class="space-y-5">
                @csrf
                @method('PUT')

                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama Bank</label>
                            <input name="nama_bank" type="text" required :value="editRekening?.nama_bank ?? ''"
                                class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-text-muted mb-1">Nomor Rekening</label>
                            <input name="nomor_rekening" type="text" required
                                :value="editRekening?.nomor_rekening ?? ''"
                                class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-semibold text-text-muted mb-1">Atas Nama</label>
                            <input name="nama_pemilik" type="text" required :value="editRekening?.nama_pemilik ?? ''"
                                class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openEditRekening = false">
                        Batal
                    </x-ui.button-secondary>

                    <x-ui.button-primary type="submit">
                        Simpan Perubahan
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT QRIS (preview kiri seperti create + preview gambar baru) --}}
<div x-show="openEditQris" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEditQris = false" @keydown.escape.window="openEditQris = false" x-data="{
        newPreviewUrl: null,
        setNewPreview(e) {
            const file = e.target.files?.[0];
            this.newPreviewUrl = file ? URL.createObjectURL(file) : null;
        },
        get currentUrl() {
            return (editQris && editQris.path_gambar) ?
                `{{ asset('storage') }}/${editQris.path_gambar}` :
                null;
        }
    }"
    x-effect="
        if (!openEditQris) {
            newPreviewUrl = null;
        }
    ">
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit QRIS</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui data QRIS.</p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditQris = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" enctype="multipart/form-data"
                :action="editQris ? `{{ url('/admin/info-qris') }}/${editQris.id}` : '#'" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PREVIEW --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-32 h-32 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="newPreviewUrl">
                                    <img :src="newPreviewUrl" alt="Preview QRIS baru"
                                        class="w-full h-full object-cover">
                                </template>

                                <template x-if="!newPreviewUrl && currentUrl">
                                    <img :src="currentUrl" alt="QRIS saat ini" class="w-full h-full object-cover">
                                </template>

                                <template x-if="!newPreviewUrl && !currentUrl">
                                    <span class="text-[11px] text-text-muted">Tidak ada gambar</span>
                                </template>
                            </div>

                            <p class="text-[11px] text-text-muted text-center">
                                <span x-show="newPreviewUrl">Preview gambar baru.</span>
                                <span x-show="!newPreviewUrl">Gambar QRIS saat ini.</span>
                            </p>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5 space-y-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama QRIS</label>
                                <input name="nama_qris" type="text" required :value="editQris?.nama_qris ?? ''"
                                    class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Keterangan
                                    (opsional)</label>
                                <textarea name="keterangan" rows="3"
                                    class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                                    x-model="editQrisKeterangan"></textarea>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Gambar Baru
                                    (opsional)</label>
                                <input name="gambar" type="file" accept="image/*" @change="setNewPreview($event)"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700">
                                <p class="text-[11px] text-text-muted mt-1">Kosongkan jika tidak mengganti gambar.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openEditQris = false">
                        Batal
                    </x-ui.button-secondary>

                    <x-ui.button-primary type="submit">
                        Simpan Perubahan
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
