{{-- resources/views/member/produk_gym/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $product = $product ?? null;
    $cartCount = count(session('cart', []));

    $imgUrl = function ($path, $fallbackText) {
        $path = trim((string) $path);
        if ($path === '') return 'https://placehold.co/1000x1333/f6efe4/cca43b?text=' . urlencode($fallbackText ?: 'PRODUK') . '&font=raleway';
        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');

        return Storage::url($path);
    };

    $imageUrl = $imgUrl($product?->foto, $product?->nama ?? 'PRODUK');
    $stok  = (int)($product?->stok ?? 0);
    $ready = $stok > 0;

    $kategori = (string)($product?->kategori ?? '');
@endphp

<x-layouts.member :pageTitle="($product?->nama ?? 'Detail Produk')">

    <style>
        /* Surface mengikuti marketplace member (cream di light, glass di dark) */
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

        .dark .surface-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(212,167,87,.14);
            box-shadow: 0 18px 50px rgba(0,0,0,.45);
        }
        .dark .surface-card:hover{
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }

        .chip-soft{
            background: rgba(255,255,255,.55);
            border: 1px solid rgba(212,167,87,.20);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .chip-soft{
            background: rgba(0,0,0,.22);
            border: 1px solid rgba(212,167,87,.14);
        }
    </style>

    {{-- Wrapper: biarkan tinggi “mengikuti konten” supaya BODY scrollbar bekerja normal --}}
    <section class="relative">

        {{-- Background halus (ABSOLUTE, bukan FIXED) supaya tidak mengganggu scroll --}}
        <div class="absolute inset-0 -z-10 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-b from-brand-shell/60 via-transparent to-transparent dark:from-black/30"></div>
            <div class="absolute top-[-120px] right-[-120px] w-[520px] h-[520px] bg-gold-500/10 rounded-full blur-[120px] opacity-70"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-10">

            {{-- Top: breadcrumb + cart --}}
            <div class="flex items-center justify-between gap-4 sm:gap-6 mb-8 pb-4 border-b border-brand-borderSoft/35 dark:border-brand-borderSoft/10 min-w-0">

                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.15em] text-brand-nav/55 dark:text-brand-silver/60 min-w-0 flex-1">
                    <a href="{{ route('member.produk_gym.index') }}"
                       class="hover:text-gold-600 dark:hover:text-gold-500 flex items-center gap-1 transition-colors flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Marketplace
                    </a>
                    <span class="text-brand-borderSoft/55 dark:text-brand-borderSoft/40 flex-shrink-0">/</span>
                    <span class="text-brand-nav/80 dark:text-brand-silver truncate min-w-0">
                        {{ $kategori ?: 'Produk' }}
                    </span>
                </nav>

                <a href="{{ route('member.produk_gym.cart') }}"
                   class="relative w-11 h-11 flex items-center justify-center rounded-full
                          bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all
                          shadow-lg hover:shadow-gold-glow group flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full border-2 border-white dark:border-brand-sidebar shadow-sm font-bold">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-14 items-start">

                {{-- Image --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24">
                    <div class="relative w-full aspect-[3/4] rounded-3xl overflow-hidden surface-card group">
                        <img src="{{ $imageUrl }}"
                             alt="{{ $product?->nama }}"
                             class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent dark:from-black/70"></div>

                        @if($kategori)
                            <div class="absolute top-4 left-4 z-20">
                                <span class="px-3 py-1 bg-white/70 dark:bg-black/55 backdrop-blur-md
                                             border border-brand-borderSoft/35 dark:border-white/10
                                             text-brand-nav dark:text-white text-[10px] font-bold uppercase tracking-[0.15em] rounded-full">
                                    {{ $kategori }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Details --}}
                <div class="lg:col-span-8 min-w-0">

                    <div class="mb-5 min-w-0">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-display font-black
                                   text-brand-nav dark:text-white leading-[1.05] tracking-tight break-words">
                            {{ $product?->nama }}
                        </h1>
                        <p class="text-brand-nav/45 dark:text-brand-silver/55 text-[10px] font-mono tracking-widest uppercase mt-2">
                            SKU: {{ str_pad((int)($product?->id ?? 0), 5, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-5 mb-7 border-b border-brand-borderSoft/35 dark:border-brand-borderSoft/10 pb-7">
                        <div class="min-w-0">
                            <span class="text-2xl lg:text-3xl font-display font-bold text-brand-nav dark:text-gold-500 tracking-wide">
                                Rp {{ number_format((int)($product?->harga ?? 0), 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="w-[1px] h-8 bg-brand-borderSoft/45 dark:bg-brand-borderSoft/20"></div>

                        <div class="flex items-center gap-2">
                            @if($ready)
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                <div>
                                    <p class="text-[10px] font-bold text-green-600 dark:text-green-400 uppercase tracking-wider leading-none">Ready</p>
                                    <p class="text-[10px] text-brand-nav/55 dark:text-brand-silver mt-0.5">Sisa {{ $stok }}</p>
                                </div>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                <div>
                                    <p class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider leading-none">Habis</p>
                                    <p class="text-[10px] text-brand-nav/55 dark:text-brand-silver mt-0.5">Restock Soon</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-9">
                        <h3 class="text-xs font-bold text-brand-nav dark:text-white uppercase tracking-[0.2em] mb-4">
                            Product Details
                        </h3>
                        <div class="text-brand-nav/70 dark:text-brand-silver/80 text-sm leading-relaxed text-justify">
                            {{ $product?->deskripsi ?: 'Produk ini dirancang untuk memberikan performa maksimal. Material berkualitas tinggi menjamin ketahanan untuk penggunaan jangka panjang di gym maupun latihan mandiri.' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-10">
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Authentic</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">100% Original</p>
                        </div>
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Pickup</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">Ambil di Gym</p>
                        </div>
                        <div class="p-3 rounded-2xl chip-soft text-center">
                            <p class="text-gold-600 dark:text-gold-500 text-xs font-bold uppercase mb-1">Secure</p>
                            <p class="text-[10px] text-brand-nav/60 dark:text-brand-silver">Transfer / QRIS</p>
                        </div>
                    </div>

                    {{-- CTA (mobile fixed, desktop static) --}}
                    <div class="lg:static">
                        {{-- Mobile fixed bar --}}
                        <div class="fixed bottom-0 left-0 right-0 z-[90] lg:hidden
                                    bg-brand-shell/92 dark:bg-black/70 backdrop-blur-md
                                    border-t border-brand-borderSoft/35 dark:border-brand-borderSoft/15 px-4 py-4">
                            @if($ready)
                                <form action="{{ route('member.produk_gym.cart.add', $product->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-sm uppercase tracking-widest
                                                   rounded-full transition-all shadow-[0_4px_20px_-5px_rgba(234,179,8,0.40)]
                                                   hover:shadow-[0_8px_30px_-5px_rgba(234,179,8,0.50)]">
                                        Masukkan Keranjang
                                    </button>
                                </form>
                            @else
                                <button disabled
                                        class="w-full py-4 bg-black/10 dark:bg-white/10 text-brand-nav/35 dark:text-brand-silver/30
                                               font-bold text-sm uppercase tracking-widest rounded-full cursor-not-allowed
                                               border border-brand-borderSoft/25 dark:border-brand-borderSoft/10">
                                    Stok Habis
                                </button>
                            @endif
                        </div>

                        {{-- Desktop inline --}}
                        <div class="hidden lg:block">
                            @if($ready)
                                <form action="{{ route('member.produk_gym.cart.add', $product->id) }}" method="POST" class="w-full max-w-md">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-4 bg-gold-500 hover:bg-gold-400 text-brand-nav font-bold text-sm uppercase tracking-widest
                                                   rounded-full transition-all shadow-[0_4px_20px_-5px_rgba(234,179,8,0.35)]
                                                   hover:shadow-[0_8px_30px_-5px_rgba(234,179,8,0.45)]">
                                        Masukkan Keranjang
                                    </button>
                                </form>
                            @else
                                <button disabled
                                        class="w-full max-w-md py-4 bg-black/10 dark:bg-white/10 text-brand-nav/35 dark:text-brand-silver/30
                                               font-bold text-sm uppercase tracking-widest rounded-full cursor-not-allowed
                                               border border-brand-borderSoft/25 dark:border-brand-borderSoft/10">
                                    Stok Habis
                                </button>
                            @endif
                        </div>

                        {{-- Spacer agar konten tidak ketutup fixed CTA di mobile --}}
                        <div class="h-24 lg:hidden"></div>
                    </div>

                </div>
            </div>

            {{-- Related Products --}}
            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                <div class="border-t border-brand-borderSoft/35 dark:border-brand-borderSoft/10 pt-14 mt-14">
                    <div class="flex items-end justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-xl font-display font-bold text-brand-nav dark:text-white mb-1">ITEM SEJENIS</h3>
                            <p class="text-brand-nav/55 dark:text-brand-silver text-xs">Lengkapi perlengkapan latihan Anda.</p>
                        </div>
                        <a href="{{ route('member.produk_gym.index') }}"
                           class="group flex items-center gap-2 text-gold-600 dark:text-gold-500 text-xs font-bold hover:text-brand-nav dark:hover:text-white transition-colors flex-shrink-0">
                            LIHAT SEMUA
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $related)
                            @php
                                $rSlug = $related->id . '-' . Str::slug($related->nama ?? 'produk');
                                $rImg = $imgUrl($related->foto, $related->nama);
                                $rReady = ((int)($related->stok ?? 0)) > 0;
                            @endphp

                            <a href="{{ route('member.produk_gym.show', $rSlug) }}"
                               class="group rounded-3xl overflow-hidden surface-card flex flex-col">

                                <div class="relative w-full aspect-[3/4] bg-black/5 dark:bg-[#0b0b0b] overflow-hidden">
                                    <img src="{{ $rImg }}"
                                         alt="{{ $related->nama }}"
                                         class="w-full h-full object-cover object-center brightness-[0.98] dark:brightness-95
                                                group-hover:brightness-105 group-hover:scale-[1.06]
                                                transition duration-700"
                                         loading="lazy">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-black/0 to-transparent dark:from-black/75"></div>

                                    @if(!$rReady)
                                        <div class="absolute top-3 right-3 px-2 py-1 bg-red-600/90 backdrop-blur text-white text-[9px] font-bold uppercase tracking-wider rounded">
                                            Habis
                                        </div>
                                    @endif

                                    @if(!empty($related->kategori))
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2 py-1 bg-white/70 text-brand-nav border border-brand-borderSoft/35
                                                         dark:bg-black/60 dark:text-white dark:border-white/10
                                                         backdrop-blur text-[9px] font-bold uppercase tracking-wider rounded">
                                                {{ $related->kategori }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4 flex flex-col flex-1 border-t border-brand-borderSoft/25 dark:border-brand-borderSoft/10">
                                    <h4 class="text-brand-nav dark:text-white font-bold text-sm line-clamp-2 min-h-[2.5rem] group-hover:text-gold-600 dark:group-hover:text-gold-500 transition-colors">
                                        {{ $related->nama }}
                                    </h4>

                                    <div class="mt-3 pt-3 border-t border-brand-borderSoft/25 dark:border-brand-borderSoft/10 flex items-center justify-between gap-2">
                                        <p class="text-brand-nav dark:text-white font-bold font-display text-sm">
                                            Rp {{ number_format((int)($related->harga ?? 0), 0, ',', '.') }}
                                        </p>

                                        @if($rReady)
                                            <span class="text-[9px] font-bold text-green-700 dark:text-green-400 bg-green-500/10 px-2 py-0.5 rounded">
                                                Stok {{ (int)($related->stok ?? 0) }}
                                            </span>
                                        @else
                                            <span class="text-[9px] font-bold text-red-600 dark:text-red-400 bg-red-500/10 px-2 py-0.5 rounded">
                                                0 Unit
                                            </span>
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

</x-layouts.member>
