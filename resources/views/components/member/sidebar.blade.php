{{-- resources/views/components/member/sidebar.blade.php --}}
@php
    $current = request()->route()?->getName() ?? '';

    $isDashboard  = str($current)->startsWith('member.dashboard');
    $isIzin       = str($current)->startsWith('member.izin_latihan');
    $isKehadiran  = str($current)->startsWith('member.kehadiran');
    $isProduk     = str($current)->startsWith('member.produk_gym');
    $isCoach      = str($current)->startsWith('member.coach');

    // optional badge (kirim dari layout / view composer)
    $izinPending  = (int)($izinPending ?? 0);

    // buka dropdown jika salah satu submenu aktif
    $openKehadiranOnLoad = ($isIzin || $isKehadiran) ? 'true' : 'false';
@endphp

<div
    x-data="{
        mobileOpen: false,
        openKehadiran: {{ $openKehadiranOnLoad }},
    }"
    @toggle-member-sidebar.window="mobileOpen = !mobileOpen"
    class="relative z-40"
    aria-label="Member Navigation"
>
    {{-- OVERLAY MOBILE --}}
    <div
        class="fixed inset-0 bg-black/50 md:hidden"
        x-show="mobileOpen"
        x-cloak
        x-transition.opacity
        @click="mobileOpen = false"
    ></div>

    {{-- SIDEBAR (desktop + mobile slide-in) --}}
    <aside
        class="flex flex-col fixed inset-y-0 left-0 w-64 bg-brand-black text-brand-white
               shadow-2xl transform transition-transform duration-200
               -translate-x-full md:translate-x-0"
        :class="{ 'translate-x-0': mobileOpen }"
    >
        {{-- HEADER LOGO --}}
        <div
            class="h-16 flex items-center gap-3 px-6 border-b border-brand-borderSoft/40
                   bg-gradient-to-r from-brand-gunmetal to-brand-black"
        >
            <img
                src="{{ asset('images/Logo.png') }}"
                alt="BETA GYM Logo"
                class="h-11 w-11 object-contain rounded-2xl shadow-gold-glow"
                loading="lazy"
            >
            <div class="leading-tight">
                <div class="text-[11px] tracking-[0.25em] uppercase text-gold-300 font-semibold">
                    BETA GYM
                </div>
                <div class="text-[11px] text-brand-silver">
                    Area Member
                </div>
            </div>

            {{-- CLOSE (MOBILE) --}}
            <button
                type="button"
                class="ml-auto md:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl
                       text-brand-silver hover:text-white hover:bg-brand-gunmetal/40"
                @click="mobileOpen = false"
                aria-label="Tutup menu"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- MENU --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-6 custom-scrollbar">
            {{-- SECTION: AREA MEMBER --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-2">
                    Area Member
                </div>

                {{-- Dashboard --}}
                <a
                    href="{{ route('member.dashboard') }}"
                    @click="mobileOpen = false"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ $isDashboard
                                ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $isDashboard ? 'page' : 'false' }}"
                >
                    <i
                        data-lucide="layout-dashboard"
                        class="w-5 h-5 transition-transform duration-300
                               {{ $isDashboard ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>
                    <span>Dashboard</span>

                    @if($isDashboard)
                        <div class="ml-auto w-1.5 h-8 bg-gradient-to-b from-gold-400 to-gold-600 rounded-full animate-pulse"></div>
                    @endif
                </a>
            </div>

            {{-- ================== KEHADIRAN (MENU UTAMA + SUBMENU) ================== --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-2">
                    Kehadiran
                </div>

                @php
                    $kehadiranGroupActive = ($isIzin || $isKehadiran);
                @endphp

                <button
                    type="button"
                    @click="openKehadiran = !openKehadiran"
                    class="w-full group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ $kehadiranGroupActive
                                ? 'bg-gradient-to-r from-gold-500/15 to-transparent text-gold-300 shadow-lg shadow-gold-500/10'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white' }}"
                    :aria-expanded="openKehadiran.toString()"
                    aria-controls="kehadiran-submenu"
                >
                    <i
                        data-lucide="check-square"
                        class="w-5 h-5 transition-transform duration-300
                               {{ $kehadiranGroupActive ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>

                    <span class="truncate">Kehadiran</span>

                    @if ($izinPending > 0)
                        <span class="ml-auto px-2 py-0.5 text-[10px] bg-accent-500 text-white rounded-full font-semibold">
                            {{ $izinPending }}
                        </span>
                    @endif

                    <i
                        data-lucide="chevron-down"
                        class="w-4 h-4 transition-transform duration-300 {{ $izinPending > 0 ? '' : 'ml-auto' }}"
                        :class="{ 'rotate-180': openKehadiran }"
                    ></i>
                </button>

                {{-- SUBMENU --}}
                <div
                    id="kehadiran-submenu"
                    x-show="openKehadiran"
                    x-cloak
                    x-transition
                    class="space-y-1 mt-1"
                    role="menu"
                >
                    {{-- Izin Latihan --}}
                    <a
                        href="{{ route('member.izin_latihan.index') }}"
                        @click="mobileOpen = false"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-xl
                               {{ $isIzin
                                    ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                    : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem"
                        aria-current="{{ $isIzin ? 'page' : 'false' }}"
                    >
                        <i data-lucide="file-text" class="w-4 h-4 {{ $isIzin ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Izin Latihan</span>
                    </a>

                    {{-- Riwayat Kehadiran --}}
                    <a
                        href="{{ route('member.kehadiran.index') }}"
                        @click="mobileOpen = false"
                        class="group flex items-center gap-3 pl-12 pr-4 py-2.5 text-sm transition-all duration-200 rounded-xl
                               {{ $isKehadiran
                                    ? 'text-gold-300 font-medium bg-gradient-to-r from-gold-500/10 to-transparent'
                                    : 'text-brand-silver hover:text-white hover:bg-brand-gunmetal/30' }}"
                        role="menuitem"
                        aria-current="{{ $isKehadiran ? 'page' : 'false' }}"
                    >
                        <i data-lucide="qr-code" class="w-4 h-4 {{ $isKehadiran ? 'text-gold-300' : 'text-brand-silver/70' }}"></i>
                        <span>Riwayat Kehadiran</span>
                    </a>
                </div>
            </div>

            {{-- SECTION: LAYANAN --}}
            <div class="space-y-2">
                <div class="px-4 text-[11px] font-bold tracking-wider uppercase text-brand-silver/70 mb-2">
                    Layanan
                </div>
                {{-- Paket Membership --}}
                <a
                    href="{{ route('member.paket_membership.index') }}"
                    @click="mobileOpen = false"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ str($current)->startsWith('member.paket_membership')
                                ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ str($current)->startsWith('member.paket_membership') ? 'page' : 'false' }}"
                >
                    <i
                        data-lucide="credit-card"
                        class="w-5 h-5 transition-transform duration-300
                               {{ str($current)->startsWith('member.paket_membership') ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>
                    <span>Paket Membership</span>
                </a>

                {{-- Produk Gym --}}
                <a
                    href="{{ route('member.produk_gym.index') }}"
                    @click="mobileOpen = false"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ $isProduk
                                ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $isProduk ? 'page' : 'false' }}"
                >
                    <i
                        data-lucide="shopping-bag"
                        class="w-5 h-5 transition-transform duration-300
                               {{ $isProduk ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>
                    <span>Produk Gym</span>
                </a>

                {{-- Coach --}}
                <a
                    href="{{ route('member.coach.index') }}"
                    @click="mobileOpen = false"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ $isCoach
                                ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $isCoach ? 'page' : 'false' }}"
                >
                    <i
                        data-lucide="users"
                        class="w-5 h-5 transition-transform duration-300
                               {{ $isCoach ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>
                    <span>Coach</span>
                </a>
            </div>
        </nav>
    </aside>
</div>
