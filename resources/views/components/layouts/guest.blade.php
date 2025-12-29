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
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    {{-- ========================================================= --}}
    {{-- FLOATING WHATSAPP BUTTON (CLEAN URL / NO STATUS BAR PREVIEW) --}}
    {{-- ========================================================= --}}
    <div class="fixed bottom-6 right-6 z-[100] animate-slide-up" style="animation-delay: 1s">
        
        <div class="relative group">
            
            {{-- 1. Custom Tooltip --}}
            <div class="absolute bottom-full right-0 mb-3 w-max opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 pointer-events-none">
                <div class="bg-white text-gray-800 px-4 py-2 rounded-xl shadow-xl border border-gray-100 text-xs font-bold whitespace-nowrap">
                    Chat Admin WhatsApp
                    <div class="absolute bottom-[-6px] right-6 w-3 h-3 bg-white transform rotate-45 border-r border-b border-gray-100"></div>
                </div>
            </div>

            {{-- 2. The Button (Menggunakan 'button' + 'onclick' agar URL tidak muncul di pojok layar) --}}
            <button onclick="window.open('https://wa.me/{{ env('WA_ADMIN_NUMBER') }}?text=Halo%20Admin%20BETA%20GYM,%20saya%20butuh%20bantuan.', '_blank')"
                    class="relative flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-[0_4px_20px_rgba(37,211,102,0.4)] hover:bg-[#1da851] hover:scale-110 transition-all duration-300 hover:shadow-[0_8px_30px_rgba(37,211,102,0.5)] cursor-pointer">
                
                {{-- Icon WhatsApp --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" class="transform group-hover:rotate-12 transition-transform duration-300">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>

                {{-- Notification Dot --}}
                <span class="absolute top-0 right-0 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-red-500 border-2 border-[#25D366]"></span>
                </span>
            </button>
        </div>
    </div>
</body>
</html>
