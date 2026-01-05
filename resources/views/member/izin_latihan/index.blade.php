{{-- resources/views/member/izin_latihan/index.blade.php --}}

@php
    use Carbon\Carbon;

    $pageTitle    = $pageTitle ?? 'Izin Membership';
    $pageSubtitle = $pageSubtitle ?? 'Ajukan kompensasi jika Anda tidak dapat hadir pada periode tertentu.';

    /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $daftar_izin */
    $daftar_izin = $daftar_izin ?? collect();

    $isPaginator = $daftar_izin instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $rows = $isPaginator ? $daftar_izin->getCollection() : collect($daftar_izin);

    $startNo = $isPaginator ? (($daftar_izin->currentPage() - 1) * $daftar_izin->perPage()) : 0;

    $statusMeta = [
        'pending'   => ['label' => 'Pending',   'variant' => 'warning'],
        'disetujui' => ['label' => 'Disetujui', 'variant' => 'success'],
        'ditolak'   => ['label' => 'Ditolak',   'variant' => 'danger'],
    ];
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-6xl mx-auto space-y-4 pt-2">

        {{-- FLASH MESSAGE (error saja, success lewat toast global) --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle" :subtitle="$pageSubtitle" />

        {{-- GARIS PEMBATAS (gaya “emas/border” mengikuti tema Anda) --}}
        <hr class="border-t border-brand-borderSoft">

        {{-- TOOLBAR: tombol sejajar di mobile, di desktop rata kanan --}}
        <div class="grid grid-cols-2 gap-3 sm:flex sm:justify-end sm:gap-3">
            {{-- Riwayat --}}
            <a href="{{ route('member.izin_latihan.history') }}" class="w-full sm:w-auto">
                <x-ui.button-secondary class="w-full justify-center gap-2">
                    <i data-lucide="history" class="w-4 h-4"></i>

                    {{-- Mobile: singkat --}}
                    <span class="sm:hidden">Riwayat</span>
                    {{-- Desktop: lengkap --}}
                    <span class="hidden sm:inline">Riwayat Kompensasi</span>
                </x-ui.button-secondary>
            </a>

            {{-- Ajukan --}}
            <a href="{{ route('member.izin_latihan.create') }}" class="w-full sm:w-auto">
                <x-ui.button-primary class="w-full justify-center gap-2">
                    {{-- Mobile: singkat --}}
                    <span class="sm:hidden">Ajukan</span>
                    {{-- Desktop: lengkap --}}
                    <span class="hidden sm:inline">Ajukan Kompensasi</span>

                    <i data-lucide="calendar-plus" class="w-4 h-4 ml-1"></i>
                </x-ui.button-primary>
            </a>
        </div>

        {{-- CARD TABEL --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            {{-- Header Card --}}
            <div class="px-4 sm:px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-text-main">
                        Pengajuan Kompensasi (Pending)
                    </h2>
                    <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                        Pengajuan kompensasi yang masih menunggu persetujuan Admin.
                    </p>
                </div>
            </div>

            {{-- TABEL: scroll hanya mobile --}}
            <div class="px-2 sm:px-3 pb-2 pt-1">
                <div class="overflow-x-auto sm:overflow-x-hidden custom-scrollbar">
                    <table class="min-w-[860px] sm:min-w-0 w-full table-auto sm:table-fixed text-sm">
                        <thead>
                            <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                                <th class="pl-3 pr-2 py-3 text-left text-xs font-semibold tracking-wide text-text-muted/90 uppercase w-[70px]">
                                    No
                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold tracking-wide text-text-muted/90 uppercase sm:w-[290px]">
                                    Periode Kompensasi (Diajukan)
                                </th>

                                <th class="px-3 py-3 text-center text-xs font-semibold tracking-wide text-text-muted/90 uppercase sm:w-[160px]">
                                    Durasi Diajukan
                                </th>

                                <th class="px-3 py-3 text-center text-xs font-semibold tracking-wide text-text-muted/90 uppercase sm:w-[140px]">
                                    Status
                                </th>

                                <th class="px-3 py-3 text-left text-xs font-semibold tracking-wide text-text-muted/90 uppercase sm:w-[280px]">
                                    Alasan
                                </th>

                                <th class="px-3 py-3 text-center text-xs font-semibold tracking-wide text-text-muted/90 uppercase sm:w-[120px]">
                                    Detail
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-brand-borderSoft/80">
                            @forelse($rows as $izin)
                                @php
                                    $mulai   = $izin->tanggal_mulai ? Carbon::parse($izin->tanggal_mulai) : null;
                                    $selesai = $izin->tanggal_selesai ? Carbon::parse($izin->tanggal_selesai) : null;

                                    $meta = $statusMeta[$izin->status] ?? $statusMeta['pending'];

                                    $alasan = trim((string) ($izin->alasan ?? ''));
                                    if ($alasan === '') $alasan = '-';

                                    $no = $startNo + $loop->iteration;
                                @endphp

                                <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                    {{-- No --}}
                                    <td class="pl-3 pr-2 py-3 align-top text-text-main font-semibold">
                                        {{ $no }}
                                    </td>

                                    {{-- Periode + Diajukan --}}
                                    <td class="px-3 py-3 align-top">
                                        <div class="text-text-main font-medium whitespace-nowrap">
                                            {{ $mulai ? $mulai->translatedFormat('d M Y') : '-' }}
                                            —
                                            {{ $selesai ? $selesai->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-text-muted mt-0.5">
                                            Diajukan:
                                            {{ $izin->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Durasi --}}
                                    <td class="px-3 py-3 text-center align-middle">
                                        <span class="font-semibold text-text-main whitespace-nowrap">
                                            {{ (int) ($izin->jumlah_hari ?? 0) }}
                                            <span class="text-xs text-text-muted ml-0.5">hari</span>
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-3 py-3 text-center align-middle">
                                        <x-ui.badge :variant="$meta['variant']">{{ $meta['label'] }}</x-ui.badge>
                                    </td>

                                    {{-- Alasan (desktop tidak overflow; dipotong) --}}
                                    <td class="px-3 py-3 align-middle">
                                        <div class="text-text-main sm:truncate" title="{{ $alasan }}">
                                            {{ $alasan }}
                                        </div>
                                    </td>

                                    {{-- Detail --}}
                                    <td class="px-3 py-3 text-center align-middle">
                                        <a
                                            href="{{ route('member.izin_latihan.detail', $izin->id) }}?from=index"
                                            class="inline-flex items-center justify-center gap-1 text-[13px] font-semibold
                                                   text-gold-600 hover:text-gold-500 transition-colors whitespace-nowrap"
                                        >
                                            Lihat
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-text-muted text-sm italic">
                                        Belum ada pengajuan kompensasi yang sedang diproses.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($isPaginator && method_exists($daftar_izin, 'links'))
                    <div class="mt-4 px-1 sm:px-0">
                        {{ $daftar_izin->links() }}
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>
</x-layouts.member>
