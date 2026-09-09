{{-- resources/views/components/admin/navbar.blade.php --}}
@props([
    'pageTitle' => null,
    'pageSubtitle' => null,
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
    class="fixed top-0 left-0 md:left-64 right-0 h-16 flex items-center z-30
           bg-brand-shell/95 backdrop-blur-sm border-b border-brand-borderSoft shadow-header"
    x-data="{
        showNotifications: false,
        showProfile: false,
        searchQuery: '',

        // ===== THEME (tanpa DB, pakai localStorage) =====
        theme: 'light',

        initTheme() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.theme = saved ?? (prefersDark ? 'dark' : 'light');
            this.applyTheme();
        },

        applyTheme() {
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
            document.documentElement.dataset.theme = this.theme;
        },

        toggleTheme() {
            this.theme = (this.theme === 'dark') ? 'light' : 'dark';
            localStorage.setItem('theme', this.theme);
            this.applyTheme();
        },

        // ===== SOUND NOTIF =====
        soundUrl: '{{ asset('sounds/notif.mp3') }}',
        soundEnabled: true,
        soundUnlocked: false,
        lastSoundAt: 0,
        soundCooldownMs: 400,

        initSound() {
            const saved = localStorage.getItem('admin_notif_sound');
            this.soundEnabled = saved === null ? true : (saved === '1');

            const unlock = async () => {
                if (this.soundUnlocked) return;

                try {
                    // Unlock autoplay policy via gesture user (lebih stabil: pakai Audio baru)
                    const a = new Audio(this.soundUrl);
                    a.muted = true;
                    await a.play();
                    a.pause();
                    a.currentTime = 0;
                    a.muted = false;

                    this.soundUnlocked = true;

                    window.removeEventListener('click', unlock);
                    window.removeEventListener('keydown', unlock);
                    window.removeEventListener('touchstart', unlock);
                } catch (e) {
                    // kalau gagal, akan retry di gesture berikutnya
                }
            };

            window.addEventListener('click', unlock);
            window.addEventListener('keydown', unlock);
            window.addEventListener('touchstart', unlock);
        },

        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            localStorage.setItem('admin_notif_sound', this.soundEnabled ? '1' : '0');
        },

        playNotifSound() {
            if (!this.soundEnabled) return;
            if (!this.soundUnlocked) return;

            const now = Date.now();
            if (now - this.lastSoundAt < this.soundCooldownMs) return;
            this.lastSoundAt = now;

            try {
                // Pakai instance baru setiap play -> lebih konsisten untuk repeated play
                const a = new Audio(this.soundUrl);
                a.currentTime = 0;
                a.play().catch(() => {});
            } catch (e) {
                // silent
            }
        },

        // ===== NOTIF REALTIME (POLLING) =====
        notifPollUrl: '{{ route('admin.notifikasi.poll') }}',
        unreadCount: {{ (int) $notificationCount }},
        notifItems: [],
        lastMaxId: 0,

        pollMs: 30000,
        pollTimer: null,

        initNotifications() {
            // Polling background setiap 30s saat tab aktif
            this.pollTimer = setInterval(() => {
                if (!document.hidden) {
                    this.fetchNotif(false);
                }
            }, this.pollMs);

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && this.showNotifications) {
                    this.fetchNotif(true);
                }
            });
        },

        async fetchNotif(fullRefresh) {
            try {
                const since = fullRefresh ? 0 : this.lastMaxId;
                const url = new URL(this.notifPollUrl, window.location.origin);
                url.searchParams.set('since_id', String(since));

                const res = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;

                const data = await res.json();
                if (!data || data.success !== true) return;

                this.unreadCount = Number(data.unreadCount ?? 0);

                const incoming = Array.isArray(data.items) ? data.items : [];
                const maxId = Number(data.maxId ?? 0);

                if (fullRefresh) {
                    this.notifItems = incoming.slice(0, 10);
                    this.lastMaxId = maxId;
                    return;
                }

                // incremental: prepend new items (jika ada)
                if (incoming.length > 0) {
                    const existingIds = new Set(this.notifItems.map(n => n.id));
                    const newOnes = [];

                    for (const n of incoming) {
                        if (!existingIds.has(n.id)) newOnes.push(n);
                    }

                    if (newOnes.length > 0) {
                        this.notifItems = [...newOnes, ...this.notifItems].slice(0, 10);

                        // IMPORTANT: bunyikan walaupun tab sedang hidden
                        this.playNotifSound();
                    }

                    this.lastMaxId = Math.max(this.lastMaxId, maxId);
                }
            } catch (e) {
                // silent
            }
        },

        openNotifications() {
            this.showNotifications = !this.showNotifications;
            this.showProfile = false;

            if (this.showNotifications && this.notifItems.length === 0) {
                this.fetchNotif(true);
            }
        },

        formatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            if (isNaN(d.getTime())) return iso;
            return d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        },
    }"
    x-init="initTheme(); initSound(); initNotifications();"
    @click.away="showNotifications = false; showProfile = false"
    role="banner"
>
    <div
        class="w-full px-4 lg:px-8 flex items-center justify-between gap-3 md:gap-4
               flex-wrap"
    >
        {{-- KIRI: tombol mobile + breadcrumb --}}
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <button
                @click="window.dispatchEvent(new CustomEvent('toggle-mobile-menu'))"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-xl
                    bg-brand-card border border-brand-borderSoft shadow-light
                    hover:bg-brand-gunmetal/40 hover:text-brand-white transition-colors duration-200"
                aria-label="Toggle mobile menu"
            >
                <i data-lucide="menu" class="w-5 h-5 text-text-main"></i>
            </button>

            <div class="min-w-0">
                <nav class="flex items-center text-xs sm:text-sm font-bold text-text-muted"
                     aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-1 hover:text-gold-400 transition-colors min-w-0">
                        <i data-lucide="home" class="w-4 h-4" stroke-width="3"></i>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>

                    @if($pageTitle)
                        <i data-lucide="chevron-right"
                           class="w-4 h-4 mx-1.5 text-text-muted flex-shrink-0"
                           stroke-width="3"></i>

                        <span class="text-text-main font-bold truncate
                                   max-w-[140px] sm:max-w-[200px] md:max-w-[260px]">
                            {{ $pageTitle }}
                        </span>
                    @endif
                </nav>
            </div>
        </div>

        {{-- KANAN: search + notif + profile --}}
        <div class="flex items-center gap-2 lg:gap-3 flex-shrink-0">
            <div
                class="hidden lg:flex items-center bg-brand-card rounded-full px-4 py-2
                       border border-brand-borderSoft shadow-light
                       min-w-[220px] xl:min-w-[260px]
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
                    @click.stop="openNotifications()"
                    class="relative inline-flex items-center justify-center w-9 h-9 rounded-full
                           bg-brand-card border border-brand-borderSoft shadow-light
                           hover:bg-brand-gunmetal/40 transition-all duration-200"
                    :class="{ 'ring-2 ring-gold-500/30': showNotifications }"
                    aria-label="Notifications"
                    :aria-expanded="showNotifications"
                >
                    <i data-lucide="bell" class="w-4 h-4 text-text-main"></i>

                    <span
                        id="adminNotifBadge"
                        class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full
                            bg-accent-500 text-[10px] text-white font-semibold
                            min-w-[18px] h-[18px] px-1 animate-pulse"
                        x-show="unreadCount > 0"
                        x-text="unreadCount > 9 ? '9+' : String(unreadCount)"
                    ></span>
                </button>

                {{-- Dropdown notifikasi --}}
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
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-semibold text-text-main">Notifikasi</h3>

                            <button
                                type="button"
                                @click.stop="toggleSound()"
                                class="text-[11px] px-2 py-1 rounded-full border border-brand-borderSoft
                                       hover:bg-brand-gunmetal/15 transition-colors"
                                :class="soundEnabled ? 'text-gold-400' : 'text-text-muted'"
                                x-text="soundEnabled ? 'Suara: ON' : 'Suara: OFF'"
                                aria-label="Toggle sound"
                            ></button>
                        </div>

                        <span class="text-xs text-accent-400 font-medium"
                              x-show="unreadCount > 0"
                              x-text="unreadCount + ' baru'"></span>
                    </div>

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar">
                        <template x-if="notifItems.length > 0">
                            <div>
                                <template x-for="n in notifItems" :key="n.id">
                                    <a
                                        :href="n.go_url"
                                        class="block px-4 py-3 hover:bg-brand-gunmetal/15 transition-colors
                                               border-b border-brand-borderSoft/60"
                                    >
                                        <div class="flex gap-3">
                                            <div class="mt-1.5 flex-shrink-0">
                                                <span
                                                    class="inline-flex h-2 w-2 rounded-full"
                                                    :class="n.is_read ? 'bg-brand-borderSoft' : 'bg-accent-500'"
                                                ></span>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-text-main font-medium mb-0.5 truncate" x-text="n.title"></p>
                                                <p class="text-xs text-text-muted truncate" x-text="n.body"></p>
                                                <span class="text-[10px] text-text-muted mt-1 inline-block" x-text="formatTime(n.created_at)"></span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <template x-if="notifItems.length === 0">
                            <div class="px-4 py-8 text-center">
                                <i data-lucide="bell-off" class="w-8 h-8 text-text-muted mx-auto mb-2"></i>
                                <p class="text-sm text-text-muted">Tidak ada notifikasi</p>
                            </div>
                        </template>
                    </div>

                    <div class="px-4 py-2.5 border-t border-brand-borderSoft bg-brand-shell/60">
                        <a href="{{ route('admin.notifikasi.index') }}" class="text-xs text-gold-400 hover:text-gold-300 font-medium transition-colors">
                            Lihat semua notifikasi →
                        </a>
                    </div>
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
                    :aria-expanded="showProfile"
                >
                    <div class="w-8 h-8 rounded-full overflow-hidden border border-brand-borderSoft bg-brand-surface-50
                               flex items-center justify-center shadow-md">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Foto Profil" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gold-400 to-gold-600
                                       flex items-center justify-center text-xs font-bold text-brand-black">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>

                    <div class="leading-tight hidden sm:block text-left">
                        <div class="text-xs font-semibold text-text-main truncate max-w-[100px]
                                   group-hover:text-gold-400 transition-colors">
                            {{ auth()->user()->name ?? 'Admin' }}
                        </div>
                        <div class="text-[10px] text-text-muted">
                            {{ auth()->user()?->role ? Str::title(auth()->user()->role) : 'Administrator' }}
                        </div>
                    </div>
                    <i data-lucide="chevron-down"
                       class="w-4 h-4 text-text-muted transition-transform duration-200"
                       :class="{ 'rotate-180': showProfile }"></i>
                </button>

                <div
                    x-show="showProfile"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute right-0 mt-2 w-64 bg-brand-card border border-brand-borderSoft
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
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Profil Saya</span>
                        </a>

                        {{-- <button type="button"
                                @click="toggleTheme()"
                                class="w-full flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors text-left">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-shell border border-brand-borderSoft">
                                    <i data-lucide="moon" class="w-4 h-4 text-text-muted" x-show="theme === 'light'"></i>
                                    <i data-lucide="sun" class="w-4 h-4 text-text-muted" x-show="theme === 'dark'"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="text-sm text-text-main font-medium">Mode / Tema</div>
                                    <div class="text-[11px] text-text-muted" x-text="theme === 'dark' ? 'Gelap' : 'Terang'"></div>
                                </div>
                            </div>

                            <div class="relative w-11 h-6 rounded-full transition"
                                 :class="theme === 'dark' ? 'bg-primary-dark' : 'bg-neutral-300'">
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition"
                                      :class="theme === 'dark' ? 'translate-x-5' : ''"></span>
                            </div>
                        </button> --}}

                        <a href="#"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-brand-gunmetal/20 transition-colors">
                            <i data-lucide="help-circle" class="w-4 h-4 text-text-muted"></i>
                            <span class="text-sm text-text-main">Bantuan</span>
                        </a>
                    </div>

                    <div class="border-t border-brand-borderSoft py-2 bg-brand-shell/60">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-accent-500/10 transition-colors text-left">
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

