{{-- resources/views/components/layouts/member.blade.php --}}
@props([
    'title' => 'BETA GYM – Area Member',
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.webp') }}">

    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');

            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-brand-bg text-text-main antialiased overflow-x-clip">
    <div class="min-h-screen flex bg-brand-bg">
        <x-member.sidebar />

        <div class="flex-1 flex flex-col md:pl-64 min-w-0">
            <x-member.navbar :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

            {{-- KONTEN --}}
            <main id="main-content" class="flex-1 mt-20 px-4 lg:px-8 pb-10 min-w-0 overflow-visible">
                {{ $slot }}
            </main>

            <x-member.footer />
        </div>
    </div>

    <x-ui.toast />

    @stack('scripts')
</body>

</html>
