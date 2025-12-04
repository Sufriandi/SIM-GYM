<header
    x-data="{ mobileMenuOpen: false, scrolled: false }"
    @scroll.window="scrolled = (window.pageYOffset > 20)"
    :class="{ 'bg-brand-nav/90 backdrop-blur-md border-b border-brand-borderSoft/10 py-3': scrolled, 'bg-transparent py-5 border-transparent': !scrolled }"
    class="fixed top-0 w-full z-50 transition-all duration-normal ease-smooth border-b"
>
    <div class="container flex items-center justify-between">

        {{-- BRAND LOGO --}}
        <a href="/" class="flex items-center gap-3 group">
            {{-- Logo Icon --}}
            <div class="w-10 h-10 rounded-xl bg-gold-500 flex items-center justify-center text-brand-nav font-display font-bold text-xl shadow-gold-glow group-hover:scale-102 transition-transform">
                <img
                src="{{ asset('images/Logo.png') }}"
                alt="BETA GYM Logo">
            </div>
            {{-- Logo Text --}}
            <div class="flex flex-col leading-none">
                <span class="font-display font-bold text-xl tracking-wider text-brand-white">
                    BETA <span class="text-gold-500">GYM</span>
                </span>
                <span class="text-[10px] uppercase tracking-[0.2em] text-brand-silver group-hover:text-gold-500 transition-colors">
                    Build a Better You
                </span>
            </div>
        </a>

        {{-- DESKTOP MENU --}}
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-brand-silver">
            @foreach(['program' => 'Program', 'fasilitas' => 'Fasilitas', 'membership' => 'Membership'] as $id => $label)
                <a href="#{{ $id }}" class="relative hover:text-brand-white transition-colors group py-2">
                    {{ $label }}
                    {{-- Underline Animation --}}
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold-500 transition-all duration-normal ease-smooth group-hover:w-full"></span>
                </a>
            @endforeach
        </nav>

        {{-- ACTIONS (Desktop) --}}
        <div class="hidden md:flex items-center gap-5">
            <a href="{{ route('login') }}"
               class="text-sm font-bold font-heading tracking-wide text-brand-white hover:text-gold-500 transition-colors">
                MASUK
            </a>
            
            <a href="{{ route('register') }}"
               class="px-6 py-2.5 rounded-pill text-sm font-bold text-brand-white 
                      bg-accent-gradient bg-[length:200%_200%] animate-gradient-move 
                      shadow-btn-primary hover:shadow-btn-primary-hover 
                      hover:-translate-y-0.5 active:scale-98 
                      transition-all duration-normal ease-smooth">
                DAFTAR MEMBER
            </a>
        </div>

        {{-- MOBILE MENU BUTTON --}}
        <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-brand-white hover:text-gold-500 transition-colors focus:outline-none"
        >
            <i data-lucide="menu" class="w-8 h-8" x-show="!mobileMenuOpen"></i>
            <i data-lucide="x" class="w-8 h-8" x-show="mobileMenuOpen" style="display: none;"></i>
        </button>
    </div>

    {{-- MOBILE MENU DROPDOWN --}}
    <div
        x-show="mobileMenuOpen"
        style="display: none;"
        x-transition:enter="transition ease-out duration-normal"
        x-transition:enter-start="opacity-0 -translate-y-5"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-fast"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-5"
        class="absolute top-full left-0 w-full bg-brand-nav/95 backdrop-blur-xl border-t border-brand-borderSoft/10 md:hidden shadow-sidebar"
    >
        <div class="flex flex-col p-6 gap-4 text-center">
            <a href="#program" @click="mobileMenuOpen = false" class="text-brand-silver hover:text-gold-500 font-display text-lg tracking-wide py-3 border-b border-brand-borderSoft/5">PROGRAM</a>
            <a href="#fasilitas" @click="mobileMenuOpen = false" class="text-brand-silver hover:text-gold-500 font-display text-lg tracking-wide py-3 border-b border-brand-borderSoft/5">FASILITAS</a>
            <a href="#membership" @click="mobileMenuOpen = false" class="text-brand-silver hover:text-gold-500 font-display text-lg tracking-wide py-3 border-b border-brand-borderSoft/5">MEMBERSHIP</a>

            <div class="flex flex-col gap-3 mt-4">
                <a href="{{ route('login') }}" class="py-3 text-brand-white border border-brand-borderSoft/20 rounded-pill hover:bg-brand-surface-200/5 transition">Masuk</a>
                <a href="{{ route('register') }}" class="py-3 rounded-pill font-bold bg-accent-500 text-brand-white hover:bg-accent-600 transition shadow-btn-primary">Daftar Member</a>
            </div>
        </div>
    </div>
</header>

