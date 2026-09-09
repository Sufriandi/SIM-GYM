<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Area | BETA GYM</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@400;700&family=Bebas+Neue&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Font Families */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
        }
        body {
            font-family: 'Roboto', sans-serif;
        }
        :root {
            --navbar-height: 90px;
        }

        .member-main-wrapper {
        padding-top: var(--navbar-height) !important;
    }
    </style>
</head>

{{-- Terapkan background utama tema Beta Gym --}}
<body class="bg-dark-background text-text-primary font-body">

    @include('layouts.components.navbar-member')

    <main class="member-main-wrapper container mx-auto p-4 md:p-8">
        {{-- Konten (Form/Riwayat/Dashboard) akan dimuat di sini --}}
        @yield('content')
    </main>

    {{-- 2. Skrip Penangan Notifikasi --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Handle Success messages (Toast - Position top-end)
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

        // Handle Error messages (Modal - Position Center)
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                // 🚨 PERBAIKAN: Hapus 'position: "top"' agar SweetAlert2 default ke 'center'
            });
        @endif

        // Handle Validation Errors (Modal - Position Center)
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: 'Terdapat beberapa kesalahan input. Silakan periksa formulir Anda.',
                confirmButtonText: 'OK',
                background: '#1a1f32',
                color: '#ffffff',
                // 🚨 PERBAIKAN: Hapus 'position: "top"' agar SweetAlert2 default ke 'center'
            });
        @endif
    });
</script>
    {{-- Script untuk Toggle Menu Mobile --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
