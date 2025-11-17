{{-- resources/views/components/guest/navbar.blade.php --}}

<header class="bg-brand-shell border-b border-brand-borderSoft shadow-header">
    <div class="container flex items-center justify-between py-3 gap-4">

        {{-- BRAND --}}
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-beta-gym.png') }}" alt="BETA GYM" class="h-9 w-9 object-contain">
            <div class="leading-tight">
                <div class="font-display tracking-[0.25em] text-[11px] text-text-main uppercase">
                    BETA <span class="text-accent-500">GYM</span>
                </div>
                <div class="text-[11px] text-text-muted uppercase">
                    Build a Better You
                </div>
            </div>
        </div>

        {{-- MENU --}}
        <nav class="hidden md:flex items-center gap-6 text-sm text-text-muted">
            <a href="#program" class="hover:text-text-main">Program</a>
            <a href="#fasilitas" class="hover:text-text-main">Fasilitas</a>
            <a href="#membership" class="hover:text-text-main">Membership</a>
        </nav>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}"
               class="text-xs font-medium text-text-muted hover:text-text-main">
                Masuk
            </a>
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold rounded-pill
                      bg-accent-500 text-brand-white shadow-btn-primary
                      transition-all duration-normal ease-smooth
                      hover:bg-accent-600 hover:shadow-btn-primary-hover hover:-translate-y-0.5
                      active:scale-98">
                Daftar Member
            </a>
        </div>

    </div>
</header>
