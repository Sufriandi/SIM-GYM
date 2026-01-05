{{-- resources/views/components/member/navbar.blade.php --}}
@props([
    'pageTitle' => null,
    'pageSubtitle' => null,   // tetap di-abaikan di navbar
    'notificationCount' => 0,
])
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $authUser = auth()->user();
    $avatarUrl = ($authUser && !empty($authUser->foto)) ? Storage::url($authUser->foto) : null;

    $initials = Str::of($authUser?->name ?: 'AD')
        ->trim()
        ->explode(' ')
        ->map(fn($p) => Str::upper(Str::substr($p, 0, 1)))
        ->take(2)
        ->join('');
@endphp
<header
    class="fixed top-0 left-0 md:left-64 right-0 h-16 flex items-center z-[80]
           bg-brand-shell/95 backdrop-blur-sm border-b border-brand-borderSoft shadow-header"
    x-data="{
        showNotifications: false,
        showProfile: false,
        searchQuery: ''
    }"
    @click.away="showNotifications = false; showProfile = false"
    role="banner"
>
    <div
        class="w-full px-4 lg:px-8 flex items-center justify-between gap-3 md:gap-4 flex-wrap"
    >
        {{-- KIRI: tombol mobile + breadcrumb --}}
        <div class="flex items-center gap-3 min-w-0 flex-1">
            {{-- Toggle sidebar mobile --}}
            <button
                @click="window.dispatchEvent(new CustomEvent('toggle-member-sidebar'))"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl
                       bg-brand-card border border-brand-borderSoft shadow-light
                       hover:bg-brand-gunmetal/40 hover:text-brand-white transition-colors duration-200"
                aria-label="Toggle member sidebar"
            >
                <i data-lucide="menu" class="w-5 h-5 text-text-main"></i>
            </button>

            {{-- Breadcrumb --}}
            <div class="min-w-0">
                <nav
                    class="flex items-center text-xs sm:text-sm font-bold text-text-muted"
                    aria-label="Breadcrumb"
                >
                    <a
                        href="{{ route('member.dashboard') }}"
                        class="inline-flex items-center gap-1 hover:text-gold-400 transition-colors min-w-0"
                    >
                        <i data-lucide="home" class="w-4 h-4" stroke-width="3"></i>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>

                    @if($pageTitle)
                        <i
                            data-lucide="chevron-right"
                            class="w-4 h-4 mx-1.5 text-text-muted flex-shrink-0"
                            stroke-width="3"
                        ></i>

                        <span
                            class="text-text-main font-bold truncate
                                   max-w-[140px] sm:max-w-[200px] md:max-w-[260px]"
                        >
                            {{ $pageTitle }}
                        </span>
                    @endif
                </nav>
            </div>
        </div>

        {{-- KANAN: (optional) search + notif + profile --}}
        <div class="flex items-center gap-2 lg:gap-3 flex-shrink-0">
            {{-- Search desktop --}}
            <div
                class="hidden lg:flex items-center bg-brand-card rounded-full px-4 py-2
                       border border-brand-borderSoft shadow-light
                       min-w-[220px] xl:min-w-[260px]
                       hover:border-gold-500/40 hover:shadow-gold-glow/60
                       transition-all duration-200 group"
            >
                <i
                    data-lucide="search"
                    class="w-4 h-4 text-text-muted mr-2 group-hover:text-gold-400 transition-colors"
                ></i>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari produk, izin, coach…"
                    class="bg-transparent border-0 text-sm text-text-main w-full
                           focus:outline-none placeholder:text-text-muted/60"
                    @keydown.enter.prevent="console.log('Search member:', searchQuery)"
                >
            </div>

            {{-- Search mobile icon --}}
            <button
                class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-full
                       bg-brand-card border border-brand-borderSoft shadow-light
                       hover:bg-brand-gunmetal/40 transition-colors duration-200"
                aria-label="Search"
            >
                <i data-lucide="search" class="w-4 h-4 text-text-main"></i>
            </button>

            {{-- Notifikasi (opsional, default 0) --}}
            <div class="relative">
                <button
                    @click.stop="showNotifications = !showNotifications; showProfile = false"
                    class="relative inline-flex items-center justify-center w-9 h-9 rounded-full
                           bg-brand-card border border-brand-borderSoft shadow-light
                           hover:bg-brand-gunmetal/40 transition-all duration-200"
                    :class="{ 'ring-2 ring-gold-500/30': showNotifications }"
                    aria-label="Notifications"
                    :aria-expanded="showNotifications"
                >
                    <i data-lucide="bell" class="w-4 h-4 text-text-main"></i>
                    @if($notificationCount > 0)
                        <span
                            class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full
                                   bg-accent-500 text-[10px] text-white font-semibold
                                   min-w-[18px] h-[18px] px-1 animate-pulse"
                        >
                            {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                        </span>
                    @endif
                </button>

                {{-- Dropdown notif --}}
                <div
                    x-show="showNotifications"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute right-0 mt-2 w-80 bg-brand-card border border-brand-borderSoft
                           rounded-2xl shadow-2xl z-50 overflow-hidden"
                >
                    <div class="px-4 py-3 border-b border-brand-borderSoft flex items-center justify-between bg-brand-shell/80">
                        <h3 class="text-sm font-semibold text-text-main">Notifikasi</h3>
                        @if($notificationCount > 0)
                            <span class="text-xs text-accent-400 font-medium">{{ $notificationCount }} baru</span>
                        @endif
                    </div>

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                        @if($notificationCount > 0)
                            <div class="px-4 py-3 text-sm text-text-main">
                                Ada {{ $notificationCount }} update terbaru untuk akun Anda.
                            </div>
                        @else
                            <div class="px-4 py-8 text-center">
                                <i data-lucide="bell-off" class="w-8 h-8 text-text-muted mx-auto mb-2"></i>
                                <p class="text-sm text-text-muted">Tidak ada notifikasi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PROFIL MEMBER --}}
            <div class="relative">
                <button
                    @click.stop="showProfile = !showProfile; showNotifications = false"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-full bg-brand-card border border-brand-borderSoft
                           shadow-light hover:bg-brand-gunmetal/40 transition-all duration-200 group"
                    :class="{ 'ring-2 ring-gold-500/30': showProfile }"
                    aria-label="User menu"
                    :aria-expanded="showProfile"
                >
                    <div
    class="w-8 h-8 rounded-full overflow-hidden border border-brand-borderSoft bg-brand-surface-50
           flex items-center justify-center shadow-md"
>
    @if($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="Foto Profil" class="w-full h-full object-cover">
    @else
        <div
            class="w-full h-full bg-gradient-to-br from-gold-400 to-gold-600
                   flex items-center justify-center text-xs font-bold text-brand-black"
        >
            {{ $initials }}
        </div>
    @endif
</div>

                    <div class="leading-tight hidden sm:block text-left">
                        <div
                            class="text-xs font-semibold text-text-main truncate max-w-[120px]
                                   group-hover:text-gold-400 transition-colors"
                        >
                            {{ auth()->user()->name ?? 'Member' }}
                        </div>
                        <div class="text-[10px] text-text-muted">
                            Member
                        </div>
                    </div>
                    <i
                        data-lucide="chevron-down"
                        class="w-4 h-4 text-text-muted transition-transform duration-200"
                        :class="{ 'rotate-180': showProfile }"
                    ></i>
                </button>

                {{-- Dropdown profil --}}
                <div
                    x-show="showProfile"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute right-0 mt-2 w-56 bg-brand-card border border-brand-borderSoft
                           rounded-2xl shadow-2xl overflow-hidden z-50"
                >
                    <div class="px-4 py-3 border-b border-brand-borderSoft bg-brand-shell/70">
                        <p class="text-sm font-semibold text-text-main truncate">
                            {{ auth()->user()->name ?? 'Member' }}
                        </p>
                        <p class="text-xs text-text-muted truncate">
                            {{ auth()->user()->email ?? 'member@betagym.com' }}
                        </p>
                    </div>

                    <div class="py-2">
                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors"
                        >
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Profil & Pengaturan</span>
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors"
                        >
                            <i data-lucide="help-circle" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Bantuan</span>
                        </a>
                    </div>

                    <div class="border-t border-brand-borderSoft py-2 bg-brand-shell/60">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-accent-500/10 transition-colors text-left"
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
