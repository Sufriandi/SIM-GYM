{{-- resources/views/admin/stok_produk/history.blade.php --}}

@php
    use Carbon\Carbon;
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar semua pergerakan stok produk (masuk/keluar/penyesuaian)."
>
    {{-- JUDUL + SUBTITLE ATAS --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Riwayat lengkap setiap penambahan, penyesuaian, dan pengurangan stok produk."
    />

    {{-- TOMBOL KEMBALI DI BAWAH SUBTITLE --}}
    <div class="mt-2">
        <x-ui.back-button
            href="{{ route('admin.stok_produk.index') }}"
            text="Kembali ke Daftar Stok Produk"
        />
    </div>

    {{-- GARIS PEMBATAS --}}
    <hr class="border-t border-brand-borderSoft mb-6 mt-2">

    {{-- CARD UTAMA --}}
    <div class="w-full">
        <x-ui.card class="border-brand-borderSoft">
            {{-- HEADER CARD --}}
            <div class="mb-5">
                <h2 class="text-2xl font-heading text-text-main mb-1">
                    Riwayat Pergerakan Stok
                </h2>
                <p class="text-sm text-text-muted">
                    Semua log stok yang telah dicatat.
                </p>
            </div>

            {{-- WRAPPER TABEL (RESPONSIVE) --}}
            <div>
                <table class="w-full border-collapse text-xs md:text-sm lg:text-base md:min-w-[900px]">
                    <thead>
                        <tr class="border-b-2 border-brand-borderSoft/80 bg-brand-shell/60">
                            {{-- KOLOM HEADERS --}}
                            <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase w-[6%]">
                                No
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase w-[20%]">
                                Produk
                            </th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap w-[15%]">
                                Tanggal
                            </th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap w-[10%]">
                                Perubahan
                            </th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase w-[10%]">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase w-[30%]">
                                Keterangan
                            </th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap w-[9%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody x-data="{ openDetailId: null }">
                        @forelse ($riwayat_stok as $log)
                            @php
                                $perubahan = $log->jumlah;
                                $status = $perubahan > 0 ? 'Masuk' : ($perubahan < 0 ? 'Keluar' : 'Penyesuaian (0)');
                                $badgeColor = $perubahan > 0 ? 'success' : ($perubahan < 0 ? 'danger' : 'info');
                                $produkNama = $log->produk?->nama ?? '[Produk Dihapus]';
                                $perubahanDisplay = ($perubahan > 0 ? '+' : '') . number_format(abs($perubahan));
                                
                                // Membersihkan Keterangan dari Penanda Teknis
                                $keteranganBersih = preg_replace('/\s*\[[^\]]*\]/', '', $log->keterangan);
                            @endphp
                            <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-surface-50 transition-colors h-16">
                                {{-- NO (global pagination) --}}
                                <td class="px-4 py-4 text-center align-middle text-xs text-text-muted">
                                    {{ $loop->iteration + ($riwayat_stok->currentPage() - 1) * $riwayat_stok->perPage() }}
                                </td>

                                {{-- PRODUK --}}
                                <td class="px-4 py-4 text-left align-middle text-text-main">
                                    <span class="font-semibold {{ $log->produk ? 'text-text-main' : 'text-danger italic' }}">
                                        {{ $produkNama }}
                                    </span>
                                </td>

                                {{-- TANGGAL LOG (HANYA TANGGAL) --}}
                                <td class="px-4 py-4 text-center align-middle">
                                    <span class="text-sm text-text-muted">
                                        {{ Carbon::parse($log->tanggal)->locale('id')->isoFormat('D MMM YYYY') }}
                                    </span>
                                </td>

                                {{-- PERUBAHAN --}}
                                <td class="px-4 py-4 text-center align-middle">
                                    <span class="font-bold text-sm {{ $perubahan > 0 ? 'text-success' : ($perubahan < 0 ? 'text-danger' : 'text-info') }}">
                                        {{ $perubahanDisplay }}
                                    </span>
                                </td>

                                {{-- STATUS BADGE --}}
                                <td class="px-4 py-4 text-center align-middle">
                                    <x-ui.badge :variant="$badgeColor">
                                        {{ strtoupper($status) }}
                                    </x-ui.badge>
                                </td>

                                {{-- KET. LOG (MENGGUNAKAN KETERANGAN BERSIH) --}}
                                <td class="px-4 py-4 text-left align-middle">
                                    <p class="text-xs md:text-sm text-text-muted line-clamp-2">
                                        {{ $keteranganBersih ?? '-' }}
                                    </p>
                                </td>

                                {{-- AKSI (DETAIL, EDIT SAJA) --}}
                                <td class="px-4 py-4 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- 1. DETAIL --}}
                                        <div class="relative group flex items-center justify-center">
                                            <a
                                                href="{{ route('admin.stok_produk.history.detail', $log->id) }}"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                                title="Lihat Detail Log"
                                            >
                                                <i data-lucide="eye" class="w-6 h-6"></i>
                                            </a>
                                            <span class="pointer-events-none absolute top-[30px] mt-1 left-1/2 -translate-x-1/2 text-[10px] font-medium text-info opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                Detail
                                            </span>
                                        </div>

                                        {{-- EDIT --}}
                                        <div x-data="{ editing: false }" class="relative flex flex-col items-center justify-start h-full">
                                            <button 
                                                type="button"
                                                @mouseenter="editing = true"
                                                @mouseleave="editing = false"
                                                @click.stop="resetEditForm(); openEdit = true" 
                                                title="Edit Produk"
                                                class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="square-pen" class="w-6 h-6"></i>
                                            </button>
                                            <span x-show="editing" 
                                                x-cloak 
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-2"
                                                class="absolute top-[33px] text-[10px] font-medium text-yellow-600 whitespace-nowrap z-0">
                                                Edit
                                            </span>
                                        </div>
                                        
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-text-muted italic">
                                    Belum ada riwayat pergerakan stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $riwayat_stok->onEachSide(1)->links() }}
            </div>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.admin>