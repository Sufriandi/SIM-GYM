{{-- resources/views/marketplace/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    // --- DATA PROCESSING ---
    $product = $product ?? null;
    $cartCount = count(session('cart', []));

    // --- HELPER IMAGE ---
    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/1000x1333/151515/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');
        return Storage::url($path);
    };

    $imageUrl = $imgUrl($product?->foto, $product?->nama ?? 'PRODUK');
    $stok = (int)($product?->stok ?? 0);
    $ready = $stok > 0;
@endphp

<x-layouts.guest :title="($product->nama ?? 'Detail Produk') . ' – BETA GYM'">

    {{-- ==================================================================== --}}
    {{-- PREMIUM BACKGROUND --}}
    {{-- ==================================================================== --}}
    <div class="fixed inset-0 pointer-events-none z-0 bg-[#0a0a0a]">
        <div class="absolute top-[-20%] right-[10%] w-[800px] h-[800px] bg-gold-500/5 rounded-full blur-[180px] opacity-60"></div>
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+');"></div>
    </div>

    <section class="relative min-h-screen pt-24 pb-32 z-10">
        <div class="container mx-auto px-6 lg:px-12">
            
            {{-- ==================================================================== --}}
            {{-- 1. PAGE HEADER --}}
            {{-- ==================================================================== --}}
            <div class="flex items-center justify-between gap-6 mb-10 pb-4 border-b border-brand-borderSoft/10 animate-slide-up">
                
                {{-- A. Breadcrumb (Clean) --}}
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.15em] text-brand-silver/60">
                    <a href="{{ route('guest.marketplace.index') }}" class="hover:text-gold-500 flex items-center gap-1 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        Marketplace
                    </a>
                    <span class="text-brand-borderSoft/40">/</span>
                    <span class="text-brand-silver">{{ $product->kategori }}</span>
                </nav>

                {{-- B. Cart Button (REVISI: Icon Bulat Simple seperti Index) --}}
                <a href="{{ route('guest.marketplace.cart') }}" 
                   class="relative w-11 h-11 flex items-center justify-center rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all shadow-lg hover:shadow-gold-glow group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full border-2 border-brand-dark shadow-sm font-bold">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- ==================================================================== --}}
            {{-- 2. MAIN PRODUCT LAYOUT --}}
            {{-- ==================================================================== --}}
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-start">
                
                {{-- LEFT COLUMN: PRODUCT VISUAL (4 Cols) --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24 animate-slide-up" style="animation-duration: 0.6s">
                    <div class="relative w-full aspect-[3/4] bg-[#151515] rounded-2xl overflow-hidden border border-brand-borderSoft/10 shadow-2xl group">
                        
                        <img src="{{ $imageUrl }}" 
                             alt="{{ $product->nama }}" 
                             class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105 cursor-zoom-in"
                             onclick="window.open(this.src, '_blank')">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="px-3 py-1 bg-brand-black/60 backdrop-blur-md border border-brand-white/10 text-white text-[10px] font-bold uppercase tracking-[0.15em] rounded">
                                {{ $product->kategori }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: PRODUCT DETAILS (8 Cols) --}}
                <div class="lg:col-span-8 flex flex-col h-full animate-slide-up" style="animation-duration: 0.8s">
                    
                    {{-- A. Title --}}
                    <div class="mb-6">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-display font-black text-white leading-[0.95] uppercase tracking-tight mb-2">
                            {{ $product->nama }}
                        </h1>
                        <p class="text-brand-silver/50 text-[10px] font-mono tracking-widest uppercase">
                            SKU: {{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    {{-- B. Price & Stock (Compact) --}}
                    <div class="flex items-center gap-6 mb-8 border-b border-brand-borderSoft/10 pb-8">
                        <div>
                            <span class="text-3xl lg:text-4xl font-display font-bold text-gold-500 tracking-wide">
                                Rp {{ number_format((int)($product->harga ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-[1px] h-8 bg-brand-borderSoft/20"></div>
                        <div class="flex items-center gap-2">
                            @if($ready)
                                <span class="relative flex h-2.5 w-2.5">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                </span>
                                <div>
                                    <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider leading-none">Ready</p>
                                    <p class="text-[10px] text-brand-silver mt-0.5">Sisa {{ $stok }}</p>
                                </div>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                <div>
                                    <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider leading-none">Habis</p>
                                    <p class="text-[10px] text-brand-silver mt-0.5">Restock Soon</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- C. Description --}}
                    <div class="mb-10">
                        <h3 class="text-xs font-bold text-white uppercase tracking-[0.2em] mb-4">
                            Product Details
                        </h3>
                        <div class="prose prose-invert prose-p:text-brand-silver/80 prose-p:text-sm prose-p:leading-relaxed max-w-none text-justify">
                            <p>{{ $product->deskripsi ?: 'Produk ini dirancang untuk memberikan performa maksimal. Material berkualitas tinggi menjamin ketahanan untuk penggunaan jangka panjang di gym maupun latihan mandiri.' }}</p>
                        </div>
                    </div>

                    {{-- D. Features --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-10">
                        <div class="p-3 border border-brand-borderSoft/10 rounded-lg bg-brand-surface-200/5 text-center">
                            <p class="text-gold-500 text-xs font-bold uppercase mb-1">Authentic</p>
                            <p class="text-[10px] text-brand-silver">100% Original</p>
                        </div>
                        <div class="p-3 border border-brand-borderSoft/10 rounded-lg bg-brand-surface-200/5 text-center">
                            <p class="text-gold-500 text-xs font-bold uppercase mb-1">Pickup</p>
                            <p class="text-[10px] text-brand-silver">Ambil di Gym</p>
                        </div>
                        <div class="p-3 border border-brand-borderSoft/10 rounded-lg bg-brand-surface-200/5 text-center">
                            <p class="text-gold-500 text-xs font-bold uppercase mb-1">Secure</p>
                            <p class="text-[10px] text-brand-silver">Transfer / QRIS</p>
                        </div>
                    </div>

                    {{-- E. ACTION BUTTON (REVISI: CAPSULE & SLEEK) --}}
                    {{-- Sticky on Mobile, Static on Desktop --}}
                    <div class="fixed bottom-0 left-0 w-full bg-[#0a0a0a]/90 backdrop-blur-md border-t border-brand-borderSoft/20 p-4 z-50 lg:static lg:bg-transparent lg:border-none lg:p-0 lg:z-auto">
                        <div class="container mx-auto lg:px-0">
                            
                            @if($ready)
                                <form action="{{ url('/cart/add/'.$product->id) }}" method="POST" class="w-full">
                                    @csrf
                                    {{-- TOMBOL CAPSULE: Lebih modern, tidak kotak kaku --}}
                                    <button type="submit" class="group relative w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-sm uppercase tracking-widest rounded-full transition-all duration-300 shadow-[0_4px_20px_-5px_rgba(234,179,8,0.4)] hover:shadow-[0_8px_30px_-5px_rgba(234,179,8,0.5)] hover:-translate-y-0.5 overflow-hidden flex items-center justify-center gap-3">
                                        <span>Masukkan Keranjang</span>
                                        {{-- Icon Bergeser saat Hover --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300 group-hover:translate-x-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full py-4 bg-brand-surface-200/20 text-brand-silver/30 font-bold text-sm uppercase tracking-widest rounded-full cursor-not-allowed border border-brand-borderSoft/10">
                                    Stok Habis
                                </button>
                            @endif

                        </div>
                    </div>
                    
                    {{-- Mobile Spacer --}}
                    <div class="lg:hidden h-20"></div>

                </div>
            </div>

            {{-- ==================================================================== --}}
            {{-- 3. ITEM SEJENIS (CONSISTENT DESIGN) --}}
            {{-- ==================================================================== --}}
            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                <div class="border-t border-brand-borderSoft/10 pt-20 mt-20 animate-slide-up" style="animation-delay: 0.2s">
                    <div class="flex items-end justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-display font-bold text-white mb-1">ITEM SEJENIS</h3>
                            <p class="text-brand-silver text-xs">Lengkapi perlengkapan latihan Anda.</p>
                        </div>
                        <a href="{{ route('guest.marketplace.index') }}" class="group flex items-center gap-2 text-gold-500 text-xs font-bold hover:text-white transition-colors">
                            LIHAT SEMUA 
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $related)
                            @php
                                $rSlug = $related->id . '-' . Str::slug($related->nama ?? 'produk');
                                $rImg = $imgUrl($related->foto, $related->nama);
                                $rReady = ((int)$related->stok) > 0;
                            @endphp
                            
                            {{-- CARD PREMIUM (Same as Index) --}}
                            <a href="{{ route('guest.marketplace.show', $rSlug) }}" class="group block bg-brand-sidebar rounded-2xl overflow-hidden border border-brand-borderSoft/10 hover:border-gold-500/30 transition-all hover:-translate-y-1 hover:shadow-xl">
                                <div class="aspect-[3/4] bg-[#151515] overflow-hidden relative">
                                    <img src="{{ $rImg }}" alt="{{ $related->nama }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    @if(!$rReady)
                                        <div class="absolute top-3 right-3 px-2 py-1 bg-red-600/90 backdrop-blur text-white text-[9px] font-bold uppercase tracking-wider rounded">Habis</div>
                                    @endif
                                    <div class="absolute inset-0 bg-brand-black/10 group-hover:bg-brand-black/0 transition duration-500"></div>
                                </div>
                                
                                <div class="p-4">
                                    <p class="text-[9px] text-brand-silver/50 font-bold uppercase mb-1 tracking-wider">{{ $related->kategori ?: 'ITEM' }}</p>
                                    <h4 class="text-brand-white font-bold text-sm line-clamp-1 mb-3 group-hover:text-gold-500 transition-colors">{{ $related->nama }}</h4>
                                    
                                    <div class="pt-3 border-t border-brand-borderSoft/10 flex justify-between items-center">
                                        <p class="text-brand-white font-bold font-display text-sm">Rp {{ number_format((int)$related->harga, 0, ',', '.') }}</p>
                                        @if($rReady)
                                            <span class="text-[9px] font-bold text-green-400 bg-green-500/10 px-2 py-0.5 rounded">Stok {{ $related->stok }}</span>
                                        @else
                                            <span class="text-[9px] font-bold text-red-400 bg-red-500/10 px-2 py-0.5 rounded">0 Unit</span>
                                        @endif
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