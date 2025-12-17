{{-- resources/views/member/kehadiran/scan.blade.php --}}
@php
    $pageTitle = 'Konfirmasi Kehadiran';
@endphp

<x-layouts.member :title="$pageTitle . ' – BETA GYM'">
    <div class="max-w-lg mx-auto py-10 px-4">
        <div class="bg-brand-card border border-brand-borderSoft rounded-3xl shadow-card-soft p-6">
            <h1 class="text-lg font-heading font-semibold text-brand-text mb-2 text-center">
                Konfirmasi Kehadiran
            </h1>

            <p class="text-sm text-brand-textSoft text-center mb-6">
                Pastikan Anda sudah berada di area gym sebelum menekan tombol
                <span class="font-semibold">Catat Kehadiran</span>.
            </p>

            @isset($periodeAktif)
                <div class="mb-6 rounded-2xl border border-brand-borderSoft bg-brand-surface-50 px-4 py-3 text-sm">
                    <p class="text-[11px] font-semibold tracking-wide uppercase text-brand-textSoft mb-1">
                        Periode Absensi Aktif
                    </p>
                    <p class="font-semibold text-brand-text">
                        {{ ucfirst($periodeAktif->tipe_periode) }}
                    </p>
                    <p class="text-brand-textSoft text-sm">
                        {{ $periodeAktif->tanggal_mulai->format('d M Y') }}
                        &ndash;
                        {{ $periodeAktif->tanggal_selesai->format('d M Y') }}
                    </p>
                </div>
            @endisset

            <form method="POST" action="{{ route('member.absensi.store') }}" class="space-y-4">
                @csrf

                {{-- token dari QR --}}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="text-xs text-brand-textSoft">
                    <p>Tanggal saat ini:</p>
                    <p class="font-semibold text-brand-text">
                        {{ now()->format('d M Y') }} &middot; {{ now()->format('H:i') }} WIB
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3 pt-4">
                    <a href="{{ route('member.kehadiran.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium border border-brand-borderSoft text-brand-textSoft hover:bg-brand-surface-50">
                        Batal
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-primary-dark text-white border border-primary-dark hover:bg-primary-dark/90">
                        <i data-lucide="check-square" class="w-4 h-4 mr-2"></i>
                        Catat Kehadiran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.member>
