{{-- resources/views/member/izin_latihan/index.blade.php --}}

@php
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Izin Membership';

    // Hitung total pending (bisa paginator atau collection biasa)
    $totalPending = $daftar_izin instanceof \Illuminate\Pagination\AbstractPaginator
        ? $daftar_izin->total()
        : $daftar_izin->count();
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    pageSubtitle="Daftar pengajuan izin latihan yang sedang menunggu persetujuan."
>
    <div class="max-w-6xl mx-auto space-y-4">

        {{-- FLASH MESSAGE (error saja, success pakai toast global) --}}
        @if(session('error'))
            <x-ui.toast type="danger" class="mb-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar pengajuan izin latihan yang sedang menunggu persetujuan."
        />

        {{-- GARIS PEMBATAS SEPERTI ADMIN --}}
        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- BARIS TOMBOL AKSI (KANAN) --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-end mb-2">
            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                {{-- Tombol ke Riwayat --}}
                <a href="{{ route('member.izin_latihan.history') }}">
                    <x-ui.button-secondary class="inline-flex items-center">
                        <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                        Riwayat Lengkap
                    </x-ui.button-secondary>
                </a>

                {{-- Tombol Ajukan Baru --}}
                <a href="{{ route('member.izin_latihan.create') }}">
                    <x-ui.button-primary class="inline-flex items-center">
                        Ajukan Izin Baru
                        <i data-lucide="calendar-plus" class="w-4 h-4 ml-2"></i>
                    </x-ui.button-primary>
                </a>
            </div>
        </div>

        {{-- CARD PENGAJUAN SAAT INI --}}
        <x-ui.card class="overflow-hidden border border-brand-borderSoft">
            {{-- HEADER CARD (mirip history) --}}
            <div
                class="px-4 sm:px-6 py-4 border-b border-brand-borderSoft
                       flex items-center justify-between gap-4"
            >
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-text-main">
                        Pengajuan Saat Ini
                    </h2>
                    <p class="text-xs sm:text-sm text-text-muted mt-0.5">
                        Pengajuan izin yang sedang menunggu persetujuan Admin.
                    </p>
                </div>

                @if($totalPending > 0)
                    <div
                        class="inline-flex items-center justify-center px-4 py-1.5 rounded-full
                               bg-brand-shell/80 border border-brand-borderSoft text-[13px]
                               text-gold-700 font-semibold whitespace-nowrap"
                    >
                        {{ $totalPending }} izin pending
                    </div>
                @endif
            </div>

            {{-- TABEL PENGAJUAN (layout disamakan dengan history) --}}
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

                            {{-- ALASAN (disamakan lebar dengan kolom Diproses di history) --}}
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase w-[20%]"
                            >
                                Alasan
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
                                $mulai   = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                $selesai = \Carbon\Carbon::parse($izin->tanggal_selesai);
                                $buat    = $izin->created_at
                                    ? \Carbon\Carbon::parse($izin->created_at)
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
                                        {{ $buat?->translatedFormat('d M Y, H:i') ?? '-' }}
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
                                <td class="px-3 py-3 text-center align-middle whitespace-nowrap">
                                    <x-ui.badge variant="warning">
                                        Pending
                                    </x-ui.badge>
                                </td>

                                {{-- Alasan (vertikal tengah, dipersempit) --}}
                                <td class="px-3 py-3 text-left align-middle">
                                    <span class="block text-text-main truncate max-w-[14rem]">
                                        {{ $izin->alasan ? Str::limit($izin->alasan, 70) : '-' }}
                                    </span>
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
                                <td colspan="5" class="px-4 py-8 text-center text-text-muted text-sm">
                                    Belum ada izin latihan yang sedang diajukan.
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
