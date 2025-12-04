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
    pageSubtitle="Daftar izin latihan yang statusnya masih pending."
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
            subtitle="Daftar izin latihan yang statusnya masih pending."
        />

        {{-- DESKRIPSI SINGKAT + TOMBOL AKSI --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-text-muted max-w-xl">
                Kelola pengajuan izin latihan Anda di sini. Ajukan izin baru atau lihat riwayat lengkap.
            </div>

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

        {{-- CARD IZIN PENDING --}}
        <x-ui.card class="overflow-hidden">
            {{-- HEADER CARD --}}
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-text-main">
                        Izin Pending
                    </h2>
                    <p class="text-sm text-text-muted">
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

            {{-- TABEL IZIN (tanpa scroll horizontal) --}}
            <div>
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="border-y border-brand-borderSoft bg-brand-shell/60">
                            {{-- PERIODE IZIN --}}
                            <th
                                class="pl-3 pr-2 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase whitespace-nowrap"
                            >
                                Periode Izin
                            </th>

                            {{-- DURASI (rapat ke kiri, kolom kecil) --}}
                            <th
                                class="px-1 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase whitespace-nowrap w-16"
                            >
                                Durasi
                            </th>

                            {{-- DIAJUKAN --}}
                            <th
                                class="px-2 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase whitespace-nowrap w-40"
                            >
                                Diajukan
                            </th>

                            {{-- STATUS --}}
                            <th
                                class="px-2 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase whitespace-nowrap w-32"
                            >
                                Status
                            </th>

                            {{-- ALASAN (fleksibel + truncate) --}}
                            <th
                                class="px-2 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase"
                            >
                                Alasan
                            </th>

                            {{-- DETAIL --}}
                            <th
                                class="px-2 py-3 text-center text-xs font-semibold tracking-wide
                                       text-text-muted/90 uppercase whitespace-nowrap w-[110px]"
                            >
                                Detail
                            </th>
                        </tr>
                    </thead>

                    {{-- SAMAIN DENGAN ADMIN: divide-y & hover:bg-brand-surface-50 --}}
                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse($daftar_izin as $izin)
                            @php
                                $mulai   = \Carbon\Carbon::parse($izin->tanggal_mulai);
                                $selesai = \Carbon\Carbon::parse($izin->tanggal_selesai);
                                $buat    = $izin->created_at ? \Carbon\Carbon::parse($izin->created_at) : null;
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                {{-- PERIODE IZIN --}}
                                <td class="pl-3 pr-2 py-3 align-top whitespace-nowrap">
                                    <div class="text-text-main font-medium">
                                        {{ $mulai->translatedFormat('d M Y') }} —
                                        {{ $selesai->translatedFormat('d M Y') }}
                                    </div>
                                </td>

                                {{-- DURASI --}}
                                <td class="px-1 py-3 text-center align-top whitespace-nowrap">
                                    <span class="font-semibold text-text-main">
                                        {{ $izin->jumlah_hari }}
                                    </span>
                                    <span class="text-xs text-text-muted ml-0.5">
                                        hari
                                    </span>
                                </td>

                                {{-- DIAJUKAN --}}
                                <td class="px-2 py-3 text-left align-top text-text-muted whitespace-nowrap">
                                    {{ $buat?->translatedFormat('d M Y, H:i') ?? '-' }}
                                </td>

                                {{-- STATUS (selalu pending di halaman ini) --}}
                                <td class="px-2 py-3 text-left align-top whitespace-nowrap">
                                    <x-ui.badge variant="warning">
                                        Pending
                                    </x-ui.badge>
                                </td>

                                {{-- ALASAN – dibatasi & dipotong "..." --}}
                                <td class="px-2 py-3 text-left align-top">
                                    <span class="block max-w-[13rem] truncate text-text-main">
                                        {{ $izin->alasan ? Str::limit($izin->alasan, 70) : '-' }}
                                    </span>
                                </td>

                                {{-- DETAIL (halaman terpisah) --}}
                                <td class="px-2 py-3 text-right align-top whitespace-nowrap">
                                    <a
                                        href="{{ route('member.izin_latihan.detail', $izin->id) }}"
                                        class="inline-flex items-center gap-1 text-[13px] font-semibold
                                               text-gold-600 hover:text-gold-500 transition-colors"
                                    >
                                        Lihat detail
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-text-muted text-sm">
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
