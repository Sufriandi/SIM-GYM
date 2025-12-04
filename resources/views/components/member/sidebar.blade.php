{{-- resources/views/components/member/sidebar.blade.php --}}
@php
    $current = request()->route()?->getName() ?? '';

    $isDashboard = str($current)->startsWith('member.dashboard');
    $isIzin      = str($current)->startsWith('member.izin_latihan');
    $isProduk    = str($current)->startsWith('member.produk_gym');
    $isCoach     = str($current)->startsWith('member.coach');
@endphp

<div
    x-data="{ mobileOpen: false }"
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

                {{-- Izin Latihan --}}
                <a
                    href="{{ route('member.izin_latihan.index') }}"
                    class="group flex items-center gap-4 px-4 py-3 rounded-2xl text-sm font-medium
                           transition-all duration-300
                           {{ $isIzin
                                ? 'bg-gradient-to-r from-gold-500/20 to-transparent text-gold-300 shadow-lg shadow-gold-500/20'
                                : 'text-brand-silver hover:bg-brand-gunmetal/40 hover:text-white hover:translate-x-1' }}"
                    aria-current="{{ $isIzin ? 'page' : 'false' }}"
                >
                    <i
                        data-lucide="calendar-clock"
                        class="w-5 h-5 transition-transform duration-300
                               {{ $isIzin ? 'text-gold-300' : 'group-hover:scale-110' }}"
                    ></i>
                    <span>Izin Latihan</span>
                </a>

                {{-- Produk Gym --}}
                <a
                    href="{{ route('member.produk_gym.index') }}"
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
