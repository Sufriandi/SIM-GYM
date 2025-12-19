{{-- resources/views/admin/izin_latihan/history.blade.php --}}

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Daftar izin latihan yang sudah diproses oleh Admin.">
    <x-ui.section-header :title="$pageTitle"
        subtitle="Daftar izin latihan yang sudah disetujui atau ditolak oleh Admin." />

    <div class="mt-2 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <x-ui.back-button href="{{ route('admin.izin_latihan.index') }}" text="Kembali ke Permintaan Pending" />

        {{-- SORTING --}}
        <div class="relative" x-data="{ showSort: false }">
            <form action="{{ route('admin.izin_latihan.history') }}" method="GET" class="w-full">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showSort = !showSort"
                        class="inline-flex items-center gap-2 rounded-full border border-brand-borderSoft bg-brand-card px-4 py-2 text-sm font-medium text-text-muted hover:text-text-main hover:bg-brand-surface-50 transition"
                    >
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span>Sorting</span>
                    </button>

                    <div class="rounded-full border border-brand-borderSoft bg-brand-surface-50 px-3 py-2">
                        <span class="text-xs font-semibold text-text-main">
                            {{ $riwayat_izin->total() }} Data
                        </span>
                    </div>
                </div>

                <div
                    x-show="showSort"
                    x-cloak
                    @click.outside="showSort = false"
                    class="absolute right-0 mt-2 w-[280px] rounded-2xl border border-brand-borderSoft bg-brand-card shadow-xl p-4 z-20"
                >
                    <div class="flex items-center justify-between pb-2 border-b border-brand-borderSoft/50">
                        <h4 class="text-sm font-semibold text-text-main">Urutkan Riwayat</h4>
                        <a href="{{ route('admin.izin_latihan.history') }}" class="text-xs text-danger hover:underline">
                            Reset
                        </a>
                    </div>

                    <div class="mt-3 space-y-2">
                        <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                            Pilih urutan
                        </label>

                        <select name="sort" class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell text-sm text-text-main px-3 py-2">
                            <option value="processed_newest" {{ request('sort', 'processed_newest') === 'processed_newest' ? 'selected' : '' }}>
                                Diproses · Terbaru
                            </option>
                            <option value="processed_oldest" {{ request('sort') === 'processed_oldest' ? 'selected' : '' }}>
                                Diproses · Terlama
                            </option>
                            <option value="approved_max" {{ request('sort') === 'approved_max' ? 'selected' : '' }}>
                                Disetujui (H) · Terbanyak
                            </option>
                            <option value="approved_min" {{ request('sort') === 'approved_min' ? 'selected' : '' }}>
                                Disetujui (H) · Tersedikit
                            </option>
                            <option value="requested_max" {{ request('sort') === 'requested_max' ? 'selected' : '' }}>
                                Diajukan (H) · Terbanyak
                            </option>
                            <option value="requested_min" {{ request('sort') === 'requested_min' ? 'selected' : '' }}>
                                Diajukan (H) · Tersedikit
                            </option>
                        </select>

                        <p class="text-[11px] text-text-muted">
                            Default menggunakan tanggal persetujuan terbaru.
                        </p>

                        <button type="submit" class="w-full bg-primary-dark text-white text-sm font-medium py-2 rounded-xl">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <hr class="border-t border-brand-borderSoft mb-6 mt-3">

    {{-- WRAPPER ALPINE UNTUK MODAL DETAIL --}}
    <div x-data="{ openDetailId: null }"
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
        <div class="w-full">
            <x-ui.card class="border-brand-borderSoft">
                <div class="mb-5">
                    <h2 class="text-2xl font-heading text-text-main mb-1">
                        Riwayat Persetujuan &amp; Penolakan
                    </h2>
                    <p class="text-sm text-text-muted">
                        Semua izin yang sudah <span class="font-semibold text-success">disetujui</span> atau
                        <span class="font-semibold text-danger">ditolak</span> oleh Admin.
                    </p>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse text-xs md:text-sm lg:text-base md:min-w-[900px]">
                        <thead>
                            <tr class="border-b-2 border-brand-borderSoft/80 bg-brand-shell/60">
                                <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase w-[6%]">
                                    No
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase">
                                    Member
                                </th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Diajukan (H)
                                </th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Disetujui (H)
                                </th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-wide text-text-main uppercase w-[28%]">
                                    Ket. Admin
                                </th>
                                <th class="px-4 py-3 text-center text-[11px] font-semibold tracking-wide text-text-main uppercase whitespace-nowrap">
                                    Detail
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayat_izin as $izin)
                                @php
                                    // DB terbaru: ambil nama dari users.name (fallback ke members.nama jika ada data lama)
                                    $memberDisplayName = $izin->member?->user?->name;
                                    if (!$memberDisplayName) {
                                        $memberDisplayName = $izin->member?->nama;
                                    }
                                    $memberDisplayName = $memberDisplayName ?: '[Member Dihapus]';
                                @endphp

                                <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-surface-50 transition-colors h-16">
                                    <td class="px-4 py-4 text-center align-middle text-xs text-text-muted">
                                        {{ $loop->iteration + ($riwayat_izin->currentPage() - 1) * $riwayat_izin->perPage() }}
                                    </td>

                                    <td class="px-4 py-4 text-left align-middle">
                                        @if ($izin->member && $izin->member->user)
                                            <div class="text-base font-bold text-text-main">
                                                {{ $memberDisplayName }}
                                            </div>
                                        @else
                                            <div class="text-sm text-danger italic">
                                                {{ $memberDisplayName }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-center align-middle">
                                        <span class="font-semibold text-text-main">{{ $izin->jumlah_hari }}</span>
                                        <span class="ml-1 text-xs md:text-[13px] text-text-muted align-middle">Hari</span>
                                    </td>

                                    <td class="px-4 py-4 text-center align-middle">
                                        @if ($izin->status === 'disetujui')
                                            <span class="font-semibold text-success">{{ (int) ($izin->durasi_izin_disetujui ?? 0) }}</span>
                                        @else
                                            <span class="font-semibold text-danger">0</span>
                                        @endif
                                        <span class="ml-1 text-xs md:text-[13px] text-text-muted align-middle">Hari</span>
                                    </td>

                                    <td class="px-4 py-4 text-center align-middle">
                                        @if ($izin->status === 'disetujui')
                                            <x-ui.badge variant="success">DISETUJUI</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="danger">DITOLAK</x-ui.badge>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-left align-middle">
                                        <p class="text-xs md:text-sm text-text-muted line-clamp-2">
                                            {{ $izin->keterangan_admin ?: 'Tidak ada keterangan.' }}
                                        </p>
                                    </td>

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

                <div class="mt-6">
                    {{ $riwayat_izin->onEachSide(1)->links() }}
                </div>
            </x-ui.card>
        </div>

        {{-- MODAL DETAIL --}}
        @foreach ($riwayat_izin as $izin)
            @include('admin.izin_latihan.modals.detail', ['izin' => $izin])
        @endforeach
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.admin>
