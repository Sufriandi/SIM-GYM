{{-- resources/views/member/absensi/scan.blade.php --}}

@php
    use Illuminate\Support\Carbon;

    $pageTitle    = $pageTitle    ?? 'Absensi Kehadiran';
    $pageSubtitle = $pageSubtitle ?? 'Scan QR dari admin lalu konfirmasi untuk mencatat kehadiran Anda.';

    // Variabel yang sebaiknya dikirim dari controller:
    // $periode  : instance AbsensiPeriode|null
    // $token    : string|null
    // $sudahAbsen : KehadiranMember|null (atau bool) untuk hari ini
    // $today    : Carbon (opsional, bisa pakai now() di sini)
    $today = $today ?? Carbon::now();

    $modeLabel = 'Periode';
    if (isset($periode)) {
        if ($periode->tipe_periode === 'harian') {
            $modeLabel = 'Periode Harian';
        } elseif ($periode->tipe_periode === 'mingguan') {
            $modeLabel = 'Periode Mingguan';
        } elseif ($periode->tipe_periode === 'bulanan') {
            $modeLabel = 'Periode Bulanan';
        }
    }
@endphp

<x-layouts.member :pageTitle="$pageTitle" :pageSubtitle="$pageSubtitle">
    <div class="max-w-3xl mx-auto space-y-6 pb-10">

        {{-- FLASH MESSAGE (jika ada) --}}
        @if (session('success'))
            <x-ui.toast type="success">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        @if (session('error'))
            <x-ui.toast type="danger">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- HEADER STATUS --}}
        <section class="bg-brand-card border border-brand-borderSoft rounded-3xl px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-brand-text">Absensi Hari Ini</h1>
                <p class="text-xs text-brand-textSoft mt-1">
                    {{ $today->translatedFormat('l, d F Y') }} • Jam server: {{ $today->format('H:i') }} WIB
                </p>
            </div>

            @if(isset($periode))
                <div class="text-right">
                    <p class="text-xs font-semibold text-brand-textSoft uppercase tracking-wide">
                        {{ $modeLabel }}
                    </p>
                    <p class="text-xs text-brand-textSoft mt-1">
                        {{ \Illuminate\Support\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y') }}
                        &mdash;
                        {{ \Illuminate\Support\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y') }}
                    </p>
                </div>
            @endif
        </section>

        {{-- KONTEN UTAMA --}}
        @if (!isset($periode) || ! $token)
            {{-- QR tidak valid --}}
            <section class="bg-brand-card border border-red-200 rounded-3xl px-6 py-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 border border-red-200 mb-4">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                </div>
                <h2 class="text-base font-semibold text-brand-text mb-2">
                    QR Code tidak valid atau periode absensi tidak ditemukan
                </h2>
                <p class="text-sm text-brand-textSoft max-w-md mx-auto mb-6">
                    Pastikan Anda memindai QR code terbaru yang disediakan admin di area gym.
                    Jika masalah berlanjut, silakan hubungi petugas.
                </p>
                <a
                    href="{{ route('member.dashboard') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-brand-nav text-gold-500 hover:bg-brand-sidebar transition-colors"
                >
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali ke Dashboard
                </a>
            </section>

        @elseif(isset($sudahAbsen) && $sudahAbsen)
            {{-- SUDAH ABSEN HARI INI --}}
            <section class="bg-brand-card border border-brand-borderSoft rounded-3xl px-6 py-8">
                <div class="flex items-start gap-4">
                    <div class="mt-1">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-semibold text-brand-text mb-1">
                            Kehadiran Anda sudah tercatat
                        </h2>
                        <p class="text-sm text-brand-textSoft mb-4">
                            Anda telah melakukan absensi untuk hari ini pada:
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                            <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                                <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Tanggal</p>
                                <p class="mt-1 text-brand-text">
                                    {{ \Illuminate\Support\Carbon::parse($sudahAbsen->tanggal)->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                                <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Jam Masuk</p>
                                <p class="mt-1 text-brand-text">
                                    {{ $sudahAbsen->jam_masuk ? \Illuminate\Support\Carbon::parse($sudahAbsen->jam_masuk)->format('H:i') . ' WIB' : '-' }}
                                </p>
                            </div>
                            <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                                <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Status</p>
                                <p class="mt-1 text-emerald-600 font-semibold">
                                    Tercatat
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a
                                href="{{ route('member.dashboard') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-brand-nav text-gold-500 hover:bg-brand-sidebar transition-colors"
                            >
                                <i data-lucide="home" class="w-4 h-4"></i>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        @else
            {{-- QR VALID & BELUM ABSEN --}}
            <section class="bg-brand-card border border-brand-borderSoft rounded-3xl px-6 py-8">
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="mt-1">
                            <div class="w-10 h-10 rounded-full bg-brand-surface-100 border border-brand-borderSoft flex items-center justify-center">
                                <i data-lucide="qr-code" class="w-6 h-6 text-brand-text"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-base font-semibold text-brand-text mb-1">
                                Konfirmasi Kehadiran
                            </h2>
                            <p class="text-sm text-brand-textSoft">
                                QR code yang Anda scan valid untuk periode absensi ini.
                                Tekan tombol di bawah untuk mencatat kehadiran Anda.
                            </p>
                        </div>
                    </div>

                    {{-- Info ringkas periode --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                            <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Periode</p>
                            <p class="mt-1 text-brand-text">
                                {{ $modeLabel }}
                            </p>
                        </div>
                        <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                            <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Tanggal</p>
                            <p class="mt-1 text-brand-text">
                                {{ $today->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <div class="bg-brand-surface-50 rounded-xl px-4 py-3 border border-brand-borderSoft/60">
                            <p class="text-[11px] uppercase tracking-wide text-brand-textSoft font-semibold">Berlaku s.d.</p>
                            <p class="mt-1 text-brand-text">
                                {{ \Illuminate\Support\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </div>

                    {{-- FORM KONFIRMASI --}}
                    <form
                        method="POST"
                        action="{{ route('member.absensi.store') }}"
                        class="mt-4"
                    >
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        @error('token')
                            <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                        @enderror

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                                       text-sm font-semibold bg-brand-nav text-gold-500
                                       hover:bg-gold-500 hover:text-brand-nav
                                       focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 focus:ring-offset-brand-bg
                                       transition-colors"
                            >
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                Konfirmasi Kehadiran
                            </button>

                            <a
                                href="{{ route('member.dashboard') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                                       border border-brand-borderSoft text-brand-textSoft hover:text-brand-text hover:bg-brand-surface-50
                                       transition-colors"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </section>
        @endif
    </div>
</x-layouts.member>
