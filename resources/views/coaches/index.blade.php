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
        .edge-fade-x{
            -webkit-mask-image: linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
            mask-image: linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
        }
        .input-shell{
            background: rgba(0,0,0,.22);
            border: 1px solid rgba(212,167,87,.16);
        }
        .input-shell:focus{
            outline: none;
            border-color: rgba(212,167,87,.35);
            box-shadow: 0 0 0 4px rgba(212,167,87,.10);
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
                            Pilih coach sesuai kebutuhan. Lihat detailnya, lalu konsultasi langsung untuk jadwal dan program personal.
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
                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-7">
                        @foreach($coaches as $coach)
                            @php
                                $imageUrl = $imgUrl($coach->foto, $coach->nama);
                                $slug = $coach->id . '-' . Str::slug($coach->nama ?? 'coach');

                                $waNumber = $coach->no_hp ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $coach->no_hp)) : null;
                                if ($waNumber && Str::startsWith($waNumber, '8')) $waNumber = '62' . $waNumber;
                            @endphp

                            <div class="group rounded-3xl overflow-hidden surface-card flex flex-col">
                                {{-- Image --}}
                                <a href="{{ route('guest.coaches.show', $slug) }}" class="block">
                                    {{-- tinggi gambar disesuaikan agar 2 kolom mobile tetap proporsional --}}
                                    <div class="relative h-[240px] sm:h-[320px] lg:h-[360px] w-full overflow-hidden">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $coach->nama }}"
                                            class="w-full h-full object-cover object-top brightness-95
                                                   group-hover:brightness-110 group-hover:scale-[1.04]
                                                   transition duration-700"
                                            onerror="this.onerror=null; this.src='https://placehold.co/900x1200/111827/FACC15?text={{ urlencode($coach->nama) }}';"
                                        >
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>

                                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5">
                                            <h3 class="text-base sm:text-xl font-bold font-heading drop-shadow
                                                       text-brand-text group-hover:text-gold-500 transition-colors duration-300">
                                                {{ $coach->nama }}
                                            </h3>
                                        </div>
                                    </div>
                                </a>

                                {{-- Content --}}
                                <div class="p-4 sm:p-5 flex flex-col flex-1">
                                    <p class="text-[11px] sm:text-sm text-brand-textSoft leading-relaxed line-clamp-3 min-h-[52px]">
                                        {{ $coach->deskripsi ?: 'Hubungi coach ini untuk info program latihan dan ketersediaan jadwal.' }}
                                    </p>

                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-brand-textSoft/80">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-gold-500/60"></i>
                                            <span class="truncate">{{ $coach->alamat ?: 'Lokasi Gym' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-brand-textSoft/80">
                                            <i data-lucide="phone" class="w-4 h-4 text-gold-500/60"></i>
                                            <span class="truncate">{{ $coach->no_hp ?: '-' }}</span>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="mt-5 sm:mt-6 grid grid-cols-5 gap-3">
                                        <a href="{{ route('guest.coaches.show', $slug) }}"
                                           class="col-span-2 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                                  bg-black/18 hover:bg-black/24 text-brand-text transition
                                                  border border-gold-500/16 flex items-center justify-center">
                                            Detail
                                        </a>

                                        @if($waNumber)
                                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                                               class="col-span-3 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                                      text-white transition flex items-center justify-center gap-2
                                                      hover:-translate-y-[1px]"
                                               style="background: linear-gradient(90deg, rgba(37,211,102,.95), rgba(18,140,126,.95));
                                                      box-shadow: 0 18px 45px rgba(37,211,102,.16);">
                                                <svg viewBox="0 0 24 24" class="w-4 h-4 sm:w-5 sm:h-5 fill-current" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                </svg>
                                                Chat
                                            </a>
                                        @else
                                            <button disabled
                                                    class="col-span-3 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                                           bg-black/14 text-brand-textSoft/60 cursor-not-allowed
                                                           border border-gold-500/10">
                                                Unavailable
                                            </button>
                                        @endif
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
