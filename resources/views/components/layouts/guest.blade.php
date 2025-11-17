{{-- resources/views/components/layouts/guest.blade.php --}}
@props([
    'title' => 'BETA GYM',
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

        {{-- NAVBAR GUEST (component) --}}
        <x-guest.navbar />

        {{-- KONTEN UTAMA --}}
        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- FOOTER GUEST (component) --}}
        <x-guest.footer />

    </div>
    <x-ui.toast />
</body>
</html>
