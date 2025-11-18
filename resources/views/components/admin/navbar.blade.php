{{-- resources/views/components/admin/navbar.blade.php --}}
@props([
    'pageTitle' => null,
    'pageSubtitle' => null,   // kita abaikan di navbar supaya tidak dobel
    'notificationCount' => 0,
])

<header
    class="fixed top-0 left-0 md:left-64 right-0 h-16 flex items-center z-30
           bg-brand-shell/95 backdrop-blur-sm border-b border-brand-borderSoft shadow-header"
    x-data="{
        showNotifications: false,
        showProfile: false,
        searchQuery: ''
    }"
    @click.away="showNotifications = false; showProfile = false"
    role="banner"
>
    <div class="w-full px-4 lg:px-8 flex items-center justify-between gap-4">

        {{-- KIRI: tombol mobile + breadcrumb --}}
        <div class="flex items-center gap-3 min-w-0 flex-1">

            {{-- Toggle sidebar mobile --}}
            <button
                @click="$dispatch('toggle-mobile-menu')"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl
                       bg-brand-card border border-brand-borderSoft shadow-light
                       hover:bg-brand-gunmetal/40 hover:text-brand-white transition-colors duration-200"
                aria-label="Toggle mobile menu"
            >
                <i data-lucide="menu" class="w-5 h-5 text-text-main"></i>
            </button>

            {{-- Breadcrumb --}}
<div class="min-w-0">
    <nav class="flex items-center text-sm font-bold text-text-muted" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}"
            class="inline-flex items-center gap-1 hover:text-gold-400 transition-colors">
            {{-- ICON UKURAN BESAR DAN KETEBALAN GARIS DITINGKATKAN (stroke-width="3") --}}
            <i data-lucide="home" class="w-4 h-4" stroke-width="3"></i>
            {{-- TEXT DIBOLD dan UKURAN DIBESARKAN --}}
            <span class="hidden sm:inline">Dashboard</span>
        </a>

        @if($pageTitle)
            {{-- ICON PEMISAH UKURAN BESAR DAN KETEBALAN GARIS DITINGKATKAN (stroke-width="3") --}}
            <i data-lucide="chevron-right" class="w-4 h-4 mx-1.5 text-text-muted" stroke-width="3"></i>
            <span class="text-text-main font-bold truncate max-w-[200px] md:max-w-[260px]">
                {{-- TEXT DIBOLD dan UKURAN DIBESARKAN --}}
                {{ $pageTitle }}
            </span>
        @endif
    </nav>
</div>
        </div>

        {{-- KANAN: search + notif + profile --}}
        <div class="flex items-center gap-2 lg:gap-3">

            {{-- Search desktop --}}
            <div
                class="hidden lg:flex items-center bg-brand-card rounded-full px-4 py-2
                       border border-brand-borderSoft shadow-light
                       min-w-[240px] xl:min-w-[280px]
                       hover:border-gold-500/40 hover:shadow-gold-glow/60
                       transition-all duration-200 group"
            >
                <i data-lucide="search"
                   class="w-4 h-4 text-text-muted mr-2 group-hover:text-gold-400 transition-colors"></i>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari member, produk…"
                    class="bg-transparent border-0 text-sm text-text-main w-full
                           focus:outline-none placeholder:text-text-muted/60"
                    @keydown.enter.prevent="console.log('Search:', searchQuery)"
                >
                <kbd
                    class="hidden xl:inline-block px-1.5 py-0.5 text-[10px] text-text-muted
                           bg-brand-gunmetal/20 rounded border border-brand-borderSoft"
                >
                    ⌘K
                </kbd>
            </div>

            {{-- Search mobile --}}
            <button
                class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-full
                       bg-brand-card border border-brand-borderSoft shadow-light
                       hover:bg-brand-gunmetal/40 transition-colors duration-200"
                aria-label="Search"
            >
                <i data-lucide="search" class="w-4 h-4 text-text-main"></i>
            </button>

            {{-- Notifikasi --}}
            <div class="relative">
                <button
                    @click.stop="showNotifications = !showNotifications; showProfile = false"
                    class="relative inline-flex items-center justify-center w-9 h-9 rounded-full
                           bg-brand-card border border-brand-borderSoft shadow-light
                           hover:bg-brand-gunmetal/40 transition-all duration-200"
                    :class="{ 'ring-2 ring-gold-500/30': showNotifications }"
                    aria-label="Notifications"
                    aria-expanded="showNotifications"
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

                {{-- Dropdown notifikasi --}}
                <div
                    x-show="showNotifications"
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
                            <a
                                href="#"
                                class="block px-4 py-3 hover:bg-brand-gunmetal/15 transition-colors
                                       border-b border-brand-borderSoft/60"
                            >
                                <div class="flex gap-3">
                                    <div class="mt-1.5 flex-shrink-0">
                                        <span class="inline-flex h-2 w-2 rounded-full bg-accent-500"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-text-main font-medium mb-0.5">
                                            Izin latihan pending
                                        </p>
                                        <p class="text-xs text-text-muted truncate">
                                            Ada {{ $notificationCount }} izin yang menunggu persetujuan.
                                        </p>
                                        <span class="text-[10px] text-text-muted mt-1 inline-block">
                                            Baru saja
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @else
                            <div class="px-4 py-8 text-center">
                                <i data-lucide="bell-off" class="w-8 h-8 text-text-muted mx-auto mb-2"></i>
                                <p class="text-sm text-text-muted">Tidak ada notifikasi</p>
                            </div>
                        @endif
                    </div>

                    @if($notificationCount > 0)
                        <div class="px-4 py-2.5 border-t border-brand-borderSoft bg-brand-shell/60">
                            <a href="#" class="text-xs text-gold-400 hover:text-gold-300 font-medium transition-colors">
                                Lihat semua notifikasi →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Profil --}}
            <div class="relative">
                <button
                    @click.stop="showProfile = !showProfile; showNotifications = false"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-full bg-brand-card border border-brand-borderSoft
                           shadow-light hover:bg-brand-gunmetal/40 transition-all duration-200 group"
                    :class="{ 'ring-2 ring-gold-500/30': showProfile }"
                    aria-label="User menu"
                    aria-expanded="showProfile"
                >
                    <div
                        class="w-8 h-8 rounded-full bg-gradient-to-br from-gold-400 to-gold-600
                               flex items-center justify-center text-xs font-bold text-brand-black shadow-md"
                    >
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="leading-tight hidden sm:block text-left">
                        <div
                            class="text-xs font-semibold text-text-main truncate max-w-[100px]
                                   group-hover:text-gold-400 transition-colors"
                        >
                            {{ auth()->user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[10px] text-text-muted">
                            Administrator
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
                            {{ auth()->user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-text-muted truncate">
                            {{ auth()->user()->email ?? 'admin@betagym.com' }}
                        </p>
                    </div>

                    <div class="py-2">
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors"
                        >
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Profil Saya</span>
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors"
                        >
                            <i data-lucide="settings" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Pengaturan</span>
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

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endpush
@endonce
