{{-- resources/views/layouts/components/navbar-member.blade.php --}}

<nav class="fixed top-0 left-0 right-0 h-16 bg-dark-card border-b-2 border-gold-900 z-50">
    <div class="container mx-auto px-4 lg:px-8 flex items-center justify-between h-full">

        {{-- KIRI: Logo & Navigasi --}}
        <div class="flex items-center space-x-6">
            {{-- Logo --}}
            <a href="{{ route('member.dashboard') }}" class="flex items-center space-x-2 flex-shrink-0">
                <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo" class="h-10 w-auto"
                    style="filter: drop-shadow(0 0 5px rgba(200, 168, 112, 0.5));">
                <span class="text-lg font-heading tracking-wider text-gold"><span class="text-accent">BETA</span>
                    GYM</span>
            </a>

            {{-- Navigasi Link (Desktop) --}}
            <div class="hidden lg:flex items-center space-x-6">
                <a href="{{ route('member.dashboard') }}"
                    class="text-sm font-semibold uppercase {{ Request::is('member/dashboard') ? 'text-gold' : 'text-text-secondary' }} hover:text-gold transition duration-200">Dashboard</a>

                {{-- ✅ PERBAIKAN: Hanya satu link untuk Izin Latihan --}}
                <a href="{{ route('member.izin_latihan.index') }}"
                    class="text-sm font-semibold uppercase {{ Request::is('member/izin-latihan*') ? 'text-gold' : 'text-text-secondary' }} hover:text-gold transition duration-200">Izin
                    Latihan</a>
            </div>
        </div>

        {{-- KANAN: Profil Dropdown & Hamburger (Mobile) --}}
        <div class="flex items-center space-x-4">
            @auth
                {{-- (Dropdown Profil Anda - tidak berubah) --}}
                <div class="flex items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center space-x-2 lg:space-x-3 px-2 py-2 rounded-gym bg-dark-surface border border-gold-900 hover:border-gold transition duration-200 focus:outline-none group">
                                <div class="relative">
                                    <img class="h-8 w-8 rounded-full object-cover border-2 border-gold"
                                        src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=c8a870&color=16213e&font-size=0.4&bold=true"
                                        alt="{{ Auth::user()->name }}">
                                    <span
                                        class="absolute bottom-0 right-0 w-3 h-3 bg-success border-2 border-dark-card rounded-full"></span>
                                </div>
                                <div class="text-left hidden md:block">
                                    <div
                                        class="text-sm font-semibold text-text-primary group-hover:text-gold transition duration-200">
                                        {{ Auth::user()->name }}
                                    </div>
                                </div>
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
                                    <a href="{{ route('profile.edit') }}"
                                        class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-dark-surface hover:text-gold transition duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Profile Saya
                                    </a>

                                    <a href="#"
                                        class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-dark-surface hover:text-gold transition duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                                clip-rule="evenodd" />
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
                                        <button type="submit"
                                            class="flex items-center w-full px-4 py-2 text-sm text-accent hover:bg-accent/10 transition duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"
                                                    clip-rule="evenodd" />
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

            {{-- Hamburger (Mobile) --}}
            <button id="mobile-menu-toggle" class="lg:hidden text-text-secondary hover:text-gold p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu"
        class="hidden lg:hidden bg-dark-card border-t-2 border-gold-900 shadow-lg absolute top-16 left-0 w-full">
        <div class="flex flex-col space-y-1 p-4">
            <a href="{{ route('member.dashboard') }}"
                class="px-4 py-2 rounded-md {{ Request::is('member/dashboard') ? 'bg-dark-surface text-gold' : 'text-text-secondary' }} hover:bg-dark-surface hover:text-gold transition duration-200 font-semibold uppercase">Dashboard</a>

            <a href="{{ route('member.izin_latihan.index') }}"
                class="px-4 py-2 rounded-md {{ Request::is('member/izin-latihan*') ? 'bg-dark-surface text-gold' : 'text-text-secondary' }} hover:bg-dark-surface hover:text-gold transition duration-200 font-semibold uppercase">Izin
                Latihan</a>
        </div>
    </div>
</nav>
