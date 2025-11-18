{{-- resources/views/components/layouts/member.blade.php --}}
@props([
    'title' => 'BETA GYM Member',
    'pageTitle' => null,
    'pageSubtitle' => null,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-bg text-text-main min-h-screen font-sans antialiased">

    <div class="min-h-screen flex flex-col">

        {{-- NAVBAR MEMBER (component) --}}
        <x-member.navbar
            :page-title="$pageTitle"
            :page-subtitle="$pageSubtitle"
        />

        {{-- KONTEN UTAMA --}}
        <main class="flex-1 container py-6 space-y-5">
            {{ $slot }}
        </main>

        {{-- FOOTER KECIL (optional, bisa juga component) --}}
        <x-member.footer />

    </div>
    <x-ui.toast />
</body>
</html>
