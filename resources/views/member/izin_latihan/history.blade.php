{{-- resources/views/member/izin_latihan/history.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Riwayat Pengajuan Izin Lengkap';
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    pageSubtitle="Semua izin latihan yang pernah Anda ajukan."
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
            subtitle="Berisi izin dengan status Pending, Disetujui, maupun Ditolak."
        />

        {{-- TOMBOL KEMBALI --}}
        <div>
            <x-ui.back-button
                href="{{ route('member.izin_latihan.index') }}"
                text="Kembali ke Pengajuan Saat Ini"
            />
        </div>

        {{-- PEMBATAS --}}
        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- BARIS TOMBOL AJUKAN (KANAN) --}}
        <div class="flex justify-start md:justify-end mb-2">
            <a href="{{ route('member.izin_latihan.create') }}">
                <x-ui.button-primary class="inline-flex items-center">
                    Ajukan Izin Baru
                    <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                </x-ui.button-primary>
            </a>
        </div>

        {{-- CARD TABEL RIWAYAT --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            {{-- HEADER CARD (disamakan dengan index) --}}
            <div
                class="px-4 sm:px-6 py-4 border-b border-brand-borderSoft
                       flex items-center justify-between gap-4"
            >
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-text-main">
                        Riwayat Pengajuan Izin
                    </h2>
                    <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                        Pantau seluruh pengajuan izin yang pernah Anda lakukan.
                    </p>
                </div>
            </div>

            {{-- TABEL RIWAYAT (layout & alignment seragam dengan index) --}}
            <div class="px-2 sm:px-3 pb-2 pt-1">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-shell/60">
                            {{-- PERIODE IZIN + DIAJUKAN --}}
                            <th
                                class="pl-3 pr-2 py-3 text-left text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[34%]"
                            >
                                Periode Izin
                            </th>

                            {{-- DURASI --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[18%]"
                            >
                                Durasi
                            </th>

                            {{-- STATUS --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[18%]"
                            >
                                Status
                            </th>

                            {{-- DIPROSES --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[20%]"
                            >
                                Diproses
                            </th>

                            {{-- DETAIL --}}
                            <th
                                class="px-3 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[10%]"
                            >
                                Detail
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse($daftar_izin as $izin)
                            @php
                                $mulai      = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                $selesai    = \Carbon\Carbon::parse($izin->tanggal_selesai);
                                $diprosesAt = $izin->tanggal_persetujuan
                                    ? \Carbon\Carbon::parse($izin->tanggal_persetujuan)
                                    : null;
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                {{-- Periode + Diajukan (2 baris) --}}
                                <td class="pl-3 pr-2 py-3 align-top">
                                    <div class="text-text-main font-medium whitespace-nowrap">
                                        {{ $mulai->translatedFormat('d M Y') }} —
                                        {{ $selesai->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-text-muted mt-0.5">
                                        Diajukan:
                                        {{ $izin->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                    </div>
                                </td>

                                {{-- Durasi (vertikal tengah) --}}
                                <td class="px-3 py-3 text-center align-middle">
                                    <span class="font-semibold text-text-main whitespace-nowrap">
                                        {{ $izin->jumlah_hari }}
                                        <span class="text-xs text-text-muted ml-0.5">hari</span>
                                    </span>
                                </td>

                                {{-- Status (vertikal tengah) --}}
                                <td class="px-3 py-3 text-center align-middle">
                                    @if($izin->status === 'pending')
                                        <x-ui.badge variant="warning">Pending</x-ui.badge>
                                    @elseif($izin->status === 'disetujui')
                                        <x-ui.badge variant="success">Disetujui</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Ditolak</x-ui.badge>
                                    @endif
                                </td>

                                {{-- Diproses (vertikal tengah) --}}
                                <td class="px-3 py-3 text-center align-middle text-text-muted">
                                    @if($diprosesAt)
                                        <span class="whitespace-nowrap text-xs sm:text-sm">
                                            {{ $diprosesAt->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    @else
                                        <span class="text-[11px] italic">Belum diproses</span>
                                    @endif
                                </td>

                                {{-- Detail (vertikal tengah) --}}
                                <td class="px-3 py-3 text-right align-middle">
                                    <a
                                        href="{{ route('member.izin_latihan.detail', $izin->id) }}"
                                        class="inline-flex items-center gap-1 text-[13px] font-semibold
                                               text-gold-600 hover:text-gold-500 transition-colors whitespace-nowrap"
                                    >
                                        Lihat detail
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-text-muted text-sm italic">
                                    Belum ada riwayat izin latihan.
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
