{{-- resources/views/components/layouts/member.blade.php --}}
@props([
    'title' => 'BETA GYM – Area Member',
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<!DOCTYPE html>
<html lang="id" class="h-full overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Penambahan Tag Meta Deskripsi untuk optimasi SEO -->
    <meta name="description" content="Dashboard member SIM GYM untuk pantau membership, riwayat kehadiran, pengajuan izin latihan, serta beragam informasi program kebugaran.">

    <!-- Preload LCP Image -->
    <link rel="preload" as="image" href="{{ asset('images/dashboard-hero.webp') }}" fetchpriority="high">

    <link rel="icon" type="image/webp" href="{{ asset('images/Logo.webp') }}"fetchpriority="high" alt="logo">

    <script>
    (function () {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved || (prefersDark ? 'dark' : 'light');

        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.dataset.theme = theme;
    })();
    </script>

    <script src="https://unpkg.com/lucide@latest" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-brand-bg text-text-main antialiased overflow-x-hidden">
    <div class="min-h-screen flex bg-brand-bg">
        <x-member.sidebar />

        <div class="flex-1 flex flex-col md:pl-64 min-w-0">
            <x-member.navbar :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

            {{-- KONTEN --}}
            <main class="flex-1 mt-20 px-4 lg:px-8 pb-10 min-w-0 overflow-visible">
                {{ $slot }}
            </main>

            <x-member.footer />
        </div>
    </div>

    <x-ui.toast />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>
