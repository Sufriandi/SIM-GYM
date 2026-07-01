{{-- resources/views/components/layouts/admin.blade.php --}}
@props([
    'title' => 'BETA GYM – Admin',
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<!DOCTYPE html>
<html lang="id" class="h-full overflow-x-hidden">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.webp') }}">

    {{-- Lucide Icons --}}
    <script defer src="https://unpkg.com/lucide@latest"></script>
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

            // Default: ikut sistem kalau belum pernah pilih
            const theme = saved || (prefersDark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-brand-bg text-text-main antialiased">
    <div class="min-h-screen flex bg-brand-bg">
        {{-- SIDEBAR (FIXED / DI DALAM KOMPONEN) --}}
        <x-admin.sidebar />

        {{-- WRAPPER KANAN (NAVBAR + CONTENT) --}}
        <div class="flex-1 flex flex-col md:pl-64 min-w-0">
            {{-- NAVBAR (FIXED / STICKY DI DALAM KOMPONEN) --}}
            <x-admin.navbar :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

            {{-- CONTENT (SCROLLABLE, TANPA HORIZONTAL OVERFLOW) overflow-y-auto overflow-x-hidden custom-scrollbar --}}
            <main class="flex-1 mt-20 px-4 lg:px-8 pb-8 ">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- TOAST GLOBAL --}}
    <x-ui.toast />

    {{-- Init Lucide --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>

    {{-- ALPINE.JS GLOBAL UNTUK ADMIN --}}
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sidebarNav', (defaultOpen) => ({
                openKehadiran: JSON.parse(localStorage.getItem('sidebar-openKehadiran') ?? (
                    defaultOpen ? 'true' : 'false')),

                toggleKehadiran() {
                    this.openKehadiran = !this.openKehadiran;
                    localStorage.setItem('sidebar-openKehadiran', JSON.stringify(this.openKehadiran));
                },
            }));
        });
    </script>

    @stack('scripts')
</body>

</html>
