{{-- resources/views/admin/rekening/modals/edit.blade.php --}}

{{-- EDIT REKENING --}}
<div x-show="openEditRekening" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEditRekening = false" @keydown.escape.window="openEditRekening = false">

    <div
        class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Rekening</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui data rekening bank.</p>
            </div>

            <button type="button" class="p-2 rounded-xl hover:bg-brand-surface-50" @click="openEditRekening=false">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 py-5">
            <form method="POST" :action="editRekening ? `{{ url('/admin/info-rekening') }}/${editRekening.id}` : '#'"
                class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama Bank</label>
                        <input name="nama_bank" type="text" required
                            class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                            :value="editRekening?.nama_bank ?? ''">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-text-muted mb-1">Nomor Rekening</label>
                        <input name="nomor_rekening" type="text" required
                            class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                            :value="editRekening?.nomor_rekening ?? ''">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-semibold text-text-muted mb-1">Atas Nama</label>
                        <input name="nama_pemilik" type="text" required
                            class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                            :value="editRekening?.nama_pemilik ?? ''">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-xl border border-brand-borderSoft text-sm"
                        @click="openEditRekening=false">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-primary-dark text-white text-sm font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT QRIS --}}
<div x-show="openEditQris" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEditQris = false" @keydown.escape.window="openEditQris = false">

    <div
        class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit QRIS</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui data QRIS.</p>
            </div>

            <button type="button" class="p-2 rounded-xl hover:bg-brand-surface-50" @click="openEditQris=false">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 py-5">
            <form method="POST" enctype="multipart/form-data"
                :action="editQris ? `{{ url('/admin/info-qris') }}/${editQris.id}` : '#'" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-semibold text-text-muted mb-1">Nama QRIS</label>
                    <input name="nama_qris" type="text" required
                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                        :value="editQris?.nama_qris ?? ''">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-text-muted mb-1">Keterangan (opsional)</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm"
                        x-model="editQrisKeterangan"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-text-muted mb-1">Gambar Baru (opsional)</label>
                    <input name="gambar" type="file" accept="image/*"
                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm">
                    <p class="text-[11px] text-text-muted mt-1">Kosongkan jika tidak mengganti gambar.</p>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="px-4 py-2 rounded-xl border border-brand-borderSoft text-sm"
                        @click="openEditQris=false">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-primary-dark text-white text-sm font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
