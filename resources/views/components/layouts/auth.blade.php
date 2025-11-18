{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'BETA GYM | Auth' }}</title>

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-bg text-text-main antialiased custom-scrollbar">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        {{-- Slot isi halaman (login / register) --}}
        {{ $slot }}
    </div>

    @stack('scripts')
</body>
</html>
