{{-- resources/views/admin/stok_produk/modals/create.blade.php --}}
<div
    x-show="openTambahStok"
    x-cloak
    x-transition
    @click.self="openTambahStok = false"
    class="fixed inset-0 z-[999] overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm"
>
    <div
        @click.away="openTambahStok = false"
        class="relative w-full max-w-xl my-auto rounded-3xl shadow-2xl border border-brand-borderSoft 
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell overflow-hidden"
    >
        <form action="{{ route('admin.stok_produk.store') }}" method="POST">
            @csrf

            {{-- HEADER --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                <div>
                    <h2 class="text-xl font-semibold text-text-main">Tambah Stok Produk</h2>
                    <p class="text-sm text-text-muted mt-0.5">Isi detail produk yang akan ditambahkan ke stok.</p>
                </div>
                <button type="button"
                        class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                        @click="openTambahStok = false">
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>

            {{-- BODY --}}
            <div class="px-6 pb-6 pt-4 space-y-4">

                {{-- PRODUK --}}
                <div>
                    <x-ui.label for="produk_id">Produk <span class="text-danger">*</span></x-ui.label>
                    <div class="relative">
                        <select name="produk_id" id="produk_id" required
                                class="custom-select w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($allProduk as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>

                {{-- STOK MASUK --}}
                <div>
                    <x-ui.label for="stok">Jumlah Stok Masuk <span class="text-danger">*</span></x-ui.label>
                    <input type="number" name="stok" id="stok" required min="1"
                           class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft">
                </div>

                {{-- KETERANGAN --}}
                <div>
                    <x-ui.label for="keterangan">Keterangan (opsional)</x-ui.label>
                    <textarea name="keterangan" id="keterangan"
                              class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft"
                              rows="3"></textarea>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="flex justify-end gap-2 px-6 pb-5">
                <x-ui.button-secondary type="button" @click="openTambahStok = false">Batal</x-ui.button-secondary>
                <x-ui.button-primary type="submit">Simpan</x-ui.button-primary>
            </div>
        </form>
    </div>
</div>
