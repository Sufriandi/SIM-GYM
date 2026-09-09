{{-- resources/views/member/coach/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle    = $pageTitle    ?? 'Coach';
    $pageSubtitle = $pageSubtitle ?? 'Temukan coach yang sesuai kebutuhan latihan Anda, lalu konsultasi langsung untuk jadwal dan program.';
    // kompatibel: ?search=... (member) atau ?q=... (fallback)
    $search       = $search       ?? request('search', request('q', ''));

    /**
     * Resolver URL gambar (robust seperti versi guest).
     */
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

    /**
     * Normalisasi WhatsApp: 08xx -> 628xx, hapus non-digit, handle yang diawali 8.
     */
    $waNormalize = function ($raw) {
        $raw = (string) $raw;
        $digits = preg_replace('/\D/', '', $raw) ?: '';
        if ($digits === '') return null;

        if (Str::startsWith($digits, '0')) $digits = '62' . substr($digits, 1);
        if (Str::startsWith($digits, '8')) $digits = '62' . $digits;

        return $digits;
    };

    // HERO background (bebas diganti)
    $heroBg = asset('images/hero-coach.jpg');
@endphp

<x-layouts.member
    :title="$pageTitle"
    :page-title="$pageTitle"
    :page-subtitle="$pageSubtitle"
>
    {{-- ========================================================= --}}
    {{-- HERO (tidak memaksa dark; aman untuk toggle light/dark) --}}
    {{-- ========================================================= --}}
    <section class="relative overflow-hidden rounded-3xl border border-brand-borderSoft/60 shadow-card-strong mb-8">
        <div class="absolute inset-0">
            <img
                src="{{ $heroBg }}"
                alt="Coaching Background"
                class="w-full h-full object-cover"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1576678927484-cc907957088c?q=80&w=2070&auto=format&fit=crop';"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/20"></div>

            <div class="absolute -top-24 -right-24 w-[560px] h-[560px] bg-gold-500/10 rounded-full blur-[160px]"></div>
            <div class="absolute -bottom-24 -left-24 w-[560px] h-[560px] bg-accent-500/10 rounded-full blur-[170px]"></div>

            <div class="absolute inset-0 opacity-[0.07]"
                 style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 38px 38px;"></div>
        </div>

        <div class="relative z-10 px-6 md:px-10 py-10 md:py-12">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
                <div class="max-w-2xl">
                    <span class="text-gold-300 font-bold tracking-widest uppercase text-xs font-heading block">
                        Coach
                    </span>

                    <h1 class="mt-3 font-display font-extrabold leading-[0.95] tracking-tight
                               text-[clamp(34px,4.6vw,64px)] text-white">
                        TEMUKAN<br>
                        <span class="text-transparent bg-clip-text bg-brand-gold">MENTOR LATIHANMU</span>
                    </h1>

                    <p class="text-white/80 text-base md:text-lg mt-5 max-w-lg leading-relaxed">
                        Pilih coach sesuai kebutuhan. Buka detailnya, lalu chat WhatsApp untuk konsultasi program dan jadwal.
                    </p>
                </div>

                {{-- Search --}}
                <div class="w-full lg:w-[420px]">
                    <form method="GET" action="{{ route('member.coach.index') }}" class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="w-full pl-5 pr-14 py-4 rounded-full
                                   bg-white/10 backdrop-blur-md text-white placeholder:text-white/60
                                   border border-white/15 focus:outline-none focus:ring-2 focus:ring-gold-500/30 focus:border-gold-500/30 transition"
                            placeholder="Cari nama, spesialisasi, atau fokus latihan…"
                        >
                        <button
                            type="submit"
                            class="absolute right-2 top-2 w-10 h-10 rounded-full
                                   bg-gold-500/90 hover:bg-gold-500 text-brand-nav
                                   border border-gold-500/20 shadow-gold-glow transition flex items-center justify-center"
                            aria-label="Search"
                        >
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </button>
                    </form>

                    @if($search)
                        <div class="mt-2 text-right">
                            <a href="{{ route('member.coach.index') }}"
                               class="text-xs font-bold text-gold-300 hover:text-gold-200 transition">
                                Reset Search
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/30 to-transparent"></div>
    </section>

    {{-- ========================================================= --}}
    {{-- GRID --}}
    {{-- ========================================================= --}}
    <div class="space-y-6">
        @if($coaches->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div class="w-24 h-24 rounded-3xl bg-brand-card border border-brand-borderSoft flex items-center justify-center mb-6 shadow-card-soft">
                    <i data-lucide="user-x" class="w-10 h-10 text-brand-textSoft/60"></i>
                </div>
                <h3 class="text-2xl font-bold font-display text-brand-text mb-2">Coach Tidak Ditemukan</h3>
                <p class="text-brand-textSoft max-w-md">
                    @if($search)
                        Kami tidak menemukan coach dengan kata kunci "{{ $search }}".
                    @else
                        Belum ada coach yang tersedia saat ini.
                    @endif
                </p>
            </div>
        @else
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-brand-text">
                    Coach Tersedia
                    <span class="text-gold-500">({{ method_exists($coaches, 'total') ? $coaches->total() : $coaches->count() }})</span>
                </h3>
            </div>

            {{-- Mobile 2 kolom, md 3 kolom, xl 4 kolom --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 lg:gap-7">
                @foreach($coaches as $coach)
                    @php
                        $imageUrl = $imgUrl($coach->foto ?? '', $coach->nama ?? 'COACH');

                        $slug = ($coach->id ?? 0) . '-' . Str::slug($coach->nama ?? 'coach');

                        $waNumber = $waNormalize($coach->no_hp ?? null);
                    @endphp

                    <div class="group rounded-3xl overflow-hidden bg-brand-card border border-brand-borderSoft shadow-card-soft
                                hover:shadow-card-strong transition-all duration-300 flex flex-col">
                        {{-- Image (klik -> show) --}}
                        <a href="{{ route('member.coach.show', $slug) }}" class="block">
                            <div class="relative h-[220px] sm:h-[300px] lg:h-[340px] w-full overflow-hidden bg-brand-surface-50">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $coach->nama }}"
                                    class="w-full h-full object-cover object-top
                                           group-hover:scale-[1.04] transition duration-700"
                                    onerror="this.onerror=null; this.src='https://placehold.co/900x1200/111827/FACC15?text={{ urlencode($coach->nama ?? 'COACH') }}&font=raleway';"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-transparent"></div>

                                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
                                    <h3 class="text-sm sm:text-lg font-bold font-heading drop-shadow text-white">
                                        {{ $coach->nama ?? 'Coach' }}
                                    </h3>
                                </div>
                            </div>
                        </a>

                        {{-- Content --}}
                        <div class="p-4 sm:p-5 flex flex-col flex-1">
                            <p class="text-[11px] sm:text-sm text-brand-textSoft leading-relaxed line-clamp-3 min-h-[48px]">
                                {{ $coach->deskripsi ?: 'Hubungi coach ini untuk info program latihan dan ketersediaan jadwal.' }}
                            </p>

                            <div class="mt-4 space-y-2">
                                <div class="flex items-center gap-2 text-[11px] sm:text-xs text-brand-textSoft/80">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gold-500/70"></i>
                                    <span class="truncate">BETA GYM Center</span>
                                </div>
                            </div>

                            {{-- Actions (Detail -> show, Chat -> WA) --}}
                            <div class="mt-5 sm:mt-6 grid grid-cols-5 gap-3">
                                <a
                                    href="{{ route('member.coach.show', $slug) }}"
                                    class="col-span-2 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                           bg-brand-shell hover:bg-brand-surface-100 text-brand-text transition
                                           border border-brand-borderSoft flex items-center justify-center"
                                >
                                    Detail
                                </a>

                                @if($waNumber)
                                    <a
                                        href="https://wa.me/{{ $waNumber }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="col-span-3 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                               text-white transition flex items-center justify-center gap-2
                                               hover:-translate-y-[1px]"
                                        style="background: linear-gradient(90deg, rgba(37,211,102,.95), rgba(18,140,126,.95));
                                               box-shadow: 0 18px 45px rgba(37,211,102,.16);"
                                        aria-label="Chat WhatsApp"
                                    >
                                        {{-- WhatsApp icon (SVG) --}}
                                        <svg viewBox="0 0 24 24" class="w-4 h-4 sm:w-5 sm:h-5 fill-current" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        Chat
                                    </a>
                                @else
                                    <button
                                        disabled
                                        class="col-span-3 py-3 rounded-2xl text-xs sm:text-sm font-bold font-heading
                                               bg-brand-shell text-brand-textSoft/60 cursor-not-allowed
                                               border border-brand-borderSoft"
                                    >
                                        Unavailable
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(method_exists($coaches, 'hasPages') && $coaches->hasPages())
                <div class="mt-10">
                    {{ $coaches->appends(['search' => $search])->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ========================================================= --}}
    {{-- CTA --}}
    {{-- ========================================================= --}}
    @php
        $ctaBg = asset('images/cta-coach1.jpg');
    @endphp

    <div
        class="mt-12 relative overflow-hidden rounded-3xl border border-brand-borderSoft shadow-card-strong"
        style="
            background-image:
                linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.35) 55%, rgba(0,0,0,.20) 100%),
                url('{{ $ctaBg }}');
            background-size: cover;
            background-position: center;
        "
    >
        <div class="absolute inset-0 bg-gradient-to-br from-black/35 via-transparent to-black/25"></div>

        <div class="relative z-10 p-8 md:p-10">
            <div class="text-center max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold text-white mb-3">
                    Butuh Bantuan Memilih Coach?
                </h3>
                <p class="text-white/80 mb-6">
                    Tim kami bisa membantu Anda menentukan coach yang paling cocok dengan tujuan latihan.
                </p>

                <a
                    href="https://wa.me/6281234567890"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-bold
                           bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-600 hover:to-gold-700
                           text-brand-nav transition"
                    aria-label="Hubungi Customer Service via WhatsApp"
                >
                    {{-- WhatsApp icon (SVG) --}}
                    <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Hubungi CS
                </a>
            </div>
        </div>
    </div>

</x-layouts.member>
