{{-- resources/views/admin/penjualan_produk/modals/detail.blade.php --}}

<div
    x-show="openDetail"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetail = false"
>
    <div
        class="relative w-full max-w-2xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Transaksi Penjualan</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi lengkap mengenai transaksi produk.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetail = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4" x-show="detailPenjualan">
            <div class="space-y-4">
                {{-- RINGKASAN --}}
                <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                    <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                        <span class="text-sm font-medium text-text-muted">
                            Tanggal Transaksi
                        </span>
                        <span class="font-semibold text-sm text-text-main"
                              x-text="formatDate(detailPenjualan.tanggal_transaksi)"></span>
                    </div>

                    <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                        <span class="text-sm font-medium text-text-muted">Pembeli</span>
                        <span class="font-semibold text-sm text-primary-dark"
                              x-text="detailPenjualan.member_name || 'Umum'"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-text-muted">Metode Pembayaran</span>
                        <span class="font-semibold text-sm text-primary-dark"
                              x-text="detailPenjualan.metode_pembayaran"></span>
                    </div>
                </div>

                {{-- PRODUK --}}
                <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                    <h3 class="text-base font-semibold text-text-main border-b border-brand-borderSoft pb-2 mb-2">
                        Produk yang Dijual
                    </h3>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Nama Produk</span>
                        <span class="font-semibold text-sm text-text-main"
                              x-text="detailPenjualan.produk.nama"></span>
                    </div>

                    <div
                        x-data="{
                            formatRupiah(angka) {
                                if (!angka) return '0';
                                let num = parseInt(angka);
                                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        }"
                        class="flex justify-between items-center"
                    >
                        <span class="text-sm text-text-muted">Harga Satuan</span>
                        <span class="text-sm font-medium text-text-main"
                              x-text="'Rp ' + formatRupiah(detailPenjualan.produk.harga)"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Jumlah Beli</span>
                        <span class="font-bold text-sm text-primary-dark"
                              x-text="detailPenjualan.jumlah + ' unit'"></span>
                    </div>
                </div>

                {{-- TOTAL --}}
                <div
                    x-data="{
                        formatRupiah(angka) {
                            if (!angka) return '0';
                            let num = parseInt(angka);
                            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        }
                    }"
                    class="p-4 border border-success/60 rounded-xl bg-success-soft/30"
                >
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-text-main">Total Bayar</span>
                        <span class="text-xl font-extrabold text-success"
                              x-text="'Rp ' + formatRupiah(detailPenjualan.total_harga)"></span>
                    </div>
                </div>

                {{-- KETERANGAN --}}
                <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                    <h3 class="text-sm font-medium text-text-muted">Keterangan</h3>
                    <p class="text-sm text-text-main mt-1 italic"
                       x-text="detailPenjualan.keterangan || '-'"></p>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-end mt-6">
                <x-ui.button-secondary type="button" @click="openDetail = false">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>
