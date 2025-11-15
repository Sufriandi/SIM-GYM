<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Member Area' }} - SIM GYM</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 dark:bg-gray-900">

    {{-- NAVBAR --}}
    @include('layouts.components.navbar')

    {{-- CONTENT --}}
    <main class="max-w-7xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    @include('layouts.components.footer')

</body>
</html>
