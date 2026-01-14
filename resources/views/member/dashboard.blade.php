

{{-- resources/views/member/dashboard.blade.php --}}

@php
    //use Carbon\Carbon;

    /** @var \App\Models\User $user */
    /** @var \App\Models\Member $member */

    $user   = $user ?? auth()->user();
    $member = $member ?? ($user?->member);

    // Greeting & nama depan
    $greeting    = $greeting ?? 'Halo';
    $displayName = $member?->nama ?? $user?->name ?? 'Member';
    $firstName   = explode(' ', trim($displayName))[0];

    // ID kartu pseudo
    $rawId       = $user ? substr(md5($user->id), 0, 16) : '0000000000000000';
    $formattedId = implode(' ', str_split($rawId, 4));

    // Membership info
    $membershipAktif     = $membershipAktif ?? false;
    $mulai               = $mulai ?? null;
    $akhir               = $akhir ?? null;
    $sisaHari            = $sisaHari ?? 0;
    $totalDurasi         = $totalDurasi ?? 0;
    $hariTerpakai        = $hariTerpakai ?? 0;
    $progressUsedPercent = $progressUsedPercent ?? 0;

    $tglAkhirFormatted = $akhir ? $akhir->format('d M Y') : '-';

    // Izin stats
    $izinStats = $izinStats ?? ['pending' => 0, 'disetujui' => 0, 'ditolak' => 0, 'total' => 0];

    // Absensi
    $hadirBulanIni    = $hadirBulanIni ?? 0;
    $hadirTahunIni    = $hadirTahunIni ?? 0;
    $todayAttendance  = $todayAttendance ?? null;
    $recentAttendance = $recentAttendance ?? collect();

    // Membership transaksi terakhir
    $latestMembership = $latestMembership ?? null;

    // Produk & coach
    $produkTerbaru  = $produkTerbaru ?? collect();
    $produkTerlaris = $produkTerlaris ?? collect();
    $coaches        = $coaches ?? collect();

    // Izin terbaru
    $recentIzin = $recentIzin ?? collect();
@endphp

<x-layouts.member
    :pageTitle="'Dashboard Member'"
    :pageSubtitle="'Overview Membership & Aktivitas'"
>
    <div class="space-y-8 pb-12">

        {{-- =========================================================
             1. HERO / GREETING SECTION (LIGHT BRAND THEME)
        ========================================================== --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell p-8 lg:p-10 border border-brand-borderSoft shadow-2xl"
        >
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-10 pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-80 h-80 bg-gold-500/20 rounded-full blur-3xl -mr-24 -mt-24 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gold-700/20 rounded-full blur-3xl -ml-20 -mb-24 pointer-events-none"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                {{-- LEFT: TEXT --}}
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-shell/70 border border-gold-500/40 backdrop-blur-sm mb-4">
                        <span class="w-2 h-2 rounded-full bg-gold-300 animate-pulse"></span>
                        <p class="text-gold-200 font-semibold uppercase tracking-[0.25em] text-[10px]">
                            Member Dashboard
                        </p>
                        @if($membershipAktif)
                            <span class="text-[10px] font-semibold text-emerald-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Membership Aktif
                            </span>
                        @else
                            <span class="text-[10px] font-semibold text-danger flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-danger"></span>
                                Membership Non-Aktif
                            </span>
                        @endif
                    </div>

                    <h1 class="text-4xl lg:text-5xl font-heading font-black text-text-main tracking-tight leading-tight">
                        {{ $greeting }},
                        <span class="block mt-2 bg-gradient-to-r from-gold-300 via-gold-500 to-gold-700 bg-clip-text text-transparent">
                            {{ $firstName }}!
                        </span>
                    </h1>

                    <p class="text-sm lg:text-base text-text-muted mt-3 max-w-xl">
                        Pantau status membership, kehadiran latihan, dan riwayat izin Anda dalam satu tampilan.
                    </p>

                    @if (! $membershipAktif)
                        <div class="mt-4 inline-flex items-start gap-3 px-4 py-3 rounded-2xl bg-danger-soft/20 border border-danger-soft/60 text-xs text-text-main max-w-xl">
                            <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 text-danger"></i>
                            <div>
                                <p class="font-semibold text-danger">Membership Anda sudah tidak aktif.</p>
                                <p class="mt-0.5 text-text-muted">
                                    Silakan perpanjang membership di kasir atau hubungi admin untuk melanjutkan akses penuh ke fasilitas gym.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- RIGHT: TODAY WIDGET --}}
                <div class="hidden lg:flex items-center gap-4 bg-brand-card/90 border border-brand-borderSoft px-6 py-3 rounded-2xl backdrop-blur-sm shadow-md">
                    <div class="p-3 bg-gold-500/15 rounded-xl text-gold-600">
                        <i data-lucide="calendar-days" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-text-muted font-bold uppercase tracking-[0.22em]">
                            Hari Ini
                        </p>
                        <p class="text-lg font-semibold text-text-main leading-tight">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                        <p class="text-xs text-text-muted">
                            {{ now()->format('H:i') }} WIB
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             2. PANEL MEMBERSHIP (KARTU + DETAIL)
        ========================================================== --}}
        <section
            class="rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl overflow-hidden relative group"
        >
            <div class="absolute inset-0 bg-gradient-to-r from-gold-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-5">
                {{-- LEFT: CARD VISUAL --}}
                <div class="lg:col-span-2 p-6 lg:p-8 bg-brand-shell border-b lg:border-b-0 lg:border-r border-brand-borderSoft flex items-center justify-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-gold-600 to-transparent"></div>

                    <div class="relative w-full aspect-[1.586/1] max-w-[400px] perspective-1000 group/card">
                        <div
                            class="relative h-full w-full rounded-2xl bg-brand-card border border-brand-borderSoft shadow-2xl overflow-hidden transform transition-transform duration-500 group-hover/card:scale-105 group-hover/card:rotate-1"
                        >
                            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-10"></div>
                            <div class="absolute inset-0 bg-gradient-to-br from-white/5 via-transparent to-black/40"></div>

                            <div class="relative z-10 h-full p-6 flex flex-col justify-between text-text-main">
                                <div class="flex justify-between items-start">
                                    <span class="font-heading font-black italic text-xl tracking-[0.22em] text-gold-500">
                                        BETA&nbsp;GYM
                                    </span>
                                    <i data-lucide="wifi" class="w-5 h-5 text-text-muted rotate-90"></i>
                                </div>

                                <div class="space-y-2">
                                    <div class="w-10 h-7 rounded bg-gradient-to-br from-yellow-100 via-yellow-400 to-yellow-700 shadow-md border border-white/20"></div>
                                    <p class="font-mono text-lg md:text-xl font-bold tracking-[0.25em] text-text-main drop-shadow-sm">
                                        {{ $formattedId }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-end text-[10px] uppercase tracking-wider font-bold text-text-muted">
                                    <div>
                                        <span class="block mb-0.5">Holder</span>
                                        <span class="text-text-main text-xs">
                                            {{ $displayName }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="block mb-0.5">Exp</span>
                                        <span class="text-gold-600 text-xs">
                                            {{ $akhir ? $akhir->format('m/y') : 'XX/XX' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: STATUS & DETAIL --}}
                <div class="lg:col-span-3 p-6 lg:p-10 flex flex-col justify-center relative bg-brand-shell/70">
                    {{-- STATUS HEADER --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                        <div>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.28em] mb-1">
                                Status Keanggotaan
                            </p>
                            <h2 class="text-2xl font-bold text-text-main flex items-center gap-3">
                                {{ $membershipAktif ? 'Membership Aktif' : 'Membership Berakhir' }}
                                @if($membershipAktif)
                                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-400"></i>
                                @else
                                    <i data-lucide="x-circle" class="w-6 h-6 text-danger"></i>
                                @endif
                            </h2>

                            @if ($latestMembership)
                                <p class="text-xs text-text-muted mt-1">
                                    Paket terakhir:
                                    <span class="font-semibold text-gold-600">
                                        {{ $latestMembership->paket?->nama ?? $latestMembership->paket?->tipe ?? 'Paket Membership' }}
                                    </span>
                                    —
                                    {{ $latestMembership->tanggal_mulai?->format('d M Y') }}
                                    &ndash;
                                    {{ $latestMembership->tanggal_akhir?->format('d M Y') }}
                                </p>
                            @endif
                        </div>

                        {{-- BADGE --}}
                        <div
                            class="px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest
                            {{ $membershipAktif
                                ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400'
                                : 'bg-danger-soft/15 border-danger-soft/60 text-danger' }}"
                        >
                            {{ $membershipAktif ? '● ACTIVE' : '● EXPIRED' }}
                        </div>
                    </div>

                    {{-- STAT GRID --}}
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] mb-2">
                                Sisa Masa Aktif
                            </p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-heading font-black {{ $membershipAktif ? 'text-text-main' : 'text-danger' }}">
                                    {{ $sisaHari }}
                                </span>
                                <span class="text-sm font-bold text-text-muted">Hari</span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-1">
                                Dari total
                                <span class="font-semibold text-text-main">{{ $totalDurasi ?: '–' }}</span>
                                hari membership terakhir.
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] mb-2">
                                Berlaku Sampai
                            </p>
                            <p class="text-xl font-bold text-gold-600 font-mono">
                                {{ $tglAkhirFormatted }}
                            </p>
                            @if ($mulai)
                                <p class="text-[11px] text-text-muted mt-1">
                                    Mulai aktif:
                                    {{ $mulai->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- PROGRESS + ACTION --}}
                    <div class="space-y-6">
                        {{-- PROGRESS --}}
                        <div class="space-y-2">
                            <div class="flex justify-between text-[11px] font-bold text-text-muted">
                                <span>Durasi Terpakai</span>
                                <span>{{ $membershipAktif ? 'Sedang Aktif' : 'Tidak Aktif' }}</span>
                            </div>
                            <div class="w-full h-3 bg-brand-card/60 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-1000
                                        {{ $membershipAktif ? 'bg-gradient-to-r from-gold-600 via-gold-400 to-emerald-400' : 'bg-danger-soft' }}"
                                    style="width: {{ $membershipAktif ? $progressUsedPercent : 100 }}%;"
                                ></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-text-muted">
                                <span>{{ $hariTerpakai }} hari sudah terpakai</span>
                                <span>{{ $sisaHari }} hari tersisa</span>
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex flex-col sm:flex-row gap-4 pt-2">
                            @if (! $membershipAktif)
                                <a
                                    href="#"
                                    class="flex-1 py-3.5 px-6 bg-danger hover:bg-danger/90 text-white rounded-xl font-bold uppercase tracking-wide text-sm flex items-center justify-center gap-2 shadow-lg shadow-danger/25 transition-all hover:scale-[1.02]"
                                >
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    Perpanjang Membership
                                </a>
                            @else
                                <button
                                    type="button"
                                    class="flex-1 py-3.5 px-6 bg-brand-card hover:bg-brand-surface-50 text-text-main border border-brand-borderSoft rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-colors"
                                >
                                    <i data-lucide="file-text" class="w-4 h-4 text-gold-600"></i>
                                    Lihat Riwayat Membership
                                </button>
                            @endif

                            <a
                                href="{{ route('member.kehadiran.index') }}"
                                class="flex-1 py-3.5 px-6 bg-gold-600 hover:bg-gold-500 text-black rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-transform active:scale-[0.98]"
                            >
                                <i data-lucide="clock-3" class="w-4 h-4"></i>
                                Riwayat Kehadiran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             3. QUICK ACTION + IZIN SUMMARY
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- QUICK ACTION IZIN --}}
            <div class="lg:col-span-2 relative overflow-hidden rounded-3xl bg-brand-card border border-brand-borderSoft p-1 group">
                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="relative h-full bg-brand-shell/80 rounded-[22px] p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-gold-500/10 border border-gold-500/40 flex items-center justify-center text-gold-500">
                            <i data-lucide="calendar-off" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-text-main">Berhalangan Hadir?</h3>
                            <p class="text-sm text-text-muted">
                                Ajukan izin latihan agar membership Anda dapat dikompensasi sesuai kebijakan gym.
                            </p>
                            <p class="text-[11px] text-text-muted mt-1">
                                Izin hanya dapat diajukan jika membership masih aktif.
                            </p>
                        </div>
                    </div>

                    @if ($membershipAktif)
                        <a href="{{ route('member.izin_latihan.create') }}" class="w-full md:w-auto">
                            <button
                                class="w-full md:w-auto px-6 py-3 rounded-xl bg-gold-600 hover:bg-gold-500 text-black font-semibold text-sm flex items-center justify-center gap-2 transition-transform active:scale-[0.97]"
                            >
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Buat Izin
                            </button>
                        </a>
                    @else
                        <button
                            type="button"
                            class="w-full md:w-auto px-6 py-3 rounded-xl bg-brand-card text-text-muted font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed border border-brand-borderSoft"
                            title="Membership tidak aktif. Izin hanya bisa diajukan saat membership aktif."
                        >
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            Buat Izin (Terkunci)
                        </button>
                    @endif
                </div>
            </div>

            {{-- IZIN STATS --}}
            <div class="rounded-3xl bg-brand-card border border-brand-borderSoft p-6 flex flex-col justify-between hover:border-gold-500/40 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em]">
                            Status Pengajuan Izin
                        </p>
                        <h3 class="text-4xl font-heading font-black text-text-main mt-2">
                            {{ $izinStats['pending'] ?? 0 }}
                        </h3>
                        <p class="text-xs text-text-muted mt-1">
                            Izin masih menunggu persetujuan Admin.
                        </p>
                    </div>
                    <div class="p-2 rounded-lg bg-brand-shell/70">
                        <i data-lucide="file-clock" class="w-5 h-5 text-gold-500"></i>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 text-[11px] text-text-muted">
                    <div class="flex flex-col">
                        <span class="font-semibold text-text-main">{{ $izinStats['disetujui'] ?? 0 }}</span>
                        <span class="uppercase tracking-[0.22em] text-[10px]">Disetujui</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-semibold text-text-main">{{ $izinStats['ditolak'] ?? 0 }}</span>
                        <span class="uppercase tracking-[0.22em] text-[10px]">Ditolak</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-semibold text-text-main">{{ $izinStats['total'] ?? 0 }}</span>
                        <span class="uppercase tracking-[0.22em] text-[10px]">Total</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-brand-borderSoft flex justify-between items-center">
                    <span class="text-[11px] text-text-muted">
                        Kelola pengajuan di halaman izin latihan.
                    </span>
                    <a
                        href="{{ route('member.izin_latihan.index') }}"
                        class="text-[11px] font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1"
                    >
                        Lihat Pending
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
        </section>

        {{-- =========================================================
             4. RINGKASAN AKTIVITAS BULAN INI
        ========================================================== --}}
        <section class="grid grid-cols-1 md:grid-cols-4 gap-4 lg:gap-6">
            {{-- Hadir Bulan Ini --}}
            <div class="rounded-2xl bg-brand-card border border-brand-borderSoft p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em]">
                            Hadir Bulan Ini
                        </p>
                        <p class="text-3xl font-heading font-black text-text-main mt-1">
                            {{ $hadirBulanIni }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/40">
                        <i data-lucide="check-square-2" class="w-4 h-4 text-emerald-400"></i>
                    </div>
                </div>
                <p class="text-[11px] text-text-muted mt-1">
                    Dari {{ now()->daysInMonth }} hari di bulan {{ now()->translatedFormat('F') }}.
                </p>
            </div>

            {{-- Hadir Tahun Ini --}}
            <div class="rounded-2xl bg-brand-card border border-brand-borderSoft p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em]">
                            Hadir Tahun Ini
                        </p>
                        <p class="text-3xl font-heading font-black text-text-main mt-1">
                            {{ $hadirTahunIni }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg bg-sky-500/10 border border-sky-500/40">
                        <i data-lucide="calendar-check-2" class="w-4 h-4 text-sky-400"></i>
                    </div>
                </div>
                <p class="text-[11px] text-text-muted mt-1">
                    Total sesi latihan terdaftar sepanjang {{ now()->year }}.
                </p>
            </div>

            {{-- Izin Disetujui --}}
            <div class="rounded-2xl bg-brand-card border border-brand-borderSoft p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em]">
                            Izin Disetujui
                        </p>
                        <p class="text-3xl font-heading font-black text-emerald-400 mt-1">
                            {{ $izinStats['disetujui'] ?? 0 }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/40">
                        <i data-lucide="badge-check" class="w-4 h-4 text-emerald-400"></i>
                    </div>
                </div>
                <p class="text-[11px] text-text-muted mt-1">
                    Total izin latihan yang telah dikompensasi oleh admin.
                </p>
            </div>

            {{-- Izin Ditolak --}}
            <div class="rounded-2xl bg-brand-card border border-brand-borderSoft p-4 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em]">
                            Izin Ditolak
                        </p>
                        <p class="text-3xl font-heading font-black text-danger mt-1">
                            {{ $izinStats['ditolak'] ?? 0 }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg bg-danger-soft/20 border border-danger-soft/60">
                        <i data-lucide="circle-x" class="w-4 h-4 text-danger"></i>
                    </div>
                </div>
                <p class="text-[11px] text-text-muted mt-1">
                    Perhatikan kembali alasan dan periode saat mengajukan izin.
                </p>
            </div>
        </section>

        {{-- =========================================================
             5. AKTIVITAS HARI INI + RIWAYAT ABSEN
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- AKTIVITAS HARI INI --}}
            <div class="lg:col-span-1 rounded-3xl bg-brand-card border border-brand-borderSoft p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-text-main">
                            Aktivitas Hari Ini
                        </h3>
                        <p class="text-[11px] text-text-muted mt-0.5">
                            Ringkasan status kehadiran dan membership hari ini.
                        </p>
                    </div>
                    <div class="p-2 rounded-xl bg-brand-shell/70">
                        <i data-lucide="sun-medium" class="w-4 h-4 text-gold-500"></i>
                    </div>
                </div>

                <div class="mt-2 space-y-3">
                    {{-- Status Membership --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-shell/70 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4 h-4 text-gold-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-text-main">
                                Membership
                            </p>
                            <p class="text-[11px] text-text-muted">
                                {{ $membershipAktif ? 'Anda dapat menggunakan fasilitas gym dan melakukan absensi.' : 'Membership berakhir, absensi & izin terkunci hingga diperpanjang.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Status Absensi Hari Ini --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-shell/70 flex items-center justify-center">
                            <i data-lucide="{{ $todayAttendance ? 'check-circle-2' : 'circle-dashed' }}" class="w-4 h-4 {{ $todayAttendance ? 'text-emerald-400' : 'text-text-muted' }}"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-text-main">
                                Absensi Hari Ini
                            </p>
                            @if($todayAttendance)
                                <p class="text-[11px] text-text-muted">
                                    Hadir pada
                                    {{ $todayAttendance->tanggal?->format('d M Y') }}
                                    pukul
                                    {{ $todayAttendance->jam_masuk ? Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : '-' }}.
                                </p>
                            @else
                                <p class="text-[11px] text-text-muted">
                                    Belum ada kehadiran tercatat hari ini.
                                    @if ($membershipAktif)
                                        Scan QR absensi di gym untuk mencatat kehadiran.
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Pending Izin --}}
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-shell/70 flex items-center justify-center">
                            <i data-lucide="file-clock" class="w-4 h-4 text-gold-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-text-main">
                                Izin Pending
                            </p>
                            <p class="text-[11px] text-text-muted">
                                {{ $izinStats['pending'] ?? 0 }} izin masih menunggu tindakan Admin.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-brand-borderSoft">
                    <a
                        href="{{ route('member.kehadiran.index') }}"
                        class="text-[11px] font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1"
                    >
                        Lihat riwayat kehadiran lengkap
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            {{-- RIWAYAT ABSEN TERAKHIR --}}
            <div class="lg:col-span-2 rounded-3xl bg-brand-card border border-brand-borderSoft p-6 overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-text-main">
                            Kehadiran Terakhir
                        </h3>
                        <p class="text-[11px] text-text-muted">
                            5 kehadiran terakhir yang tercatat dalam sistem.
                        </p>
                    </div>
                    <div class="hidden sm:flex text-[11px] text-text-muted gap-2">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Valid
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-300"></span> Ditinjau
                        </span>
                    </div>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="border-b border-brand-borderSoft text-[10px] uppercase tracking-[0.18em] text-text-muted">
                                <th class="py-2 pr-4 text-left">Tanggal</th>
                                <th class="py-2 px-4 text-left">Jam Masuk</th>
                                <th class="py-2 px-4 text-left">Periode</th>
                                <th class="py-2 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-borderSoft/70">
                            @forelse($recentAttendance as $row)
                                <tr class="hover:bg-brand-surface-50/50 transition-colors">
                                    <td class="py-2 pr-4 align-middle text-text-main">
                                        {{ $row->tanggal?->format('d M Y') }}
                                    </td>
                                    <td class="py-2 px-4 align-middle text-text-muted">
                                        {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                    </td>
                                    <td class="py-2 px-4 align-middle text-text-muted text-[11px]">
                                        {{ $row->absensiPeriode?->tipe_periode
                                            ? ucfirst($row->absensiPeriode->tipe_periode).' · '.$row->absensiPeriode->tanggal_mulai?->format('d M')
                                            : '-' }}
                                    </td>
                                    <td class="py-2 px-4 align-middle">
                                        @php
                                            $isValid = $row->is_valid ?? true;
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                {{ $isValid ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/40' : 'bg-yellow-500/10 text-yellow-300 border border-yellow-500/40' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $isValid ? 'bg-emerald-400' : 'bg-yellow-300' }}"></span>
                                            {{ $isValid ? 'Valid' : 'Perlu Ditinjau' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-[11px] text-text-muted">
                                        Belum ada kehadiran tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- =========================================================
             6. RIWAYAT IZIN TERBARU
        ========================================================== --}}
        <section class="rounded-3xl bg-brand-card border border-brand-borderSoft p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-text-main">
                        Riwayat Izin Terbaru
                    </h3>
                    <p class="text-[11px] text-text-muted">
                        5 pengajuan izin terakhir beserta statusnya.
                    </p>
                </div>
                <a
                    href="{{ route('member.izin_latihan.history') }}"
                    class="text-[11px] font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1"
                >
                    Lihat semua
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentIzin as $izin)
                    @php
                        $status = $izin->status;
                        $statusMeta = match ($status) {
                            'pending'   => ['label' => 'Pending',   'class' => 'bg-warning-soft/15 text-warning border-warning-soft/60'],
                            'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/40'],
                            'ditolak'   => ['label' => 'Ditolak',   'class' => 'bg-danger-soft/15 text-danger border-danger-soft/60'],
                            default     => ['label' => ucfirst($status), 'class' => 'bg-brand-shell/40 text-text-main border-brand-borderSoft'],
                        };
                    @endphp
                    <div class="rounded-2xl bg-brand-shell/70 border border-brand-borderSoft px-4 py-3 flex items-start gap-3">
                        <div class="mt-1">
                            <div class="w-8 h-8 rounded-full bg-brand-card flex items-center justify-center">
                                <i data-lucide="sticky-note" class="w-4 h-4 text-gold-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-xs text-text-main">
                                    <span class="font-semibold">
                                        {{ $izin->tanggal_mulai?->translatedFormat('d M Y') }}
                                        @if($izin->tanggal_selesai && $izin->tanggal_selesai->ne($izin->tanggal_mulai))
                                            &ndash; {{ $izin->tanggal_selesai?->translatedFormat('d M Y') }}
                                        @endif
                                    </span>
                                    <span class="text-text-muted">
                                        · {{ $izin->jumlah_hari }} hari diajukan
                                    </span>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusMeta['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $statusMeta['label'] }}
                                </span>
                            </div>
                            <p class="text-[11px] text-text-muted line-clamp-2">
                                {{ $izin->alasan ?: 'Tanpa keterangan.' }}
                            </p>
                            @if($izin->keterangan_admin)
                                <p class="text-[10px] text-text-muted mt-0.5">
                                    <span class="font-semibold text-text-main">Catatan Admin:</span>
                                    <span class="italic">{{ $izin->keterangan_admin }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-[11px] text-text-muted">
                        Belum ada pengajuan izin. Ajukan izin jika Anda tidak dapat hadir latihan pada periode tertentu.
                    </p>
                @endforelse
            </div>
        </section>

        {{-- =========================================================
             7. PRODUK GYM & COACH
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- PRODUK GYM --}}
            <div class="lg:col-span-2 rounded-3xl bg-brand-card border border-brand-borderSoft p-6 space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-base font-bold text-text-main">
                            Rekomendasi Produk Gym
                        </h3>
                        <p class="text-[11px] text-text-muted">
                            Pilihan minuman, suplemen, dan kebutuhan latihan yang tersedia di BETA GYM.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($produkTerbaru as $produk)
                        <div class="rounded-2xl bg-brand-shell/80 border border-brand-borderSoft overflow-hidden flex">
                            <div class="w-24 h-24 bg-brand-card flex-shrink-0">
                                @if($produk->foto)
                                    <img
                                        src="{{ asset('storage/'.$produk->foto) }}"
                                        alt="{{ $produk->nama }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[10px] text-text-muted">
                                        No Image
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 px-4 py-3 flex flex-col justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-text-main line-clamp-1">
                                        {{ $produk->nama }}
                                    </p>
                                    <p class="text-[10px] text-text-muted">
                                        {{ $produk->kategori ?? 'Produk' }}
                                        · Stok {{ $produk->stok }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-sm font-bold text-gold-600">
                                        Rp {{ number_format($produk->harga ?? 0, 0, ',', '.') }}
                                    </p>
                                    <span class="text-[10px] text-text-muted">
                                        Tersedia di kasir
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-[11px] text-text-muted">
                            Belum ada produk yang terdaftar. Silakan hubungi petugas jika ingin membeli minuman atau suplemen.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- COACH --}}
            <div class="rounded-3xl bg-brand-card border border-brand-borderSoft p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-text-main">
                            Tim Coach BETA GYM
                        </h3>
                        <p class="text-[11px] text-text-muted">
                            Konsultasikan program latihan dan pola hidup sehat Anda bersama coach kami.
                        </p>
                    </div>
                    <div class="p-2 rounded-xl bg-brand-shell/70">
                        <i data-lucide="users" class="w-4 h-4 text-gold-600"></i>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($coaches as $coach)
                        <div class="rounded-2xl bg-brand-shell/80 border border-brand-borderSoft px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-card overflow-hidden flex-shrink-0">
                                @if($coach->foto)
                                    <img
                                        src="{{ asset('storage/'.$coach->foto) }}"
                                        alt="{{ $coach->nama }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[10px] text-text-muted">
                                        CG
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-text-main">
                                    {{ $coach->nama }}
                                </p>
                                <p class="text-[11px] text-text-muted line-clamp-2">
                                    {{ $coach->deskripsi ?: 'Coach siap membantu Anda menyusun program latihan yang sesuai dengan tujuan.' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[11px] text-text-muted">
                            Data coach belum tersedia di sistem.
                        </p>
                    @endforelse
                </div>
            </div>
        </section>

    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(148, 126, 77, 0.7);
        }
    </style>
</x-layouts.member>
