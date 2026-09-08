{{-- resources/views/layouts/components/sidebar.blade.php (FINAL DENGAN FONT BOLD) --}}

<div id="admin-sidebar"
    class="sidebar bg-dark-card border-r-2 border-gold-900 overflow-y-auto pb-4 pt-4
            lg:translate-x-0">

    {{-- 1. HEADER & LOGO BETA GYM --}}
    <div class="sidebar-header flex items-center justify-center mb-8 px-5 pt-4 pb-6 border-b-2 border-gold-900">
        <div class="w-1/3 flex justify-center items-center mr-3">
            <div class="relative">
                <img src="{{ asset('images/logo.webp') }}" alt="BETA GYM Logo" class="h-14 w-auto"
                    style="filter: drop-shadow(0 0 10px rgba(200, 168, 112, 0.6));">
                <div class="absolute inset-0 bg-gold opacity-20 blur-xl rounded-full"></div>
            </div>
        </div>
        <div class="w-2/3 text-left">
            <h1 class="text-2xl tracking-wider font-heading leading-tight mb-1">
                <span class="text-gold">BETA</span>
                <span class="text-accent block text-xl -mt-1">GYM</span>
            </h1>
            <div class="flex items-center gap-1">
                <div class="h-0.5 w-8 bg-gold"></div>
                <small class="text-text-secondary text-xs uppercase tracking-wider">Admin Panel</small>
            </div>
        </div>
    </div>

    {{-- 2. MENU NAVIGATION --}}
    <nav class="sidebar-menu px-3">
        {{-- Dashboard Menu --}}
        <div class="mb-1">
            <a href="{{ route('admin.dashboard') }}"
                class="menu-item flex items-center text-text-secondary hover:text-gold px-4 py-2.5 rounded-gym transition-all duration-300 border-l-4 border-transparent group
                       {{ Request::is('admin/dashboard') ? 'menu-active' : '' }}">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-md bg-dark-surface mr-3 group-hover:bg-gold-900 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </span>
                <span class="font-semibold text-xs uppercase tracking-wider">Dashboard</span>
            </a>
        </div>

        {{-- Divider --}}
        <div class="my-3 mx-4">
            <div class="h-px bg-gradient-to-r from-transparent via-gold-800 to-transparent"></div>
        </div>

        {{-- MANAJEMEN MEMBER (Dropdown) --}}
        <div class="mb-1">
            <a href="#"
                class="menu-toggle menu-item flex items-center justify-between text-text-secondary hover:text-gold px-4 py-2.5 rounded-gym transition-all duration-300 border-l-4 border-transparent group {{ Request::is('admin/members*') ? 'menu-active' : '' }}">
                <span class="flex items-center">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-dark-surface mr-3 group-hover:bg-gold-900 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                    </span>
                    <span class="font-semibold text-xs uppercase tracking-wider">Manajemen Member</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="toggle-arrow h-4 w-4 text-gold-700" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            {{-- Submenu Member --}}
            <ul class="submenu-list mt-1 ml-4 space-y-0.5">
                <li>
                    <a href="#"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{ Request::is('admin/members') && !Request::is('admin/members/create') ? 'submenu-active' : '' }}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Daftar Member
                    </a>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{ Request::is('admin/members/create') ? 'submenu-active' : '' }}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Tambah Member Baru
                    </a>
                </li>
            </ul>
        </div>

        {{-- MANAJEMEN PRODUK (Dropdown) --}}
        <div class="mb-1">
            <a href="#"
                class="menu-toggle menu-item flex items-center justify-between text-text-secondary hover:text-gold px-4 py-2.5 rounded-gym transition-all duration-300 border-l-4 border-transparent group {{ Request::is('admin/produk*') ? 'menu-active' : '' }}">
                <span class="flex items-center">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-dark-surface mr-3 group-hover:bg-gold-900 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span class="font-semibold text-xs uppercase tracking-wider">Manajemen Produk</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="toggle-arrow h-4 w-4 text-gold-700" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            {{-- Submenu Produk --}}
            <ul class="submenu-list mt-1 ml-4 space-y-0.5">
                <li>
                    <a href="#"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{ Request::is('admin/produk') && !Request::is('admin/produk/create') ? 'submenu-active' : '' }}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Daftar Produk
                    </a>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{ Request::is('admin/produk/create') ? 'submenu-active' : '' }}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Tambah Produk Baru
                    </a>
                </li>
            </ul>
        </div>

        {{-- Divider --}}
        <div class="my-3 mx-4">
            <div class="h-px bg-gradient-to-r from-transparent via-gold-800 to-transparent"></div>
        </div>

        {{-- IZIN & ABSENSI (Dropdown) --}}
        <div class="mb-1">
            <a href="#"
                class="menu-toggle menu-item flex items-center justify-between text-text-secondary hover:text-gold px-4 py-2.5 rounded-gym transition-all duration-300 border-l-4 border-transparent group {{ Request::is('admin/izin-latihan*') ? 'menu-active' : '' }}">
                <span class="flex items-center">
                    <span
                        class="flex items-center justify-center w-8 h-8 rounded-md bg-dark-surface mr-3 group-hover:bg-gold-900 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span class="font-semibold text-xs uppercase tracking-wider">Izin & Absensi</span>
                    @if ($izinPending ?? 0 > 0)
                        <span
                            class="ml-2 bg-accent text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $izinPending }}</span>
                    @endif
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="toggle-arrow h-4 w-4 text-gold-700" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>

            {{-- Submenu Izin & Absensi --}}
            <ul class="submenu-list mt-1 ml-4 space-y-0.5 {{ Request::is('admin/izin-latihan*') ? 'active' : '' }}">
                <li>
                    <a href="{{ route('admin.izin_latihan.index') }}"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{ Request::is('admin/izin-latihan') ? 'submenu-active' : '' }}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Daftar Izin
                    </a>
                </li>
                <li>
                    <a href="#"
                        class="flex items-center text-text-secondary hover:text-gold px-4 py-1.5 rounded-md transition-all duration-200 text-xs {{-- Request::is('admin/absensi') ? 'submenu-active' : '' --}}">
                        <span class="w-6 flex justify-center mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gold-700"></span>
                        </span>
                        Data Absensi
                    </a>
                </li>
            </ul>
        </div>

        {{-- LAPORAN & STATISTIK --}}
        <div class="mb-1">
            <a href="#"
                class="menu-item flex items-center text-text-secondary hover:text-gold px-4 py-2.5 rounded-gym transition-all duration-300 border-l-4 border-transparent group {{ Request::is('admin/laporan*') ? 'menu-active' : '' }}">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-md bg-dark-surface mr-3 group-hover:bg-gold-900 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                </span>
                <span class="font-semibold text-xs uppercase tracking-wider">Laporan & Statistik</span>
            </a>
        </div>

    </nav>
</div>
