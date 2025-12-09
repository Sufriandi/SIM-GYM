{{-- MODAL DETAIL INVENTARIS --}}
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
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Detail Inventaris
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi lengkap alat dan kondisinya.
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
        <div class="px-6 pb-6 pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- KIRI: DATA ALAT + DESKRIPSI (2 kartu, mirip coach) --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- DATA ALAT --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-1">
                            Data Alat
                        </h3>
                        <p class="text-xs text-text-muted mb-3">
                            Detail informasi alat yang terdaftar.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- NAMA ALAT --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5 uppercase">
                                    Nama Alat
                                </p>
                                <p class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug">
                                    <span x-text="detailItem ? detailItem.nama : ''"></span>
                                </p>
                            </div>

                           {{-- KONDISI --}}
<div class="min-w-0">
    <p class="text-[11px] text-text-muted mb-0.5 uppercase">
        Kondisi
    </p>
    <div class="flex items-center">
        <p
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
            :class="{
                'bg-success-soft text-success-dark': detailItem && detailItem.kondisi === 'Baik',
                'bg-warning-soft text-warning-dark': detailItem && detailItem.kondisi === 'Maintenance',
                'bg-danger-soft text-danger-dark': detailItem && detailItem.kondisi === 'Rusak'
            }"
        >
            {{-- TITIK KECIL DI KIRI TEKS --}}
            <span
                class="inline-block w-2 h-2 rounded-full mr-2"
                :class="{
                    'bg-emerald-500': detailItem && detailItem.kondisi === 'Baik',
                    'bg-amber-500': detailItem && detailItem.kondisi === 'Maintenance',
                    'bg-rose-500': detailItem && detailItem.kondisi === 'Rusak'
                }"
            ></span>

            <span x-text="detailItem ? detailItem.kondisi : ''"></span>
        </p>
    </div>
</div>

                        </div>
                    </div>

                    {{-- DESKRIPSI ALAT (kartu terpisah, ada box putih di dalam) --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-3">
                        <h3 class="text-sm font-semibold text-text-main mb-2">
                            Deskripsi Alat
                        </h3>

                        <div
                            class="mt-1 p-3 rounded-xl bg-brand-surface-50 border border-brand-borderSoft
                                   text-sm text-text-main min-h-[80px] break-words whitespace-normal leading-relaxed"
                        >
                            <p class="leading-relaxed break-words">
                                <span
                                    x-text="
                                        detailItem && detailItem.deskripsi
                                            ? detailItem.deskripsi
                                            : 'Belum ada deskripsi untuk alat ini.'
                                    "
                                ></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO ALAT (disamain dengan foto coach) --}}
                <div class="space-y-4">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-3 flex flex-col gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-main">
                                Foto Alat
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Tampilan foto terbaru dari alat.
                            </p>
                        </div>

                        <div
                            class="w-full h-64 rounded-2xl overflow-hidden bg-brand-surface-50
                                   flex items-center justify-center
                                   shadow-[0_12px_32px_rgba(0,0,0,0.18)]"
                        >
                            {{-- Kalau ada foto --}}
                            <template x-if="detailItem && detailItem.foto">
                                <img
                                    :src="detailItem.foto"
                                    alt="Foto alat"
                                    class="max-w-full max-h-full object-contain"
                                >
                            </template>

                            {{-- Kalau tidak ada foto --}}
                            <template x-if="!detailItem || !detailItem.foto">
                                <span class="text-[11px] text-text-muted">
                                    Tidak ada foto
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER MOBILE: TOMBOL TUTUP --}}
            <div class="mt-6 flex justify-end lg:hidden">
                <x-ui.button-secondary type="button" @click="openDetail = false">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>
