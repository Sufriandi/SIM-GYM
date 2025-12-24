@php
    use Illuminate\Support\Facades\Route;

    $loginUrl    = Route::has('login') ? route('login') : url('/login');
    $registerUrl = Route::has('register') ? route('register') : url('/register');

    $links = [
        ['label' => 'Home',        'href' => url('/'),            'active' => request()->is('/')],
        ['label' => 'Marketplace', 'href' => url('/marketplace'), 'active' => request()->is('marketplace*')],
        ['label' => 'Coaches',     'href' => url('/coaches'),     'active' => request()->is('coaches*')],
    ];
@endphp

<header class="fixed top-0 left-0 right-0 z-40 bg-brand-nav/80 backdrop-blur-md border-b border-brand-borderSoft/10">
    <div class="container mx-auto px-6 h-16 flex items-center justify-between"
         x-data="{ open: false }">

        <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-brand-surface-200/10 border border-brand-borderSoft/15 flex items-center justify-center shrink-0 overflow-hidden">
                <img src="{{ asset('images/Logo.png') }}" alt="BETA GYM" class="w-8 h-8 object-contain">
            </div>

            <div class="leading-tight min-w-0">
                <p class="font-display font-bold text-brand-white tracking-wide truncate">BETA GYM</p>
                <p class="text-[10px] text-brand-silver/70 uppercase tracking-widest truncate">Build a Better You</p>
            </div>
        </a>

        {{-- Desktop --}}
        <nav class="hidden lg:flex items-center gap-1">
            @foreach($links as $lnk)
                <a href="{{ $lnk['href'] }}"
                   class="px-4 py-2 rounded-pill text-sm font-bold font-heading transition
                          {{ $lnk['active'] ? 'bg-gold-500 text-brand-nav' : 'text-brand-white/90 hover:bg-brand-white/10' }}">
                    {{ $lnk['label'] }}
                </a>
            @endforeach

            <a href="{{ url('/dashboard') }}"
               class="ml-1 px-4 py-2 rounded-pill text-sm font-bold font-heading transition
                      {{ request()->is('dashboard') ? 'bg-brand-white/10 text-brand-white' : 'text-brand-white/80 hover:bg-brand-white/10' }}">
                Dashboard
            </a>
        </nav>

        <div class="hidden lg:flex items-center gap-3">
            <a href="{{ $loginUrl }}"
               class="px-5 py-2 rounded-pill border border-brand-borderSoft/20 text-brand-white font-bold hover:bg-brand-white/10 transition">
                Login
            </a>
            <a href="{{ $registerUrl }}"
               class="px-5 py-2 rounded-pill bg-gold-500 text-brand-nav font-bold hover:bg-gold-400 transition shadow-gold-glow">
                Register
            </a>
        </div>

        {{-- Mobile toggle --}}
        <button class="lg:hidden w-10 h-10 rounded-xl border border-brand-borderSoft/15 bg-brand-surface-200/10 flex items-center justify-center"
                @click="open = !open" aria-label="Open Menu">
            <i data-lucide="menu" class="w-5 h-5 text-brand-white"></i>
        </button>

        {{-- Mobile panel --}}
        <div x-cloak x-show="open" @click.away="open=false"
             class="absolute top-16 left-0 right-0 lg:hidden bg-brand-nav/95 border-b border-brand-borderSoft/10">
            <div class="container mx-auto px-6 py-4 flex flex-col gap-2">
                @foreach($links as $lnk)
                    <a href="{{ $lnk['href'] }}"
                       class="px-4 py-3 rounded-2xl font-bold transition
                              {{ $lnk['active'] ? 'bg-gold-500 text-brand-nav' : 'text-brand-white/90 hover:bg-brand-white/10' }}">
                        {{ $lnk['label'] }}
                    </a>
                @endforeach

                <a href="{{ url('/dashboard') }}"
                   class="px-4 py-3 rounded-2xl font-bold transition text-brand-white/90 hover:bg-brand-white/10">
                    Dashboard
                </a>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ $loginUrl }}"
                       class="py-3 rounded-2xl border border-brand-borderSoft/20 text-center text-brand-white font-bold hover:bg-brand-white/10 transition">
                        Login
                    </a>
                    <a href="{{ $registerUrl }}"
                       class="py-3 rounded-2xl bg-gold-500 text-center text-brand-nav font-bold hover:bg-gold-400 transition">
                        Register
                    </a>
                </div>
            </div>
        </div>

    </div>
</header>
