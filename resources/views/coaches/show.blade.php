{{-- resources/views/coaches/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $coach = $coach ?? null;

    // 1. Helper Image Resolver
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/1200x1600/111827/FACC15?text=' . urlencode($fallbackText ?: 'COACH') . '&font=raleway';
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');
        return Storage::url($path);
    };

    $imageUrl = $imgUrl($coach?->foto, $coach?->nama ?? 'COACH');

    // 2. Normalisasi WhatsApp (untuk CTA saja, TIDAK ditampilkan nomornya)
    $waNumber = $coach?->no_hp ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp)) : null;
    if ($waNumber && Str::startsWith($waNumber, '8')) $waNumber = '62' . $waNumber;

    // 3. Related Coaches
    $relatedCoaches = \App\Models\Coach::query()
        ->where('id', '!=', $coach->id)
        ->inRandomOrder()
        ->limit(4)
        ->get();
@endphp

<x-layouts.guest :title="($coach->nama ?? 'Coach') . ' – BETA GYM'">

    {{-- Mini style agar warna card konsisten dengan index coach (tanpa coklat) --}}
    <style>
        .surface-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(212,167,87,.14);
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .surface-card:hover{
            transform: translateY(-2px);
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }
        .chip-soft{
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(212,167,87,.16);
        }
    </style>

    {{-- BACKGROUND ATMOSPHERE --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden bg-brand-dark">
        <div class="absolute top-[-10%] right-[-10%] w-[800px] h-[800px] bg-gold-500/5 rounded-full blur-[150px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-brand-surface-200/10 rounded-full blur-[150px]"></div>
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(to right, #888 1px, transparent 1px), linear-gradient(to bottom, #888 1px, transparent 1px); background-size: 50px 50px;">
        </div>
    </div>

    {{-- NOTE: pt diperkecil supaya tidak ada gap besar dari navbar --}}
    {{-- FIX MOBILE: overflow-x-hidden untuk mencegah horizontal scroll dari chip/teks panjang --}}
    <section class="relative pt-20 md:pt-12 pb-16 md:pb-20 z-10 overflow-x-hidden">
        <div class="container mx-auto px-6">

            {{-- ========================================================= --}}
            {{-- 1) BREADCRUMB (sesuai contoh Marketplace) --}}
            {{-- FIX MOBILE: flex-wrap + min-w-0 + truncate yang benar --}}
            {{-- ========================================================= --}}
            <div class="mb-6 md:mb-8">
                <nav class="flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-[0.15em] text-brand-silver/60 w-full min-w-0">
                    <a href="{{ route('guest.coaches.index') }}" class="hover:text-gold-500 flex items-center gap-1 transition-colors shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Pelatih
                    </a>
                    <span class="text-brand-borderSoft/40 shrink-0">/</span>
                    <span class="text-brand-silver truncate max-w-[72vw] sm:max-w-none min-w-0">
                        {{ $coach?->nama ?? 'Detail' }}
                    </span>
                </nav>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-14 items-start">

                {{-- ========================================================= --}}
                {{-- LEFT: FOTO COACH (HANYA NAMA, tanpa Available/alamat/no hp) --}}
                {{-- ========================================================= --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24 animate-slide-up" style="animation-duration: 0.7s">
                    <div class="relative rounded-[2rem] overflow-hidden border border-brand-borderSoft/20 shadow-2xl bg-brand-sidebar group">

                        <div class="aspect-[3/4] w-full relative overflow-hidden bg-brand-surface-200">
                            <img src="{{ $imageUrl }}"
                                 alt="{{ $coach->nama }}"
                                 class="w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-105"
                                 onerror="this.onerror=null; this.src='https://placehold.co/1200x1600/111827/FACC15?text={{ urlencode($coach->nama ?? 'COACH') }}';">

                            {{-- Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-90"></div>

                            {{-- Nama saja --}}
                            <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-7">
                                <h2 class="text-3xl md:text-4xl font-display font-bold text-white drop-shadow-md">
                                    {{ $coach->nama }}
                                </h2>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- RIGHT: INFO (Terpercaya + alamat, TANPA no hp) --}}
                {{-- FIX MOBILE: wrapper min-w-0 + chip alamat w-full sm:w-auto + truncate proper --}}
                {{-- ========================================================= --}}
                <div class="lg:col-span-7 flex flex-col justify-center animate-slide-up min-w-0" style="animation-duration: 0.9s">

                    <div class="mb-8 pb-8 border-b border-brand-borderSoft/10">
                        <div class="flex flex-wrap items-center gap-2.5 mb-5 w-full min-w-0">
                            {{-- Terpercaya --}}
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-gold-500/10 text-gold-500 border border-gold-500/20 text-[11px] font-bold uppercase tracking-widest shrink-0">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Terpercaya
                            </span>

                            {{-- Alamat (biar tidak bikin overflow) --}}
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md
                                         bg-brand-surface-200/30 text-brand-silver border border-brand-borderSoft/20
                                         text-[11px] font-bold uppercase tracking-widest
                                         w-full sm:w-auto max-w-full min-w-0">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0"></i>
                                <span class="truncate min-w-0">{{ $coach->alamat ?: 'BETA Gym HQ' }}</span>
                            </span>
                        </div>

                        <h1 class="text-4xl md:text-6xl font-display font-bold text-brand-white leading-none mb-5">
                            BANGUN POTENSI <br>
                            <span class="text-transparent bg-clip-text bg-brand-gold">TERBAIK ANDA.</span>
                        </h1>

                        <p class="text-base md:text-lg text-brand-silver/90 leading-relaxed max-w-2xl border-l-4 border-gold-500 pl-6">
                            Program latihan dan pendampingan disusun berdasarkan tujuan serta kondisi awal Anda, agar progres lebih terarah dan realistis.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xs font-bold text-brand-silver uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <span class="w-8 h-[1px] bg-gold-500"></span> Tentang Coach
                        </h3>
                        <div class="prose prose-invert prose-lg text-brand-silver/80 leading-relaxed text-justify">
                            <p>
                                {{ $coach->deskripsi ?: 'Coach ini siap membantu Anda menyusun latihan yang lebih terarah sesuai kebutuhan, baik untuk pemula maupun yang sudah rutin berlatih.' }}
                            </p>
                        </div>
                    </div>

                    {{-- WhatsApp CTA (nomor tidak ditampilkan) --}}
                    <div class="p-7 sm:p-8 rounded-[2rem] bg-brand-surface-200/5 border border-brand-borderSoft/10 relative overflow-hidden group hover:border-gold-500/30 transition-all duration-500 shadow-lg">
                        <div class="absolute -top-6 -right-6 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                            <i data-lucide="message-circle" class="w-40 h-40 text-brand-white transform rotate-12"></i>
                        </div>

                        <div class="relative z-10">
                            <h3 class="text-2xl font-display font-bold text-brand-white mb-2">Mulai Konsultasi</h3>
                            <p class="text-brand-silver mb-7 max-w-md text-sm">
                                Tanyakan jadwal, sistem latihan, serta estimasi biaya. Anda bisa mulai dari konsultasi singkat terlebih dahulu.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                @if($waNumber)
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                       class="flex-1 py-4 px-6 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#1da851] hover:to-[#0e6b5e] text-white font-bold text-lg shadow-lg hover:shadow-green-500/20 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3">
                                        <svg viewBox="0 0 24 24" class="w-6 h-6 fill-current" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        Chat WhatsApp
                                    </a>
                                @else
                                    <button disabled class="flex-1 py-4 px-6 rounded-xl bg-brand-surface-200/50 border border-brand-borderSoft/10 text-brand-silver/50 font-bold text-lg cursor-not-allowed flex items-center justify-center gap-3">
                                        <i data-lucide="phone-off" class="w-5 h-5"></i>
                                        Kontak Tidak Tersedia
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- 2) EKSPLORASI COACH LAINNYA (warna sama seperti index coach) --}}
            {{-- FIX MOBILE: header flex-col di mobile supaya tidak maksa 1 baris --}}
            {{-- ========================================================= --}}
            @if(isset($relatedCoaches) && $relatedCoaches->count() > 0)
                <div class="border-t border-brand-borderSoft/10 pt-20 mt-24 animate-slide-up" style="animation-delay: 0.2s">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10 min-w-0">
                        <div class="min-w-0">
                            <h3 class="text-2xl font-display font-bold text-white mb-2 uppercase tracking-tight">Coach Lainnya</h3>
                            <p class="text-brand-silver text-sm">Temukan coach dengan fokus latihan berbeda.z</p>
                        </div>

                        <a href="{{ route('guest.coaches.index') }}"
                           class="group inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                  bg-brand-surface-200/5 border border-brand-borderSoft/10 text-gold-500
                                  text-xs font-bold uppercase tracking-wider hover:bg-gold-500 hover:text-brand-nav transition-all shrink-0">
                            Lihat Semua
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="group-hover:translate-x-0.5 transition-transform">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($relatedCoaches as $related)
                            @php
                                $rSlug = $related->id . '-' . Str::slug($related->nama ?? 'coach');
                                $rImg = $imgUrl($related->foto, $related->nama);
                            @endphp

                            {{-- CARD COACH PREMIUM --}}
                            <a href="{{ route('guest.coaches.show', $rSlug) }}"
                               class="group block bg-[#151515] rounded-[1.5rem] overflow-hidden
                                      border border-brand-borderSoft/10 hover:border-gold-500/40
                                      transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">

                                {{-- Image Area --}}
                                <div class="aspect-[3/4] bg-[#0a0a0a] overflow-hidden relative">
                                    <img src="{{ $rImg }}" alt="{{ $related->nama }}"
                                         class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
                                         onerror="this.onerror=null; this.src='https://placehold.co/800x1000/111827/FACC15?text={{ urlencode($related->nama) }}';">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                                </div>

                                {{-- Info Area --}}
                                <div class="p-5 relative">
                                    <p class="text-[10px] text-gold-500 font-bold uppercase mb-1 tracking-wider">Coach</p>

                                    {{-- Default putih, hover emas (sesuai index coach) --}}
                                    <h4 class="text-white font-bold text-base line-clamp-1 mb-3 group-hover:text-gold-500 transition-colors">
                                        {{ $related->nama }}
                                    </h4>

                                    <div class="pt-3 border-t border-brand-borderSoft/10 flex justify-between items-center">
                                        <span class="text-[10px] text-brand-silver group-hover:text-white transition-colors font-bold">
                                            Lihat Profil
                                        </span>
                                        <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-white group-hover:bg-gold-500 group-hover:text-black transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

</x-layouts.guest>
