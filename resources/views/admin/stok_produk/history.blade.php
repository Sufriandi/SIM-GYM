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

    {{-- WRAPPER ALPINE UNTUK MODAL DETAIL --}}
    <div
        x-data="{ openDetailId: null }"
        x-effect="
            const main = document.querySelector('main');
            const html = document.documentElement;
            const body = document.body;
            const locked = !!openDetailId;

            const targets = [html, body, main].filter(Boolean);

            if (locked) {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY === undefined) {
                        el.dataset.prevOverflowY = el.style.overflowY || '';
                    }
                    el.style.overflowY = 'hidden';
                });
            } else {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY !== undefined) {
                        el.style.overflowY = el.dataset.prevOverflowY;
                        delete el.dataset.prevOverflowY;
                    } else {
                        el.style.removeProperty('overflow-y');
                    }
                });
            }
        "
    >
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
                <div class="overflow-x-auto custom-scrollbar">
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
                                    Detail
                                </th>
                            </tr>
                        </thead>

                        <tbody>
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

                                    {{-- TANGGAL LOG --}}
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

                                    {{-- KET. LOG --}}
                                    <td class="px-4 py-4 text-left align-middle">
                                        <p class="text-xs md:text-sm text-text-muted line-clamp-2">
                                            {{ $keteranganBersih ?? '-' }}
                                        </p>
                                    </td>

                                    {{-- DETAIL (PAKAI MODAL, TIDAK ADA EDIT) --}}
                                    <td class="px-4 py-4 text-center align-middle">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-1 text-gold-600 hover:text-gold-500 font-semibold text-xs md:text-sm transition-colors whitespace-nowrap"
                                            @click="openDetailId = {{ $log->id }}"
                                        >
                                            <span>Lihat Detail</span>
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </button>
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

        {{-- MODAL DETAIL UNTUK SETIAP LOG STOK --}}
        @foreach ($riwayat_stok as $log)
            @include('admin.stok_produk.modals.history_detail', ['log' => $log])
        @endforeach
    </div>

    <style>
        [x-cloak] { display: none !important; }

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F5E6D6;
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D4A757;
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #A67C39;
        }
    </style>
</x-layouts.admin>
