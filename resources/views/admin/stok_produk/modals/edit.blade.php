{{-- resources/views/admin/stok_produk/modals/edit.blade.php --}}
<div
    x-show="openEditStok"
    x-cloak
    x-transition
    @click.self="openEditStok = false"
    class="fixed inset-0 z-[999] flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
>
    <div
        @click.away="openEditStok = false"
        class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft 
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
    >
        <form :action="`{{ url('admin/stok_produk') }}/${editStokForm.id}`" method="POST">
            @csrf
            @method('PUT')

            {{-- HEADER --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                <div>
                    <h2 class="text-xl font-semibold text-text-main">Edit/Penyesuaian Stok Produk</h2>
                    <p class="text-sm text-text-muted mt-0.5" x-text="editStokForm.nama"></p>
                </div>
                <button type="button"
                        class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                        @click="openEditStok = false">
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>

            {{-- BODY --}}
            <div class="px-6 pb-6 pt-4 space-y-4">

                {{-- STOK LAMA --}}
                <div>
                    <x-ui.label>Stok Lama</x-ui.label>
                    <input type="number" x-model="editStokForm.stok_lama" readonly
                           class="w-full rounded-xl border bg-brand-surface-50 text-sm px-3 py-2 border-brand-borderSoft cursor-not-allowed">
                    <input type="hidden" name="stok_lama" :value="editStokForm.stok_lama">
                </div>

                {{-- STOK BARU --}}
                <div>
                    <x-ui.label for="stok_baru">Stok Baru (Stok Akhir yang Benar) <span class="text-danger">*</span></x-ui.label>
                    <input type="number" name="stok_baru" id="stok_baru" required min="0"
                           x-model="editStokForm.stok_baru"
                           class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft">
                </div>

                {{-- KETERANGAN --}}
                <div>
                    <x-ui.label for="keterangan_edit">Keterangan Penyesuaian (wajib jika ada perubahan)</x-ui.label>
                    <textarea name="keterangan" id="keterangan_edit" rows="3"
                              x-model="editStokForm.keterangan"
                              class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft"></textarea>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="flex justify-end gap-2 px-6 pb-5">
                <x-ui.button-secondary type="button" @click="openEditStok = false">
                    Batal
                </x-ui.button-secondary>
                <x-ui.button-primary type="submit">
                    Perbarui Stok
                </x-ui.button-primary>
            </div>
        </form>
    </div>
</div>
