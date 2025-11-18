{{-- resources/views/components/layouts/member.blade.php --}}
@props([
    'pageTitle' => 'Dashboard Member',
    'pageSubtitle' => null,
])

@php
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} • BETA GYM Member</title>

    {{-- CSS & JS utama (Tailwind + JS global) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-screen bg-brand-bg text-text-main">

<div class="min-h-screen flex flex-col">

    {{-- NAVBAR MEMBER (komponen terpisah) --}}
    <x-member.navbar
        :pageTitle="$pageTitle"
        :pageSubtitle="$pageSubtitle"
        :user="$user"
    />

    {{-- MAIN CONTENT – scroll pakai custom scrollbar emas --}}
    <main class="flex-1 mt-6 px-4 lg:px-8 pb-8 overflow-y-auto custom-scrollbar">
        <div class="max-w-6xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    {{-- FOOTER MEMBER (komponen terpisah) --}}
    <x-member.footer />
</div>

{{-- ============== JS GLOBAL ============== --}}

{{-- Alpine.js untuk interaksi kecil --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

{{-- Lucide Icons (dipakai di navbar / halaman member) --}}
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    function renderLucide() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', renderLucide);
    // Kalau nanti kamu pakai Turbo/Inertia, event ini tetap aman:
    document.addEventListener('turbo:load', renderLucide);
</script>

{{-- SweetAlert2 (flash toast dll) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- FLASH MESSAGE -> TOAST --}}
@if (session('success') || session('error') || session('warning') || session('info') || session('danger'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeMap = {
                success: 'success',
                error: 'error',
                danger: 'error',
                warning: 'warning',
                info: 'info',
            };

            @foreach (['success', 'error', 'danger', 'warning', 'info'] as $key)
                @if (session($key))
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: typeMap['{{ $key }}'] ?? 'info',
                        title: {!! json_encode(session($key)) !!},
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true,
                    });
                @endif
            @endforeach
        });
    </script>
@endif

@stack('scripts')

</body>
</html>
