{{-- resources/views/member/kehadiran/show.blade.php --}}

@php
    use Illuminate\Support\Carbon;

    $sesi      = $kehadiran->sesi;
    $pageTitle = $pageTitle ?? 'Detail Kehadiran';

    $tglSesi = $sesi?->tanggal ? Carbon::parse($sesi->tanggal) : null;
    $mulai   = $sesi?->jam_mulai ? Carbon::parse($sesi->jam_mulai) : null;
    $selesai = $sesi?->jam_selesai ? Carbon::parse($sesi->jam_selesai) : null;
    $scanAt  = $kehadiran->waktu_scan ? Carbon::parse($kehadiran->waktu_scan) : null;
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    pageSubtitle="Rincian kehadiran Anda pada sesi latihan tertentu."
>
    <div class="max-w-4xl mx-auto space-y-4 pt-2">

        {{-- HEADER + BACK --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Informasi lengkap sesi dan waktu Anda melakukan scan QR."
        />

        <x-ui.back-button
            href="{{ route('member.kehadiran.index') }}"
            text="Kembali ke riwayat kehadiran"
            class="mb-2"
        />

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <x-ui.toast type="success" class="mb-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        {{-- CARD DETAIL --}}
        <div
            class="relative w-full rounded-3xl border border-brand-borderSoft
                   bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell overflow-hidden"
        >
            {{-- HEADER CARD --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                       px-6 pt-6 pb-4 border-b border-brand-borderSoft bg-brand-shell/60 gap-3"
            >
                <div>
                    <h2 class="text-lg font-semibold text-text-main">
                        {{ $sesi->nama_sesi ?? 'Sesi Latihan' }}
                    </h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        Kode sesi: <span class="font-mono text-[11px]">{{ $sesi->kode_qr ?? '-' }}</span>
                    </p>
                </div>

                <div class="text-right space-y-1">
                    <p class="text-[10px] uppercase tracking-widest text-text-muted">
                        Status Kehadiran
                    </p>
                    <div>
                        @if($kehadiran->status === 'hadir')
                            <x-ui.badge variant="success">Hadir</x-ui.badge>
                        @elseif($kehadiran->status === 'terlambat')
                            <x-ui.badge variant="warning">Terlambat</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary">
                                {{ ucfirst($kehadiran->status ?? 'N/A') }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ISI CARD --}}
            <div class="px-6 pb-6 pt-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- INFO SESI --}}
                    <div class="space-y-4">
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <h3 class="text-sm font-semibold text-text-main mb-1 flex items-center gap-2">
                                <i data-lucide="calendar-range" class="w-4 h-4 text-gold-500"></i>
                                Informasi Sesi
                            </h3>
                            <p class="text-[11px] text-text-muted border-b border-brand-borderSoft/50 pb-2 mb-3">
                                Waktu dan jadwal sesi latihan yang Anda hadiri.
                            </p>

                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-[11px] text-text-muted uppercase tracking-wider">Tanggal</p>
                                    <p class="text-text-main font-medium">
                                        {{ $tglSesi ? $tglSesi->translatedFormat('d M Y') : '-' }}
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="text-[11px] text-text-muted uppercase tracking-wider">Jam Mulai</p>
                                        <p class="text-text-main font-medium">
                                            {{ $mulai ? $mulai->format('H:i') . ' WIB' : '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-text-muted uppercase tracking-wider">Jam Selesai</p>
                                        <p class="text-text-main font-medium">
                                            {{ $selesai ? $selesai->format('H:i') . ' WIB' : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($sesi->catatan_admin)
                            <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                                <h3 class="text-sm font-semibold text-text-main mb-1 flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-gold-500"></i>
                                    Catatan Admin
                                </h3>
                                <p class="text-xs text-text-main leading-relaxed mt-1">
                                    {{ $sesi->catatan_admin }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- INFO SCAN --}}
                    <div class="space-y-4">
                        <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-5 py-4">
                            <h3 class="text-sm font-semibold text-text-main mb-1 flex items-center gap-2">
                                <i data-lucide="scan-line" class="w-4 h-4 text-gold-500"></i>
                                Detail Scan QR
                            </h3>
                            <p class="text-[11px] text-text-muted border-b border-brand-borderSoft/50 pb-2 mb-3">
                                Waktu dan informasi saat Anda melakukan scan kode QR.
                            </p>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-[11px] text-text-muted uppercase tracking-wider">Waktu Scan</p>
                                    <p class="text-text-main font-medium">
                                        {{ $scanAt ? $scanAt->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                                    </p>
                                </div>

                                @if($kehadiran->device_info)
                                    <div>
                                        <p class="text-[11px] text-text-muted uppercase tracking-wider">
                                            Informasi Perangkat
                                        </p>
                                        <p class="text-xs text-text-main leading-relaxed">
                                            {{ $kehadiran->device_info }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft px-5 py-4">
                            <h3 class="text-sm font-semibold text-text-main mb-1 flex items-center gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-gold-500"></i>
                                Catatan
                            </h3>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Data kehadiran ini digunakan sebagai referensi keaktifan latihan Anda di BETA GYM.
                                Simpan kebiasaan baik dengan selalu melakukan scan QR saat datang berlatih.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.member>
