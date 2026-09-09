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

    {{-- Local style supaya konsisten dengan “dashboard premium” --}}
    <style>
        .surface-card{
            background: #181b22;
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 10px 28px rgba(0,0,0,.70), inset 0 1px 0 rgba(255,255,255,.12);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease, background-color .25s ease;
        }
        .surface-card:hover{
            background: #1d222b;
            transform: translateY(-4px);
            border-color: rgba(212,167,87,.60);
            box-shadow: 0 20px 45px -8px rgba(212,167,87,.25), inset 0 1px 0 rgba(255,255,255,.20);
        }
        .edge-fade-x{
            -webkit-mask-image: linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
            mask-image: linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
        }
        .input-shell{
            background: rgba(0,0,0,.35);
            border: 1px solid rgba(212,167,87,.25);
        }
        .input-shell:focus{
            outline: none;
            border-color: rgba(212,167,87,.50);
            box-shadow: 0 0 0 4px rgba(212,167,87,.15);
        }
    </style>

    <div class="dark bg-brand-bg text-brand-text min-h-screen">

        {{-- ========================================================= --}}
        {{-- HERO --}}
        {{-- ========================================================= --}}
        <section class="relative pt-28 md:pt-32 pb-16 md:pb-20 overflow-hidden">
            <div class="absolute inset-0">
                {{-- background image (premium) --}}
                <img
                    src="https://images.unsplash.com/photo-1576678927484-cc907957088c?q=80&w=2070&auto=format&fit=crop"
                    alt="Coaching Background"
                    class="w-full h-full object-cover opacity-25"
                >
                <div class="absolute inset-0 bg-brand-overlay-premium"></div>

                {{-- glows --}}
                <div class="absolute -top-24 -right-24 w-[620px] h-[620px] bg-gold-500/10 rounded-full blur-[170px]"></div>
                <div class="absolute -bottom-24 -left-24 w-[620px] h-[620px] bg-accent-500/10 rounded-full blur-[180px]"></div>

                {{-- texture --}}
                <div class="absolute inset-0 opacity-[0.06]"
                     style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 38px 38px;"></div>
            </div>

            <div class="container mx-auto px-6 relative">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
                    <div class="max-w-2xl">
                        <span class="text-gold-500 font-bold tracking-widest uppercase text-xs font-heading block">
                            Coach
                        </span>
                        <h1 class="mt-3 font-display font-extrabold leading-[0.95] tracking-tight
                                   text-[clamp(38px,5.2vw,74px)]">
                            TEMUKAN<br>
                            <span class="text-transparent bg-clip-text bg-brand-gold">MENTOR LATIHANMU</span>
                        </h1>
                        <p class="text-brand-textSoft text-base md:text-lg mt-5 max-w-lg leading-relaxed">
                            Pilih mentor sesuai target kebugaran Anda. Dapatkan pendampingan profesional dan konsultasikan jadwal melalui Customer Service resmi BETA GYM.
                        </p>
                    </div>

                    {{-- Search --}}
                    <div class="w-full lg:w-[420px]">
                        <form method="GET" action="{{ route('guest.coaches.index') }}" class="relative">
                            <input
                                type="text"
                                name="q"
                                value="{{ $search }}"
                                class="w-full pl-5 pr-14 py-4 rounded-pill text-brand-text placeholder:text-brand-textSoft/60
                                       input-shell backdrop-blur-sm transition"
                                placeholder="Cari nama, spesialisasi, atau fokus latihan…"
                            >
                            <button
                                type="submit"
                                class="absolute right-2 top-2 w-10 h-10 rounded-full
                                       bg-gold-500/85 hover:bg-gold-500/95 text-brand-nav
                                       border border-gold-500/20 shadow-gold-glow transition flex items-center justify-center"
                                aria-label="Search"
                            >
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </button>
                        </form>

                        @if($search)
                            <div class="mt-2 text-right">
                                <a href="{{ route('guest.coaches.index') }}"
                                   class="text-xs font-bold text-gold-500 hover:text-gold-400 transition">
                                    Reset Search
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- GRID --}}
        {{-- ========================================================= --}}
        <section class="py-16 md:py-20 border-t border-brand-borderSoft/10">
            <div class="container mx-auto px-6">

                @if($coaches->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-24 h-24 rounded-full flex items-center justify-center mb-6 surface-card">
                            <i data-lucide="user-x" class="w-10 h-10 text-brand-textSoft/60"></i>
                        </div>
                        <h3 class="text-2xl font-bold font-display text-brand-text mb-2">Coach Tidak Ditemukan</h3>
                        <p class="text-brand-textSoft max-w-md">Kami tidak menemukan coach dengan kriteria tersebut.</p>
                    </div>
                @else

                    {{-- ✅ PERBAIKAN UTAMA: mobile 2 kolom --}}
                    {{-- - mobile: grid-cols-2 --}}
                    {{-- - sm: tetap 2 --}}
                    {{-- - md: 3 --}}
                    {{-- - lg: 3 --}}
                    {{-- - xl: 4 --}}
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5 sm:gap-4 lg:gap-5">
                        @foreach($coaches as $coach)
                            @php
                                $imageUrl = $coach->foto_url ?? $imgUrl($coach->foto, $coach->nama);
                                $slug = $coach->slug ?? ($coach->id . '-' . Str::slug($coach->nama ?? 'coach'));
                            @endphp

                            <div class="group rounded-2xl sm:rounded-3xl overflow-hidden surface-card flex flex-col">
                                {{-- Image (proporsional & compact) --}}
                                <a href="{{ route('guest.coaches.show', $slug) }}" class="block relative w-full aspect-[3/4] overflow-hidden bg-[#12141a] border-b border-white/[0.08]">
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $coach->nama }}"
                                        class="w-full h-full object-cover object-top brightness-95
                                               group-hover:brightness-105 group-hover:scale-[1.05]
                                               transition duration-700 ease-out"
                                        onerror="this.onerror=null; this.src='https://placehold.co/900x1200/111827/FACC15?text={{ urlencode($coach->nama) }}';"
                                        loading="lazy"
                                    >
                                    {{-- Gradients overlay --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#181b22] via-black/20 to-black/30 opacity-80 group-hover:opacity-50 transition-opacity duration-300"></div>

                                    {{-- Badge Pelatih Resmi --}}
                                    <div class="absolute top-2.5 left-2.5 z-10">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-black/75 border border-gold-500/30 text-gold-400 text-[9px] font-bold uppercase tracking-wider backdrop-blur-md shadow-md">
                                            <i data-lucide="award" class="w-2.5 h-2.5 text-gold-400"></i> Coach
                                        </span>
                                    </div>

                                    {{-- Nama Coach di atas foto --}}
                                    <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-3.5 z-10">
                                        <h3 class="text-sm sm:text-base font-bold font-heading drop-shadow-md text-white group-hover:text-gold-400 transition-colors duration-300 line-clamp-1" title="{{ $coach->nama }}">
                                            {{ $coach->nama }}
                                        </h3>
                                    </div>
                                </a>

                                {{-- Content --}}
                                <div class="p-3 sm:p-3.5 flex flex-col flex-1 bg-[#15171e]/95">
                                    <p class="text-[11px] sm:text-xs text-brand-textSoft/85 leading-relaxed line-clamp-2 min-h-[32px]">
                                        {{ $coach->deskripsi ?: 'Pelatih berdedikasi siap mendampingi target fitness Anda.' }}
                                    </p>

                                    {{-- Info Bar: Lokasi Gym --}}
                                    <div class="mt-3 pt-2 border-t border-white/[0.08] flex items-center justify-between text-[10px] sm:text-[11px] text-brand-textSoft">
                                        <div class="flex items-center gap-1.5 text-brand-silver/80">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-gold-500"></i>
                                            <span class="font-medium truncate">BETA GYM Center</span>
                                        </div>
                                    </div>

                                    {{-- Actions: Single Clean Profile Button --}}
                                    <div class="mt-3">
                                        <a href="{{ route('guest.coaches.show', $slug) }}"
                                           class="w-full py-2 rounded-xl text-xs font-bold font-heading
                                                  bg-white/5 hover:bg-gold-500 text-white hover:text-brand-nav
                                                  border border-white/15 hover:border-gold-500 transition-all duration-200
                                                  flex items-center justify-center gap-1.5 shadow-sm hover:shadow-gold-glow">
                                            <span>Lihat Profil</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-0.5">
                                                <path d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($coaches, 'hasPages') && $coaches->hasPages())
                        <div class="mt-14">
                            {{ $coaches->links() }}
                        </div>
                    @endif
                @endif

            </div>
        </section>

    </div>
</x-layouts.guest>
