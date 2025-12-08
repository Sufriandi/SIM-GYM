{{-- resources/views/admin/absensi/kehadiran/index.blade.php --}}
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
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Gunakan QR untuk mencatat kehadiran, lalu filter daftar absensi berdasarkan tanggal dan member."
>
    <div class="space-y-6 print:space-y-4" x-data="{ showFilter: false }">

        {{-- HEADER UTAMA + GARIS PEMBATAS --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Gunakan QR untuk mencatat kehadiran, lalu filter daftar absensi berdasarkan tanggal dan member."
        />

        <hr class="border-t border-brand-borderSoft mb-4 hidden-print">

        {{-- ===================================================== --}}
        {{-- BAR ATAS: MODE PERIODE + SEARCH + FILTER + CETAK QR   --}}
        {{-- ===================================================== --}}
        <div class="hidden-print mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- KIRI: MODE PERIODE (dibuat sejajar secara horizontal di md+) --}}
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
                    <input type="hidden" name="member" value="{{ request('member') }}">
                    <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">

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

            {{-- TENGAH: SEARCH + FILTER --}}
            <div class="relative w-full max-w-md md:max-w-xl flex-1">
                <form action="{{ route('admin.absensi.index') }}" method="GET" x-ref="searchForm">
                    {{-- mode tetap dibawa --}}
                    <input type="hidden" name="mode" value="{{ $currentMode }}">

                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm h-[46px]"
                    >
                        <div class="pl-4 text-brand-textSoft">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>

                        <input
                            type="text"
                            name="member"
                            value="{{ request('member') }}"
                            placeholder="Cari nama / username member..."
                            class="w-full bg-transparent border-none text-sm text-brand-text placeholder:text-brand-textSoft/60 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full"
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

                    {{-- FILTER DROPDOWN (TANGGAL) --}}
                    <div
                        x-show="showFilter"
                        x-cloak
                        @click.outside="showFilter = false"
                        class="absolute top-[52px] left-0 w-full bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-10"
                    >
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-brand-text">Filter Kehadiran</h4>
                                <a
                                    href="{{ route('admin.absensi.index', ['mode' => $currentMode]) }}"
                                    class="text-xs text-danger hover:underline"
                                >
                                    Reset
                                </a>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- FILTER TANGGAL --}}
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-brand-textSoft mb-1">
                                        Tanggal Kehadiran
                                    </label>
                                    <input
                                        type="date"
                                        name="tanggal"
                                        value="{{ request('tanggal') }}"
                                        class="w-full rounded-lg border bg-brand-shell text-xs text-brand-text px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                    >
                                    <p class="text-[10px] text-brand-textSoft mt-1">
                                        Kosongkan jika ingin menampilkan semua tanggal dalam periode aktif.
                                    </p>
                                </div>

                                {{-- RINGKASAN FILTER --}}
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-brand-textSoft mb-1">
                                        Ringkasan
                                    </label>
                                    <div class="text-[11px] text-brand-textSoft space-y-1.5">
                                        <p>
                                            Mode:
                                            <span class="font-semibold text-brand-text">
                                                {{ $modeLabel[$currentMode] ?? ucfirst($currentMode) }}
                                            </span>
                                        </p>
                                        <p>
                                            Member:
                                            <span class="font-semibold text-brand-text">
                                                {{ request('member') ?: 'Semua' }}
                                            </span>
                                        </p>
                                        <p>
                                            Tanggal:
                                            <span class="font-semibold text-brand-text">
                                                @if (request('tanggal'))
                                                    {{ Carbon::parse(request('tanggal'))->format('d M Y') }}
                                                @else
                                                    Semua dalam periode
                                                @endif
                                            </span>
                                        </p>
                                    </div>
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

            {{-- KANAN: TOMBOL CETAK KARTU QR --}}
            <div class="flex items-center justify-start md:justify-end">
                <a
                    href="{{ route('admin.absensi.print', [
                        'mode'    => $currentMode,
                        'tanggal' => request('tanggal'),
                        'member'  => request('member'),
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

        {{-- ================================== --}}
        {{-- CARD: PERIODE ABSENSI AKTIF + QR   --}}
        {{-- ================================== --}}
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

        {{-- ===================== --}}
        {{-- DAFTAR KEHADIRAN      --}}
        {{-- ===================== --}}
        <section class="bg-brand-card rounded-3xl border border-brand-borderSoft shadow-card-soft overflow-hidden">
            <div class="px-6 pt-6 pb-4 border-b border-brand-borderSoft/60 hidden-print flex items-center justify-between">
                <div>
                    <h3 class="text-base font-heading font-bold text-brand-text">
                        Daftar Kehadiran
                    </h3>
                    <p class="text-[11px] text-brand-textSoft mt-0.5">
                        Menampilkan kehadiran member dalam periode absensi aktif dengan filter yang sudah diterapkan.
                    </p>
                </div>
                <div class="text-[11px] text-brand-textSoft text-right">
                    <p>
                        Total:
                        <span class="font-semibold text-brand-text">
                            {{ $kehadiran->total() }} data
                        </span>
                    </p>
                </div>
            </div>

            {{-- TABEL KEHADIRAN --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-surface-100 border-b border-brand-borderSoft">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Jam Masuk
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Member
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                                Keterangan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/60">
                        @forelse ($kehadiran as $row)
                            <tr class="hover:bg-brand-surface-50">
                                <td class="px-6 py-3 align-top">
                                    {{ Carbon::parse($row->tanggal)->format('d M Y') }}
                                </td>
                                <td class="px-6 py-3 align-top">
                                    {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 align-top">
                                    <div class="font-semibold text-brand-text">
                                        {{ $row->member->nama ?? '-' }}
                                    </div>
                                    @if($row->member && $row->member->username)
                                        <div class="text-xs text-brand-textSoft">
                                            {{ '@' . $row->member->username }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 align-top text-xs text-brand-textSoft">
                                    {{ $row->is_valid ? 'Valid' : 'Perlu ditinjau' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-brand-textSoft">
                                    Belum ada kehadiran tercatat dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($kehadiran->hasPages())
                <div class="px-6 py-4 border-t border-brand-borderSoft bg-brand-card/60 hidden-print">
                    {{ $kehadiran->links() }}
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
