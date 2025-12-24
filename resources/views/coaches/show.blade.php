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

    // 2. Normalisasi WhatsApp
    $waNumber = $coach?->no_hp ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp)) : null;
    if ($waNumber && Str::startsWith($waNumber, '8')) $waNumber = '62' . $waNumber;

    // 3. Related Coaches (Logic View - Mengambil 4 coach lain agar pas dengan grid baru)
    $relatedCoaches = \App\Models\Coach::query()
        ->where('id', '!=', $coach->id)
        ->inRandomOrder()
        ->limit(4) // Ubah limit jadi 4
        ->get();
@endphp

<x-layouts.guest :title="($coach->nama ?? 'Coach') . ' – BETA GYM'">

    {{-- BACKGROUND ATMOSPHERE --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden bg-brand-dark">
        <div class="absolute top-[-10%] right-[-10%] w-[800px] h-[800px] bg-gold-500/5 rounded-full blur-[150px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-brand-surface-200/10 rounded-full blur-[150px]"></div>
        <div class="absolute inset-0 opacity-[0.03]" 
             style="background-image: linear-gradient(to right, #888 1px, transparent 1px), linear-gradient(to bottom, #888 1px, transparent 1px); background-size: 50px 50px;">
        </div>
    </div>

    <section class="relative min-h-screen pt-28 pb-24 z-10">
        <div class="container mx-auto px-6">
            
            {{-- ========================================================= --}}
            {{-- 1. NAVIGATION BAR (REVISI: CLEAN OUTLINE BUTTON) --}}
            {{-- ========================================================= --}}
            <div class="mb-10 animate-slide-up" style="animation-duration: 0.5s">
                <a href="{{ route('guest.coaches.index') }}" 
                   class="group inline-flex items-center gap-3 px-5 py-2.5 rounded-full border-2 border-brand-silver/10 bg-transparent hover:border-gold-500/50 hover:bg-brand-surface-200/30 transition-all duration-300">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-brand-silver group-hover:-translate-x-1 group-hover:text-gold-500 transition-all"></i>
                    <span class="font-bold text-sm text-brand-silver group-hover:text-white transition-colors">Kembali ke Direktori</span>
                </a>
            </div>

            <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-start">
                
                {{-- LEFT COLUMN: PROFILE IMAGE (STICKY) --}}
                <div class="lg:col-span-5 lg:sticky lg:top-28 animate-slide-up" style="animation-duration: 0.7s">
                    <div class="relative rounded-[2rem] overflow-hidden border border-brand-borderSoft/20 shadow-2xl bg-brand-sidebar group">
                        
                        {{-- Image (3x4 Ratio) --}}
                        <div class="aspect-[3/4] w-full relative overflow-hidden bg-brand-surface-200">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $coach->nama }}" 
                                 class="w-full h-full object-cover object-top transition-transform duration-700 ease-out group-hover:scale-105"
                                 onerror="this.onerror=null; this.src='https://placehold.co/1200x1600/111827/FACC15?text={{ urlencode($coach->nama ?? 'COACH') }}';">
                            
                            {{-- Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-sidebar via-transparent to-transparent opacity-80"></div>
                            
                            {{-- Badge --}}
                            <div class="absolute top-5 left-5 z-20">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-black/60 backdrop-blur-md border border-brand-white/10 shadow-lg">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success"></span>
                                    </span>
                                    <span class="text-[10px] font-bold text-white tracking-widest uppercase">Available</span>
                                </div>
                            </div>
                        </div>

                        {{-- Info Overlay --}}
                        <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                            <h2 class="text-3xl md:text-4xl font-display font-bold text-white mb-1 drop-shadow-md">{{ $coach->nama }}</h2>
                            <p class="text-gold-500 font-bold tracking-[0.2em] uppercase text-xs mb-6 drop-shadow-sm">Professional Trainer</p>
                            
                            {{-- Mini Stats --}}
                            <div class="grid grid-cols-3 gap-px bg-brand-white/10 rounded-2xl overflow-hidden backdrop-blur-md border border-brand-white/10">
                                <div class="bg-brand-black/40 p-3 text-center">
                                    <span class="block text-[10px] text-brand-silver/70 uppercase tracking-wider mb-0.5">Klien</span>
                                    <span class="block text-sm font-bold text-white">Aktif</span>
                                </div>
                                <div class="bg-brand-black/40 p-3 text-center">
                                    <span class="block text-[10px] text-brand-silver/70 uppercase tracking-wider mb-0.5">Rating</span>
                                    <span class="block text-sm font-bold text-white flex items-center justify-center gap-1">5.0 <i data-lucide="star" class="w-3 h-3 text-gold-500 fill-gold-500"></i></span>
                                </div>
                                <div class="bg-brand-black/40 p-3 text-center">
                                    <span class="block text-[10px] text-brand-silver/70 uppercase tracking-wider mb-0.5">Level</span>
                                    <span class="block text-sm font-bold text-white">Pro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: INFO --}}
                <div class="lg:col-span-7 flex flex-col justify-center animate-slide-up" style="animation-duration: 0.9s">
                    
                    <div class="mb-10 pb-10 border-b border-brand-borderSoft/10">
                        <div class="flex flex-wrap items-center gap-3 mb-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-gold-500/10 text-gold-500 border border-gold-500/20 text-[11px] font-bold uppercase tracking-widest">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Terverifikasi
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-brand-surface-200/30 text-brand-silver border border-brand-borderSoft/20 text-[11px] font-bold uppercase tracking-widest">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> {{ $coach->alamat ?: 'BETA Gym HQ' }}
                            </span>
                        </div>
                        
                        <h1 class="text-4xl md:text-6xl font-display font-bold text-brand-white leading-none mb-6">
                            BANGUN POTENSI <br>
                            <span class="text-transparent bg-clip-text bg-brand-gold">TERBAIK ANDA.</span>
                        </h1>
                        
                        <p class="text-lg text-brand-silver/90 leading-relaxed max-w-2xl border-l-4 border-gold-500 pl-6 italic">
                            "Saya berdedikasi untuk membantu Anda mencapai target fisik dan mental yang Anda impikan, satu repetisi setiap kalinya."
                        </p>
                    </div>

                    <div class="mb-10">
                        <h3 class="text-xs font-bold text-brand-silver uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                            <span class="w-8 h-[1px] bg-gold-500"></span> Tentang Coach
                        </h3>
                        <div class="prose prose-invert prose-lg text-brand-silver/80 leading-relaxed text-justify">
                            <p>
                                {{ $coach->deskripsi ?: 'Coach ini memiliki pengalaman luas dalam bidang kebugaran dan nutrisi. Fokus utamanya adalah membantu klien mencapai transformasi tubuh yang berkelanjutan melalui program latihan yang terstruktur dan pola makan yang seimbang.' }}
                            </p>
                        </div>
                    </div>

                    {{-- WhatsApp CTA --}}
                    <div class="p-8 rounded-[2rem] bg-brand-surface-200/5 border border-brand-borderSoft/10 relative overflow-hidden group hover:border-gold-500/30 transition-all duration-500 shadow-lg">
                        <div class="absolute -top-6 -right-6 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
                            <i data-lucide="message-circle" class="w-40 h-40 text-brand-white transform rotate-12"></i>
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-2xl font-display font-bold text-brand-white mb-2">Mulai Sesi Latihanmu</h3>
                            <p class="text-brand-silver mb-8 max-w-md text-sm">
                                Konsultasikan tujuan latihan, jadwal ketersediaan, dan biaya personal training.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                @if($waNumber)
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" 
                                       class="flex-1 py-4 px-6 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#1da851] hover:to-[#0e6b5e] text-white font-bold text-lg shadow-lg hover:shadow-green-500/20 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3">
                                        <svg viewBox="0 0 24 24" class="w-6 h-6 fill-current" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
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
            {{-- 2. REVISI: EKSPLORASI COACH (GRID LEBIH KECIL) --}}
            {{-- ========================================================= --}}
            @if($relatedCoaches->count() > 0)
                <div class="border-t border-brand-borderSoft/10 pt-20 mt-10 animate-slide-up" style="animation-delay: 0.2s">
                    <div class="flex items-end justify-between mb-10">
                        <div>
                            <h3 class="text-3xl font-display font-bold text-brand-white">EKSPLORASI COACH LAINNYA</h3>
                            <p class="text-brand-silver mt-2 text-sm">Temukan mentor latihan dengan spesialisasi yang berbeda.</p>
                        </div>
                        <a href="{{ route('guest.coaches.index') }}" class="hidden md:flex items-center gap-2 text-gold-500 font-bold hover:text-gold-400 text-sm transition-colors group">
                            Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    {{-- REVISI GRID: Menggunakan lg:grid-cols-4 agar kartu lebih kecil --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($relatedCoaches as $related)
                            @php
                                $rSlug = $related->id . '-' . Str::slug($related->nama ?? 'coach');
                                $rImg = $imgUrl($related->foto, $related->nama);
                            @endphp
                            
                            {{-- CARD PREVIEW --}}
                            <a href="{{ route('guest.coaches.show', $rSlug) }}" class="group relative flex flex-col h-full bg-brand-sidebar rounded-[1.5rem] border border-brand-borderSoft/10 hover:border-gold-500/40 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                                
                                {{-- Image Area (3x4 Ratio) --}}
                                <div class="aspect-[3/4] w-full overflow-hidden relative bg-brand-surface-200/5">
                                    <img src="{{ $rImg }}" 
                                         alt="{{ $related->nama }}" 
                                         class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-110"
                                         onerror="this.onerror=null; this.src='https://placehold.co/800x1000/111827/FACC15?text={{ urlencode($related->nama) }}';">
                                    
                                    {{-- Overlay Gelap saat Hover --}}
                                    <div class="absolute inset-0 bg-brand-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>

                                {{-- Info Area --}}
                                <div class="p-5 border-t border-brand-borderSoft/10 bg-brand-sidebar relative z-10">
                                    <p class="text-[10px] font-bold text-gold-500 uppercase tracking-widest mb-1">Coach</p>
                                    <h4 class="text-lg font-display font-bold text-brand-white leading-tight mb-4 group-hover:text-gold-400 transition-colors truncate">
                                        {{ $related->nama }}
                                    </h4>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-brand-silver group-hover:text-white transition-colors">Lihat Profil</span>
                                        <div class="w-8 h-8 rounded-full bg-brand-surface-200/20 flex items-center justify-center text-brand-silver group-hover:bg-gold-500 group-hover:text-brand-nav transition-all">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
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