{{-- resources/views/components/member/navbar.blade.php --}}
@props([
    'pageTitle' => null,     // sengaja tidak ditampilkan di navbar
    'pageSubtitle' => null,  // hanya untuk layout/body, bukan navbar
    'user' => null,
])

@php
    $user = $user ?? auth()->user();
@endphp

<header
    class="fixed top-0 left-0 right-0 h-16 z-30
           bg-brand-black text-brand-white
           border-b border-brand-borderSoft/60"
    x-data="{ showProfile: false }"
    @click.away="showProfile = false"
>
    <div class="w-full px-4 lg:px-8 h-full flex items-center justify-between gap-4">

        {{-- BRAND + MENU --}}
        <div class="flex items-center gap-10 min-w-0">
            {{-- LOGO + BETA GYM --}}
            <a href="{{ route('member.dashboard') }}"
               class="flex items-center gap-3 group">
                <img
                    src="{{ asset('images/Logo.png') }}"
                    alt="BETA GYM"
                    class="h-9 w-9 object-contain"
                >
                 <div class="leading-tight">
                <div
                    class="font-heading text-sm font-extrabold tracking-[0.25em] uppercase
                        text-brand-white group-hover:text-gold-100 transition-colors"
                >
                    BETA <span class="text-gold-400 group-hover:text-gold-300">GYM</span>
                </div>
            </div>
            </a>

            {{-- MENU UTAMA --}}
            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold">
                @php
                    $isDashboard = request()->routeIs('member.dashboard');
                    $isIzin      = request()->routeIs('member.izin_latihan.*');
                    $isProfile   = request()->routeIs('profile.*');
                @endphp

                <a href="{{ route('member.dashboard') }}"
                   class="relative pb-1 transition-colors
                          {{ $isDashboard ? 'text-gold-300' : 'text-brand-silver hover:text-brand-white' }}">
                    Dashboard
                    @if($isDashboard)
                        <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-gold-400 rounded-full"></span>
                    @endif
                </a>

                <a href="{{ route('member.izin_latihan.index') }}"
                   class="relative pb-1 transition-colors
                          {{ $isIzin ? 'text-gold-300' : 'text-brand-silver hover:text-brand-white' }}">
                    Izin Latihan
                    @if($isIzin)
                        <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-gold-400 rounded-full"></span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="relative pb-1 transition-colors
                          {{ $isProfile ? 'text-gold-300' : 'text-brand-silver hover:text-brand-white' }}">
                    Profil
                    @if($isProfile)
                        <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-gold-400 rounded-full"></span>
                    @endif
                </a>
            </nav>
        </div>

        {{-- AVATAR + DROPDOWN --}}
        <div class="flex items-center gap-3">
            <div class="relative">
                <button
                    @click.stop="showProfile = !showProfile"
                    class="flex items-center gap-3 pl-1 pr-3 py-1 rounded-full
                           bg-brand-card border border-brand-borderSoft
                           shadow-light
                           hover:bg-brand-shell/90
                           transition-all duration-200"
                    :class="{ 'ring-2 ring-gold-500/40': showProfile }"
                    aria-label="User menu"
                    aria-expanded="showProfile"
                >
                    {{-- Avatar huruf --}}
                    <div
                        class="w-9 h-9 rounded-full
                               bg-gradient-to-br from-gold-500 to-gold-700
                               flex items-center justify-center
                               text-xs font-bold text-brand-black shadow-md"
                    >
                        {{ strtoupper(substr($user->name ?? 'MB', 0, 2)) }}
                    </div>

                    {{-- Nama + role di dalam button --}}
                    <div class="leading-tight hidden sm:block text-left">
                        <div class="text-xs font-semibold text-brand-black truncate max-w-[120px]">
                            {{ $user->name ?? 'Member' }}
                        </div>
                        <div class="text-[11px] text-text-muted">
                            Member
                        </div>
                    </div>

                    <i data-lucide="chevron-down"
                       class="w-4 h-4 text-text-muted transition-transform duration-200"
                       :class="{ 'rotate-180': showProfile }"></i>
                </button>

                {{-- DROPDOWN PROFIL --}}
                <div
                    x-show="showProfile"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute right-0 mt-2 w-60
                           bg-brand-card border border-brand-borderSoft
                           rounded-2xl shadow-2xl overflow-hidden z-50"
                >
                    {{-- Header --}}
                    <div class="px-4 py-3 bg-brand-shell/90 border-b border-brand-borderSoft">
                        <p class="text-sm font-semibold text-text-main truncate">
                            {{ $user->name ?? 'Member' }}
                        </p>
                        <p class="text-xs text-text-muted truncate">
                            {{ $user->email ?? 'member@betagym.com' }}
                        </p>
                    </div>

                    {{-- Menu items --}}
                    <div class="py-2 bg-brand-card/95">
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-shell/80 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Profil Saya</span>
                        </a>

                        <a href="#"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-shell/80 transition-colors">
                            <i data-lucide="help-circle" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Bantuan</span>
                        </a>
                    </div>

                    {{-- Logout --}}
                    <div class="border-t border-brand-borderSoft bg-brand-shell/85 py-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-left
                                       hover:bg-accent-500/10 transition-colors"
                            >
                                <i data-lucide="log-out" class="w-4 h-4 text-accent-500"></i>
                                <span class="text-sm text-accent-500 font-medium">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</header>
