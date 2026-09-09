{{-- resources/views/member/produk_gym/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Marketplace';

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

    // Helper Image Resolver (ikut pola guest marketplace)
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/800x1066/1a1a1a/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
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

    {{-- Local styles: Floating Bar & Adaptive Cards (Mengikuti tema default member) --}}
    <style>
        .sticky-floating-bar {
            position: -webkit-sticky !important;
            position: sticky !important;
            top: 5rem !important; /* 80px: tepat 16px melayang di bawah fixed navbar (64px) */
            z-index: 50 !important;
        }
        .bar-shell {
            background: rgba(248, 242, 231, 0.95);
            border: 1.5px solid rgba(212, 167, 87, 0.40);
            box-shadow: 0 20px 50px -10px rgba(32, 25, 17, 0.20), 0 0 25px rgba(212, 167, 87, 0.15);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .dark .bar-shell {
            background: rgba(18, 18, 18, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.90);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-2 pb-24">

        {{-- ==================================================================== --}}
        {{-- 1. HERO SECTION (SESUAI POLA MARKETPLACE) --}}
        {{-- ==================================================================== --}}
        <section class="relative min-h-[46vh] sm:min-h-[50vh] flex flex-col items-center justify-center overflow-hidden rounded-[2.25rem] border border-brand-borderSoft/40 shadow-xl pb-36 pt-16">

            {{-- Background Image with Overlay --}}
            <div class="absolute inset-0 z-0">
                <img src="{{ $heroImg }}"
                     alt="Background Gym"
                     class="w-full h-full object-cover opacity-35">

                <div class="absolute inset-0 bg-gradient-to-b from-brand-shell/85 via-brand-shell/70 to-brand-shell dark:from-brand-dark/95 dark:via-brand-dark/80 dark:to-brand-dark"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 container mx-auto px-6 text-center animate-slide-up">

                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gold-500/30 bg-white/70 dark:bg-black/60 backdrop-blur-md mb-5 shadow-lg">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-gold-500"></span>
                    </span>
                    <span class="text-[9px] font-bold tracking-[0.25em] text-gold-600 dark:text-gold-500 uppercase font-heading">
                        Official Marketplace
                    </span>
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-brand-nav dark:text-white leading-tight mb-4 drop-shadow-2xl">
                    ELITE <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-400 via-gold-500 to-gold-600">GEAR</span> <br>
                    FOR ELITE <span class="italic text-brand-nav/50 dark:text-brand-silver/70 font-serif">PERFORMANCE</span>
                </h1>

                <p class="text-brand-nav/75 dark:text-brand-silver/90 text-sm md:text-base leading-relaxed max-w-xl mx-auto drop-shadow-md font-medium">
                    Koleksi peralatan premium, suplemen teruji, dan apparel eksklusif untuk member BETA GYM.
                </p>

            </div>
        </section>

        {{-- ==================================================================== --}}
        {{-- 2. STICKY FLOATING FILTER BAR (MELAYANG PERSIS SISI PENGUNJUNG) --}}
        {{-- ==================================================================== --}}
        <div class="sticky-floating-bar sticky top-20 z-50 px-4 -mt-24 pb-8 transition-all duration-300"
             style="position: -webkit-sticky; position: sticky; top: 5rem; z-index: 50;">
            <div class="container mx-auto max-w-2xl">

                {{-- Floating Pill Container --}}
                <div class="bar-shell rounded-full p-1.5 flex items-center gap-2 relative ring-1 ring-black/5 dark:ring-white/10">

                    {{-- Search Icon --}}
                    <div class="pl-5 pr-2 text-brand-nav/50 dark:text-brand-silver">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>

                    {{-- Search Input --}}
                    <form action="{{ route('member.produk_gym.index') }}" method="GET" class="flex-grow min-w-0">
                        @if($currentKat && $currentKat !== 'all')
                            <input type="hidden" name="kategori" value="{{ $currentKat }}">
                        @endif
                        <input type="text"
                               name="q"
                               value="{{ $currentQ }}"
                               class="w-full h-12 bg-transparent border-none text-brand-nav dark:text-white placeholder:text-brand-nav/40 dark:placeholder-brand-silver/40 text-sm font-medium focus:ring-0 px-0"
                               placeholder="Cari produk..."
                               autocomplete="off">
                    </form>

                    {{-- Separator --}}
                    <div class="w-[1px] h-8 bg-brand-borderSoft/60 dark:bg-white/15 mx-1"></div>

                    {{-- Filter Category Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                @click.outside="open = false"
                                class="w-12 h-12 flex items-center justify-center rounded-full transition-all cursor-pointer relative group"
                                :class="open
                                    ? 'bg-gold-500 text-brand-nav'
                                    : 'bg-black/5 text-brand-nav/70 hover:bg-black/10 dark:bg-white/10 dark:text-brand-silver dark:hover:bg-white/20'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                            </svg>

                            @if($currentKat && $currentKat !== 'all')
                                <span class="absolute top-3 right-3 w-2 h-2 bg-gold-500 rounded-full border border-white dark:border-brand-sidebar" x-show="!open"></span>
                            @endif
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 top-full mt-4 w-60 bg-brand-card dark:bg-[#1a1a1a] border border-brand-borderSoft/80 dark:border-white/15 rounded-2xl shadow-2xl overflow-hidden py-2 z-[60] ring-1 ring-black/5 dark:ring-white/10"
                             style="display: none;">

                            <div class="px-4 py-3 border-b border-brand-borderSoft/40 dark:border-white/10 bg-black/5 dark:bg-white/5 mb-1">
                                <p class="text-[10px] font-bold text-brand-nav/60 dark:text-brand-silver uppercase tracking-widest">
                                    Filter Kategori
                                </p>
                            </div>

                            <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                                <a href="{{ route('member.produk_gym.index', ['q' => $currentQ]) }}"
                                   class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ !$currentKat || $currentKat == 'all' ? 'bg-gold-500 text-brand-nav font-bold' : 'text-brand-nav dark:text-white hover:bg-black/5 dark:hover:bg-white/10' }}">
                                    <span>Semua Kategori</span>
                                    @if(!$currentKat || $currentKat == 'all')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    @endif
                                </a>

                                @foreach($kategoriOptions as $cat)
                                    <a href="{{ route('member.produk_gym.index', ['kategori' => $cat, 'q' => $currentQ]) }}"
                                       class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $currentKat == $cat ? 'bg-gold-500 text-brand-nav font-bold' : 'text-brand-nav dark:text-white hover:bg-black/5 dark:hover:bg-white/10' }}">
                                        <span>{{ $cat }}</span>
                                        @if($currentKat == $cat)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Cart Button with live counter --}}
                    <a href="{{ route('member.produk_gym.cart') }}"
                       class="relative flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all shadow-lg hover:shadow-gold-glow group"
                       title="Lihat Keranjang Belanja">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform">
                            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                        </svg>

                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-white dark:border-brand-sidebar shadow-sm font-bold animate-bounce-slow">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                </div>

                {{-- Active Filter Pills --}}
                @if($currentQ || ($currentKat && $currentKat !== 'all'))
                    <div class="flex justify-center gap-2 mt-3 animate-fade-in-up">
                        <span class="text-xs text-text-muted py-1 drop-shadow-sm">Filter:</span>

                        @if($currentKat && $currentKat !== 'all')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold text-brand-nav bg-gold-500 shadow-sm">
                                {{ $currentKat }}
                            </span>
                        @endif

                        @if($currentQ)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold text-brand-nav dark:text-white bg-white/70 dark:bg-black/50 border border-brand-borderSoft backdrop-blur-md">
                                "{{ $currentQ }}"
                            </span>
                        @endif

                        <a href="{{ route('member.produk_gym.index') }}"
                           class="text-[10px] font-bold text-red-500 hover:text-red-400 py-0.5 underline decoration-red-500/50 ml-1">
                            Reset
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================================================================== --}}
        {{-- 3. PRODUCT CATALOG GRID (4 CARDS DALAM 1 BARIS, WARNA CARD DEFAULT) --}}
        {{-- ==================================================================== --}}
        <section class="pb-24 relative z-10 pt-4">

            {{-- Small Section Header --}}
            <div class="flex items-end justify-between mb-8 pb-4 border-b border-brand-borderSoft/35 dark:border-white/10">
                <div>
                    <h3 class="text-xl font-display font-bold text-brand-nav dark:text-white">KATALOG PRODUK</h3>
                    <p class="text-xs text-text-muted dark:text-brand-silver/70 mt-1">Stok terupdate secara real-time untuk seluruh member.</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-text-muted dark:text-brand-silver/60 uppercase tracking-widest">TOTAL</p>
                    <p class="text-xl font-display font-bold text-brand-nav dark:text-white leading-none">
                        {{ $totalItems }}
                        <span class="text-sm font-sans text-text-muted dark:text-brand-silver/50 font-normal">ITEM</span>
                    </p>
                </div>
            </div>

            @if($pageCount === 0)
                <div class="py-32 text-center rounded-[2rem] bg-brand-card/70 dark:bg-[#181b22]/50 border border-dashed border-brand-borderSoft/50 dark:border-white/15 backdrop-blur-sm">
                    <div class="w-24 h-24 bg-white/60 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner border border-brand-borderSoft/40 dark:border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-text-muted/40 dark:text-brand-silver/30">
                            <path d="m21 21-4.3-4.3"/><path d="M11 8h.01"/><circle cx="11" cy="11" r="8"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold font-display text-brand-nav dark:text-white mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-text-muted dark:text-brand-silver/70 text-sm max-w-md mx-auto mb-8 leading-relaxed">
                        Kami tidak dapat menemukan produk yang sesuai dengan kriteria Anda. <br> Coba gunakan kata kunci yang lebih umum.
                    </p>
                    <a href="{{ route('member.produk_gym.index') }}"
                       class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-brand-nav text-white hover:bg-gold-500 hover:text-brand-nav dark:bg-white dark:text-brand-nav dark:hover:bg-gold-500 transition-all font-bold text-sm shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                        </svg>
                        Reset Pencarian
                    </a>
                </div>
            @else

                {{-- EXACTLY 4 CARDS PER ROW ON DESKTOP --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-7">
                    @foreach($products as $p)
                        @php
                            $slug = $p->id . '-' . Str::slug($p->nama ?? 'produk');
                            $imageUrl = $imgUrl($p->foto ?? '', $p->nama ?? 'PRODUK');
                            $stok = (int)($p->stok ?? 0);
                            $ready = $stok > 0;
                        @endphp

                        {{-- PRODUCT CARD (WARNA CARD SESUAI DEFAULT MEMBER, TIDAK GELAP) --}}
                        <div class="group relative rounded-2xl sm:rounded-3xl overflow-hidden flex flex-col
                                    bg-brand-card dark:bg-[#181b22]
                                    border border-brand-borderSoft/80 dark:border-white/15 hover:border-gold-500/60
                                    transition-all duration-300
                                    shadow-md hover:shadow-xl dark:shadow-[0_10px_28px_rgba(0,0,0,0.7)]
                                    hover:-translate-y-2">

                            {{-- Image Container (Fixed 3:4 Aspect Ratio) --}}
                            <a href="{{ route('member.produk_gym.show', $slug) }}" class="relative w-full aspect-[3/4] bg-brand-surface-100 dark:bg-[#12141a] block overflow-hidden border-b border-brand-borderSoft/30 dark:border-white/[0.08]">

                                {{-- Floating Kategori Badge --}}
                                @if(!empty($p->kategori))
                                    <div class="absolute top-3 left-3 z-20">
                                        <span class="px-2.5 py-1 bg-white/85 dark:bg-black/70 text-brand-nav dark:text-gold-400 border border-brand-borderSoft/50 dark:border-gold-500/30 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider rounded-full shadow-sm">
                                            {{ $p->kategori }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Floating Stock Status Badge --}}
                                <div class="absolute top-3 right-3 z-20">
                                    @if(!$ready)
                                        <span class="px-2.5 py-1 bg-red-500/15 text-red-600 dark:bg-red-950/90 dark:text-red-300 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider rounded-full border border-red-500/30 shadow-sm flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                        </span>
                                    @elseif($stok <= 3)
                                        <span class="px-2.5 py-1 bg-amber-500/15 text-amber-700 dark:bg-amber-950/90 dark:text-amber-300 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider rounded-full border border-amber-500/30 shadow-sm flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Sisa {{ $stok }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Product Image with Smooth Zoom --}}
                                <img src="{{ $imageUrl }}"
                                     alt="{{ $p->nama }}"
                                     class="w-full h-full object-cover object-center
                                            group-hover:scale-110 transition-all duration-700 ease-out"
                                     loading="lazy">

                                {{-- Hover Quick View Indicator --}}
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none bg-black/10 dark:bg-black/30">
                                    <div class="w-11 h-11 rounded-full bg-gold-500 text-brand-nav flex items-center justify-center shadow-[0_4px_20px_rgba(234,179,8,0.6)] transform scale-75 group-hover:scale-100 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>

                            {{-- Card Body --}}
                            <div class="p-4 sm:p-5 flex flex-col flex-grow bg-brand-card/90 dark:bg-[#15171e] relative z-10">

                                {{-- Product Title --}}
                                <a href="{{ route('member.produk_gym.show', $slug) }}" class="block mb-2.5">
                                    <h3 class="text-sm sm:text-base font-bold text-brand-nav dark:text-white leading-snug line-clamp-2 h-[2.75rem] group-hover:text-gold-600 dark:group-hover:text-gold-400 transition-colors"
                                        title="{{ $p->nama }}">
                                        {{ $p->nama }}
                                    </h3>
                                </a>

                                {{-- Stock Indicator --}}
                                <div class="mb-3">
                                    @if($ready)
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 px-2.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-400/20 animate-pulse"></span> Stok: {{ $stok }} unit
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-red-600 dark:text-red-400 bg-red-500/10 border border-red-500/25 px-2.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Stok Habis
                                        </span>
                                    @endif
                                </div>

                                {{-- Price & Action Button --}}
                                <div class="mt-auto pt-3 border-t border-brand-borderSoft/50 dark:border-white/10 flex items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] uppercase font-extrabold tracking-wider text-text-muted dark:text-brand-silver/60 block">Harga</span>
                                        <span class="text-base sm:text-lg font-display font-extrabold text-brand-nav dark:text-white group-hover:text-gold-600 dark:group-hover:text-gold-300 transition-colors">
                                            Rp {{ number_format((int)($p->harga ?? 0), 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Action Circle Button --}}
                                    <a href="{{ route('member.produk_gym.show', $slug) }}"
                                       class="w-10 h-10 rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold flex items-center justify-center transition-all duration-300 shadow-md hover:scale-110 flex-shrink-0 hover:shadow-gold-glow"
                                       title="Lihat Detail Produk">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300 group-hover:translate-x-0.5">
                                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- ========================================================= --}}
                {{-- 4. PAGINATION --}}
                {{-- ========================================================= --}}
                @if(method_exists($products, 'hasPages') && $products->hasPages())
                    <div class="mt-16 flex flex-col md:flex-row items-center justify-between border-t border-brand-borderSoft/35 dark:border-white/10 pt-8 gap-4">

                        <div class="text-xs text-text-muted dark:text-brand-silver">
                            Menampilkan <span class="font-bold text-brand-nav dark:text-white">{{ $products->firstItem() }}</span> - <span class="font-bold text-brand-nav dark:text-white">{{ $products->lastItem() }}</span> dari <span class="font-bold text-brand-nav dark:text-white">{{ $products->total() }}</span>
                        </div>

                        <div class="bg-brand-card dark:bg-[#181b22] border border-brand-borderSoft dark:border-white/15 rounded-lg p-1 flex items-center shadow-md">
                            {{-- Previous --}}
                            @if ($products->onFirstPage())
                                <span class="px-3 py-2 text-text-muted/40 dark:text-brand-silver/30 cursor-not-allowed flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Prev</span>
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="px-3 py-2 text-text-muted hover:text-brand-nav dark:text-brand-silver dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-md transition flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Prev</span>
                                </a>
                            @endif

                            <div class="w-[1px] h-4 bg-brand-borderSoft dark:bg-white/15 mx-1"></div>

                            {{-- Numbers --}}
                            <div class="flex items-center">
                                @foreach ($products->links()->elements as $element)
                                    @if (is_string($element))
                                        <span class="px-2 text-text-muted/50 text-xs">{{ $element }}</span>
                                    @endif
                                    @if (is_array($element))
                                        @foreach ($element as $page => $url)
                                            @if ($page == $products->currentPage())
                                                <span class="w-8 h-8 flex items-center justify-center rounded-md bg-gold-500 text-brand-nav font-bold text-xs shadow-sm">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-md text-text-muted hover:text-brand-nav dark:text-brand-silver dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 text-xs transition">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>

                            <div class="w-[1px] h-4 bg-brand-borderSoft dark:bg-white/15 mx-1"></div>

                            {{-- Next --}}
                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="px-3 py-2 text-text-muted hover:text-brand-nav dark:text-brand-silver dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 rounded-md transition flex items-center gap-1">
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Next</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="px-3 py-2 text-text-muted/40 dark:text-brand-silver/30 cursor-not-allowed flex items-center gap-1">
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Next</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </div>

                    </div>
                @endif

            @endif
        </section>

    </div>

</x-layouts.member>
