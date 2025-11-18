{{-- resources/views/components/layouts/admin.blade.php --}}
@props([
    'title' => 'BETA GYM – Admin',
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-brand-bg text-text-main antialiased">

<div class="h-screen flex bg-brand-bg overflow-hidden">
    {{-- SIDEBAR (FIXED) --}}
    <x-admin.sidebar />

    {{-- WRAPPER KANAN (NAVBAR + CONTENT) --}}
    <div class="flex-1 flex flex-col md:pl-64">
        {{-- NAVBAR (FIXED) --}}
        <x-admin.navbar :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        {{-- CONTENT (SCROLLABLE) --}}
        <main class="flex-1 mt-20 px-4 lg:px-8 pb-8 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- TOAST GLOBAL --}}
<x-ui.toast />

{{-- Init Lucide (supaya semua data-lucide jadi icon) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

</body>
</html>
