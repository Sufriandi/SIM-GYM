@php
    $pageTitle = $pageTitle ?? 'Kontak';
    $whatsappUrl = $whatsappUrl ?? null;
@endphp

<x-layouts.guest :title="$pageTitle">

    <section class="py-14 bg-brand-dark">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl">
                <p class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading">Contact</p>
                <h1 class="text-4xl md:text-hero font-display font-bold text-brand-white mt-2">Hubungi BETA GYM</h1>
                <p class="text-brand-silver/80 mt-3">
                    Untuk pertanyaan membership, jadwal, atau layanan gym, silakan hubungi admin.
                    Untuk coaching pribadi, hubungi coach pilihan Anda langsung dari halaman Coaches.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mt-10">
                <div class="p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                    <p class="text-brand-white font-bold">WhatsApp Admin</p>
                    <p class="text-sm text-brand-silver/70 mt-2">Respon cepat untuk pertanyaan umum.</p>

                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                           class="mt-5 inline-flex items-center justify-center gap-2 w-full py-3 rounded-2xl bg-gold-500 text-brand-nav font-bold hover:bg-gold-400 transition">
                            <i data-lucide="message-circle" class="w-5 h-5"></i> Chat WhatsApp
                        </a>
                    @else
                        <div class="mt-5 p-4 rounded-2xl bg-brand-surface-200/10 text-brand-silver/70">
                            WhatsApp admin belum dikonfigurasi (ENV).
                        </div>
                    @endif
                </div>

                <div class="p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                    <p class="text-brand-white font-bold">Lokasi</p>
                    <p class="text-sm text-brand-silver/70 mt-2">Bengkalis, Riau</p>
                    <div class="mt-5 p-5 rounded-2xl bg-brand-surface-200/5 border border-brand-borderSoft/10 text-sm">
                        <p class="text-brand-silver/70">Jam Operasional</p>
                        <p class="text-brand-white font-bold mt-1">24/7 (Member)</p>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-brand-sidebar border border-brand-borderSoft/10 shadow-card">
                    <p class="text-brand-white font-bold">Aksi Cepat</p>
                    <p class="text-sm text-brand-silver/70 mt-2">Mulai dari sini kalau sudah siap.</p>

                    <div class="mt-5 space-y-3">
                        @php
                            $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register');
                        @endphp
                        <a href="{{ $registerUrl }}" class="block w-full py-3 rounded-2xl bg-gold-500 text-brand-nav font-bold text-center hover:bg-gold-400 transition">
                            Register
                        </a>
                        <a href="{{ url('/dashboard') }}" class="block w-full py-3 rounded-2xl border border-brand-borderSoft/20 text-brand-white font-bold text-center hover:bg-brand-white/10 transition">
                            Masuk Dashboard
                        </a>
                        <a href="{{ url('/coaches') }}" class="block w-full py-3 rounded-2xl border border-brand-borderSoft/20 text-brand-white font-bold text-center hover:bg-brand-white/10 transition">
                            Lihat Coaches
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

</x-layouts.guest>
