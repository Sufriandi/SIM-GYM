@props(['waUrl' => null])

<footer class="bg-brand-footer border-t border-brand-borderSoft/10">
    <div class="container mx-auto px-6 py-12 grid md:grid-cols-4 gap-10">
        <div class="space-y-3">
            <p class="font-display font-bold text-brand-white text-xl">BETA GYM</p>
            <p class="text-sm text-brand-silver/80 leading-relaxed">
                Gym premium dengan fokus fasilitas, komunitas positif, dan latihan yang terarah.
            </p>
            <div class="flex items-center gap-3 text-brand-silver/70">
                <i data-lucide="map-pin" class="w-4 h-4 text-gold-500"></i>
                <span>Bengkalis, Riau</span>
            </div>

            @if($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-sm font-bold text-gold-500 hover:text-gold-400 transition">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    WhatsApp Admin
                </a>
            @endif
        </div>

        <div class="space-y-3">
            <p class="font-bold text-brand-white">Navigasi</p>
            <div class="flex flex-col gap-2 text-sm">
                <a class="hover:text-brand-white" href="{{ url('/marketplace') }}">Marketplace</a>
                <a class="hover:text-brand-white" href="{{ url('/coaches') }}">Coaches</a>
                <a class="hover:text-brand-white" href="{{ url('/dashboard') }}">Dashboard</a>
            </div>
        </div>

        <div class="space-y-3">
            <p class="font-bold text-brand-white">Catatan Coaches</p>
            <p class="text-sm text-brand-silver/80 leading-relaxed">
                BETA GYM tidak memiliki kerja sama resmi dengan pihak coach.
                Jika ingin coaching, silakan hubungi coach secara mandiri via kontak yang tersedia.
            </p>
        </div>

        <div class="space-y-3">
            <p class="font-bold text-brand-white">Mulai Sekarang</p>
            @php
                $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register');
            @endphp
            <div class="flex flex-col gap-3">
                <a href="{{ $registerUrl }}" class="px-5 py-3 rounded-2xl bg-gold-500 text-brand-nav font-bold hover:bg-gold-400 transition text-center">
                    Daftar Member
                </a>
                <a href="{{ url('/marketplace') }}" class="px-5 py-3 rounded-2xl border border-brand-borderSoft/20 text-brand-white font-bold hover:bg-brand-white/10 transition text-center">
                    Lihat Produk
                </a>
            </div>
        </div>
    </div>

    <div class="border-t border-brand-borderSoft/10">
        <div class="container mx-auto px-6 py-6 text-xs text-brand-silver/60 flex flex-col md:flex-row items-start md:items-center justify-between gap-2">
            <p>© {{ date('Y') }} BETA GYM. All rights reserved.</p>
            <p>Public Pages • Laravel • Tailwind</p>
        </div>
    </div>
</footer>
