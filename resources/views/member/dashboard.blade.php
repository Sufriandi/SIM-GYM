{{-- resources/views/member/dashboard.blade.php --}}

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    /** @var \App\Models\User|null $user */
    /** @var \App\Models\Member|null $member */

    $user   = $user ?? auth()->user();
    $member = $member ?? ($user?->member);

    $greeting    = $greeting ?? 'Halo';
    $displayName = $member?->nama ?? $user?->name ?? 'Member';

    // Membership
    $membershipAktif     = (bool) ($membershipAktif ?? false);
    $mulai               = $mulai ?? null;
    $akhir               = $akhir ?? null;
    $sisaHari            = (int) ($sisaHari ?? 0);
    $totalDurasi         = (int) ($totalDurasi ?? 0);
    $hariTerpakai        = (int) ($hariTerpakai ?? 0);
    $progressUsedPercent = (int) ($progressUsedPercent ?? 0);
    $barPercent          = max(0, min(100, (int) $progressUsedPercent));

    // Izin
    $izinStats = $izinStats ?? ['pending' => 0, 'disetujui' => 0, 'ditolak' => 0, 'total' => 0];

    // Kehadiran
    $hadirBulanIni    = (int) ($hadirBulanIni ?? 0);
    $todayAttendance  = $todayAttendance ?? null;
    $recentAttendance = $recentAttendance ?? collect();

    // Paket terakhir
    $latestMembership  = $latestMembership ?? null;

    // Produk & coach (controller Anda mungkin masih pakai produkTerbaru/produkTerlaris)
    $produkRekomendasi = $produkRekomendasi
        ?? ($produkRekomendasi ?? null)
        ?? ($produkTerbaru ?? null)
        ?? collect();

    $coaches = $coaches ?? collect();

    // Hero
    $heroImageUrl = $heroImageUrl ?? asset('images/dashboard-hero.jpg');
    $initialDateText = now()->translatedFormat('l, d F Y');
    $initialTimeText = now()->format('H:i:s') . ' WIB';

    // ===========================
    // URL / ROUTE sesuai ROUTE ANDA (prefix /member)
    // ===========================
    $produkIndexUrl = Route::has('member.produk_gym.index') ? route('member.produk_gym.index') : null;
    $coachIndexUrl  = Route::has('member.coach.index')      ? route('member.coach.index')      : null;

    // Membership index (karena sekarang ada di /member/membership)
    $membershipIndexUrl = Route::has('member.membership.index')
        ? route('member.membership.index')
        : (Route::has('membership.index') ? route('membership.index') : null);

    $produkShowUrl = function ($produk) {
        if (! \Illuminate\Support\Facades\Route::has('member.produk_gym.show')) return null;

        $slug = $produk->slug
            ?? ((int) $produk->id . '-' . \Illuminate\Support\Str::slug((string) ($produk->nama ?? 'produk')));

        return route('member.produk_gym.show', ['slug' => $slug]);
    };

    $coachShowUrl = function ($coach) {
        if (! \Illuminate\Support\Facades\Route::has('member.coach.show')) return null;

        $slug = $coach->slug
            ?? ((int) $coach->id . '-' . \Illuminate\Support\Str::slug((string) ($coach->nama ?? 'coach')));

        return route('member.coach.show', ['slug' => $slug]);
    };
@endphp

<x-layouts.member
    :pageTitle="'Dashboard Member'"
    :pageSubtitle="'Ringkasan Membership & Aktivitas'"
>
    <div class="space-y-8 pb-12">

        {{-- =========================================================
             HERO: nama member + realtime date/time
        ========================================================== --}}
        <section class="relative overflow-hidden rounded-3xl border border-brand-borderSoft shadow-2xl">
            <div class="absolute inset-0 bg-center bg-cover"
                 style="background-image: url('{{ $heroImageUrl }}');"
                 aria-hidden="true"></div>

            <div class="absolute inset-0 pointer-events-none
                        bg-gradient-to-br from-black/80 via-black/55 to-black/85
                        dark:from-black/90 dark:via-black/60 dark:to-black/95"></div>

            <div class="absolute top-0 right-0 w-96 h-96 bg-gold-500/18 rounded-full blur-3xl -mr-28 -mt-28 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-gold-700/18 rounded-full blur-3xl -ml-24 -mb-28 pointer-events-none"></div>

            <div class="relative p-8 lg:p-10">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-black/30 border border-white/10 backdrop-blur-sm mb-4">
                            <span class="w-2 h-2 rounded-full bg-gold-300 animate-pulse"></span>
                            <p class="text-gold-200 font-semibold uppercase tracking-[0.25em] text-[10px]">
                                Member Dashboard
                            </p>
                            <span class="text-[10px] font-semibold flex items-center gap-1 {{ $membershipAktif ? 'text-emerald-200' : 'text-danger' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $membershipAktif ? 'bg-emerald-400' : 'bg-danger' }}"></span>
                                {{ $membershipAktif ? 'Membership Aktif' : 'Membership Berakhir' }}
                            </span>
                        </div>

                        <h1 class="text-4xl lg:text-5xl font-heading font-black tracking-tight leading-tight text-white drop-shadow-[0_2px_18px_rgba(0,0,0,0.55)]">
                            {{ $greeting }},
                            <span class="block mt-2 bg-gradient-to-r from-gold-200 via-gold-400 to-gold-600 bg-clip-text text-transparent drop-shadow-[0_2px_18px_rgba(0,0,0,0.55)]">
                                {{ $displayName }}
                            </span>
                        </h1>

                        <p class="text-sm lg:text-base mt-3 max-w-xl text-white/80 drop-shadow-[0_2px_14px_rgba(0,0,0,0.5)]">
                            Pantau membership, kehadiran, dan izin latihan dalam satu tampilan yang rapi.
                        </p>
                    </div>

                    <div class="hidden lg:flex items-center gap-4 bg-black/30 border border-white/10 px-6 py-3 rounded-2xl backdrop-blur-sm shadow-md">
                        <div class="p-3 bg-gold-500/15 rounded-xl text-gold-200">
                            <i data-lucide="calendar-days" class="w-6 h-6"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-white/60 font-bold uppercase tracking-[0.22em]">
                                Hari Ini
                            </p>
                            <p class="text-lg font-semibold text-white leading-tight whitespace-nowrap" data-live-date>
                                {{ $initialDateText }}
                            </p>
                            <p class="text-xs text-white/70 whitespace-nowrap" data-live-time>
                                {{ $initialTimeText }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- =========================================================
             GRID UTAMA: Ringkasan Membership + Status Pengajuan Izin
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Ringkasan Membership --}}
            <div class="lg:col-span-8 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/10 via-transparent to-transparent pointer-events-none"></div>

                <div class="relative p-6 lg:p-8 space-y-6" data-animate-scope>

                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.28em]">
                                Ringkasan Membership
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-2 min-w-0">
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold border whitespace-nowrap
                                    {{ $membershipAktif
                                        ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400'
                                        : 'bg-danger-soft/15 border-danger-soft/60 text-danger' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $membershipAktif ? 'bg-emerald-400' : 'bg-danger' }}"></span>
                                    {{ $membershipAktif ? 'Membership Aktif' : 'Membership Berakhir' }}
                                </span>

                                @if ($latestMembership)
                                    <span class="text-xs text-text-muted min-w-0 truncate">
                                        Paket:
                                        <span class="font-semibold text-gold-600">
                                            {{ $latestMembership->paket?->nama ?? $latestMembership->paket?->tipe ?? 'Paket Membership' }}
                                        </span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-xs text-text-muted whitespace-nowrap">
                            <i data-lucide="sparkles" class="w-4 h-4 text-gold-600"></i>
                            <span>Progress durasi</span>
                        </div>
                    </div>

                    {{-- Stat grid: Sisa Hari, Berlaku Sampai, Hadir Bulan Ini --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        {{-- Sisa Hari --}}
                        <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-5 h-full flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] whitespace-nowrap">
                                    Sisa Hari
                                </p>
                                <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                                    <i data-lucide="timer" class="w-4 h-4 text-gold-600"></i>
                                </div>
                            </div>

                            <div class="mt-4 flex items-baseline gap-2 whitespace-nowrap">
                                <span class="text-4xl font-heading font-black {{ $membershipAktif ? 'text-text-main' : 'text-danger' }}"
                                      data-counter data-count-to="{{ $sisaHari }}" data-count-from="0">0</span>
                                <span class="text-xs font-bold text-text-muted">hari</span>
                            </div>

                            <p class="mt-2 text-[11px] text-text-muted whitespace-nowrap">
                                Total durasi:
                                <span class="font-semibold text-text-main">{{ $totalDurasi ?: 0 }}</span> hari
                            </p>
                        </div>

                        {{-- Berlaku Sampai (1 baris) --}}
                        <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-5 h-full flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] whitespace-nowrap">
                                    Berlaku Sampai
                                </p>
                                <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gold-600"></i>
                                </div>
                            </div>

                            <div class="mt-4">
                                <span class="inline-flex whitespace-nowrap text-lg font-extrabold text-gold-600 font-mono tracking-tight"
                                      data-typewriter
                                      data-typewriter-text="{{ $akhir ? $akhir->format('d M Y') : '-' }}">—</span>
                            </div>

                            <p class="mt-2 text-[11px] text-text-muted whitespace-nowrap">
                                Mulai:
                                <span class="font-semibold text-text-main">
                                    {{ $mulai ? $mulai->format('d M Y') : '–' }}
                                </span>
                            </p>
                        </div>

                        {{-- Hadir Bulan Ini --}}
                        <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-5 h-full flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] whitespace-nowrap">
                                    Hadir Bulan Ini
                                </p>
                                <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                                    <i data-lucide="check-square-2" class="w-4 h-4 text-emerald-400"></i>
                                </div>
                            </div>

                            <div class="mt-4 flex items-baseline gap-2 whitespace-nowrap">
                                <span class="text-4xl font-heading font-black text-text-main"
                                      data-counter data-count-to="{{ $hadirBulanIni }}" data-count-from="0">0</span>
                                <span class="text-xs font-bold text-text-muted">sesi</span>
                            </div>

                            <p class="mt-2 text-[11px] text-text-muted whitespace-nowrap">
                                Bulan {{ now()->translatedFormat('F') }}
                            </p>
                        </div>

                    </div>

                    {{-- Progress --}}
                    <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.22em] whitespace-nowrap">
                                    Durasi Terpakai
                                </p>

                                <p class="mt-2 text-sm text-text-muted whitespace-nowrap">
                                    <span class="font-semibold text-text-main">{{ $hariTerpakai }}</span>
                                    /
                                    <span class="font-semibold text-text-main">{{ $totalDurasi ?: 0 }}</span>
                                    hari
                                    <span class="text-text-muted">•</span>
                                    <span class="font-black text-gold-600">
                                        <span data-counter data-count-to="{{ $membershipAktif ? $barPercent : 0 }}" data-count-from="0">0</span>%
                                    </span>
                                </p>
                            </div>

                            <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-4 h-4 text-gold-600"></i>
                            </div>
                        </div>

                        <div class="mt-4 w-full h-3 bg-brand-card/70 rounded-full overflow-hidden border border-brand-borderSoft">
                            <div class="h-full rounded-full bg-gradient-to-r from-gold-600 via-gold-400 to-emerald-400"
                                 data-progress-bar data-progress-to="{{ $membershipAktif ? $barPercent : 0 }}"
                                 style="width:0%"></div>
                        </div>

                        <div class="mt-3 flex justify-between text-[11px] text-text-muted">
                            <span class="whitespace-nowrap">{{ $membershipAktif ? 'Sedang berjalan' : 'Tidak aktif' }}</span>
                            <span class="whitespace-nowrap">{{ $membershipAktif ? 'Sisa '.$sisaHari.' hari' : 'Perpanjang untuk aktif kembali' }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if ($membershipAktif)
                            @if(Route::has('member.membership.history'))
                                <a href="{{ route('member.membership.history') }}"
                                   class="py-3.5 px-6 rounded-2xl bg-brand-shell hover:bg-brand-surface-50 border border-brand-borderSoft text-text-main
                                          font-semibold text-sm flex items-center justify-center gap-2 transition-colors">
                                    <i data-lucide="file-text" class="w-4 h-4 text-gold-600"></i>
                                    Riwayat Membership
                                </a>
                            @else
                                <button type="button"
                                        class="py-3.5 px-6 rounded-2xl bg-brand-shell border border-brand-borderSoft text-text-muted
                                               font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed"
                                        title="Route riwayat membership belum tersedia.">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    Riwayat Membership
                                </button>
                            @endif
                        @else
                            @if($membershipIndexUrl)
                                <a href="{{ $membershipIndexUrl }}"
                                   class="py-3.5 px-6 rounded-2xl bg-gold-600 hover:bg-gold-500 text-black
                                          font-bold text-sm flex items-center justify-center gap-2 transition-transform active:scale-[0.98]">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    Perpanjang Membership
                                </a>
                            @else
                                <button type="button"
                                        class="py-3.5 px-6 rounded-2xl bg-brand-shell border border-brand-borderSoft text-text-muted
                                               font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed"
                                        title="Route membership index belum tersedia.">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    Perpanjang Membership
                                </button>
                            @endif
                        @endif

                        @if(Route::has('member.kehadiran.index'))
                            <a href="{{ route('member.kehadiran.index') }}"
                               class="py-3.5 px-6 rounded-2xl bg-brand-shell hover:bg-brand-surface-50 border border-brand-borderSoft text-text-main
                                      font-semibold text-sm flex items-center justify-center gap-2 transition-colors">
                                <i data-lucide="clock-3" class="w-4 h-4 text-gold-600"></i>
                                Riwayat Kehadiran
                            </a>
                        @else
                            <button type="button"
                                    class="py-3.5 px-6 rounded-2xl bg-brand-shell border border-brand-borderSoft text-text-muted
                                           font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed"
                                    title="Route member.kehadiran.index belum tersedia.">
                                <i data-lucide="clock-3" class="w-4 h-4"></i>
                                Riwayat Kehadiran
                            </button>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Status Pengajuan Izin --}}
            <div class="lg:col-span-4 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl p-6 h-fit self-start">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-[0.26em] whitespace-nowrap">
                            Status Pengajuan Izin
                        </p>
                        <div class="mt-3">
                            <p class="text-5xl font-heading font-black text-text-main leading-none">
                                {{ (int) ($izinStats['pending'] ?? 0) }}
                            </p>
                            <p class="text-xs text-text-muted mt-2">
                                Pending menunggu tindakan Admin.
                            </p>
                        </div>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-brand-shell/70 border border-brand-borderSoft flex items-center justify-center">
                        <i data-lucide="file-clock" class="w-5 h-5 text-gold-600"></i>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4">
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-lg font-black text-text-main">{{ (int) ($izinStats['disetujui'] ?? 0) }}</p>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-text-muted whitespace-nowrap">Disetujui</p>
                        </div>
                        <div>
                            <p class="text-lg font-black text-text-main">{{ (int) ($izinStats['ditolak'] ?? 0) }}</p>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-text-muted whitespace-nowrap">Ditolak</p>
                        </div>
                        <div>
                            <p class="text-lg font-black text-text-main">{{ (int) ($izinStats['total'] ?? 0) }}</p>
                            <p class="text-[10px] uppercase tracking-[0.22em] text-text-muted whitespace-nowrap">Total</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-brand-borderSoft space-y-3">
                    @if ($membershipAktif && Route::has('member.izin_latihan.create'))
                        <a href="{{ route('member.izin_latihan.create') }}"
                           class="w-full py-3.5 px-6 rounded-2xl bg-gold-600 hover:bg-gold-500 text-black
                                  font-bold text-sm flex items-center justify-center gap-2 transition-transform active:scale-[0.98]">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Buat Izin
                        </a>
                    @else
                        <button type="button"
                                class="w-full py-3.5 px-6 rounded-2xl bg-brand-shell border border-brand-borderSoft text-text-muted
                                       font-semibold text-sm flex items-center justify-center gap-2 cursor-not-allowed"
                                title="{{ $membershipAktif ? 'Route create izin belum tersedia.' : 'Membership tidak aktif. Izin terkunci.' }}">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            Buat Izin
                        </button>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-text-muted">Lihat daftar pengajuan Anda.</span>
                        @if(Route::has('member.izin_latihan.index'))
                            <a href="{{ route('member.izin_latihan.index') }}"
                               class="text-[11px] font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1 whitespace-nowrap">
                                Buka
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @else
                            <span class="text-[11px] text-text-muted whitespace-nowrap">Route belum tersedia</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>


        {{-- =========================================================
             Aktivitas Hari Ini + Kehadiran Terakhir
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Aktivitas Hari Ini --}}
            <div class="lg:col-span-4 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-text-main">Aktivitas Hari Ini</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">Status membership & kehadiran hari ini.</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-brand-shell/70 border border-brand-borderSoft flex items-center justify-center">
                        <i data-lucide="sun-medium" class="w-5 h-5 text-gold-600"></i>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4 h-4 text-gold-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-text-main">Membership</p>
                            <p class="text-[11px] text-text-muted mt-0.5">
                                {{ $membershipAktif ? 'Aktif — Anda dapat menggunakan fasilitas gym.' : 'Berakhir — perpanjang untuk membuka akses.' }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                            <i data-lucide="{{ $todayAttendance ? 'check-circle-2' : 'circle-dashed' }}"
                               class="w-4 h-4 {{ $todayAttendance ? 'text-emerald-400' : 'text-text-muted' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-text-main">Kehadiran Hari Ini</p>
                            @if($todayAttendance)
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    Hadir pukul {{ $todayAttendance->jam_masuk ? Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : '-' }}
                                </p>
                            @else
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    Belum ada kehadiran tercatat.
                                    @if($membershipAktif) Silakan scan QR absensi di gym. @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-card border border-brand-borderSoft flex items-center justify-center">
                            <i data-lucide="file-clock" class="w-4 h-4 text-gold-600"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-text-main">Izin Pending</p>
                            <p class="text-[11px] text-text-muted mt-0.5">
                                {{ (int) ($izinStats['pending'] ?? 0) }} pengajuan menunggu tindakan admin.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-brand-borderSoft">
                    @if(Route::has('member.kehadiran.index'))
                        <a href="{{ route('member.kehadiran.index') }}"
                           class="text-[11px] font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1 whitespace-nowrap">
                            Lihat riwayat kehadiran
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Kehadiran Terakhir --}}
            <div class="lg:col-span-8 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl p-6 overflow-hidden">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-text-main">Kehadiran Terakhir</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">5 kehadiran terakhir yang tercatat.</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-brand-shell/70 border border-brand-borderSoft flex items-center justify-center">
                        <i data-lucide="list-checks" class="w-5 h-5 text-gold-600"></i>
                    </div>
                </div>

                <div class="w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-xs">
                        <thead>
                        <tr class="border-b border-brand-borderSoft text-[10px] uppercase tracking-[0.18em] text-text-muted">
                            <th class="py-3 pr-4 text-left">Tanggal</th>
                            <th class="py-3 px-4 text-left">Jam Masuk</th>
                            <th class="py-3 pl-4 text-left">Status</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-borderSoft/70">
                        @forelse($recentAttendance as $row)
                            @php $isValid = $row->is_valid ?? true; @endphp
                            <tr class="hover:bg-brand-surface-50/40 transition-colors">
                                <td class="py-3 pr-4 align-middle text-text-main whitespace-nowrap">
                                    {{ $row->tanggal?->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 align-middle text-text-muted whitespace-nowrap">
                                    {{ $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="py-3 pl-4 align-middle">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold border
                                        {{ $isValid ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/40' : 'bg-danger-soft/15 text-danger border-danger-soft/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isValid ? 'bg-emerald-400' : 'bg-danger' }}"></span>
                                        {{ $isValid ? 'Valid' : 'Tidak Valid' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-[11px] text-text-muted">
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
             Produk + Coach (klik ke detail + teks emas + panah)
        ========================================================== --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Produk --}}
            <div class="lg:col-span-8 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-text-main">Rekomendasi Produk Gym</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">Produk dipilih acak untuk membantu Anda menemukan item yang relevan.</p>
                    </div>

                    @if($produkIndexUrl)
                        <a href="{{ $produkIndexUrl }}"
                           class="text-xs font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1 whitespace-nowrap">
                            Lihat produk gym
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($produkRekomendasi as $produk)
                        @php $url = $produkShowUrl($produk); @endphp

                        @if($url)
                            <a href="{{ $url }}"
                               class="group rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-4 hover:bg-brand-surface-50/50 transition-colors">
                        @else
                            <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-4">
                        @endif

                                <div class="w-20 h-20 rounded-2xl bg-brand-card border border-brand-borderSoft overflow-hidden flex-shrink-0">
                                    @if($produk->foto)
                                        <img src="{{ asset('storage/'.$produk->foto) }}"
                                             alt="{{ $produk->nama }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-text-muted">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-bold text-text-main line-clamp-1">
                                            {{ $produk->nama }}
                                        </p>
                                        @if($url)
                                            <i data-lucide="chevron-right"
                                               class="w-4 h-4 text-gold-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        @endif
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5 whitespace-nowrap">
                                        {{ $produk->kategori ?? 'Produk' }}
                                        @if(isset($produk->stok))
                                            · Stok {{ (int) $produk->stok }}
                                        @endif
                                    </p>

                                    <p class="mt-3 text-base font-black text-gold-600 whitespace-nowrap">
                                        Rp {{ number_format((int)($produk->harga ?? 0), 0, ',', '.') }}
                                    </p>

                                    <p class="text-[11px] text-text-muted mt-2 line-clamp-2">
                                        {{ $produk->deskripsi ?: 'Klik untuk melihat detail produk.' }}
                                    </p>
                                </div>

                        @if($url)
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <p class="text-[11px] text-text-muted">Belum ada produk yang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            {{-- Coach --}}
            <div class="lg:col-span-4 rounded-3xl bg-brand-card border border-brand-borderSoft shadow-2xl p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-base font-bold text-text-main">Tim Coach BETA GYM</h3>
                        <p class="text-[11px] text-text-muted mt-0.5">Konsultasikan program latihan Anda dengan coach kami.</p>
                    </div>

                    @if($coachIndexUrl)
                        <a href="{{ $coachIndexUrl }}"
                           class="text-xs font-semibold text-gold-600 hover:text-gold-500 flex items-center gap-1 whitespace-nowrap">
                            Lihat coach
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($coaches as $coach)
                        @php $url = $coachShowUrl($coach); @endphp

                        @if($url)
                            <a href="{{ $url }}"
                               class="group rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-3 hover:bg-brand-surface-50/50 transition-colors">
                        @else
                            <div class="rounded-2xl bg-brand-shell/60 border border-brand-borderSoft p-4 flex gap-3">
                        @endif

                                <div class="w-12 h-12 rounded-2xl bg-brand-card border border-brand-borderSoft overflow-hidden flex-shrink-0">
                                    @if($coach->foto)
                                        <img src="{{ asset('storage/'.$coach->foto) }}"
                                             alt="{{ $coach->nama }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-text-muted">
                                            <i data-lucide="user" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-sm font-bold text-text-main">
                                            {{ $coach->nama }}
                                        </p>
                                        @if($url)
                                            <i data-lucide="chevron-right"
                                               class="w-4 h-4 text-gold-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-text-muted mt-0.5 line-clamp-2">
                                        {{ $coach->deskripsi ?: 'Klik untuk melihat detail coach.' }}
                                    </p>
                                </div>

                        @if($url)
                            </a>
                        @else
                            </div>
                        @endif
                    @empty
                        <p class="text-[11px] text-text-muted">Data coach belum tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>

    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(148, 126, 77, 0.7);
        }
    </style>

    @vite('resources/js/member/dashboard.jsx')
</x-layouts.member>
