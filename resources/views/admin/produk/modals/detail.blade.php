{{-- resources/views/admin/produk/modals/detail.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $currentFotoPath = $produk->foto ?? null;
    $currentFotoUrl = $currentFotoPath
        ? Storage::url($currentFotoPath)
        : 'https://placehold.co/400x500/F5E6D6/3A2D2A?text=Tidak+Ada+Foto';

    $deskripsiRaw = $produk->deskripsi ?? '';
    $deskripsiTrimmed = trim($deskripsiRaw);
    $deskripsiHtml = $deskripsiTrimmed !== '' ? nl2br(e($deskripsiTrimmed)) : '— Tidak ada deskripsi —';
@endphp

<div x-show="openDetailId === {{ $produk->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-start justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetailId = null" @keydown.escape.window="openDetailId = null" role="dialog" aria-modal="true"
    aria-labelledby="modal-produk-detail-title">

    <div class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
                max-h-[calc(100vh-48px)] flex flex-col"
        @click.stop>

        {{-- HEADER (fixed) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80 shrink-0">
            <div>
                <h2 id="modal-produk-detail-title" class="text-xl font-semibold text-text-main">Detail Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">Informasi lengkap mengenai produk.</p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetailId = null" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-6 overflow-y-auto custom-scrollbar flex-1 overscroll-contain"
            style="-webkit-overflow-scrolling: touch;">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- KIRI (Data Grid) --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Grid Atas: Nama, Kategori, Harga, Stok --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nama Produk --}}
                        <div
                            class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft flex flex-col justify-center">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-1">Nama Produk
                            </p>
                            <p class="text-lg font-bold text-text-main leading-tight break-words">
                                {{ $produk->nama }}
                            </p>
                        </div>

                        {{-- Kategori --}}
                        <div
                            class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft flex flex-col justify-center">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-1">Kategori</p>
                            <p class="text-lg font-bold text-gold-500">
                                {{ ucwords($produk->kategori) }}
                            </p>
                        </div>

                        {{-- Harga --}}
                        <div
                            class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft flex flex-col justify-center">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-1">Harga</p>
                            <p class="text-lg font-bold text-text-main">
                                Rp {{ number_format((int) $produk->harga, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Stok --}}
                        <div
                            class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft flex flex-col justify-center">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-1">Stok Saat Ini
                            </p>
                            <p class="text-lg font-bold text-text-main">
                                {{ (int) $produk->stok }} <span class="text-sm font-normal text-text-muted">Unit</span>
                            </p>
                            <p class="text-[10px] text-text-muted mt-0.5">Stok dikelola melalui menu Stok.</p>
                        </div>

                    </div>

                    {{-- Deskripsi (Full Width di kolom kiri) --}}
                    <div class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-2">Deskripsi</p>
                        <div class="text-sm text-text-main leading-relaxed break-words">
                            {!! $deskripsiHtml !!}
                        </div>
                    </div>
                </div>

                {{-- KANAN (Foto) --}}
                <div class="lg:col-span-1 lg:self-start">
                    <div class="bg-brand-shell/50 rounded-2xl p-5 border border-brand-borderSoft h-auto flex flex-col">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-gold-600 mb-3">Foto Produk</p>

                        <div
                            class="w-full h-64 rounded-xl overflow-hidden bg-white/50 border border-brand-borderSoft/50
                                    flex items-center justify-center shadow-inner">
                            <img src="{{ $currentFotoUrl }}" alt="Foto {{ $produk->nama }}"
                                class="max-w-full max-h-full object-contain p-2"
                                onerror="this.onerror=null; this.src='https://placehold.co/400x500/F5E6D6/3A2D2A?text=Tidak+Ada+Foto';">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>
