{{-- resources/views/admin/latihan_harian/modals/detail.blade.php --}}

<div x-show="openDetail" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetail = false" @keydown.escape.window="openDetail = false">
    <div
        class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Latihan Harian</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi ringkas satu transaksi latihan harian.
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetail = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar">
            <div class="space-y-4">

                {{-- RINGKASAN MEMBER & TANGGAL --}}
                <div class="p-4 border border-brand-borderSoft rounded-2xl bg-brand-surface-50 space-y-2">
                    <div class="flex justify-between items-center border-b border-brand-borderSoft/70 pb-2">
                        <span class="text-sm font-medium text-text-muted">Tanggal</span>
                        <span class="text-sm font-semibold text-text-main"
                            x-text="detailItem ? detailItem.tanggal_label : '-'"></span>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-sm font-medium text-text-muted">Nama Pelanggan</span>
                        <span class="text-sm font-semibold text-primary-dark"
                            x-text="detailItem ? detailItem.nama : '-'"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-text-muted">Kategori</span>
                        <span class="text-sm font-semibold text-text-main"
                            x-text="detailItem ? detailItem.kategori : '-'"></span>
                    </div>
                </div>

                {{-- RINCIAN PEMBAYARAN --}}
                <div class="p-4 border border-brand-borderSoft rounded-2xl bg-brand-surface-50 space-y-2">
                    <h3 class="text-sm font-semibold text-text-main mb-1">Rincian Pembayaran</h3>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-text-muted">Tarif Latihan</span>
                        <span class="text-sm font-bold text-text-main"
                            x-text="detailItem ? detailItem.harga_label : '-'"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-text-muted">Metode Pembayaran</span>
                        <span class="text-sm font-semibold text-primary-dark"
                            x-text="detailItem ? detailItem.metode_label : '-'"></span>
                    </div>
                </div>

                {{-- KETERANGAN --}}
                <div class="p-4 border border-brand-borderSoft rounded-2xl bg-brand-surface-50">
                    <h3 class="text-sm font-semibold text-text-main mb-1">Keterangan</h3>
                    <p class="text-sm text-text-muted"
                        x-text="detailItem && detailItem.keterangan ? detailItem.keterangan : '-'"></p>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-ui.button-secondary type="button" @click="openDetail = false">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>
