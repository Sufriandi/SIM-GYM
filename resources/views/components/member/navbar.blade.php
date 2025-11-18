{{-- resources/views/components/member/navbar.blade.php --}}
@props([
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<header class="bg-brand-shell border-b border-brand-borderSoft shadow-header">
    <div class="container flex items-center justify-between py-3 gap-4">

        {{-- BRAND --}}
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-beta-gym.png') }}" alt="BETA GYM" class="h-9 w-9 object-contain">
            <div class="leading-tight">
                <div class="font-display tracking-[0.2em] text-[11px] text-text-main uppercase">
                    BETA <span class="text-accent-500">GYM</span>
                </div>
                <div class="text-[11px] text-text-muted uppercase">
                    Member Area
                </div>
            </div>
        </div>

        {{-- MENU --}}
        <nav class="hidden md:flex items-center gap-6 text-xs font-medium text-text-muted">
            <a href="{{ route('member.dashboard') }}"
               class="hover:text-text-main @if(request()->routeIs('member.dashboard')) text-text-main @endif">
                Dashboard
            </a>
            <a href="{{ route('member.izin_latihan.index') }}"
               class="hover:text-text-main @if(request()->routeIs('member.izin_latihan.*')) text-text-main @endif">
                Izin Latihan
            </a>
            <a href="{{ route('profile.edit') }}"
               class="hover:text-text-main @if(request()->routeIs('profile.*')) text-text-main @endif">
                Profil
            </a>
        </nav>

        {{-- RIGHT SIDE --}}
        <div class="flex items-center gap-3">
            @if($pageTitle)
                <div class="hidden lg:block text-right">
                    <div class="text-xs font-semibold text-text-main truncate max-w-[180px]">
                        {{ $pageTitle }}
                    </div>
                    @if($pageSubtitle)
                        <div class="text-[11px] text-text-muted truncate max-w-[180px]">
                            {{ $pageSubtitle }}
                        </div>
                    @endif
                </div>
            @endif

            <span class="hidden sm:inline text-xs text-text-muted max-w-[120px] truncate">
                {{ auth()->user()->name ?? 'Member' }}
            </span>
            <div class="w-8 h-8 rounded-full bg-brand-black text-white flex items-center justify-center text-xs font-semibold">
                {{ strtoupper(substr(auth()->user()->name ?? 'MB', 0, 2)) }}
            </div>
        </div>

    </div>
</header>
