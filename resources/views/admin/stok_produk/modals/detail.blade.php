{{-- resources/views/admin/stok_produk/modals/detail.blade.php --}}

<div
    x-show="openDetail"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetail = false"
    @keydown.escape.window="openDetail = false"
>
    <div
        class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Stok Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi stok saat ini dan keterangan produk.
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

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4" x-show="detailProduk">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- KIRI: DATA PRODUK & DESKRIPSI --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA PRODUK --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-1">
                            Data Produk
                        </h3>
                        <p class="text-xs text-text-muted mb-3">
                            Detail informasi produk dan waktu pencatatan stok.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- NAMA PRODUK --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama Produk</p>
                                <p
                                    class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug"
                                    x-text="detailProduk?.nama ?? '-'"
                                ></p>
                            </div>

                            {{-- KATEGORI --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Kategori</p>
                                <p
                                    class="text-base font-semibold text-primary-dark break-words whitespace-normal leading-snug"
                                    x-text="detailProduk?.kategori ?? '-'"
                                ></p>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-brand-borderSoft/70">
                            {{-- TANGGAL DIBUAT --}}
                            <p class="text-[11px] text-text-muted mb-0.5">Tanggal Dibuat</p>
                            <p
                                class="text-sm font-medium text-text-main leading-snug"
                                x-text="detailProduk?.tanggal_dibuat ?? '-'"
                            ></p>
                        </div>
                    </div>

                    {{-- DESKRIPSI / KETERANGAN --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-2">
                            Keterangan / Deskripsi
                        </h3>

                        <div
                            class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft
                                   text-sm text-text-main min-h-[80px] break-words whitespace-pre-line leading-relaxed"
                            x-text="detailProduk && detailProduk.deskripsi && detailProduk.deskripsi.trim() !== ''
                                    ? detailProduk.deskripsi
                                    : '— Tidak ada deskripsi —'"
                        ></div>
                    </div>
                </div>

                {{-- KANAN: RINGKASAN STOK --}}
                <div class="space-y-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-4 flex flex-col gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-text-main">
                                Ringkasan Stok
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Gambaran singkat kondisi stok saat ini untuk produk ini.
                            </p>
                        </div>

                        {{-- KARTU STOK SAAT INI --}}
                        <div
                            class="p-4 rounded-2xl bg-brand-surface-50 border border-brand-borderSoft/90
                                   shadow-[0_10px_24px_rgba(0,0,0,0.08)] flex flex-col gap-3"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-brand-shell/80 border border-brand-borderSoft/80
                                               flex items-center justify-center"
                                    >
                                        <i data-lucide="boxes" class="w-4 h-4 text-text-muted"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase">
                                            Stok Saat Ini
                                        </p>
                                        <p class="text-[11px] text-text-muted">
                                            Status ketersediaan unit di sistem.
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide uppercase"
                                    :class="detailProduk && detailProduk.stok > 0
                                        ? 'bg-success-soft/40 text-success'
                                        : 'bg-danger-soft/40 text-danger'"
                                    x-text="detailProduk && detailProduk.stok > 0 ? 'Tersedia' : 'Habis'"
                                ></span>
                            </div>

                            <div>
                                <p
                                    class="text-3xl font-extrabold leading-tight"
                                    :class="detailProduk && detailProduk.stok > 0 ? 'text-success' : 'text-danger'"
                                    x-text="(detailProduk?.stok ?? 0) + ' unit'"
                                ></p>
                                <p class="mt-1 text-[11px] text-text-muted">
                                    Unit tercatat dalam stok saat ini untuk produk ini.
                                </p>
                            </div>

                            <div class="flex items-center justify-between mt-1 text-[11px] text-text-muted/90">
                                <span>Terakhir tercatat:</span>
                                <span x-text="detailProduk?.tanggal_dibuat ?? '-'"></span>
                            </div>
                        </div>

                        <div class="text-[11px] text-text-muted leading-relaxed">
                            <p>
                                Nilai stok saat ini merupakan angka terakhir yang tercatat di sistem.
                                Penyesuaian stok atau penambahan stok baru akan mengubah angka ini
                                melalui menu penyesuaian maupun penambahan stok.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Tidak ada footer tombol; tutup via ikon X / klik overlay --}}
        </div>
    </div>
</div>
