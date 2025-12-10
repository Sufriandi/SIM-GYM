{{-- resources/views/admin/penjualan_produk/modals/edit.blade.php --}}

<div
    x-show="openEdit"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openEdit = false"
>
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Item Transaksi</h2>
                <p class="text-sm text-text-muted mt-0.5"
                   x-text="'Produk: ' + (editPenjualan ? editPenjualan.produk.nama : '')">
                    Ubah detail item transaksi ini.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEdit = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4" x-show="editPenjualan">
            <form :action="'{{ url('admin/penjualan_produk') }}/' + editForm.id"
                  method="POST"
                  class="space-y-6">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="id" :value="editForm.id">
                <input type="hidden" name="produk_id" :value="editForm.produk_id">

                {{-- ERROR VALIDASI UPDATE --}}
                @if ($errors->any() && old('_method') === 'PUT')
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                        <p class="text-sm font-semibold">Ada kesalahan input saat mengedit:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- KIRI: ITEM & JUMLAH --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">
                            Detail Item & Jumlah
                        </h3>

                        {{-- PRODUK (DISABLED) --}}
                        <div>
                            <x-ui.label for="produk_id_modal_edit">
                                Produk Dijual<span class="text-danger">*</span>
                            </x-ui.label>
                            <input
                                type="text"
                                :value="editPenjualan ? editPenjualan.produk.nama : ''"
                                disabled
                                class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft"
                            >
                            <p class="text-xs text-text-muted mt-1">
                                Produk tidak dapat diubah.
                            </p>
                        </div>

                        {{-- JUMLAH --}}
                        <div>
                            <x-ui.label for="jumlah_modal_edit">
                                Jumlah Beli<span class="text-danger">*</span>
                            </x-ui.label>
                            <input
                                type="number"
                                id="jumlah_modal_edit"
                                name="jumlah"
                                x-model.number="editForm.jumlah"
                                min="1"
                                :max="calculateMaxStockEdit()"
                                required
                                class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft
                                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('jumlah') border-danger ring-danger-soft @enderror"
                            >
                            @error('jumlah')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-text-muted mt-1">
                                Stok tersedia maksimal:
                                <span x-text="calculateMaxStockEdit()"></span>
                                (stok saat ini + jumlah lama)
                            </p>
                        </div>

                        {{-- DETAIL HARGA --}}
                        <div
                            x-data="{
                                formatRupiah(angka) {
                                    if (!angka) return '0';
                                    let num = parseInt(angka);
                                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                }
                            }"
                            x-show="editForm && editForm.harga_satuan"
                            class="mt-4 p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2"
                        >
                            <h3 class="font-semibold text-text-main">Detail Harga</h3>
                            <p class="text-sm text-text-muted">
                                Harga Satuan:
                                <span class="font-medium text-text-main"
                                      x-text="'Rp ' + formatRupiah(editForm.harga_satuan)"></span>
                            </p>
                            <p class="text-lg font-bold text-success">
                                Total Bayar Item Baru:
                                <span x-text="'Rp ' + formatRupiah(calculateEditTotal())"></span>
                                <input type="hidden" name="total_harga" :value="calculateEditTotal()">
                            </p>
                        </div>
                    </div>

                    {{-- KANAN: DATA TRANSAKSI --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">
                            Data Transaksi Penjualan Produk
                        </h3>

                        {{-- MEMBER --}}
                        <div>
                            <x-ui.label for="member_id_modal_edit">
                                Pembeli<span class="text-danger">*</span>
                            </x-ui.label>
                            <div class="relative">
                                <select
                                    id="member_id_modal_edit"
                                    name="member_id"
                                    x-model="editForm.member_id"
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           @error('member_id') border-danger ring-danger-soft @enderror"
                                >
                                    <option value="">-- Umum (Tidak Terdaftar) --</option>
                                    <template x-for="member in membersData" :key="member.id">
                                        <option :value="member.id" x-text="member.name"></option>
                                    </template>
                                </select>
                                <i data-lucide="chevron-down"
                                   class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            @error('member_id')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- METODE PEMBAYARAN --}}
                        <div>
                            <x-ui.label for="metode_pembayaran_modal_edit">
                                Metode Pembayaran<span class="text-danger">*</span>
                            </x-ui.label>
                            <div class="relative">
                                <select
                                    id="metode_pembayaran_modal_edit"
                                    name="metode_pembayaran"
                                    x-model="editForm.metode_pembayaran"
                                    required
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           @error('metode_pembayaran') border-danger ring-danger-soft @enderror"
                                >
                                    <option value="" disabled>-- Pilih Metode --</option>
                                    @foreach ($metodePembayaranOptions as $metode)
                                        <option value="{{ $metode }}">{{ $metode }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down"
                                   class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                            @error('metode_pembayaran')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_modal_edit">
                                Keterangan (Opsional)
                            </x-ui.label>
                            <textarea
                                id="keterangan_modal_edit"
                                name="keterangan"
                                rows="6"
                                x-model="editForm.keterangan"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       @error('keterangan') border-danger ring-danger-soft @enderror"
                            ></textarea>
                            @error('keterangan')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end border-t border-brand-borderSoft pt-4">
                    <x-ui.button-secondary type="button" @click="openEdit = false" class="mr-2">
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
