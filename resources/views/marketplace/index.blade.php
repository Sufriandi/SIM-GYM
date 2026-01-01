{{-- resources/views/marketplace/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    // 1. Hitung Cart
    $cartCount = count(session('cart', []));

    // 2. Data Helper
    $kategoriOptions = $categories ?? [];
    $currentQ = request('q');
    $currentKat = request('kategori');

    // 3. Helper Image Resolver
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/800x1066/1a1a1a/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');
        return Storage::url($path);
    };
@endphp

<x-layouts.guest :title="$pageTitle ?? 'Official Store'">

    {{-- ==================================================================== --}}
    {{-- 1. HERO SECTION (COMPACT & DARKER) --}}
    {{-- ==================================================================== --}}
    <section class="relative min-h-[50vh] flex flex-col items-center justify-center overflow-hidden pb-36 pt-16">

        {{-- Background Image --}}
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070&auto=format&fit=crop"
                 alt="Background Gym"
                 class="w-full h-full object-cover opacity-40">

            <div class="absolute inset-0 bg-gradient-to-b from-brand-dark/90 via-brand-dark/70 to-brand-dark"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 container mx-auto px-6 text-center animate-slide-up">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gold-500/30 bg-black/50 backdrop-blur-md mb-5 shadow-lg">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gold-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-gold-500"></span>
                </span>
                <span class="text-[9px] font-bold tracking-[0.25em] text-gold-500 uppercase font-heading">Official Marketplace</span>
            </div>

            {{-- Headline --}}
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-4 drop-shadow-2xl">
                ELITE <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-300 via-gold-500 to-gold-600">GEAR</span> <br>
                FOR ELITE <span class="italic text-brand-silver/70">PERFORMANCE</span>
            </h1>

            <p class="text-brand-silver/90 text-sm md:text-base leading-relaxed max-w-xl mx-auto drop-shadow-md font-medium">
                Koleksi peralatan premium, suplemen teruji, dan apparel eksklusif untuk member BETA GYM.
            </p>

        </div>
    </section>

    {{-- ==================================================================== --}}
    {{-- 2. STICKY FILTER BAR (COMPACT & CENTERED) --}}
    {{-- ==================================================================== --}}
    <div class="sticky top-24 z-50 px-4 -mt-24 pb-8 transition-all duration-300">
        <div class="container mx-auto max-w-2xl">

            {{-- Bar Container --}}
            <div class="bg-[#151515]/95 backdrop-blur-xl border border-brand-borderSoft/30 rounded-full p-1.5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] flex items-center gap-2 relative ring-1 ring-white/10">

                {{-- A. SEARCH ICON --}}
                <div class="pl-5 pr-2 text-brand-silver">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>

                {{-- B. SEARCH INPUT --}}
                <form action="{{ route('guest.marketplace.index') }}" method="GET" class="flex-grow min-w-0">
                    @if($currentKat && $currentKat !== 'all')
                        <input type="hidden" name="kategori" value="{{ $currentKat }}">
                    @endif
                    <input type="text" name="q" value="{{ $currentQ }}"
                           class="w-full h-12 bg-transparent border-none text-brand-white placeholder-brand-silver/40 text-sm font-medium focus:ring-0 px-0"
                           placeholder="Cari produk..."
                           autocomplete="off">
                </form>

                {{-- Separator --}}
                <div class="w-[1px] h-8 bg-brand-borderSoft/30 mx-1"></div>

                {{-- C. FILTER BUTTON --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false"
                            class="w-12 h-12 flex items-center justify-center rounded-full transition-all cursor-pointer relative group"
                            :class="open ? 'bg-gold-500 text-brand-nav' : 'bg-brand-surface-200/20 text-brand-silver hover:bg-brand-surface-200/40 hover:text-white'">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>

                        @if($currentKat && $currentKat !== 'all')
                            <span class="absolute top-3 right-3 w-2 h-2 bg-gold-500 rounded-full border border-brand-sidebar" x-show="!open"></span>
                        @endif
                    </button>

                    {{-- Dropdown Content --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 top-full mt-4 w-60 bg-[#1a1a1a] border border-brand-borderSoft/30 rounded-2xl shadow-2xl overflow-hidden py-2 z-[60] ring-1 ring-white/10"
                         style="display: none;">

                        <div class="px-4 py-3 border-b border-brand-borderSoft/10 bg-brand-surface-200/5 mb-1">
                            <p class="text-[10px] font-bold text-brand-silver uppercase tracking-widest">Filter Kategori</p>
                        </div>

                        <div class="max-h-64 overflow-y-auto custom-scrollbar p-1">
                            <a href="{{ route('guest.marketplace.index', ['q' => $currentQ]) }}"
                               class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ !$currentKat || $currentKat == 'all' ? 'bg-gold-500 text-brand-nav' : 'text-brand-white hover:bg-brand-surface-200/20' }}">
                                <span>Semua Kategori</span>
                                @if(!$currentKat || $currentKat == 'all') <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> @endif
                            </a>

                            @foreach($kategoriOptions as $cat)
                                <a href="{{ route('guest.marketplace.index', ['kategori' => $cat, 'q' => $currentQ]) }}"
                                   class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ $currentKat == $cat ? 'bg-gold-500 text-brand-nav' : 'text-brand-white hover:bg-brand-surface-200/20' }}">
                                    <span>{{ $cat }}</span>
                                    @if($currentKat == $cat) <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- D. CART BUTTON --}}
                <a href="{{ route('guest.marketplace.cart') }}"
                   class="relative flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all shadow-lg hover:shadow-gold-glow group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-brand-sidebar shadow-sm font-bold animate-bounce-slow">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

            </div>

            {{-- Active Filter Info --}}
            @if($currentQ || ($currentKat && $currentKat !== 'all'))
                <div class="flex justify-center gap-2 mt-3 animate-fade-in-up">
                    <span class="text-xs text-brand-silver py-1 shadow-black drop-shadow-md">Filter:</span>
                    @if($currentKat && $currentKat !== 'all')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-brand-nav bg-gold-500 shadow-lg">
                            {{ $currentKat }}
                        </span>
                    @endif
                    @if($currentQ)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-white bg-brand-black/50 border border-brand-borderSoft/30 backdrop-blur-md">
                            "{{ $currentQ }}"
                        </span>
                    @endif
                    <a href="{{ route('guest.marketplace.index') }}" class="text-[10px] font-bold text-red-400 hover:text-red-300 py-0.5 underline decoration-red-400/50 ml-1 shadow-black drop-shadow-md">Reset</a>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- 3. PRODUCT CATALOG GRID --}}
    {{-- ==================================================================== --}}
    <section class="pb-32 bg-brand-dark relative z-10 pt-8">
        <div class="container mx-auto px-6">

            {{-- Header Kecil --}}
            <div class="flex items-end justify-between mb-8 pb-4 border-b border-brand-borderSoft/10">
                <div>
                    <h3 class="text-xl font-display font-bold text-brand-white">KATALOG PRODUK</h3>
                    <p class="text-xs text-brand-silver mt-1">Stok terupdate secara real-time.</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-brand-silver uppercase tracking-widest">TOTAL</p>
                    <p class="text-xl font-display font-bold text-brand-white leading-none">{{ $products->total() }} <span class="text-sm font-sans text-brand-silver font-normal">ITEM</span></p>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="py-32 text-center rounded-[2rem] bg-brand-sidebar/20 border border-dashed border-brand-borderSoft/20 backdrop-blur-sm">
                    <div class="w-24 h-24 bg-brand-surface-200/10 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-silver/30"><path d="m21 21-4.3-4.3"/><path d="M11 8h.01"/><circle cx="11" cy="11" r="8"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold font-display text-brand-white mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-brand-silver text-sm max-w-md mx-auto mb-8 leading-relaxed">
                        Kami tidak dapat menemukan produk yang sesuai dengan kriteria Anda. <br> Coba gunakan kata kunci yang lebih umum.
                    </p>
                    <a href="{{ route('guest.marketplace.index') }}" class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-brand-white text-brand-nav hover:bg-gold-500 transition-all font-bold text-sm shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Reset Pencarian
                    </a>
                </div>
            @else

                {{-- GRID SYSTEM --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 lg:gap-8">
                    @foreach($products as $p)
                        @php
                            $slug = $p->id . '-' . Str::slug($p->nama ?? 'produk');
                            $imageUrl = $imgUrl($p->foto, $p->nama);
                            $ready = ((int)$p->stok) > 0;
                        @endphp

                        {{-- PRODUCT CARD (REVISI WARNA: ikut style "index coach" -> surface-card, lebih hitam & premium) --}}
                        <div class="group rounded-3xl overflow-hidden surface-card flex flex-col
                                    border border-brand-borderSoft/20 hover:border-gold-500/50
                                    transition-all duration-300 hover:shadow-[0_10px_30px_-5px_rgba(234,179,8,0.15)] hover:-translate-y-1">

                            {{-- Image Container (3:4 Ratio Fixed) --}}
                            <a href="{{ route('guest.marketplace.show', $slug) }}" class="relative w-full aspect-[3/4] bg-[#0b0b0b] block overflow-hidden">

                                {{-- Kategori Tag --}}
                                @if($p->kategori)
                                    <div class="absolute top-3 left-3 z-20">
                                        <span class="px-2 py-1 bg-black/60 backdrop-blur text-white text-[9px] font-bold uppercase tracking-wider rounded shadow-sm border border-white/10">
                                            {{ $p->kategori }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Image --}}
                                <img src="{{ $imageUrl }}"
                                     alt="{{ $p->nama }}"
                                     class="w-full h-full object-cover object-center brightness-95
                                            group-hover:brightness-110 group-hover:scale-[1.06]
                                            transition duration-700"
                                     loading="lazy">

                                {{-- Overlay (lebih “coach index” style) --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>
                            </a>

                            {{-- Card Content (tanpa bg-brand-sidebar biar ikut surface-card) --}}
                            <div class="p-4 flex flex-col flex-grow border-t border-brand-borderSoft/10 relative z-10">

                                {{-- Title --}}
                                <a href="{{ route('guest.marketplace.show', $slug) }}" class="block mb-2">
                                    <h3 class="text-sm font-bold text-brand-white leading-snug line-clamp-2 h-[2.5rem] group-hover:text-gold-500 transition-colors" title="{{ $p->nama }}">
                                        {{ $p->nama }}
                                    </h3>
                                </a>

                                {{-- Price & Stock Row --}}
                                <div class="mt-auto pt-3 border-t border-brand-borderSoft/10">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-base font-display font-bold text-brand-white">
                                            Rp {{ number_format((int)$p->harga, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Stock Info --}}
                                    <div class="flex items-center justify-between">
                                        @if($ready)
                                            <span class="text-[10px] font-bold text-success flex items-center gap-1 bg-success/10 px-2 py-0.5 rounded">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Stok: {{ $p->stok }}
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-red-500 flex items-center gap-1 bg-red-500/10 px-2 py-0.5 rounded">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                            </span>
                                        @endif

                                        {{-- Arrow Icon --}}
                                        <a href="{{ route('guest.marketplace.show', $slug) }}" class="text-brand-silver hover:text-gold-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ========================================================= --}}
                {{-- 4. CUSTOM PAGINATION --}}
                {{-- ========================================================= --}}
                @if ($products->hasPages())
                    <div class="mt-16 flex flex-col md:flex-row items-center justify-between border-t border-brand-borderSoft/10 pt-8 gap-4">

                        <div class="text-xs text-brand-silver">
                            Menampilkan <span class="font-bold text-white">{{ $products->firstItem() }}</span> - <span class="font-bold text-white">{{ $products->lastItem() }}</span> dari <span class="font-bold text-white">{{ $products->total() }}</span>
                        </div>

                        <div class="bg-brand-sidebar border border-brand-borderSoft/20 rounded-lg p-1 flex items-center shadow-lg">
                            {{-- Previous --}}
                            @if ($products->onFirstPage())
                                <span class="px-3 py-2 text-brand-silver/30 cursor-not-allowed flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Prev</span>
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="px-3 py-2 text-brand-silver hover:text-white hover:bg-brand-surface-200/20 rounded-md transition flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Prev</span>
                                </a>
                            @endif

                            <div class="w-[1px] h-4 bg-brand-borderSoft/20 mx-1"></div>

                            {{-- Numbers --}}
                            <div class="flex items-center">
                                @foreach ($products->links()->elements as $element)
                                    @if (is_string($element))
                                        <span class="px-2 text-brand-silver/50 text-xs">{{ $element }}</span>
                                    @endif
                                    @if (is_array($element))
                                        @foreach ($element as $page => $url)
                                            @if ($page == $products->currentPage())
                                                <span class="w-8 h-8 flex items-center justify-center rounded-md bg-gold-500 text-brand-nav font-bold text-xs shadow-sm mx-0.5">{{ $page }}</span>
                                            @else
                                                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-md text-brand-silver hover:text-white hover:bg-brand-surface-200/20 transition text-xs mx-0.5">{{ $page }}</a>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>

                            <div class="w-[1px] h-4 bg-brand-borderSoft/20 mx-1"></div>

                            {{-- Next --}}
                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="px-3 py-2 text-brand-silver hover:text-white hover:bg-brand-surface-200/20 rounded-md transition flex items-center gap-1">
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Next</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="px-3 py-2 text-brand-silver/30 cursor-not-allowed flex items-center gap-1">
                                    <span class="hidden sm:inline text-xs font-bold uppercase">Next</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </section>

</x-layouts.guest>
