{{-- resources/views/member/kehadiran/scan_sudah.blade.php --}}
@php
    $pageTitle = 'Kehadiran Sudah Tercatat';
@endphp

<x-layouts.member :title="$pageTitle . ' – BETA GYM'">
    <div class="max-w-lg mx-auto py-10 px-4">
        <div class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card-soft p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 rounded-full bg-success-soft flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-success"></i>
                </div>
            </div>

            <h1 class="text-lg font-heading font-semibold text-brand-text mb-2">
                Kehadiran Anda Sudah Tercatat
            </h1>

            <p class="text-sm text-brand-textSoft mb-4">
                Sistem mendeteksi bahwa Anda sudah melakukan absensi hari ini.
                Tidak perlu melakukan scan ulang.
            </p>

            @isset($periodeAktif)
                <div class="text-xs text-brand-textSoft mb-4">
                    <p class="font-semibold text-brand-text mb-1">
                        Periode Absensi Aktif
                    </p>
                    <p>
                        {{ ucfirst($periodeAktif->tipe_periode) }}:
                        {{ $periodeAktif->tanggal_mulai->format('d M Y') }}
                        &ndash;
                        {{ $periodeAktif->tanggal_selesai->format('d M Y') }}
                    </p>
                </div>
            @endisset

            <a href="{{ route('member.kehadiran.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-brand-nav text-gold-500 border border-brand-nav hover:bg-gold-500 hover:text-brand-nav transition-colors">
                <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                Lihat Riwayat Kehadiran
            </a>
        </div>
    </div>
</x-layouts.member>
