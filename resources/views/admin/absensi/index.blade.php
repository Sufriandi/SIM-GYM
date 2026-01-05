{{-- resources/views/admin/absensi/index.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Absensi Member';

    $modeLabel = [
        'harian'   => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan'  => 'Bulanan',
    ];

    $currentMode = $periodeAktif->tipe_periode ?? 'harian';

    $qrSnippet   = isset($periodeAktif->kode_qr)
        ? strtoupper(substr($periodeAktif->kode_qr, 0, 8))
        : 'UNKNOWN';

    // Opsi sorting
    $sortOptions = [
        'newest' => 'Waktu hadir · Terbaru',
        'oldest' => 'Waktu hadir · Terlama',
    ];
    $currentSort = request('sort', 'newest');

    // Apakah server memang punya data di halaman ini
    $hasServerData = $kehadiran->count() > 0;
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Gunakan QR untuk mencatat kehadiran, lalu filter daftar absensi berdasarkan tanggal dan member."
>
    <div
        x-data="{
            showFilter: false,
            searchTerm: '',
            visibleCount: 0,

            // fungsi cek prefix nama / username
            matches(name, username) {
                if (!this.searchTerm) return true;

                const term = this.searchTerm.toLowerCase();
                name = (name || '').toLowerCase();
                username = (username || '').toLowerCase();

                return name.startsWith(term) || username.startsWith(term);
            },
        }"
        class="space-y-6 print:space-y-4"
    >
        {{-- HEADER UTAMA + GARIS PEMBATAS --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Gunakan QR untuk mencatat kehadiran, lalu filter daftar absensi berdasarkan tanggal dan member."
        />

        <hr class="border-t border-brand-borderSoft mb-4 hidden-print">

        {{-- BAR ATAS: MODE PERIODE + TOMBOL CETAK KARTU QR --}}
        <div class="hidden-print mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- KIRI: MODE PERIODE --}}
            <div class="flex flex-col md:flex-row md:items-center gap-1.5 md:gap-3">
                <span class="text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                    Mode Periode
                </span>

                <form
                    action="{{ route('admin.absensi.index') }}"
                    method="GET"
                    class="flex flex-wrap items-center gap-2"
                >
                    {{-- bawa filter lain --}}
                    <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
                    <input type="hidden" name="sort" value="{{ $currentSort }}">

                    @foreach($modeOptions as $key => $label)
                        <button
                            type="submit"
                            name="mode"
                            value="{{ $key }}"
                            class="px-4 py-1.5 rounded-full text-[11px] font-semibold border transition-all duration-150
                                {{ $currentMode === $key
                                    ? 'bg-gold-700 text-brand-nav border-gold-700 shadow-md'
                                    : 'bg-brand-card text-brand-textSoft border-brand-borderSoft hover:border-gold-500 hover:text-brand-text' }}"

                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </form>
            </div>

            {{-- KANAN: TOMBOL CETAK KARTU QR --}}
            <div class="flex items-center justify-start md:justify-end">
                <a
                    href="{{ route('admin.absensi.print', [
                        'mode'    => $currentMode,
                        'tanggal' => request('tanggal'),
                        'sort'    => $currentSort,
                    ]) }}"
                    target="_blank"
                >
                    <x-ui.button-primary type="button">
                        <i data-lucide="printer" class="w-4 h-4 mr-1"></i>
                        Cetak Kartu QR
                    </x-ui.button-primary>
                </a>
            </div>
        </div>

        {{-- CARD: PERIODE ABSENSI AKTIF + QR --}}
        <section
            class="bg-brand-card rounded-[32px] border border-brand-borderSoft shadow-card-strong overflow-hidden
                   print:w-full print:shadow-none print:border"
        >
            <div class="flex flex-col md:flex-row items-center justify-between px-8 py-8 gap-8">
                {{-- Info Periode --}}
                <div class="space-y-2 md:space-y-3 md:flex-1">
                    <p class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-[0.16em] uppercase text-brand-textSoft">
                        <span class="inline-flex h-6 px-3 items-center rounded-full bg-brand-surface-100 border border-brand-borderSoft text-[10px] font-semibold text-brand-text">
                            Periode {{ $modeLabel[$currentMode] ?? strtoupper($currentMode) }}
                        </span>
                        <span>Absensi Aktif</span>
                    </p>

                    <h2 class="text-2xl md:text-3xl font-heading font-semibold tracking-tight text-brand-text">
                        Periode Absensi Aktif
                    </h2>

                    <p class="text-brand-text text-sm">
                        {{ Carbon::parse($periodeAktif->tanggal_mulai)->format('d M Y') }}
                        &ndash;
                        {{ Carbon::parse($periodeAktif->tanggal_selesai)->format('d M Y') }}
                    </p>

                    <p class="text-[11px] text-brand-textSoft max-w-lg mt-1.5">
                        Sistem otomatis membuat periode baru jika belum ada periode aktif
                        untuk hari ini pada mode yang dipilih.
                    </p>

                    <p class="text-[11px] text-brand-textSoft mt-3">
                        ID Kartu:
                        <span class="inline-flex items-center rounded-full bg-brand-surface-100 border border-brand-borderSoft px-3 py-1 font-mono text-[11px]">
                            ABS-{{ $qrSnippet }}
                        </span>
                    </p>
                </div>

                {{-- QR Code --}}
                <div class="flex flex-col items-center gap-3 md:items-end">
                    <span class="text-[11px] font-medium text-brand-textSoft uppercase tracking-wide">
                        QR Absensi Member
                    </span>

                    <div class="bg-white p-3 rounded-3xl border border-brand-borderSoft shadow-md print:border-black">
                        {!! QrCode::size(220)->margin(1)->generate($qrUrl) !!}
                    </div>

                    <p class="text-[11px] text-brand-textSoft text-center md:text-right max-w-xs">
                        Cetak kartu QR dan letakkan di area yang mudah dijangkau saat member melakukan absensi.
                    </p>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
{{-- DAFTAR KEHADIRAN 1 BULAN PENUH --}}
{{-- ========================================================= --}}
@php
    // Periode tampilan tabel: 1 bulan penuh.
    // Jika user memilih filter tanggal, bulan yang dipakai mengikuti bulan dari tanggal itu.
    $baseDate   = request('tanggal') ? Carbon::parse(request('tanggal')) : now();
    $monthStart = $baseDate->copy()->startOfMonth();
    $monthEnd   = $baseDate->copy()->endOfMonth();

    // Apakah server memang punya data di halaman ini
    $hasServerData = $kehadiran->count() > 0;
@endphp

<section class="bg-brand-card rounded-3xl border border-brand-borderSoft shadow-card-soft overflow-visible">
    {{-- HEADER + SEARCH + FILTER --}}
    <div class="px-6 pt-6 pb-4 border-b border-brand-borderSoft/60 hidden-print">
        {{-- HEADER TITLE + TOTAL --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-base font-heading font-bold text-brand-text">
                    Daftar Kehadiran
                </h3>
                <p class="text-[11px] text-brand-textSoft mt-0.5">
                    Menampilkan data 1 bulan penuh:
                    <span class="font-semibold text-brand-text">
                        {{ $monthStart->format('d M Y') }} – {{ $monthEnd->format('d M Y') }}
                    </span>
                </p>
            </div>

            <div class="text-[11px] text-brand-textSoft text-right">
                <p>
                    Total:
                    <span class="font-semibold text-brand-text">
                        {{ method_exists($kehadiran, 'total') ? $kehadiran->total() : $kehadiran->count() }} data
                    </span>
                </p>
            </div>
        </div>

        {{-- SEARCH + FILTER BAR --}}
        <div class="relative max-w-xl">
            <form action="{{ route('admin.absensi.index') }}" method="GET">
                {{-- mode tetap dibawa (untuk kartu QR), meski tabel ini tidak mengikuti mode --}}
                <input type="hidden" name="mode" value="{{ $currentMode }}">

                <div class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm h-[46px]">
                    <div class="pl-4 text-brand-textSoft">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>

                    {{-- SEARCH FRONTEND (tanpa name → tidak ikut ke query string) --}}
                    <input
                        type="text"
                        x-model="searchTerm"
                        @input="/* reset counter realtime */"
                        placeholder="Cari nama member..."
                        class="w-full bg-transparent border-none text-sm text-brand-text placeholder:text-brand-textSoft/60 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full"
                        @keydown.enter.prevent
                    >

                    <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                    {{-- BUTTON FILTER --}}
                    <button
                        type="button"
                        @click="showFilter = !showFilter"
                        class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-brand-textSoft hover:text-brand-text mr-1 rounded-full hover:bg-brand-surface-50"
                    >
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>
                </div>
                

                {{-- FILTER DROPDOWN --}}
                <div
                    x-show="showFilter"
                    x-cloak
                    @click.outside="showFilter = false"
                    @keydown.escape.window="showFilter = false"
                    class="absolute z-30 mt-3 left-0 w-full bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5"
                >
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                            <h4 class="text-sm font-semibold text-brand-text">Filter & Urutkan</h4>
                            <a
                                href="{{ route('admin.absensi.index', ['mode' => $currentMode]) }}"
                                class="text-xs text-danger hover:underline"
                            >
                                Reset
                            </a>
                        </div>
                        

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- FILTER TANGGAL (mulai dari) --}}
<div>
    <label class="block text-[10px] font-bold uppercase text-brand-textSoft mb-1">
        Mulai dari tanggal (opsional)
    </label>
    <input
        type="date"
        name="tanggal"
        value="{{ request('tanggal') }}"
        class="w-full rounded-lg border bg-brand-shell text-xs text-brand-text px-3 py-2 border-brand-borderSoft
               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
    >
    <p class="text-[10px] text-brand-textSoft mt-1">
        Jika diisi, data ditampilkan dari tanggal ini sampai akhir bulan yang dipilih.
    </p>
</div>

                            {{-- FILTER BULAN (termasuk tahun) --}}
<div>
    <label class="block text-[10px] font-bold uppercase text-brand-textSoft mb-1">
        Bulan yang ditampilkan
    </label>
    <input
        type="month"
        name="bulan"
        value="{{ request('bulan', $monthStart->format('Y-m')) }}"
        class="w-full rounded-lg border bg-brand-shell text-xs text-brand-text px-3 py-2 border-brand-borderSoft
               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
    >
    <p class="text-[10px] text-brand-textSoft mt-1">
        Pilih bulan & tahun (mis. 2025-12).
    </p>
</div>

                            

                            {{-- URUTKAN BERDASARKAN --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-brand-textSoft mb-1">
                                    Urutkan berdasarkan
                                </label>
                                <select
                                    name="sort"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-brand-text px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                >
                                    @foreach ($sortOptions as $key => $label)
                                        <option value="{{ $key }}" {{ $currentSort === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-brand-textSoft mt-1">
                                    Atur urutan data berdasarkan waktu kehadiran.
                                </p>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-primary-dark text-white text-sm font-medium py-2 rounded-lg"
                        >
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL KEHADIRAN --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-brand-surface-100 border-b border-brand-borderSoft">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-brand-textSoft uppercase tracking-wide w-[60px]">
                        No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                        Tanggal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                        Member
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                        Jam Masuk
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                        Keterangan
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-brand-borderSoft/60">
                @forelse ($kehadiran as $row)
                    @php
                        $rowName     = optional($row->member->user)->name ?? '';
                        $rowUsername = optional($row->member->user)->username ?? '';
                    @endphp

                    <tr
                        data-row
                        x-show="matches(@js($rowName), @js($rowUsername))"
                        x-effect="
                            // Counter stabil (tidak terus nambah saat re-render)
                            const ok = matches(@js($rowName), @js($rowUsername));
                            if (ok && !$el.__counted) { $el.__counted = true; visibleCount = (visibleCount || 0) + 1; }
                            if (!ok && $el.__counted) { $el.__counted = false; visibleCount = Math.max((visibleCount || 1) - 1, 0); }
                        "
                        class="hover:bg-brand-surface-50"
                    >
                        {{-- NO --}}
                        <td class="px-4 py-4 text-center align-middle text-xs text-brand-textSoft">
                            {{ $loop->iteration + ($kehadiran->currentPage() - 1) * $kehadiran->perPage() }}
                        </td>

                        {{-- TANGGAL --}}
                        <td class="px-6 py-4 align-middle">
                            {{ Carbon::parse($row->tanggal)->format('d M Y') }}
                        </td>

                        {{-- MEMBER (NAMA SAJA) --}}
                        <td class="px-6 py-4 align-middle text-sm font-semibold text-brand-text">
                            {{ optional($row->member->user)->name ?? '-' }}
                        </td>

                        {{-- JAM MASUK --}}
                        <td class="px-6 py-4 align-middle">
                            {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                        </td>

                        {{-- KETERANGAN --}}
                        <td class="px-6 py-4 align-middle text-xs text-brand-textSoft">
                            {{ $row->is_valid ? 'Valid' : 'Perlu ditinjau' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-brand-textSoft">
                            Belum ada kehadiran tercatat pada bulan ini.
                        </td>
                    </tr>
                @endforelse

                {{-- Pesan jika server punya data, tapi hasil pencarian di halaman ini kosong --}}
                @if ($hasServerData)
                    <tr x-show="searchTerm && (visibleCount === 0 || !visibleCount)">
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-brand-textSoft">
                            Data yang Anda cari tidak ditemukan pada bulan ini.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($kehadiran->hasPages())
        <div class="px-6 py-4 border-t border-brand-borderSoft bg-brand-card/60 hidden-print">
            {{ $kehadiran->appends([
                'mode'    => $currentMode,       // untuk kartu QR
                'tanggal' => request('tanggal'), // untuk bulan yang dipilih
                'sort'    => $currentSort,
            ])->links() }}
        </div>
    @endif
</section>

    </div>

    {{-- CSS khusus untuk print --}}
    <style>
        @media print {
            body {
                background: #ffffff !important;
            }

            nav, header, footer,
            .hidden-print {
                display: none !important;
            }

            .shadow-card-strong,
            .shadow-card-soft {
                box-shadow: none !important;
            }

            .bg-brand-card {
                background: #ffffff !important;
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</x-layouts.admin>
