{{-- resources/views/member/izin_latihan/history.blade.php --}}

@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Riwayat Pengajuan Kompensasi';

    // Untuk kolom "No" pada pagination
    $startNumber = method_exists($daftar_izin, 'firstItem') && $daftar_izin->firstItem()
        ? (int) $daftar_izin->firstItem()
        : 1;
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    pageSubtitle="Semua pengajuan kompensasi membership yang pernah Anda ajukan."
>
    <div class="max-w-6xl mx-auto space-y-4 pt-2">

        {{-- FLASH MESSAGE (error saja, success lewat toast global) --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- JUDUL + SUBTITLE --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Berisi pengajuan dengan status Pending, Disetujui, maupun Ditolak."
        />

        {{-- GARIS PEMBATAS (emas) --}}
        <hr class="border-t border-brand-borderSoft mb-2">

        {{-- BARIS AKSI (Kembali kiri, Ajukan kanan) --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-2">
            {{-- Kembali ke Pengajuan Kompensasi --}}
            <div>
                <x-ui.back-button
                    href="{{ route('member.izin_latihan.index') }}"
                    text="Kembali ke Pengajuan Kompensasi"
                />
            </div>

            {{-- Ajukan Kompensasi Baru --}}
            <div class="md:text-right">
                <a href="{{ route('member.izin_latihan.create') }}">
                    <x-ui.button-primary class="inline-flex items-center">
                        Ajukan Kompensasi Baru
                        <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </a>
            </div>
        </div>

        {{-- CARD TABEL RIWAYAT --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            {{-- HEADER CARD --}}
            <div
                class="px-4 sm:px-6 py-4 border-b border-brand-borderSoft
                       flex items-center justify-between gap-4"
            >
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-text-main">
                        Riwayat Pengajuan Kompensasi
                    </h2>
                    <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                        Pantau seluruh pengajuan kompensasi membership yang pernah Anda lakukan.
                    </p>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="px-2 sm:px-3 pb-2 pt-1 overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                            {{-- NO --}}
                            <th
                                class="pl-3 pr-2 py-3 text-left text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[6%]"
                            >
                                No
                            </th>

                            {{-- PERIODE KOMPENSASI + DIAJUKAN --}}
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[30%]"
                            >
                                Periode Kompensasi
                            </th>

                            {{-- DURASI DIAJUKAN --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[14%]"
                            >
                                Durasi Diajukan
                            </th>

                            {{-- DURASI DISETUJUI --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[14%]"
                            >
                                Durasi Disetujui
                            </th>

                            {{-- STATUS --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[14%]"
                            >
                                Status
                            </th>

                            {{-- DIPROSES --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[16%]"
                            >
                                Diproses
                            </th>

                            {{-- DETAIL --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[6%]"
                            >
                                Detail
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse($daftar_izin as $izin)
                            @php
                                $mulai   = Carbon::parse($izin->tanggal_mulai);
                                $selesai = Carbon::parse($izin->tanggal_selesai);

                                $diprosesAt = $izin->tanggal_persetujuan
                                    ? Carbon::parse($izin->tanggal_persetujuan)
                                    : null;

                                $approvedDays = ($izin->status === 'disetujui')
                                    ? (int) ($izin->durasi_izin_disetujui ?? 0)
                                    : 0;

                                $rowNo = $startNumber + $loop->index;
                                $detailUrl = route('member.izin_latihan.detail', $izin->id)
                                    . '?from=history&back=' . urlencode(request()->fullUrl());
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                {{-- No --}}
                                <td class="pl-3 pr-2 py-3 align-top text-text-muted">
                                    {{ $rowNo }}
                                </td>

                                {{-- Periode + Diajukan --}}
                                <td class="px-3 py-3 align-top">
                                    <div class="text-text-main font-medium whitespace-nowrap">
                                        {{ $mulai->translatedFormat('d M Y') }} —
                                        {{ $selesai->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-text-muted mt-0.5">
                                        Diajukan:
                                        {{ $izin->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                    </div>
                                </td>

                                {{-- Durasi Diajukan --}}
                                <td class="px-3 py-3 text-center align-middle">
                                    <span class="font-semibold text-text-main whitespace-nowrap">
                                        {{ (int) ($izin->jumlah_hari ?? 0) }}
                                        <span class="text-xs text-text-muted ml-0.5">hari</span>
                                    </span>
                                </td>

                                {{-- Durasi Disetujui --}}
                                <td class="px-3 py-3 text-center align-middle">
                                    <span class="font-semibold text-text-main whitespace-nowrap">
                                        {{ $approvedDays }}
                                        <span class="text-xs text-text-muted ml-0.5">hari</span>
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-3 text-center align-middle">
                                    @if($izin->status === 'pending')
                                        <x-ui.badge variant="warning">Pending</x-ui.badge>
                                    @elseif($izin->status === 'disetujui')
                                        <x-ui.badge variant="success">Disetujui</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                    @endif
                                </td>

                                {{-- Diproses --}}
                                <td class="px-3 py-3 text-center align-middle text-text-muted">
                                    @if($diprosesAt)
                                        <span class="whitespace-nowrap text-xs sm:text-sm">
                                            {{ $diprosesAt->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    @else
                                        <span class="text-[11px] italic">Belum diproses</span>
                                    @endif
                                </td>

                                {{-- Detail --}}
                                <td class="px-3 py-3 text-right align-middle">
                                    <a
                                        href="{{ $detailUrl }}"
                                        class="inline-flex items-center gap-1 text-[13px] font-semibold
                                               text-gold-600 hover:text-gold-500 transition-colors whitespace-nowrap"
                                    >
                                        Detail
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-text-muted text-sm italic">
                                    Belum ada riwayat pengajuan kompensasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($daftar_izin, 'links'))
                <div class="mt-4">
                    {{ $daftar_izin->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.member>
