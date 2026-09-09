@php
    use Illuminate\Support\Facades\Route;

    $loginUrl = Route::has('login') ? route('login') : url('/login');
    $registerUrl = Route::has('register') ? route('register') : url('/register');

    // Menu Data (Bahasa Indonesia)
    $links = [
        ['label' => 'Beranda', 'href' => url('/'), 'active' => request()->is('/')],
        ['label' => 'Membership', 'href' => url('/membership'), 'active' => request()->is('membership*')],
        ['label' => 'Marketplace', 'href' => url('/marketplace'), 'active' => request()->is('marketplace*')],
        ['label' => 'Pelatih', 'href' => url('/coaches'), 'active' => request()->is('coaches*')],
    ];
@endphp

{{-- 
    NAVBAR PREMIUM DARK (BAHASA INDONESIA)
--}}
<header x-data="{
    scrolled: false,
    mobileOpen: false,
    init() {
        this.scrolled = window.pageYOffset > 20;
    }
}" @scroll.window="scrolled = (window.pageYOffset > 20)"
    :class="scrolled
        ?
        'bg-brand-nav/90 backdrop-blur-md border-b border-white/5 py-4 shadow-xl' :
        'bg-transparent border-b border-transparent py-6'"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 w-full ease-out">
    <div class="container mx-auto px-6 md:px-12">
        <div class="flex items-center justify-between">

            {{-- ========================================================= --}}
            {{-- 1. LOGO BRAND --}}
            {{-- ========================================================= --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group relative z-50">
                <div
                    class="relative w-9 h-9 flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM"
                        class="w-full h-full object-contain relative z-10">
                    {{-- Glow tipis di belakang logo --}}
                    <div
                        class="absolute inset-0 bg-gold-500/20 blur-lg rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                </div>

                <div class="flex flex-col justify-center">
                    <span class="font-display font-bold text-xl text-white tracking-wide leading-none">
                        BETA GYM
                    </span>
                    <span
                        class="text-[10px] font-bold text-gold-500 uppercase tracking-[0.25em] leading-tight group-hover:text-white transition-colors duration-300">
                        Premium
                    </span>
                </div>
            </a>

            {{-- ========================================================= --}}
            {{-- 2. DESKTOP MENU (INDONESIA) --}}
            {{-- ========================================================= --}}
            <nav class="hidden lg:flex items-center gap-10">
                @foreach ($links as $lnk)
                    <a href="{{ $lnk['href'] }}" class="relative group py-2 block">

                        {{-- Label Menu --}}
                        <span
                            class="text-sm font-heading font-bold tracking-wider uppercase transition-colors duration-300
                                     {{ $lnk['active'] ? 'text-gold-500' : 'text-brand-textSoft group-hover:text-white' }}">
                            {{ $lnk['label'] }}
                        </span>

                        {{-- GARIS EMAS (ANIMATED UNDERLINE) --}}
                        <span
                            class="absolute bottom-0 left-0 h-[2px] bg-gold-500 rounded-full transition-all duration-300 ease-out shadow-[0_0_8px_rgba(212,167,87,0.6)]
                                     {{ $lnk['active'] ? 'w-full' : 'w-0 group-hover:w-full' }}">
                        </span>
                    </a>
                @endforeach
            </nav>

            {{-- ========================================================= --}}
            {{-- 3. ACTION BUTTONS (INDONESIA) --}}
            {{-- ========================================================= --}}
            <div class="hidden lg:flex items-center gap-6">
                <a href="{{ $loginUrl }}"
                    class="text-sm font-bold text-white hover:text-gold-500 transition-colors uppercase tracking-wider">
                    Masuk
                </a>

                <a href="{{ $registerUrl }}"
                    class="group relative px-6 py-2.5 bg-gold-500 text-brand-nav font-black text-sm uppercase tracking-widest rounded-full overflow-hidden transition-transform duration-300 hover:scale-105 shadow-[0_0_15px_-5px_rgba(212,167,87,0.5)]">
                    <span class="relative z-10 flex items-center gap-2">
                        Gabung Member <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                    {{-- Efek Kilau Lewat --}}
                    <div
                        class="absolute top-0 -left-[100%] w-full h-full bg-gradient-to-r from-transparent via-white/30 to-transparent transform -skew-x-12 transition-all duration-700 group-hover:left-[100%]">
                    </div>
                </a>
            </div>

            {{-- ========================================================= --}}
            {{-- 4. MOBILE HAMBURGER --}}
            {{-- ========================================================= --}}
            <button
                class="lg:hidden relative z-50 text-white hover:text-gold-500 transition-colors focus:outline-none p-1"
                @click="mobileOpen = !mobileOpen" aria-label="Menu">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="4" x2="20" y1="12" y2="12" />
                        <line x1="4" x2="20" y1="6" y2="6" />
                        <line x1="4" x2="20" y1="18" y2="18" />
                    </svg>
                    <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </div>
            </button>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- 5. MOBILE MENU OVERLAY (INDONESIA) --}}
    {{-- ========================================================= --}}
    <div x-cloak x-show="mobileOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4" @click.away="mobileOpen = false"
        class="fixed inset-x-0 top-0 pt-24 pb-8 z-40 lg:hidden bg-brand-nav/98 backdrop-blur-xl border-b border-white/10 shadow-2xl">

        <div class="container mx-auto px-6 flex flex-col gap-6">
            <nav class="flex flex-col gap-1">
                @foreach ($links as $lnk)
                    <a href="{{ $lnk['href'] }}"
                        class="text-lg font-heading font-bold uppercase tracking-wide py-3 border-b border-white/5 flex items-center justify-between group
                              {{ $lnk['active'] ? 'text-gold-500' : 'text-gray-400 hover:text-white' }}">
                        <span>{{ $lnk['label'] }}</span>
                        @if ($lnk['active'])
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="grid grid-cols-2 gap-4 mt-2">
                <a href="{{ $loginUrl }}"
                    class="py-3.5 rounded-xl border border-white/10 text-center text-white font-bold uppercase tracking-wider hover:bg-white/5 transition">
                    Masuk
                </a>
                <a href="{{ $registerUrl }}"
                    class="py-3.5 rounded-xl bg-gold-500 text-center text-brand-nav font-bold uppercase tracking-wider hover:bg-gold-400 transition shadow-lg">
                    Daftar
                </a>
            </div>
        </div>
    </div>
</header>
