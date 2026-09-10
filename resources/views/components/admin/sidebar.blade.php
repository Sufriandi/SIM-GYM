{{-- resources/views/components/admin/sidebar.blade.php --}}

@php
    $current = request()->route()?->getName() ?? '';

    /**
     * Helper: route active (mendukung wildcard)
     * - Kalau input "admin.rekening." => dianggap "admin.rekening.*"
     */
    $active = function (string $pattern): bool {
        if (str_ends_with($pattern, '.')) {
            $pattern .= '*';
        }
        return request()->routeIs($pattern);
    };

    // ================== ACTIVE STATES ==================
    // Dashboard
    $dashboardActive = $active('admin.dashboard');

    // Member
    $memberActive = $active('admin.members.');

    // =========================
    // MEMBERSHIP (refactor)
    // =========================
    $membershipPaketActive = $active('admin.paket_memberships.');
    $membershipTransaksiActive = $active('admin.transaksi_membership.');

    // Optional: membership_groups kalau masih ada
    $membershipGroupActive = Route::has('admin.membership_groups.index') ? $active('admin.membership_groups.') : false;

    $membershipActive = $membershipPaketActive || $membershipTransaksiActive || $membershipGroupActive;

    // =========================
    // PRODUK (refactor transaksi-produk)
    // =========================
    $produkMasterActive = $active('admin.produk.');
    $stokActive = $active('admin.stok_produk.');

    // Parent aktif jika berada di route admin.transaksi_produk.* (index/store/history/destroy)
    $transaksiProdukActive = $active('admin.transaksi_produk.');

    // Highlight submenu
    $transaksiProdukHistoryActive = request()->routeIs('admin.transaksi_produk.history');
    // selain history, anggap kasir (index/store/destroy) -> kasir aktif
    $transaksiProdukKasirActive = $transaksiProdukActive && !$transaksiProdukHistoryActive;

    $produkManagementOpen = $transaksiProdukActive || $produkMasterActive || $stokActive;

    // Latihan Harian
    $latihanHarianActive = $active('admin.latihan_harian.');

    // Lainnya
    $coachActive = $active('admin.coaches.');
    $inventarisActive = $active('admin.inventaris.');

    // Kehadiran (operasional admin)
    $izinActive = $active('admin.izin_latihan.');
    $absensiActive = $active('admin.absensi.');
    $kehadiranOpen = $izinActive || $absensiActive;

    // Profil gym
    $profilGymActive = $active('admin.profil_gym.');

    // =========================
    // REKENING (khusus: index beda prefix dengan aksi CRUD)
    // =========================
    $rekeningIndexActive = $active('admin.rekening.');
    $infoRekeningActive = $active('admin.info-rekening.');
    $rekeningActive = $rekeningIndexActive || $infoRekeningActive;

    // =========================
    // LAPORAN / ANALITIK (Development)
    // =========================
    $laporanActive = $active('admin.laporan.');
    $laporanOpen = $laporanActive;

    // Default open
    $membershipManagementOpen = $membershipActive;

    // Notif (fallback agar tidak undefined)
    $izinPending = $izinPending ?? 0;
@endphp

<div x-data="{
    mobileOpen: false,
    openKehadiran: {{ $kehadiranOpen ? 'true' : 'false' }},
    openProduk: {{ $produkManagementOpen ? 'true' : 'false' }},
    openMembership: {{ $membershipManagementOpen ? 'true' : 'false' }},
    openLaporan: {{ $laporanOpen ? 'true' : 'false' }},
}" @toggle-mobile-menu.window="mobileOpen = !mobileOpen" class="relative z-40"
    aria-label="Admin Navigation">
    {{-- OVERLAY MOBILE --}}
    <div class="fixed inset-0 bg-black/50 md:hidden" x-show="mobileOpen" x-cloak x-transition.opacity
        @click="mobileOpen = false"></div>

    <aside
        class="flex flex-col fixed inset-y-0 left-0 w-64 bg-brand-black text-brand-white
               shadow-2xl transform transition-transform duration-200
               -translate-x-full md:translate-x-0"
        :class="{ 'translate-x-0': mobileOpen }">
        {{-- LOGO --}}
        <div
            class="h-16 flex items-center gap-3 px-6 border-b border-brand-borderSoft/40
                   bg-gradient-to-r from-brand-gunmetal to-brand-black">
            <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo"
                class="h-11 w-11 object-contain rounded-2xl shadow-gold-glow" loading="lazy">
            <div class="leading-tight">
                <div class="text-[11px] tracking-[0.25em] uppercase text-gold-300 font-semibold">
                    BETA GYM
                </div>
                <div class="text-[11px] text-brand-silver">
                    Admin Panel
                </div>
            </div>
        </div>

        {{-- NAV --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-8 custom-scrollbar">

            {{-- ================== DASHBOARD ================== --}}
            <a href="{{ route('admin.dashboard') }}"
                class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                    {{ $dashboardActive
                        ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                        : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                aria-current="{{ $dashboardActive ? 'page' : 'false' }}">
                <i data-lucide="layout-dashboard"
                    class="w-5 h-5 transition-transform duration-300 {{ $dashboardActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                <span>Dashboard</span>
                @if ($dashboardActive)
                    <div
                        class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse">
                    </div>
                @endif
            </a>

            {{-- ================== MANAJEMEN ================== --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Manajemen
                </div>

                {{-- Kelola Member --}}
                <a href="{{ route('admin.members.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $memberActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $memberActive ? 'page' : 'false' }}">
                    <i data-lucide="users"
                        class="w-5 h-5 transition-transform duration-300 {{ $memberActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Member</span>

                    @if ($memberActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse">
                        </div>
                    @endif
                </a>

                {{-- Kelola Membership (Dropdown) --}}
                <button type="button" @click="openMembership = !openMembership"
                    class="w-full flex items-center justify-start gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $membershipActive ? 'text-gold-300 bg-brand-gunmetal/40' : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openMembership" aria-controls="membership-submenu">
                    <i data-lucide="badge-check" class="w-5 h-5 shrink-0"></i>
                    <span class="flex-1 text-left leading-tight">Kelola Membership</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': openMembership }"></i>
                </button>

                <div x-show="openMembership" x-cloak x-collapse id="membership-submenu" class="space-y-1 mt-1 pl-4"
                    role="menu">
                    {{-- Paket Membership --}}
                    <a href="{{ route('admin.paket_memberships.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $membershipPaketActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $membershipPaketActive ? 'page' : 'false' }}">
                        <i data-lucide="layers"
                            class="w-4 h-4 {{ $membershipPaketActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Paket Membership</span>
                    </a>

                    {{-- Penjualan / Transaksi Membership (refactor) --}}
                    <a href="{{ route('admin.transaksi_membership.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $membershipTransaksiActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $membershipTransaksiActive ? 'page' : 'false' }}">
                        <i data-lucide="ticket-percent"
                            class="w-4 h-4 {{ $membershipTransaksiActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Penjualan Membership</span>
                    </a>

                    {{-- Optional: Membership Group --}}
                    @if (Route::has('admin.membership_groups.index'))
                        <a href="{{ route('admin.membership_groups.index') }}"
                            class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                                {{ $membershipGroupActive
                                    ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                    : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                            role="menuitem" aria-current="{{ $membershipGroupActive ? 'page' : 'false' }}">
                            <i data-lucide="users-2"
                                class="w-4 h-4 {{ $membershipGroupActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                            <span>Anggota Paket (Group)</span>
                        </a>
                    @endif
                </div>

                {{-- Kelola Produk (Dropdown) --}}
                <button type="button" @click="openProduk = !openProduk"
                    class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $produkManagementOpen ? 'text-gold-300 bg-brand-gunmetal/40' : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openProduk" aria-controls="produk-submenu">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                    <span>Kelola Produk</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': openProduk }"></i>
                </button>

                <div x-show="openProduk" x-cloak x-collapse id="produk-submenu" class="space-y-1 mt-1 pl-4"
                    role="menu">
                    <a href="{{ route('admin.produk.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $produkMasterActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $produkMasterActive ? 'page' : 'false' }}">
                        <i data-lucide="package"
                            class="w-4 h-4 {{ $produkMasterActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Produk</span>
                    </a>

                    <a href="{{ route('admin.stok_produk.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $stokActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $stokActive ? 'page' : 'false' }}">
                        <i data-lucide="box"
                            class="w-4 h-4 {{ $stokActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Stok Produk</span>
                    </a>

                    {{-- Transaksi Produk (Kasir) --}}
                    <a href="{{ route('admin.transaksi_produk.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $transaksiProdukKasirActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $transaksiProdukKasirActive ? 'page' : 'false' }}">
                        <i data-lucide="shopping-cart"
                            class="w-4 h-4 {{ $transaksiProdukKasirActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Transaksi Produk</span>
                    </a>

                    {{-- Riwayat Transaksi Produk --}}
                    <a href="{{ route('admin.transaksi_produk.history') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $transaksiProdukHistoryActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $transaksiProdukHistoryActive ? 'page' : 'false' }}">
                        <i data-lucide="history"
                            class="w-4 h-4 {{ $transaksiProdukHistoryActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                </div>

                {{-- Latihan Harian --}}
                <a href="{{ route('admin.latihan_harian.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $latihanHarianActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $latihanHarianActive ? 'page' : 'false' }}">
                    <i data-lucide="activity"
                        class="w-5 h-5 transition-transform duration-300 {{ $latihanHarianActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Latihan Harian</span>
                </a>

                {{-- Coach --}}
                <a href="{{ route('admin.coaches.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $coachActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $coachActive ? 'page' : 'false' }}">
                    <i data-lucide="user-check"
                        class="w-5 h-5 transition-transform duration-300 {{ $coachActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Coach</span>
                </a>

                {{-- Inventaris --}}
                <a href="{{ route('admin.inventaris.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $inventarisActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $inventarisActive ? 'page' : 'false' }}">
                    <i data-lucide="package-search"
                        class="w-5 h-5 transition-transform duration-300 {{ $inventarisActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Inventaris Alat</span>
                </a>
            </div>

            {{-- ================== KEHADIRAN ================== --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Kehadiran
                </div>

                <button type="button" @click="openKehadiran = !openKehadiran"
                    class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $izinActive || $absensiActive ? 'text-gold-300 bg-brand-gunmetal/40' : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openKehadiran" aria-controls="kehadiran-submenu">
                    <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    <span>Kelola Kehadiran</span>

                    @if ($izinPending > 0)
                        <span
                            class="ml-auto px-2 py-0.5 text-[10px] bg-accent-500 text-white rounded-full font-semibold">
                            {{ $izinPending }}
                        </span>
                    @endif

                    <i data-lucide="chevron-down"
                        class="w-4 h-4 transition-transform duration-300 {{ $izinPending > 0 ? '' : 'ml-auto' }}"
                        :class="{ 'rotate-180': openKehadiran }"></i>
                </button>

                <div x-show="openKehadiran" x-cloak x-collapse id="kehadiran-submenu" class="space-y-1 mt-1"
                    role="menu">
                    <a href="{{ route('admin.izin_latihan.index') }}"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $izinActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $izinActive ? 'page' : 'false' }}">
                        <i data-lucide="file-text"
                            class="w-4 h-4 {{ $izinActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Daftar Izin</span>
                    </a>

                    <a href="{{ route('admin.absensi.index') }}"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $absensiActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $absensiActive ? 'page' : 'false' }}">
                        <i data-lucide="qr-code"
                            class="w-4 h-4 {{ $absensiActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Absensi QR &amp; Data</span>
                    </a>
                </div>
            </div>

            {{-- ================== KONFIGURASI ================== --}}
            <div class="space-y-2 mt-4">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Konfigurasi
                </div>

                {{-- <a href="{{ route('admin.profil_gym.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $profilGymActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $profilGymActive ? 'page' : 'false' }}">
                    <i data-lucide="settings-2"
                        class="w-5 h-5 transition-transform duration-300 {{ $profilGymActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Profil Gym</span>
                    @if ($profilGymActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse">
                        </div>
                    @endif
                </a> --}}

                {{-- Rekening --}}
                <a href="{{ route('admin.rekening.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $rekeningActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $rekeningActive ? 'page' : 'false' }}">
                    <i data-lucide="credit-card"
                        class="w-5 h-5 transition-transform duration-300 {{ $rekeningActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Rekening</span>
                    @if ($rekeningActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse">
                        </div>
                    @endif
                </a>
            </div>

            {{-- ================== ANALITIK / LAPORAN ================== --}}
            <div class="space-y-2 mt-4">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Analitik
                </div>

                <button type="button" @click="openLaporan = !openLaporan"
                    class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
            {{ $laporanActive ? 'text-gold-300 bg-brand-gunmetal/40' : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openLaporan" aria-controls="laporan-submenu">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="flex-1 text-left">Laporan &amp; Statistik</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': openLaporan }"></i>
                </button>

                <div x-show="openLaporan" x-cloak x-collapse id="laporan-submenu" class="space-y-1 mt-1"
                    role="menu">
                    @php
                        $lapAbsensiActive = request()->routeIs('admin.laporan.kehadiran.*');
                        $lapKeuanganActive = request()->routeIs('admin.laporan.keuangan.*');
                    @endphp

                    {{-- Laporan Absensi --}}
                    <a href="{{ route('admin.laporan.kehadiran.index') }}"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                {{ $lapAbsensiActive
                    ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                    : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $lapAbsensiActive ? 'page' : 'false' }}">
                        <i data-lucide="qr-code"
                            class="w-4 h-4 {{ $lapAbsensiActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Laporan Kehadiran</span>
                    </a>

                    {{-- ✅ Laporan Keuangan (AKTIF / TIDAK TERKUNCI) --}}
                    <a href="{{ route('admin.laporan.keuangan.index') }}"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                {{ $lapKeuanganActive
                    ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                    : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $lapKeuanganActive ? 'page' : 'false' }}">
                        <i data-lucide="wallet"
                            class="w-4 h-4 {{ $lapKeuanganActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Laporan Keuangan</span>
                    </a>


                </div>
            </div>

        </nav>
    </aside>
</div>
