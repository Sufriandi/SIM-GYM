{{-- resources/views/member/produk_gym/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle  = $pageTitle ?? 'Marketplace';

    // Kompatibel: controller bisa mengirim $products atau $produks
    $products   = $products ?? ($produks ?? collect());
    $categories = $categories ?? [];

    $kategoriOptions = collect($categories)->filter()->values()->all();
    $currentQ   = request('q');
    $currentKat = request('kategori');

    // Hitung cart (member: jumlah total quantity)
    $cartRaw = session('cart', []);
    $cartCount = 0;
    foreach ($cartRaw as $it) {
        $cartCount += (int)($it['quantity'] ?? 1);
    }
    if ($cartCount <= 0 && !empty($cartRaw)) {
        $cartCount = count($cartRaw);
    }

    // Image resolver (ikut pola guest)
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') {
            // fallback cream
            return 'https://placehold.co/800x1066/f6efe4/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
        }
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/'))  $path = Str::after($path, 'public/');

        return Storage::url($path);
    };

    $heroImg = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop';

    $totalItems = method_exists($products, 'total')
        ? (int) $products->total()
        : (is_countable($products) ? count($products) : 0);

    $pageCount = (is_object($products) && method_exists($products, 'count')) ? (int) $products->count() : 0;
@endphp

<x-layouts.member :title="($pageTitle ?? 'Marketplace') . ' – BETA GYM'">

    <style>
        /* ===== CARD (LIGHT: CREAM) ===== */
        .surface-card {
            background: linear-gradient(180deg, rgba(246,239,228,.92), rgba(246,239,228,.78));
            border: 1px solid rgba(212,167,87,.22);
            box-shadow: 0 16px 44px rgba(20, 22, 26, .10);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .surface-card:hover{
            transform: translateY(-2px);
            border-color: rgba(234,179,8,.40);
            box-shadow: 0 26px 70px rgba(20, 22, 26, .14);
        }

        /* ===== CARD (DARK) ===== */
        .dark .surface-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(212,167,87,.14);
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
        }
        .dark .surface-card:hover{
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }

        /* ===== STICKY BAR (LIGHT) ===== */
        .bar-shell{
            background: rgba(246, 239, 228, .92); /* cream glass (lebih solid biar teks kebaca) */
            border: 1px solid rgba(212,167,87,.22);
            box-shadow: 0 22px 60px rgba(20, 22, 26, .12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* ===== STICKY BAR (DARK) ===== */
        .dark .bar-shell{
            background: rgba(16,16,16,.92);
            border: 1px solid rgba(148,163,184,.18);
            box-shadow: 0 25px 60px -15px rgba(0,0,0,.90);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-2 pb-20">

        {{-- ========================================================= --}}
        {{-- HERO (gambar lebih jelas, teks tetap kontras light/dark) --}}
        {{-- ========================================================= --}}
        {{-- HERO (pendek seperti coach) --}}
<section class="relative mb-0">
    <div
        class="relative min-h-[300px] md:min-h-[340px] lg:min-h-[380px]
               rounded-[2.25rem] overflow-hidden
               border border-brand-borderSoft/50 shadow-xl
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- Background --}}
        <div class="absolute inset-0">
            <img
                src="{{ $heroImg }}"
                alt="Background Gym"
                class="w-full h-full object-cover object-center
                       contrast-110 saturate-110
                       dark:contrast-125 dark:saturate-100">

            {{-- Overlay: teks jelas, gambar tetap kelihatan --}}
            <div class="absolute inset-0 bg-gradient-to-b
                        from-black/55 via-black/35 to-black/75
                        dark:from-black/70 dark:via-black/50 dark:to-black/85"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 px-6 md:px-10 pt-14 md:pt-16 pb-24 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                        border border-gold-500/30 bg-black/45 backdrop-blur-md mb-5 shadow-lg">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-gold-500"></span>
                </span>
                <span class="text-[9px] font-bold tracking-[0.25em] text-gold-400 uppercase font-heading">
                    Official Marketplace
                </span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold
                       text-white leading-tight mb-4
                       drop-shadow-[0_2px_20px_rgba(0,0,0,0.60)]">
                ELITE
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-300 via-gold-500 to-gold-600">
                    GEAR
                </span>
                <br>
                FOR ELITE
                <span class="italic text-white/70 font-serif">
                    PERFORMANCE
                </span>
            </h1>

            <p class="text-white/80 text-sm md:text-base leading-relaxed max-w-xl mx-auto font-medium">
                Koleksi peralatan premium, suplemen teruji, dan apparel eksklusif untuk member BETA GYM.
            </p>
        </div>
    </div>
</section>

{{-- ============================================== --}}
{{-- SEARCHBAR (1x saja): ngambang + STICKY jalan   --}}
{{-- Struktur: bar di di dalam hero    --}}
{{-- ============================================== --}}
<div class="-mt-14 pb-8 relative z-[70]">
    {{-- top-0 = nempel ke top <main> --}}
    {{-- top-[-16px] = “naik” 1rem untuk nutup gap mt-20 vs navbar h-16 --}}
    <div class="sticky top-[-16px] z-[90] pt-4">
        <div class="mx-auto max-w-2xl px-2 sm:px-4">
            <div class="bar-shell rounded-full p-1.5 flex items-center gap-2 relative ring-1 ring-black/5 dark:ring-white/10">

            <div class="pl-5 pr-2 text-brand-nav/45 dark:text-brand-silver">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
            </div>

            <form action="{{ route('member.produk_gym.index') }}" method="GET" class="flex-grow min-w-0">
                @if($currentKat && $currentKat !== 'all')
                    <input type="hidden" name="kategori" value="{{ $currentKat }}">
                @endif
                <input
                    type="text"
                    name="q"
                    value="{{ $currentQ }}"
                    class="w-full h-12 bg-transparent border-none
                           text-brand-nav dark:text-white
                           placeholder:text-brand-nav/40 dark:placeholder-brand-silver/45
                           text-sm font-medium focus:ring-0 px-0"
                    placeholder="Cari produk..."
                    autocomplete="off"
                >
            </form>

            <div class="w-[1px] h-8 bg-brand-borderSoft/45 dark:bg-brand-borderSoft/30 mx-1"></div>

            <div class="relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    @click.outside="open = false"
                    class="w-12 h-12 flex items-center justify-center rounded-full transition-all cursor-pointer relative group"
                    :class="open
                        ? 'bg-gold-500 text-brand-nav'
                        : 'bg-black/5 text-brand-nav/55 hover:bg-black/10 hover:text-brand-nav dark:bg-brand-surface-200/20 dark:text-brand-silver dark:hover:bg-brand-surface-200/40 dark:hover:text-white'">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>

                    @if($currentKat && $currentKat !== 'all')
                        <span class="absolute top-3 right-3 w-2 h-2 bg-gold-500 rounded-full border border-white dark:border-brand-sidebar" x-show="!open"></span>
                    @endif
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    class="absolute right-0 top-full mt-4 w-60
                           bg-brand-card dark:bg-[#1a1a1a]
                           border border-brand-borderSoft/45 dark:border-brand-borderSoft/30
                           rounded-2xl shadow-2xl overflow-hidden py-2 z-[95]
                           ring-1 ring-black/5 dark:ring-white/10"
                    style="display: none;">

                    <div class="px-4 py-3 border-b border-brand-borderSoft/30 dark:border-brand-borderSoft/10 bg-black/2 dark:bg-brand-surface-200/5 mb-1">
                        <p class="text-[10px] font-bold text-brand-nav/60 dark:text-brand-silver uppercase tracking-widest">
                            Filter Kategori
                        </p>
                    </div>

                    <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                        <a
                            href="{{ route('member.produk_gym.index', ['q' => $currentQ]) }}"
                            class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors
                                   {{ !$currentKat || $currentKat == 'all'
                                        ? 'bg-gold-500 text-brand-nav'
                                        : 'text-brand-nav hover:bg-black/5 dark:text-brand-white dark:hover:bg-brand-surface-200/20' }}">
                            <span>Semua Kategori</span>
                        </a>

                        @foreach($kategoriOptions as $cat)
                            <a
                                href="{{ route('member.produk_gym.index', ['kategori' => $cat, 'q' => $currentQ]) }}"
                                class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors
                                       {{ $currentKat == $cat
                                            ? 'bg-gold-500 text-brand-nav'
                                            : 'text-brand-nav hover:bg-black/5 dark:text-brand-white dark:hover:bg-brand-surface-200/20' }}">
                                <span>{{ $cat }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <a
                href="{{ route('member.produk_gym.cart') }}"
                class="relative flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full
                       bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all
                       shadow-lg hover:shadow-gold-glow group">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform">
                    <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>

                @if($cartCount > 0)
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-5 h-5 flex items-center justify-center rounded-full
                                 border-2 border-white dark:border-brand-sidebar shadow-sm font-bold animate-bounce-slow">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
        </div>

        @if($currentQ || ($currentKat && $currentKat !== 'all'))
            <div class="flex justify-center gap-2 mt-3">
                <span class="text-xs text-brand-nav/45 dark:text-brand-silver py-1">Filter:</span>

                @if($currentKat && $currentKat !== 'all')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-brand-nav bg-gold-500 shadow-lg">
                        {{ $currentKat }}
                    </span>
                @endif

                @if($currentQ)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold
                                 text-brand-nav bg-white/60 border border-brand-borderSoft/45 backdrop-blur-md
                                 dark:text-white dark:bg-brand-black/50 dark:border-brand-borderSoft/30">
                        "{{ $currentQ }}"
                    </span>
                @endif

                <a href="{{ route('member.produk_gym.index') }}"
                   class="text-[10px] font-bold text-red-500 hover:text-red-400 py-0.5 underline decoration-red-500/40 ml-1">
                    Reset
                </a>
            </div>
        @endif
    </div>
</div>
</div>



        

        {{-- CATALOG GRID (tetap) --}}
        <section class="pb-32 relative z-10 pt-8">
            <div class="container mx-auto px-6">

                <div class="flex items-end justify-between mb-8 pb-4 border-b border-brand-borderSoft/35 dark:border-brand-borderSoft/10">
                    <div>
                        <h3 class="text-xl font-display font-bold text-brand-nav dark:text-brand-white">KATALOG PRODUK</h3>
                        <p class="text-xs text-brand-nav/60 dark:text-brand-silver mt-1">Stok terupdate secara real-time.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-brand-nav/45 dark:text-brand-silver uppercase tracking-widest">TOTAL</p>
                        <p class="text-xl font-display font-bold text-brand-nav dark:text-brand-white leading-none">
                            {{ $totalItems }}
                            <span class="text-sm font-sans text-brand-nav/45 dark:text-brand-silver font-normal">ITEM</span>
                        </p>
                    </div>
                </div>

                @if($pageCount === 0)
                    <div class="py-32 text-center rounded-[2rem]
                                bg-brand-card/70 dark:bg-brand-sidebar/20
                                border border-dashed border-brand-borderSoft/35 dark:border-brand-borderSoft/20
                                backdrop-blur-sm">
                        <div class="w-24 h-24 bg-white/60 dark:bg-brand-surface-200/10 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-brand-borderSoft/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-nav/30 dark:text-brand-silver/30">
                                <path d="m21 21-4.3-4.3"/><path d="M11 8h.01"/><circle cx="11" cy="11" r="8"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold font-display text-brand-nav dark:text-brand-white mb-2">Produk Tidak Ditemukan</h3>
                        <p class="text-brand-nav/60 dark:text-brand-silver text-sm max-w-md mx-auto mb-8 leading-relaxed">
                            Kami tidak dapat menemukan produk yang sesuai dengan kriteria Anda. <br> Coba gunakan kata kunci yang lebih umum.
                        </p>
                        <a href="{{ route('member.produk_gym.index') }}"
                           class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-brand-nav text-white hover:bg-gold-500 hover:text-brand-nav transition-all font-bold text-sm shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                            </svg>
                            Reset Pencarian
                        </a>
                    </div>
                @else

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 lg:gap-8">
                        @foreach($products as $p)
                            @php
                                $slug = $p->id . '-' . Str::slug($p->nama ?? 'produk');
                                $imageUrl = $imgUrl($p->foto ?? '', $p->nama ?? 'PRODUK');
                                $ready = ((int)($p->stok ?? 0)) > 0;
                            @endphp

                            <div class="group rounded-3xl overflow-hidden surface-card flex flex-col
                                        hover:shadow-[0_10px_30px_-5px_rgba(234,179,8,0.15)]">

                                <a href="{{ route('member.produk_gym.show', $slug) }}" class="relative w-full aspect-[3/4] bg-black/5 dark:bg-[#0b0b0b] block overflow-hidden">
                                    @if(!empty($p->kategori))
                                        <div class="absolute top-3 left-3 z-20">
                                            <span class="px-2 py-1
                                                         bg-white/70 text-brand-nav border border-brand-borderSoft/35
                                                         dark:bg-black/60 dark:text-white dark:border-white/10
                                                         backdrop-blur text-[9px] font-bold uppercase tracking-wider rounded shadow-sm">
                                                {{ $p->kategori }}
                                            </span>
                                        </div>
                                    @endif

                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $p->nama }}"
                                         class="w-full h-full object-cover object-center
                                                brightness-[0.98] dark:brightness-95
                                                group-hover:brightness-105 group-hover:scale-[1.06]
                                                transition duration-700"
                                         loading="lazy">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-black/0 to-transparent dark:from-black/85 dark:via-black/20"></div>
                                </a>

                                <div class="p-4 flex flex-col flex-grow border-t border-brand-borderSoft/25 dark:border-brand-borderSoft/10 relative z-10">
                                    <a href="{{ route('member.produk_gym.show', $slug) }}" class="block mb-2">
                                        <h3 class="text-sm font-bold text-brand-nav dark:text-brand-white leading-snug line-clamp-2 h-[2.5rem]
                                                   group-hover:text-gold-600 dark:group-hover:text-gold-500 transition-colors"
                                            title="{{ $p->nama }}">
                                            {{ $p->nama }}
                                        </h3>
                                    </a>

                                    <div class="mt-auto pt-3 border-t border-brand-borderSoft/25 dark:border-brand-borderSoft/10">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-base font-display font-bold text-brand-nav dark:text-brand-white">
                                                Rp {{ number_format((int)($p->harga ?? 0), 0, ',', '.') }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            @if($ready)
                                                <span class="text-[10px] font-bold text-success flex items-center gap-1 bg-success/10 px-2 py-0.5 rounded">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Stok: {{ (int)($p->stok ?? 0) }}
                                                </span>
                                            @else
                                                <span class="text-[10px] font-bold text-red-500 flex items-center gap-1 bg-red-500/10 px-2 py-0.5 rounded">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                                </span>
                                            @endif

                                            <a href="{{ route('member.produk_gym.show', $slug) }}" class="text-brand-nav/45 hover:text-gold-600 dark:text-brand-silver dark:hover:text-gold-500 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($products, 'hasPages') && $products->hasPages())
                        <div class="mt-14">
                            {{ $products->onEachSide(1)->links() }}
                        </div>
                    @endif

                @endif
            </div>
        </section>

    </div>

</x-layouts.member>
