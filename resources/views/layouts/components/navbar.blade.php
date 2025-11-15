{{-- resources/views/layouts/components/navbar.blade.php --}}

<nav class="fixed top-0 left-0 right-0 h-16 bg-dark-card border-b-2 border-gold-900 z-50 flex items-center justify-between pr-4 lg:pr-6 transition-all duration-300"
     style="padding-left: calc(var(--sidebar-width) + 1.5rem);">

    {{-- KONTEN KIRI (Breadcrumb) --}}
    <div class="flex items-center">
        {{-- Breadcrumb --}}
        <div class="flex items-center space-x-2 text-sm">
            <a href="/admin/dashboard" class="text-text-secondary hover:text-gold transition duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
            </a>
            <span class="text-gold-700">/</span>
            <span class="text-gold font-heading uppercase tracking-wide" style="font-weight: 600;">{{ $pageTitle ?? 'Dashboard' }}</span>
        </div>
    </div>

    {{-- KONTEN KANAN (Search, Notifikasi & Profil) --}}
    <div class="flex items-center space-x-4">

        {{-- Quick Search Bar --}}
        <div class="relative hidden md:block">
            <input type="text"
                   placeholder="Quick search..."
                   class="w-64 bg-dark-surface border border-gold-900 rounded-gym px-4 py-2 text-sm text-text-primary placeholder-text-secondary focus:outline-none focus:border-gold transition duration-200">
            <button class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gold-700 hover:text-gold transition duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </div>

        {{-- Notifikasi Button & Dropdown Container --}}
<div class="relative">

    {{-- 1. Tombol Pemicu Dropdown --}}
    {{-- 🚨 PERBAIKAN: Gunakan button kembali untuk memicu JS/Dropdown --}}
    <button id="notification-toggle"
        class="relative flex items-center justify-center w-10 h-10 rounded-gym bg-dark-surface border border-gold-900 text-text-secondary hover:text-gold hover:border-gold transition duration-200 focus:outline-none group"
        aria-expanded="false"
        aria-controls="notification-panel">

        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.872 5.556 6 8.356 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- Badge Notifikasi Dinamis --}}
        @if ($izinPending > 0)
        <span class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-accent rounded-full border-2 border-dark-card animate-pulse">
            {{ $izinPending }}
        </span>
        @endif

    </button>

    {{-- 2. Placeholder Panel Dropdown Notifikasi --}}
    <div id="notification-panel"
         class="absolute right-0 mt-3 w-80 bg-dark-card rounded-premium shadow-lg z-50 border border-gold-800 p-4 transform scale-0 opacity-0 transition duration-200 ease-out origin-top-right"
         role="menu" aria-orientation="vertical" aria-labelledby="notification-toggle">

        <h5 class="text-gold font-heading text-lg border-b border-dark-surface pb-2 mb-2">Notifikasi ({{ $izinPending }})</h5>

        {{-- Item Notifikasi Izin Latihan (Contoh) --}}
        @if ($izinPending > 0)
            <a href="{{ route('admin.izin_latihan.index') }}" class="block text-sm text-text-primary hover:bg-dark-surface p-2 rounded-gym">
                <span class="text-accent">{{ $izinPending }} Permintaan Izin Baru</span> menanti persetujuan.
            </a>
        @else
            <p class="text-sm text-text-secondary italic p-2">Tidak ada notifikasi baru.</p>
        @endif

        {{-- Di sini Anda bisa menambahkan notifikasi untuk Produk, dll. --}}

        <div class="mt-2 border-t border-dark-surface pt-2">
            <a href="#" class="block text-xs text-gold hover:underline text-center">Lihat Semua</a>
        </div>

    </div>
</div>

        {{-- Divider --}}
        <div class="h-8 w-px bg-gold-900"></div>

        {{-- User Profile Dropdown --}}
        @auth
            <div class="flex items-center">
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">
                        <button class="flex items-center space-x-3 px-3 py-2 rounded-gym bg-dark-surface border border-gold-900 hover:border-gold transition duration-200 focus:outline-none group">

                            {{-- Avatar --}}
                            <div class="relative">
                                <img class="h-9 w-9 rounded-full object-cover border-2 border-gold"
                                    src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=c8a870&color=16213e&font-size=0.4&bold=true"
                                    alt="{{ Auth::user()->name }}">
                                {{-- Online indicator --}}
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-success border-2 border-dark-card rounded-full"></span>
                            </div>

                            {{-- User Info --}}
                            <div class="text-left hidden lg:block">
                                <div class="text-sm font-semibold text-text-primary group-hover:text-gold transition duration-200">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="text-xs text-text-secondary">
                                    Administrator
                                </div>
                            </div>

                            {{-- Dropdown Arrow --}}
                            <svg class="h-4 w-4 text-gold-700 group-hover:text-gold transition duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Custom styled dropdown --}}
                        <div class="py-1 bg-dark-card border-2 border-gold-900 rounded-gym">

                            {{-- User Info Header --}}
                            <div class="px-4 py-3 border-b border-gold-900">
                                <p class="text-sm text-text-primary font-semibold">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-text-secondary">{{ Auth::user()->email }}</p>
                            </div>

                            {{-- Menu Items --}}
                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-dark-surface hover:text-gold transition duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    Profile Saya
                                </a>

                                <a href="#" class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-dark-surface hover:text-gold transition duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                    </svg>
                                    Pengaturan
                                </a>
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-gold-900"></div>

                            {{-- Logout --}}
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-accent hover:bg-accent/10 transition duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>

                        </div>
                    </x-slot>
                </x-dropdown>
            </div>
        @endauth

    </div>
</nav>

{{-- Responsive: Mobile Search Toggle (Optional) --}}
<style>
    /* Smooth dropdown animation */
    [x-cloak] { display: none !important; }

    /* Custom scrollbar untuk dropdown jika terlalu panjang */
    .dropdown-content {
        max-height: 400px;
        overflow-y: auto;
    }

    .dropdown-content::-webkit-scrollbar {
        width: 4px;
    }

    .dropdown-content::-webkit-scrollbar-track {
        background: #16213e;
    }

    .dropdown-content::-webkit-scrollbar-thumb {
        background: #c8a870;
        border-radius: 10px;
    }
</style>
// Skrip Dropdown Notifikasi
<script>
    // Logika JS sederhana untuk toggle dropdown (Anda bisa ganti ini dengan Alpine.js atau Tailwind/Flowbite JS)
    document.getElementById('notification-toggle').addEventListener('click', function() {
        const panel = document.getElementById('notification-panel');
        if (panel.classList.contains('scale-0')) {
            panel.classList.remove('scale-0', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        } else {
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-0', 'opacity-0');
        }
    });

    // Menutup dropdown jika klik di luar
    document.addEventListener('click', function(event) {
        const toggle = document.getElementById('notification-toggle');
        const panel = document.getElementById('notification-panel');
        if (!panel.contains(event.target) && !toggle.contains(event.target)) {
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-0', 'opacity-0');
        }
    });
</script>
