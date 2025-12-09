{{-- resources/views/admin/produk/modals/detail.blade.php --}}

@php
    // Deskripsi (newline -> <br>, fallback "— Tidak ada deskripsi —")
    $deskripsiRaw = $produk->deskripsi ?? '';
    $deskripsiTrimmed = trim($deskripsiRaw);
    $deskripsiHtml = $deskripsiTrimmed !== ''
        ? nl2br(e($deskripsiTrimmed))
        : '— Tidak ada deskripsi —';
@endphp

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
               max-h-[95vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-2 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Informasi lengkap mengenai produk.
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
        <div class="px-5 pb-5 pt-3">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- KIRI: DATA PRODUK & DESKRIPSI --}}
                <div class="lg:col-span-2 space-y-3.5">
                    {{-- DATA PRODUK --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-2.5">
                        <h3 class="text-sm font-semibold text-text-main mb-1">
                            Data Produk
                        </h3>
                        <p class="text-xs text-text-muted mb-2">
                            Detail informasi produk yang terdaftar.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- NAMA PRODUK --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Nama Produk</p>
                                <p class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug">
                                    {{ $produk->nama }}
                                </p>
                            </div>

                            {{-- KATEGORI --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Kategori</p>
                                <p class="text-base font-semibold text-primary-dark break-words whitespace-normal leading-snug">
                                    {{ ucwords($produk->kategori) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            {{-- HARGA --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Harga (Rp)</p>
                                <p class="text-base font-semibold text-text-main break-words whitespace-normal leading-snug">
                                    {{ number_format($produk->harga, 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- STOK --}}
                            <div class="min-w-0">
                                <p class="text-[11px] text-text-muted mb-0.5">Stok Saat Ini</p>
                                <div class="p-2 rounded-xl bg-success-soft border border-success/70 flex justify-between items-center">
                                    <span class="text-sm font-extrabold text-success">
                                        {{ $produk->stok }} unit
                                    </span>
                                </div>
                                <p class="text-[11px] text-text-muted mt-1">
                                    Stok dikelola melalui menu Stok Produk.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DESKRIPSI PRODUK --}}
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-2.5">
                        <h3 class="text-sm font-semibold text-text-main mb-1.5">
                            Deskripsi Produk
                        </h3>

                        <div
                            class="mt-1 p-2.5 rounded-xl bg-brand-surface-50 border border-brand-borderSoft
                                   text-sm text-text-main min-h-[96px] break-words whitespace-normal leading-relaxed"
                        >
                            {!! $deskripsiHtml !!}
                        </div>
                    </div>
                </div>

                {{-- KANAN: FOTO PRODUK --}}
                <div class="space-y-3.5">
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-2.5 flex flex-col gap-2.5">
                        <div>
                            <h3 class="text-base font-semibold text-text-main">
                                Foto Produk
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5">
                                Tampilan foto terbaru dari produk.
                            </p>
                        </div>

                        <div
                            class="w-full h-64 rounded-2xl overflow-hidden bg-brand-surface-50
                                   flex items-center justify-center
                                   shadow-[0_12px_32px_rgba(0,0,0,0.18)]"
                        >
                            <img
                                src="{{ $currentFotoUrl }}"
                                alt="Foto {{ $produk->nama }}"
                                class="max-w-full max-h-full object-contain"
                                onerror="this.onerror=null; this.src='https://placehold.co/400x500/F5E6D6/3A2D2A?text=Tidak+Ada+Foto';"
                            >
                        </div>
                    </div>
                </div>
            </div>
            {{-- Footer tombol tetap dihilangkan --}}
        </div>
    </div>
</div>
