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

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    <script>
    (function () {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Default: ikut sistem kalau belum pernah pilih
    const theme = saved || (prefersDark ? 'dark' : 'light');

    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.dataset.theme = theme;
    })();
    </script>
    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-bg text-text-main antialiased">
    <div class="min-h-screen flex bg-brand-bg">
        {{-- SIDEBAR MEMBER (FIXED) --}}
        <x-member.sidebar />

        {{-- WRAPPER KANAN: NAVBAR + CONTENT + FOOTER --}}
        <div class="flex-1 flex flex-col md:pl-64 min-w-0">
            {{-- NAVBAR MEMBER --}}
            <x-member.navbar :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

            {{-- KONTEN --}}
            <main
                class="flex-1 mt-20 px-4 lg:px-8 pb-10 overflow-y-auto overflow-x-hidden custom-scrollbar"
            >
                {{ $slot }}
            </main>

            {{-- FOOTER MEMBER --}}
            <x-member.footer />

        </div>
    </div>

    {{-- TOAST GLOBAL (kalau ada komponen UI kamu) --}}
    <x-ui.toast />

    {{-- Init Lucide --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>

    {{-- Alpine.js GLOBAL UNTUK MEMBER --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('scripts')
</body>
</html>
