{{-- resources/views/admin/izin_latihan/history.blade.php --}}

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Daftar izin latihan yang sudah diproses oleh Admin.">
    {{-- JUDUL + SUBTITLE ATAS --}}
    <x-ui.section-header :title="$pageTitle"
        subtitle="Daftar izin latihan yang sudah disetujui atau ditolak oleh Admin." />

    {{-- TOMBOL KEMBALI DI BAWAH SUBTITLE --}}
    <div class="mt-2">
        <x-ui.back-button href="{{ route('admin.izin_latihan.index') }}" text="Kembali ke Permintaan Pending" />
    </div>

    {{-- GARIS PEMBATAS --}}
    <hr class="border-t border-brand-borderSoft mb-6 mt-2">

    {{-- WRAPPER ALPINE UNTUK MODAL DETAIL --}}
    <div x-data="{
        openDetailId: null,
    }"
        x-effect="
            // optional: kunci scroll saat modal detail terbuka
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
        ">
        {{-- CARD UTAMA --}}
        <div class="w-full">
            <x-ui.card class="border-brand-borderSoft">
                {{-- HEADER CARD --}}
                <div class="mb-5">
                    <h2 class="text-2xl font-heading text-text-main mb-1">
                        Riwayat Persetujuan &amp; Penolakan
                    </h2>
                    <p class="text-sm text-text-muted">
                        Semua izin yang sudah <span class="font-semibold text-success">disetujui</span> atau
                        <span class="font-semibold text-danger">ditolak</span> oleh Admin.
                    </p>
                </div>

                {{-- WRAPPER TABEL (RESPONSIVE) --}}
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse text-xs md:text-sm lg:text-base md:min-w-[800px]">
                        <thead>
                            <tr class="border-b-2 border-brand-borderSoft/80 bg-brand-shell/60">
                                {{-- NO --}}
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase w-[6%]">
                                    No
                                </th>
                                {{-- MEMBER --}}
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase">
                                    Member
                                </th>
                                {{-- DIAJUKAN (H) --}}
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Diajukan (H)
                                </th>
                                {{-- DISETUJUI (H) --}}
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Disetujui (H)
                                </th>
                                {{-- STATUS --}}
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase">
                                    Status
                                </th>
                                {{-- KET ADMIN (Diperkecil lebarnya) --}}
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase w-[25%]">
                                    Ket. Admin
                                </th>
                                {{-- DETAIL --}}
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Detail
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayat_izin as $izin)
                                <tr
                                    class="border-b border-brand-borderSoft/60 hover:bg-brand-surface-50 transition-colors h-16">
                                    {{-- NO (global pagination) --}}
                                    <td class="px-4 py-4 text-center align-middle text-xs text-text-muted">
                                        {{ $loop->iteration + ($riwayat_izin->currentPage() - 1) * $riwayat_izin->perPage() }}
                                    </td>

                                    {{-- MEMBER --}}
                                    <td class="px-4 py-4 text-left align-middle text-text-main">
                                        {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                    </td>

                                    {{-- DIAJUKAN (H) – SELALU 1 BARIS --}}
                                    <td class="px-4 py-4 text-center align-middle">
                                        <span class="font-semibold text-text-main">
                                            {{ $izin->jumlah_hari }}
                                        </span>
                                        <span class="ml-1 text-xs md:text-[13px] text-text-muted align-middle">
                                            Hari
                                        </span>
                                    </td>

                                    {{-- DISETUJUI (H) – SELALU 1 BARIS --}}
                                    <td class="px-4 py-4 text-center align-middle">
                                        @if ($izin->status === 'disetujui')
                                            <span class="font-semibold text-success">
                                                {{ $izin->durasi_izin_disetujui ?? 0 }}
                                            </span>
                                        @else
                                            <span class="font-semibold text-danger">
                                                0
                                            </span>
                                        @endif
                                        <span class="ml-1 text-xs md:text-[13px] text-text-muted align-middle">
                                            Hari
                                        </span>
                                    </td>

                                    {{-- STATUS BADGE --}}
                                    <td class="px-4 py-4 text-center align-middle">
                                        @if ($izin->status === 'disetujui')
                                            <x-ui.badge variant="success">
                                                DISETUJUI
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">
                                                DITOLAK
                                            </x-ui.badge>
                                        @endif
                                    </td>

                                    {{-- KET. ADMIN – DIBATASI 2 BARIS --}}
                                    <td class="px-4 py-4 text-left align-middle">
                                        <p class="text-xs md:text-sm text-text-muted line-clamp-2">
                                            {{ $izin->keterangan_admin ?? 'Tidak ada keterangan.' }}
                                        </p>
                                    </td>

                                    {{-- DETAIL (PAKAI MODAL, BUKAN ROUTE) --}}
                                    <td class="px-4 py-4 text-center align-middle">
                                        <button type="button"
                                            class="inline-flex items-center justify-center gap-1 text-gold-600 hover:text-gold-500 font-semibold text-xs md:text-sm transition-colors whitespace-nowrap"
                                            @click="openDetailId = {{ $izin->id }}">
                                            <span>Lihat Detail</span>
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-text-muted italic">
                                        Belum ada data riwayat izin.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-6">
                    {{ $riwayat_izin->onEachSide(1)->links() }}
                </div>
            </x-ui.card>
        </div>

        {{-- MODAL DETAIL UNTUK SETIAP IZIN DI RIWAYAT --}}
        @foreach ($riwayat_izin as $izin)
            @include('admin.izin_latihan.modals.detail', ['izin' => $izin])
        @endforeach
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-layouts.admin>
