@props(['title' => 'BETA GYM – Build a Better You'])

@php
    use Illuminate\Support\Str;

    // WA Admin dari .env
    // contoh .env: WHATSAPP_NUMBER=6281212345678 atau 081212345678
    $waRaw = env('WHATSAPP_NUMBER', '');
    $waDigits = preg_replace('/\D+/', '', (string) $waRaw);

    if ($waDigits !== '') {
        if (Str::startsWith($waDigits, '0')) $waDigits = '62' . substr($waDigits, 1);
        if (Str::startsWith($waDigits, '8')) $waDigits = '62' . $waDigits;
    }

    $waText = rawurlencode('Halo Admin BETA GYM, saya mau tanya membership dan fasilitas.');
    $waUrl  = $waDigits !== '' ? "https://wa.me/{$waDigits}?text={$waText}" : null;
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.png') }}">
    <meta name="theme-color" content="#0b0f14">


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap');

        [x-cloak]{ display:none !important; }

        /* clamp util */
        .line-clamp-1{ display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:1; overflow:hidden; }
        .line-clamp-2{ display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; }
        .line-clamp-3{ display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3; overflow:hidden; }

        /* anim */
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .animate-float { animation: float 7s ease-in-out infinite; }

        @keyframes slideUpFade { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
        .animate-slide-up { animation: slideUpFade .85s ease-out both; }
    </style>

    @stack('styles')
</head>

<body class="bg-brand-dark text-brand-silver font-sans antialiased overflow-x-hidden selection:bg-gold-500 selection:text-brand-nav">
    <x-guest.navbar />

    <main class="pt-20">
        {{ $slot }}
    </main>

    <x-guest.footer :wa-url="$waUrl" />

    {{-- Floating WhatsApp Admin (pengganti halaman contact) --}}
    @if($waUrl)
        <div class="fixed bottom-6 right-6 z-50" x-data="{ tip: true }" x-init="setTimeout(()=>tip=false, 3200)">
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="group relative w-14 h-14 rounded-2xl bg-gold-500 text-brand-nav shadow-gold-glow flex items-center justify-center hover:bg-gold-400 transition">
                <i data-lucide="message-circle" class="w-7 h-7"></i>

                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-success ring-2 ring-brand-dark"></span>

                <div x-cloak x-show="tip"
                     class="absolute right-16 top-1/2 -translate-y-1/2 px-4 py-2 rounded-2xl
                            bg-brand-nav/95 border border-brand-borderSoft/15 text-brand-white text-sm shadow-card whitespace-nowrap">
                    Chat WhatsApp Admin
                </div>
            </a>
        </div>
    @endif

    @stack('scripts')

    <script>
        if (window.lucide) lucide.createIcons();
    </script>
</body>
</html>
