@php
    $current = request()->route()?->getName() ?? '';

    $active = fn($prefix) => str($current)->startsWith($prefix);

    // Menu states (top level)
    $dashboardActive = $active('admin.dashboard');
    // pastikan hanya match ke admin.members.*
    $memberActive = str($current)->startsWith('admin.members.');

    // Membership (parent + children)
    $membershipPaketActive = $active('admin.paket_memberships'); // Kelola Paket Membership
    $membershipPenjualanActive = $active('admin.memberships'); // Penjualan Membership
    $membershipGroupActive = $active('admin.membership_groups'); // Halaman grup (tanpa menu langsung)
    $membershipActive = $membershipPaketActive || $membershipPenjualanActive || $membershipGroupActive;

    $penjualanActive = $active('admin.penjualan_produk');
    $produkMasterActive = $active('admin.produk');
    $stokActive = $active('admin.stok_produk');

    $coachActive = $active('admin.coaches');
    $inventarisActive = $active('admin.inventaris');

    $izinActive = $active('admin.izin_latihan');
    $absensiActive = $active('admin.absensi');
    $laporanActive = $active('admin.reports');

    // Profil gym
    $profilGymActive = $active('admin.profil_gym');

    // Submenu states (server-side default)
    $kehadiranOpen = $izinActive || $absensiActive;
    $produkManagementOpen = $penjualanActive || $produkMasterActive || $stokActive;
    $membershipManagementOpen = $membershipActive;

    // Notification counts (fallback dari controller)
    $izinPending = $izinPending ?? 0;
@endphp

<div x-data="{
    mobileOpen: false,
    openKehadiran: {{ $kehadiranOpen ? 'true' : 'false' }},
    openProduk: {{ $produkManagementOpen ? 'true' : 'false' }},
    openMembership: {{ $membershipManagementOpen ? 'true' : 'false' }},
}" @toggle-mobile-menu.window="mobileOpen = !mobileOpen" class="relative z-40"
    aria-label="Admin Navigation">

    {{-- OVERLAY untuk mobile --}}
    <div class="fixed inset-0 bg-black/50 md:hidden" x-show="mobileOpen" x-cloak x-transition.opacity
        @click="mobileOpen = false"></div>

    {{-- SIDEBAR --}}
    <aside
        class="flex flex-col fixed inset-y-0 left-0 w-64 bg-brand-black text-brand-white
               shadow-2xl transform transition-transform duration-200
               -translate-x-full md:translate-x-0"
        :class="{ 'translate-x-0': mobileOpen }">

        {{-- Logo Section --}}
        <div
            class="h-16 flex items-center gap-3 px-6 border-b border-brand-borderSoft/40
                   bg-gradient-to-r from-brand-gunmetal to-brand-black">
            <img src="{{ asset('images/Logo.png') }}" alt="BETA GYM Logo"
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

        {{-- Navigation Menu --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-8 custom-scrollbar">

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
                class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                    {{ $dashboardActive
                        ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                        : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                aria-current="{{ $dashboardActive ? 'page' : 'false' }}">
                <i data-lucide="layout-dashboard"
                    class="w-5 h-5 transition-transform duration-300
                        {{ $dashboardActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                <span>Dashboard</span>
                @if ($dashboardActive)
                    <div
                        class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                               rounded-full animate-pulse">
                    </div>
                @endif
            </a>

            {{-- Manajemen Section --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Manajemen
                </div>

                {{-- Manajemen Member --}}
                <a href="{{ route('admin.members.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $memberActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $memberActive ? 'page' : 'false' }}">
                    <i data-lucide="users"
                        class="w-5 h-5 transition-transform duration-300
                            {{ $memberActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Member</span>
                    @if ($memberActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                                   rounded-full animate-pulse">
                        </div>
                    @endif
                </a>

                {{-- Manajemen Membership (parent collapsible) --}}
                <button @click="openMembership = !openMembership" type="button"
                    class="w-full flex items-center justify-start gap-4 px-4 py-3
           rounded-2xl text-sm font-medium transition-all duration-300
           {{ $membershipActive
               ? 'text-gold-300 bg-brand-gunmetal/40'
               : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openMembership" aria-controls="membership-submenu">
                    <i data-lucide="badge-check" class="w-5 h-5 shrink-0"></i>

                    {{-- teks dibuat flex-1 & text-left supaya rata kiri walau 2 baris --}}
                    <span class="flex-1 text-left leading-tight">
                        Kelola Membership
                    </span>

                    <i data-lucide="chevron-down" class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': openMembership }"></i>
                </button>

                {{-- Submenu Membership --}}
                <div x-show="openMembership" x-cloak x-collapse x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" id="membership-submenu" class="space-y-1 mt-1 pl-4"
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

                    {{-- Penjualan Membership --}}
                    <a href="{{ route('admin.memberships.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $membershipPenjualanActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $membershipPenjualanActive ? 'page' : 'false' }}">
                        <i data-lucide="ticket-percent"
                            class="w-4 h-4 {{ $membershipPenjualanActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Penjualan Membership</span>
                    </a>
                </div>

                {{-- Manajemen Produk (parent collapsible) --}}
                <button @click="openProduk = !openProduk" type="button"
                    class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $produkManagementOpen
                            ? 'text-gold-300 bg-brand-gunmetal/40'
                            : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openProduk" aria-controls="produk-submenu">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                    <span>Kelola Produk</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': openProduk }"></i>
                </button>

                {{-- Submenu Produk --}}
                <div x-show="openProduk" x-cloak x-collapse x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" id="produk-submenu" class="space-y-1 mt-1 pl-4" role="menu">

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

                    <a href="{{ route('admin.penjualan_produk.index') }}"
                        class="group flex items-center gap-3 pl-8 pr-4 py-2.5 text-sm transition-all duration-200 rounded-lg
                            {{ $penjualanActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $penjualanActive ? 'page' : 'false' }}">
                        <i data-lucide="shopping-cart"
                            class="w-4 h-4 {{ $penjualanActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Penjualan Produk</span>
                    </a>
                </div>

                {{-- Manajemen Coach --}}
                <a href="{{ route('admin.coaches.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $coachActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $coachActive ? 'page' : 'false' }}">
                    <i data-lucide="user-check"
                        class="w-5 h-5 transition-transform duration-300
                            {{ $coachActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Coach</span>
                    @if ($coachActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                                   rounded-full animate-pulse">
                        </div>
                    @endif
                </a>

                {{-- Manajemen Inventaris --}}
                <a href="{{ route('admin.inventaris.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $inventarisActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $inventarisActive ? 'page' : 'false' }}">
                    <i data-lucide="package-search"
                        class="w-5 h-5 transition-transform duration-300
                            {{ $inventarisActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Inventaris Alat</span>

                    @if ($inventarisActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                                   rounded-full animate-pulse">
                        </div>
                    @endif
                </a>
            </div>

            {{-- Kehadiran Section --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Kehadiran
                </div>

                {{-- Parent Menu Kehadiran --}}
                <button @click="openKehadiran = !openKehadiran" type="button"
                    class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $izinActive || $absensiActive
                            ? 'text-gold-300 bg-brand-gunmetal/40'
                            : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/40' }}"
                    x-bind:aria-expanded="openKehadiran" aria-controls="kehadiran-submenu">
                    <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    <span>Kelola Kehadiran</span>
                    @if ($izinPending > 0)
                        <span
                            class="ml-auto px-2 py-0.5 text-[10px] bg-accent-500 text-white rounded-full font-semibold"
                            aria-label="{{ $izinPending }} izin pending">
                            {{ $izinPending }}
                        </span>
                    @endif
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 transition-transform duration-300 {{ $izinPending > 0 ? '' : 'ml-auto' }}"
                        :class="{ 'rotate-180': openKehadiran }"></i>
                </button>

                {{-- Submenu Kehadiran --}}
                <div x-show="openKehadiran" x-cloak x-collapse x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" id="kehadiran-submenu" class="space-y-1 mt-1" role="menu">

                    <a href="{{ route('admin.izin_latihan.index') }}"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200
                            {{ $izinActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $izinActive ? 'page' : 'false' }}">
                        <i data-lucide="file-text"
                            class="w-4 h-4 {{ $izinActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Daftar Izin</span>
                        @if ($izinPending > 0)
                            <span class="ml-auto text-[10px] text-accent-400 font-semibold">
                                {{ $izinPending }}
                            </span>
                        @endif
                    </a>

                    <a href="#"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200
                            {{ $absensiActive
                                ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem" aria-current="{{ $absensiActive ? 'page' : 'false' }}">
                        <i data-lucide="check-square"
                            class="w-4 h-4 {{ $absensiActive ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Data Absensi</span>
                    </a>
                </div>
            </div>

            {{-- Konfigurasi / Profil Gym --}}
            <div class="space-y-2 mt-4">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Konfigurasi
                </div>

                <a href="{{ route('admin.profil_gym.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $profilGymActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $profilGymActive ? 'page' : 'false' }}">
                    <i data-lucide="settings-2"
                        class="w-5 h-5 transition-transform duration-300
                            {{ $profilGymActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Kelola Profil Gym</span>

                    @if ($profilGymActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                                   rounded-full animate-pulse">
                        </div>
                    @endif
                </a>
            </div>

            {{-- Analitik Section --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-3">
                    Analitik
                </div>

                <a href="#"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium transition-all duration-300
                        {{ $laporanActive
                            ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                            : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $laporanActive ? 'page' : 'false' }}">
                    <i data-lucide="bar-chart-3"
                        class="w-5 h-5 transition-transform duration-300
                            {{ $laporanActive ? 'text-gold-300' : 'group-hover:scale-110' }}"></i>
                    <span>Laporan & Statistik</span>
                    @if ($laporanActive)
                        <div
                            class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600
                                   rounded-full animate-pulse">
                        </div>
                    @endif
                </a>
            </div>
        </nav>
    </aside>
</div>
