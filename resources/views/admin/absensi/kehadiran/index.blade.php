{{-- resources/views/admin/absensi/kehadiran/index.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    $pageTitle = $pageTitle ?? 'Absensi Member';
    $mode      = $mode ?? config('absensi.mode', 'harian');

    $modeLabels = [
        'harian'   => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan'  => 'Bulanan',
    ];
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Pantau kehadiran latihan member melalui scan kode QR."
>
    <div class="max-w-6xl mx-auto space-y-8 pb-10">

        {{-- ========================================================= --}}
        {{-- 1. KARTU QR + INFO PERIODE --}}
        {{-- ========================================================= --}}
        <section
            class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card
                   px-6 py-6 md:px-8 md:py-7 flex flex-col md:flex-row gap-8 md:gap-10"
        >
            {{-- Kiri: Info periode & pengaturan --}}
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                            bg-brand-surface-100 border border-brand-borderSoft">
                    <span class="text-[10px] font-semibold tracking-[0.18em] uppercase text-brand-textSoft">
                        Periode Absensi Aktif
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-brand-nav text-[10px] font-semibold text-gold-300">
                        {{ strtoupper($periodeAktif->tipe_periode) }}
                    </span>
                </div>

                <div>
                    <h2 class="text-xl md:text-2xl font-heading font-bold text-brand-text mb-1">
                        Periode Absensi Aktif ({{ strtoupper($periodeAktif->tipe_periode) }})
                    </h2>
                    <p class="text-sm text-brand-textSoft">
                        {{ Carbon::parse($periodeAktif->tanggal_mulai)->translatedFormat('d M Y') }}
                        –
                        {{ Carbon::parse($periodeAktif->tanggal_selesai)->translatedFormat('d M Y') }}
                    </p>
                </div>

                <div class="space-y-2 text-sm text-brand-textSoft">
                    <p>
                        <span class="font-semibold">Kode QR:</span>
                        <span class="font-mono text-xs break-all">{{ $periodeAktif->kode_qr }}</span>
                    </p>
                    <p>
                        Mode periode bisa diubah melalui tombol di bawah.
                        Sistem otomatis membuat periode baru jika belum ada yang aktif untuk hari ini
                        pada mode yang dipilih.
                    </p>
                </div>

                {{-- Pengaturan mode + tombol cetak --}}
                <div class="flex flex-wrap items-center gap-3 pt-3">
                    <form
                        method="POST"
                        action="{{ route('admin.absensi.kehadiran.update_mode') }}"
                        class="inline-flex items-center gap-2 flex-wrap"
                    >
                        @csrf
                        <span class="text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                            Mode Periode:
                        </span>

                        @foreach($modeLabels as $key => $label)
                            <button
                                type="submit"
                                name="mode"
                                value="{{ $key }}"
                                class="px-3.5 py-1.5 rounded-full text-[11px] font-semibold border
                                       transition-all duration-150
                                       {{ $mode === $key
                                            ? 'bg-brand-nav text-gold-300 border-brand-nav shadow-sm'
                                            : 'bg-brand-bg text-brand-text border-brand-borderSoft hover:border-gold-500 hover:text-brand-text' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </form>

                    <a
                        href="{{ route('admin.absensi.kehadiran.print') }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[11px] font-semibold
                               border border-brand-borderSoft bg-white hover:bg-brand-surface-50
                               text-brand-text shadow-sm"
                    >
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Cetak QR
                    </a>
                </div>
            </div>

            {{-- Kanan: QR --}}
            <div class="w-full md:w-auto flex flex-col items-center justify-center gap-3">
                <span class="text-[11px] font-semibold tracking-[0.16em] uppercase text-brand-textSoft">
                    Scan untuk absen
                </span>

                <div class="bg-white border border-brand-borderSoft rounded-3xl p-3 shadow-card-strong">
                    <img
                        src="data:image/png;base64,{{ base64_encode(
                            QrCode::format('png')
                                ->size(260)
                                ->margin(1)
                                ->generate($qrUrl)
                        ) }}"
                        alt="QR Absensi BETA GYM"
                        class="block w-[220px] h-[220px]"
                    >
                </div>

                <p class="text-[11px] text-center text-brand-textSoft max-w-xs">
                    Gunakan tombol <span class="font-semibold">Cetak QR</span> untuk mencetak kartu ini
                    dan tempel di meja resepsionis atau area masuk gym.
                </p>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- 2. DAFTAR KEHADIRAN --}}
        {{-- ========================================================= --}}
        <section
            class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card overflow-hidden"
        >
            {{-- Header + filter --}}
            <div class="px-6 py-4 border-b border-brand-borderSoft/70 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-base md:text-lg font-heading font-bold text-brand-text">
                        Daftar Kehadiran
                    </h3>
                    <p class="text-xs md:text-sm text-brand-textSoft mt-1">
                        Rekap kehadiran member pada periode aktif saat ini.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('admin.absensi.kehadiran.index') }}"
                    class="flex flex-wrap items-center gap-2 md:justify-end"
                >
                    {{-- Filter tanggal --}}
                    <div class="flex items-center gap-2">
                        <label for="tanggal" class="text-[11px] font-semibold text-brand-textSoft uppercase tracking-wide">
                            Tanggal
                        </label>
                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            value="{{ request('tanggal') }}"
                            class="px-2.5 py-1.5 rounded-xl border border-brand-borderSoft bg-brand-bg
                                   text-xs text-brand-text focus:ring-1 focus:ring-gold-500 focus:border-gold-500"
                        >
                    </div>

                    {{-- Cari member --}}
                    <div class="flex items-center gap-2">
                        <label for="member" class="sr-only">Member</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-2 flex items-center">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-brand-textSoft"></i>
                            </span>
                            <input
                                type="text"
                                id="member"
                                name="member"
                                value="{{ request('member') }}"
                                placeholder="Cari nama / username"
                                class="pl-7 pr-2.5 py-1.5 rounded-xl border border-brand-borderSoft bg-brand-bg
                                       text-xs text-brand-text placeholder-brand-textSoft/70
                                       focus:ring-1 focus:ring-gold-500 focus:border-gold-500 w-44 md:w-52"
                            >
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="px-3.5 py-1.5 rounded-xl text-[11px] font-semibold bg-brand-nav text-gold-300
                               hover:bg-brand-sidebar transition-colors"
                    >
                        Terapkan
                    </button>
                </form>
            </div>

            {{-- Tabel --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-brand-surface-100 border-b border-brand-borderSoft/80">
                            <th class="px-6 py-3 text-left text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                                Jam Masuk
                            </th>
                            <th class="px-6 py-3 text-left text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                                Member
                            </th>
                            <th class="px-6 py-3 text-left text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft">
                                Keterangan
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kehadiran as $row)
                            <tr class="border-b border-brand-borderSoft/60 last:border-0 hover:bg-brand-surface-50">
                                <td class="px-6 py-3 align-top text-sm text-brand-text">
                                    {{ Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-3 align-top text-sm text-brand-text">
                                    {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 align-top text-sm text-brand-text">
                                    <div class="font-semibold">
                                        {{ $row->member->nama ?? '-' }}
                                    </div>
                                    @if(optional($row->member)->username)
                                        <div class="text-xs text-brand-textSoft">
                                            {{ '@' . $row->member->username }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 align-top text-sm text-brand-text">
                                    @if($row->is_valid)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                                     bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Valid
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                                     bg-red-50 text-red-700 border border-red-200">
                                            Tidak Valid
                                        </span>
                                    @endif

                                    @if($row->ip_address || $row->device_info)
                                        <div class="mt-1 text-[11px] text-brand-textSoft leading-snug">
                                            @if($row->ip_address)
                                                IP: {{ $row->ip_address }}
                                            @endif
                                            @if($row->device_info)
                                                <br>Perangkat: {{ $row->device_info }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-brand-textSoft">
                                    Belum ada kehadiran tercatat dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($kehadiran, 'links'))
                <div class="px-6 py-4 border-t border-brand-borderSoft/70">
                    {{ $kehadiran->appends([
                        'tanggal' => request('tanggal'),
                        'member'  => request('member'),
                    ])->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.admin>
