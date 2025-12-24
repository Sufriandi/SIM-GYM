{{-- resources/views/coaches/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Coaches';
    $search    = request('q', '');

    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') {
            return 'https://placehold.co/900x1200/111827/FACC15?text=' . urlencode($fallbackText ?: 'COACH') . '&font=raleway';
        }
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/'))  $path = Str::after($path, 'public/');
        return Storage::url($path);
    };
@endphp

<x-layouts.guest :title="$pageTitle . ' – BETA GYM'">

    {{-- HERO --}}
    <section class="relative pt-32 pb-20 overflow-hidden bg-brand-dark">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#A67C39 1px, transparent 1px); background-size: 30px 30px;"></div>
        
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-end justify-between gap-10">
                <div class="max-w-2xl">
                    <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading mb-2 block">World Class Trainers</span>
                    <h1 class="text-5xl md:text-7xl font-display font-bold text-brand-white leading-none">
                        TEMUKAN <br>
                        <span class="text-transparent bg-clip-text bg-brand-gold">MENTOR LATIHANMU</span>
                    </h1>
                    <p class="text-brand-silver text-lg mt-6 max-w-lg leading-relaxed">
                        Pilih coach yang sesuai dengan gayamu. Hubungi mereka langsung untuk konsultasi program personal.
                    </p>
                </div>

                {{-- Search Box Compact --}}
                <div class="w-full lg:w-auto">
                    <form method="GET" action="{{ route('guest.coaches.index') }}" class="relative w-full lg:w-[400px]">
                        <input type="text" name="q" value="{{ $search }}" 
                            class="w-full pl-6 pr-14 py-4 bg-brand-sidebar border border-brand-borderSoft/30 rounded-full text-brand-white placeholder-brand-silver/50 focus:outline-none focus:border-gold-500/50 focus:ring-2 focus:ring-gold-500/20 transition-all shadow-lg"
                            placeholder="Cari nama atau spesialisasi...">
                        <button type="submit" class="absolute right-2 top-2 p-2 bg-gold-500 text-brand-nav rounded-full hover:bg-gold-400 transition-colors">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </button>
                    </form>
                    @if($search)
                        <div class="mt-2 text-right">
                            <a href="{{ route('guest.coaches.index') }}" class="text-xs font-bold text-gold-500 hover:text-gold-400">Reset Search</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- COACH GRID --}}
    <section class="py-20 bg-brand-dark border-t border-brand-borderSoft/5">
        <div class="container mx-auto px-6">

            @if($coaches->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-24 h-24 bg-brand-sidebar rounded-full flex items-center justify-center mb-6 border border-brand-borderSoft/20 animate-pulse">
                        <i data-lucide="user-x" class="w-10 h-10 text-brand-silver/50"></i>
                    </div>
                    <h3 class="text-2xl font-bold font-display text-brand-white mb-2">Coach Tidak Ditemukan</h3>
                    <p class="text-brand-silver max-w-md">Kami tidak dapat menemukan coach dengan kriteria tersebut.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($coaches as $coach)
                        @php
                            $imageUrl = $imgUrl($coach->foto, $coach->nama);
                            $slug = $coach->id . '-' . Str::slug($coach->nama ?? 'coach');
                            
                            // Normalisasi WA
                            $waNumber = $coach->no_hp ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp)) : null;
                            if ($waNumber && Str::startsWith($waNumber, '8')) $waNumber = '62' . $waNumber;
                        @endphp

                        <div class="group relative bg-brand-sidebar rounded-[2rem] overflow-hidden border border-brand-borderSoft/10 hover:border-gold-500/50 hover:shadow-[0_0_30px_rgba(234,179,8,0.15)] transition-all duration-500 flex flex-col">
                            
                            {{-- Image Area (REVISI: Tidak Grayscale, tapi Brightness Play) --}}
                            <div class="relative h-[400px] w-full overflow-hidden">
                                {{-- Default: agak gelap (brightness-90). Hover: Terang & Zoom --}}
                                <img src="{{ $imageUrl }}" 
                                     alt="{{ $coach->nama }}" 
                                     class="w-full h-full object-cover object-top brightness-90 group-hover:brightness-110 group-hover:scale-105 transition-all duration-700"
                                     onerror="this.onerror=null; this.src='https://placehold.co/900x1200/111827/FACC15?text={{ urlencode($coach->nama) }}';">
                                
                                {{-- Gradient Bawah --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-brand-sidebar via-brand-sidebar/10 to-transparent opacity-90"></div>

                                {{-- Overlay Nama di Atas Gambar --}}
                                <div class="absolute bottom-0 left-0 right-0 p-6 z-10 translate-y-2 group-hover:translate-y-0 transition-transform duration-500">
                                    <h3 class="text-2xl font-bold font-display text-brand-white leading-tight mb-1 drop-shadow-md">
                                        {{ $coach->nama }}
                                    </h3>
                                    <p class="text-gold-500 text-xs font-bold uppercase tracking-wider drop-shadow-sm">Professional Coach</p>
                                </div>
                            </div>

                            {{-- Info Content --}}
                            <div class="p-6 pt-2 flex flex-col flex-grow relative z-20 bg-brand-sidebar">
                                <div class="mb-6 min-h-[60px]">
                                    <p class="text-brand-silver text-sm line-clamp-3 leading-relaxed">
                                        {{ $coach->deskripsi ?: 'Hubungi coach ini untuk informasi lebih lanjut mengenai program latihan dan ketersediaan jadwal.' }}
                                    </p>
                                </div>

                                {{-- Meta Info --}}
                                <div class="flex flex-col gap-2 mb-6">
                                    <div class="flex items-center gap-3 text-xs text-brand-silver/70">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gold-500/50"></i>
                                        <span class="truncate">{{ $coach->alamat ?: 'Lokasi Gym' }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-brand-silver/70">
                                        <i data-lucide="phone" class="w-4 h-4 text-gold-500/50"></i>
                                        <span>{{ $coach->no_hp ?: '-' }}</span>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="mt-auto grid grid-cols-5 gap-3">
                                    {{-- Detail Button --}}
                                    <a href="{{ route('guest.coaches.show', $slug) }}" 
                                       class="col-span-2 py-3 rounded-2xl border border-brand-borderSoft/20 text-brand-white text-sm font-bold flex items-center justify-center hover:bg-brand-white/5 transition-colors">
                                        Detail
                                    </a>

                                    {{-- WA Button (REVISI: Menggunakan SVG Asli) --}}
                                    @if($waNumber)
                                        <a href="https://wa.me/{{ $waNumber }}" target="_blank"
                                           class="col-span-3 py-3 rounded-2xl bg-gradient-to-r from-[#25D366] to-[#128C7E] hover:from-[#20bd5a] hover:to-[#0e6b5e] text-white text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-lg hover:-translate-y-1">
                                            {{-- SVG WhatsApp Resmi --}}
                                            <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                            </svg>
                                            Chat
                                        </a>
                                    @else
                                        <button disabled class="col-span-3 py-3 rounded-2xl bg-brand-surface-200/20 text-brand-silver/50 text-sm font-bold flex items-center justify-center cursor-not-allowed">
                                            Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($coaches->hasPages())
                    <div class="mt-16">
                        {{ $coaches->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

</x-layouts.guest>