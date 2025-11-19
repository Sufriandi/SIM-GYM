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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full bg-brand-bg text-text-main antialiased">

<div class="h-screen flex flex-col bg-brand-bg">

    {{-- NAVBAR MEMBER (fixed di atas) --}}
    <x-member.navbar
        :pageTitle="$pageTitle"
        :pageSubtitle="$pageSubtitle"
        :user="$user"
    />

    {{-- WRAPPER KONTEN + FOOTER (scroll di sini, mulai di bawah navbar) --}}
    <div class="flex-1 mt-16 overflow-y-auto custom-scrollbar">
        <div class="px-4 lg:px-8 pb-8">
            <div class="max-w-6xl mx-auto mt-6 space-y-4">
                {{ $slot }}
            </div>

            {{-- FOOTER scroll bareng konten, bukan fix --}}
            <x-member.footer />
        </div>
    </div>
</div>

{{-- ============== JS GLOBAL ============== --}}

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    function renderLucide() {
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }
    document.addEventListener('DOMContentLoaded', renderLucide);
    document.addEventListener('turbo:load', renderLucide);
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

{{-- Shadow kecil saat scroll (opsional) --}}
<script>
    document.addEventListener("scroll", () => {
        const header = document.querySelector("header");
        if (!header) return;

        if (window.scrollY > 8) {
            header.classList.add("shadow-header");
        } else {
            header.classList.remove("shadow-header");
        }
    });
</script>

@stack('scripts')

</body>
</html>
