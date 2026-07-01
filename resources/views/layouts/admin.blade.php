<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | BETA GYM</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&family=Bebas+Neue&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* CSS Variables */
        :root {
            --sidebar-width: 250px;
            --navbar-height: 64px;
        }

        /* Font Families */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
        }
        body {
            font-family: 'Roboto', sans-serif;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 55; /* Diperbaiki dari issue sebelumnya */
            transition: transform 0.3s ease-in-out;
        }

        /* Main Content Wrapper */
        .admin-main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            width: calc(100% - var(--sidebar-width));
            min-height: calc(100vh - var(--navbar-height));
            transition: all 0.3s ease-in-out;
        }

        /* Sidebar Animations */
        .submenu-list {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .submenu-list.active {
            max-height: 500px;
        }

        .toggle-arrow {
            transition: transform 0.3s ease;
        }
        .toggle-arrow.rotated {
            transform: rotate(90deg);
        }

        /* Menu States */
        .menu-active {
            background: linear-gradient(90deg, rgba(200, 168, 112, 0.15) 0%, transparent 100%);
            border-left-color: #c8a870 !important;
            color: #c8a870 !important;
            /* HAPUS: font-weight: 600; */
        }

        .menu-item:hover {
            background: linear-gradient(90deg, rgba(200, 168, 112, 0.08) 0%, transparent 100%);
        }

        .submenu-active {
            background-color: rgba(200, 168, 112, 0.1);
            color: #c8a870 !important;
            border-left: 4px solid #c8a870;
            /* HAPUS: padding-left: 1rem !important; (biarkan diatur oleh Tailwind) */
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: #16213e;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #c8a870;
            border-radius: 10px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #9a7a4a;
        }

        /* Mobile Responsive */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 60;
            }
            .sidebar-mobile-active {
                transform: translateX(0);
            }
            .admin-main-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-dark-background text-text-primary font-body">

    {{-- Navbar --}}
    @include('layouts.components.navbar')

    {{-- Mobile Overlay --}}
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black/60 z-50 hidden lg:hidden">
    </div>

    {{-- Sidebar --}}
    @include('layouts.components.sidebar')

    {{-- Main Content --}}
    <main class="admin-main-wrapper p-6 lg:p-8">
        @yield('content')
    </main>

    {{-- JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Toggle Sidebar Mobile
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('sidebar-overlay');

            function toggleSidebar() {
                if (sidebar && overlay) {
                    sidebar.classList.toggle('sidebar-mobile-active');
                    overlay.classList.toggle('hidden');
                }
            }

            if (toggleButton) {
                toggleButton.addEventListener('click', toggleSidebar);
            }
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            // Submenu Toggle Logic
            function closeOtherSubmenus(currentToggle) {
                document.querySelectorAll('.menu-toggle').forEach(otherToggle => {
                    if (otherToggle !== currentToggle) {
                        const otherSubmenu = otherToggle.nextElementSibling;
                        const otherArrow = otherToggle.querySelector('.toggle-arrow');
                        if (otherSubmenu && otherSubmenu.classList.contains('active')) {
                            otherSubmenu.classList.remove('active');
                            if (otherArrow) otherArrow.classList.remove('rotated');
                        }
                    }
                });
            }

            function checkActiveSubmenu() {
                const activeSubmenuLink = document.querySelector('.submenu-active');
                if (activeSubmenuLink) {
                    const submenu = activeSubmenuLink.closest('.submenu-list');
                    if (submenu) {
                        const toggle = submenu.previousElementSibling;
                        const arrow = toggle ? toggle.querySelector('.toggle-arrow') : null;

                        submenu.classList.add('active');
                        if (arrow) arrow.classList.add('rotated');
                        if (toggle && !toggle.classList.contains('menu-active')) {
                            toggle.classList.add('menu-active');
                        }
                    }
                }
            }

            document.querySelectorAll('.menu-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeOtherSubmenus(this);

                    const submenu = this.nextElementSibling;
                    const arrow = this.querySelector('.toggle-arrow');

                    if (submenu) submenu.classList.toggle('active');
                    if (arrow) arrow.classList.toggle('rotated');
                });
            });

            checkActiveSubmenu();
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Handle Success messages (Toast)
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
                position: 'top-end',
                iconColor: '#28a745',
                background: '#1a1f32',
                color: '#ffffff',
                customClass: {
                    container: 'swal2-custom-offset',
                }
            });
        @endif

        // 🚨 PERBAIKAN: Handle DANGER messages (Toast untuk penolakan)
        @if (session('danger'))
            Swal.fire({
                icon: 'error', // Gunakan icon error/silang untuk penolakan
                title: 'Ditolak!',
                text: '{{ session('danger') }}',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
                position: 'top-end',
                iconColor: '#dc3545', // Merah gelap
                background: '#1a1f32',
                color: '#ffffff',
                customClass: {
                    container: 'swal2-custom-offset',
                }
            });
        @endif

        // Handle Error messages (Modal)
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                position: 'top',
            });
        @endif

        // Handle Validation Errors
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: 'Terdapat beberapa kesalahan input. Silakan periksa formulir Anda.',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                position: 'top',
            });
        @endif
    });
</script>
{{-- 1. Sertakan Library SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 2. Skrip Penangan Notifikasi --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Handle Success messages (Toast)
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
                position: 'top-end',
                iconColor: '#28a745',
                background: '#1a1f32',
                color: '#ffffff',
                customClass: {
                    container: 'swal2-custom-offset',
                }
            });
        @endif

        // 🚨 PERBAIKAN: Handle DANGER messages (Toast untuk penolakan)
        @if (session('danger'))
            Swal.fire({
                icon: 'error', // Gunakan icon error/silang untuk penolakan
                title: 'Ditolak!',
                text: '{{ session('danger') }}',
                showConfirmButton: false,
                timer: 4000,
                toast: true,
                position: 'top-end',
                iconColor: '#dc3545', // Merah gelap
                background: '#1a1f32',
                color: '#ffffff',
                customClass: {
                    container: 'swal2-custom-offset',
                }
            });
        @endif

        // Handle Error messages (Modal)
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                position: 'top',
            });
        @endif

        // Handle Validation Errors
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: 'Terdapat beberapa kesalahan input. Silakan periksa formulir Anda.',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                position: 'top',
            });
        @endif
    });
</script>

    @stack('scripts')
</body>
</html>
