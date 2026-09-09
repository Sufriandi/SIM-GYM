{{-- resources/views/member/produk_gym/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $product = $product ?? null;
    $cartCount = count(session('cart', []));

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
    $stok  = (int)($product?->stok ?? 0);
    $ready = $stok > 0;
@endphp

<x-layouts.member :pageTitle="($product->nama ?? 'Detail Produk') . ' – BETA GYM'" :pageSubtitle="''">

    <style>
        /* =========================================================
           FIX: Animate slide-up wajib jalan (tanpa reduce-motion disable)
           - Scoped agar tidak ketimpa CSS global
           - Inline style animation-duration tetap dipakai
        ========================================================== */
        @keyframes memberSlideUpSoft {
            from { opacity: 0; transform: translate3d(0, 16px, 0); }
            to   { opacity: 1; transform: translate3d(0, 0, 0); }
        }

        .member-product-show .animate-slide-up{
            animation-name: memberSlideUpSoft !important;
            animation-duration: .65s;
            animation-timing-function: cubic-bezier(.2,.8,.2,1) !important;
            animation-fill-mode: both !important;
            will-change: transform, opacity;
        }

        /* =========================================================
           LIGHT: background putih, card cream
           DARK : background gelap, card kembali dark-glass
        ========================================================== */
        :root {
            /* LIGHT */
            --card-cream-1: rgba(249, 242, 231, .92);
            --card-cream-2: rgba(245, 237, 223, .86);
            --card-border: rgba(212,167,87,.22);
            --card-shadow: rgba(0,0,0,.14);

            --chip-cream: rgba(249, 242, 231, .80);
            --chip-border: rgba(212,167,87,.18);

            --text-main: rgba(32, 25, 17, .95);
            --text-muted: rgba(108, 90, 70, .78);
            --divider: rgba(32, 25, 17, .14);

            --bottom-cream: rgba(255, 255, 255, .86);
            --bottom-border: rgba(212,167,87,.20);
        }

        .dark {
            /* DARK (original feel) */
            --card-cream-1: rgba(255,255,255,.06);
            --card-cream-2: rgba(255,255,255,.03);
            --card-border: rgba(212,167,87,.14);
            --card-shadow: rgba(0,0,0,.45);

            --chip-cream: rgba(0,0,0,.22);
            --chip-border: rgba(212,167,87,.14);

            --text-main: rgba(255,255,255,.92);
            --text-muted: rgba(226,232,240,.58);
            --divider: rgba(255,255,255,.16);

            --bottom-cream: rgba(10,10,10,.90);
            --bottom-border: rgba(255,255,255,.10);
        }

        .surface-card{
            background: linear-gradient(180deg, var(--card-cream-1), var(--card-cream-2));
            border: 1px solid var(--card-border);
            box-shadow: 0 18px 50px var(--card-shadow);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .surface-card:hover{
            transform: translateY(-2px);
            border-color: rgba(212,167,87,.30);
            box-shadow: 0 26px 70px rgba(0,0,0,.18);
        }
        .dark .surface-card:hover{
            border-color: rgba(212,167,87,.22);
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
        }

        .chip-soft{
            background: var(--chip-cream);
            border: 1px solid var(--chip-border);
        }

        .t-main{ color: var(--text-main); }
        .t-muted{ color: var(--text-muted); }
        .t-divider{ background: var(--divider); }

        .prose-produk p{
            color: var(--text-muted) !important;
        }

        .bottom-bar-cream{
            background: var(--bottom-cream);
            border-top: 1px solid var(--bottom-border);
            box-shadow: 0 -12px 35px rgba(0,0,0,.10);
        }
        .dark .bottom-bar-cream{
            box-shadow: 0 -12px 35px rgba(0,0,0,.35);
        }

        .related-media-bg{
            background: rgba(249, 242, 231, .55);
        }
        .dark .related-media-bg{
            background: #0b0b0b;
        }

        .badge-kat{
            background: rgba(0,0,0,.55);
            border: 1px solid rgba(255,255,255,.10);
            color: rgba(255,255,255,.95);
        }
    </style>

    {{-- Background: putih (light), gelap (dark). Card yang cream. --}}
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute inset-0 bg-white dark:bg-[#0a0a0a]"></div>

        {{-- Glow tipis agar white tidak flat --}}
        <div class="absolute top-[-20%] right-[10%] w-[800px] h-[800px] bg-gold-500/10 rounded-full blur-[180px] opacity-60 dark:bg-gold-500/5 dark:opacity-60"></div>
        <div class="absolute bottom-[-25%] left-[0%] w-[700px] h-[700px] bg-gold-500/8 rounded-full blur-[180px] opacity-55 dark:bg-gold-500/4 dark:opacity-55"></div>

        {{-- Noise --}}
        <div class="absolute inset-0 opacity-[0.06] dark:opacity-[0.02]"
             style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjMDAwIiBmaWxsLW9wYWNpdHk9IjAuMDMiLz4KPC9zdmc+');"></div>
        <div class="absolute inset-0 hidden dark:block opacity-[0.02]"
             style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+');"></div>
    </div>

    <section class="member-product-show relative min-h-screen pt-12 pb-32 z-10"
             x-data="{
                 qty: 1,
                 maxStock: {{ $stok }},
                 unitPrice: {{ (int)($product->harga ?? 0) }},
                 formatRupiah(v) {
                     return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
                 }
             }">
        <div class="container mx-auto px-6 lg:px-12">

            <div class="flex items-center justify-between gap-4 sm:gap-6 mb-10 pb-4 border-b border-brand-borderSoft/30 animate-slide-up min-w-0">

                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.15em] t-muted min-w-0 flex-1">
                    <a href="{{ route('member.produk_gym.index') }}" class="hover:text-gold-600 flex items-center gap-1 transition-colors flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Marketplace
                    </a>
                    <span class="text-brand-borderSoft/40 flex-shrink-0">/</span>
                    <span class="t-muted truncate min-w-0">{{ $product->kategori }}</span>
                </nav>

                <a href="{{ route('member.produk_gym.cart') }}"
                   class="relative w-11 h-11 flex items-center justify-center rounded-full bg-gold-500 hover:bg-gold-400 text-brand-nav transition-all shadow-lg hover:shadow-gold-glow group flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-rotate-6 transition-transform">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] w-4 h-4 flex items-center justify-center rounded-full border-2 border-white dark:border-brand-dark shadow-sm font-bold">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>

           <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-start" data-sticky-container>

                {{-- FOTO PRODUK: sticky on desktop with zoom hint --}}
                <div class="lg:col-span-5 md:sticky md:top-24 self-start animate-slide-up" style="animation-duration: 0.6s">
                    <div class="relative w-full aspect-[3/4] rounded-3xl overflow-hidden surface-card group shadow-2xl">
                        <img src="{{ $imageUrl }}"
                             alt="{{ $product->nama }}"
                             class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105 cursor-zoom-in"
                             onclick="window.open(this.src, '_blank')">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/15 to-transparent pointer-events-none"></div>

                        <div class="absolute top-4 left-4 z-20">
                            <span class="px-3.5 py-1.5 badge-kat backdrop-blur-md border border-white/15 text-white text-[10px] font-extrabold uppercase tracking-[0.15em] rounded-full shadow-lg">
                                {{ $product->kategori }}
                            </span>
                        </div>

                        <div class="absolute bottom-4 right-4 z-20 opacity-75 group-hover:opacity-100 transition-opacity">
                            <span class="px-3 py-1.5 rounded-xl bg-black/60 backdrop-blur-md text-white text-[10px] font-semibold flex items-center gap-1.5 border border-white/15 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" x2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/>
                                </svg>
                                Lihat Penuh
                            </span>
                        </div>
                    </div>
                </div>

                {{-- DETAIL PRODUK & CHECKOUT SECTION --}}
                <div class="lg:col-span-7 flex flex-col h-full animate-slide-up min-w-0" style="animation-duration: 0.8s">
                    <div class="mb-5 min-w-0">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="px-3 py-1 text-[10px] font-extrabold tracking-widest uppercase rounded-full bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/20">
                                {{ $product->kategori }}
                            </span>
                            <span class="text-[10px] font-mono tracking-wider t-muted">
                                SKU: {{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-display font-black t-main leading-[1.05] uppercase tracking-tight break-words">
                            {{ $product->nama }}
                        </h1>
                    </div>

                    {{-- Price & Stock Badge Card --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-gold-500/5 dark:bg-white/5 border border-gold-500/20 mb-7 shadow-sm">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gold-600 dark:text-gold-400 block mb-0.5">
                                Harga Khusus Member
                            </span>
                            <span class="text-3xl sm:text-4xl font-display font-black text-brand-nav dark:text-white tracking-tight">
                                Rp {{ number_format((int)($product->harga ?? 0), 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            @if($ready)
                                <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                    </span>
                                    <div class="text-left">
                                        <p class="text-[10px] font-black uppercase tracking-wider leading-none">Ready Stock</p>
                                        <p class="text-[10px] opacity-80 mt-1 font-medium leading-none">Sisa {{ $stok }} unit</p>
                                    </div>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                    <div class="text-left">
                                        <p class="text-[10px] font-black uppercase tracking-wider leading-none">Habis</p>
                                        <p class="text-[10px] opacity-80 mt-1 font-medium leading-none">Restock Soon</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Product Details --}}
                    <div class="mb-7">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-1.5 h-3.5 rounded-full bg-gold-500 inline-block"></span>
                            <h3 class="text-xs font-bold t-main uppercase tracking-[0.2em]">
                                Detail Produk
                            </h3>
                        </div>
                        <div class="prose prose-invert prose-p:text-sm prose-p:leading-relaxed max-w-none text-justify prose-produk">
                            <p>{{ $product->deskripsi ?: 'Produk ini dirancang untuk memberikan performa maksimal. Material berkualitas tinggi menjamin ketahanan untuk penggunaan jangka panjang di gym maupun latihan mandiri.' }}</p>
                        </div>
                    </div>

                    {{-- 3 Feature Highlights --}}
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8">
                        <div class="p-3.5 rounded-2xl surface-card text-center flex flex-col items-center justify-center border border-brand-borderSoft/60 hover:border-gold-500/40 transition-all group">
                            <div class="w-9 h-9 rounded-xl bg-gold-500/10 text-gold-600 dark:text-gold-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </div>
                            <p class="text-gold-600 dark:text-gold-400 text-xs font-bold uppercase tracking-wider mb-0.5">Authentic</p>
                            <p class="text-[10px] t-muted font-medium">100% Original</p>
                        </div>

                        <div class="p-3.5 rounded-2xl surface-card text-center flex flex-col items-center justify-center border border-brand-borderSoft/60 hover:border-gold-500/40 transition-all group">
                            <div class="w-9 h-9 rounded-xl bg-gold-500/10 text-gold-600 dark:text-gold-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </div>
                            <p class="text-gold-600 dark:text-gold-400 text-xs font-bold uppercase tracking-wider mb-0.5">Pickup</p>
                            <p class="text-[10px] t-muted font-medium">Ambil di Gym</p>
                        </div>

                        <div class="p-3.5 rounded-2xl surface-card text-center flex flex-col items-center justify-center border border-brand-borderSoft/60 hover:border-gold-500/40 transition-all group">
                            <div class="w-9 h-9 rounded-xl bg-gold-500/10 text-gold-600 dark:text-gold-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="5" rx="2"/>
                                    <line x1="2" x2="22" y1="10" y2="10"/>
                                </svg>
                            </div>
                            <p class="text-gold-600 dark:text-gold-400 text-xs font-bold uppercase tracking-wider mb-0.5">Secure</p>
                            <p class="text-[10px] t-muted font-medium">Transfer / QRIS</p>
                        </div>
                    </div>

                    {{-- Unified Luxury Purchase Action Card (Desktop & In-Page) --}}
                    @if($ready)
                        <div class="surface-card rounded-3xl p-6 sm:p-7 border border-brand-borderSoft/80 dark:border-white/10 shadow-xl relative overflow-hidden backdrop-blur-md">
                            <div class="absolute -top-16 -right-16 w-36 h-36 bg-gold-500/10 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="space-y-6 relative z-10">
                                {{-- Top Row: Quantity Control & Total Subtotal --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 pb-6 border-b border-brand-borderSoft/60 dark:border-white/10">
                                    {{-- Quantity Stepper Section --}}
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-brand-nav/80 dark:text-white/80">
                                                Atur Kuantitas
                                            </span>
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gold-500/10 text-gold-600 dark:text-gold-400 border border-gold-500/20">
                                                Maks. {{ $stok }}
                                            </span>
                                        </div>

                                        {{-- Stepper Component --}}
                                        <div class="inline-flex items-center rounded-2xl border border-brand-borderSoft dark:border-white/15 bg-white/70 dark:bg-black/40 p-1 shadow-sm w-fit">
                                            <button type="button"
                                                    @click="qty = Math.max(1, qty - 1)"
                                                    :disabled="qty <= 1"
                                                    class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-brand-nav dark:text-white disabled:opacity-25 disabled:cursor-not-allowed transition-all active:scale-95"
                                                    aria-label="Kurangi Jumlah">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14"/>
                                                </svg>
                                            </button>

                                            <div class="px-3 min-w-[3.5rem] text-center">
                                                <span class="block text-base font-black font-display text-brand-nav dark:text-white leading-none" x-text="qty">1</span>
                                                <span class="text-[9px] uppercase tracking-wider t-muted font-medium block mt-0.5">Unit</span>
                                            </div>

                                            <button type="button"
                                                    @click="qty = Math.min(maxStock, qty + 1)"
                                                    :disabled="qty >= maxStock"
                                                    class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-brand-nav dark:text-white disabled:opacity-25 disabled:cursor-not-allowed transition-all active:scale-95"
                                                    aria-label="Tambah Jumlah">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M5 12h14"/><path d="M12 5v14"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Subtotal Display Section --}}
                                    <div class="sm:text-right flex flex-col justify-center">
                                        <span class="text-[10px] uppercase font-bold tracking-widest text-gold-600 dark:text-gold-400 block mb-1">
                                            Total Pembayaran
                                        </span>
                                        <div class="font-display font-black text-2xl sm:text-3xl text-brand-nav dark:text-white tracking-tight"
                                             x-text="formatRupiah(qty * unitPrice)">
                                            Rp {{ number_format((int)($product->harga ?? 0), 0, ',', '.') }}
                                        </div>
                                        <span class="text-[10px] t-muted block mt-0.5">
                                            Estimasi bersih &bull; Tanpa biaya admin
                                        </span>
                                    </div>
                                </div>

                                {{-- Action Buttons: 2 Distinct Premium Actions --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    {{-- Form + Keranjang (Secondary Luxury Action) --}}
                                    <form action="{{ route('member.produk_gym.cart.add', $product->id) }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="quantity" :value="qty">
                                        <button type="submit"
                                                class="w-full py-4 px-5 rounded-2xl border-2 border-gold-500/40 hover:border-gold-500 bg-white/50 dark:bg-white/5 hover:bg-gold-500/10 text-brand-nav dark:text-white font-bold text-xs uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3 group shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                            <span class="w-8 h-8 rounded-xl bg-gold-500/15 text-gold-600 dark:text-gold-400 flex items-center justify-center transition-transform group-hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                                                </svg>
                                            </span>
                                            <span class="font-extrabold tracking-wider">+ Keranjang</span>
                                        </button>
                                    </form>

                                    {{-- Form Beli Langsung (Hero Gradient Primary Action) --}}
                                    <form action="{{ route('member.produk_gym.buy_now', $product->id) }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="quantity" :value="qty">
                                        <button type="submit"
                                                class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 via-gold-500 to-amber-500 hover:from-amber-300 hover:via-gold-400 hover:to-amber-400 text-brand-nav font-black text-xs uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(212,167,87,0.45)] hover:shadow-[0_12px_30px_-5px_rgba(212,167,87,0.65)] hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-3 group">
                                            <span class="w-8 h-8 rounded-xl bg-black/15 text-brand-nav flex items-center justify-center transition-transform group-hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
                                                </svg>
                                            </span>
                                            <span class="font-black tracking-wider">Beli Langsung</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-1">
                                                <path d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Trust & Quality Highlights Strip --}}
                                <div class="pt-4 border-t border-brand-borderSoft/40 dark:border-white/10 flex flex-wrap items-center justify-between gap-3 text-[10px] t-muted">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span>Stok Terverifikasi</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gold-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        <span>Jaminan Asli 100%</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                        <span>QRIS & Bank Transfer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Out of Stock Card --}}
                        <div class="surface-card rounded-3xl p-6 border border-brand-borderSoft/50 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center mx-auto mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                            </div>
                            <h4 class="font-bold text-sm uppercase tracking-wider text-red-500 mb-1">Stok Habis Sementara</h4>
                            <p class="text-xs t-muted max-w-sm mx-auto mb-4">Produk ini sedang dalam proses restock oleh pihak gym. Silakan periksa kembali nanti atau hubungi staf gym.</p>
                            <a href="{{ route('member.produk_gym.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-gold-600 hover:text-gold-500">
                                &larr; Kembali ke Katalog Marketplace
                            </a>
                        </div>
                    @endif

                    {{-- Mobile Fixed Bottom Floating Bar --}}
                    @if($ready)
                        <div class="lg:hidden fixed bottom-0 left-0 right-0 z-50 p-3 sm:p-4 bg-white/95 dark:bg-[#121318]/95 backdrop-blur-xl border-t border-brand-borderSoft/80 dark:border-white/10 shadow-[0_-10px_25px_rgba(0,0,0,0.15)]">
                            <div class="flex items-center gap-2.5 max-w-lg mx-auto">
                                {{-- Stepper compact --}}
                                <div class="inline-flex items-center rounded-xl border border-brand-borderSoft dark:border-white/15 bg-brand-surface-50/80 dark:bg-white/5 p-0.5 flex-shrink-0">
                                    <button type="button"
                                            @click="qty = Math.max(1, qty - 1)"
                                            :disabled="qty <= 1"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-brand-nav dark:text-white disabled:opacity-30 transition-colors"
                                            aria-label="Kurangi">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/></svg>
                                    </button>
                                    <span class="w-6 text-center text-xs font-black text-brand-nav dark:text-white" x-text="qty"></span>
                                    <button type="button"
                                            @click="qty = Math.min(maxStock, qty + 1)"
                                            :disabled="qty >= maxStock"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/10 text-brand-nav dark:text-white disabled:opacity-30 transition-colors"
                                            aria-label="Tambah">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    </button>
                                </div>

                                {{-- Button + Keranjang (Icon pill) --}}
                                <form action="{{ route('member.produk_gym.cart.add', $product->id) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <input type="hidden" name="quantity" :value="qty">
                                    <button type="submit"
                                            class="w-10 h-10 rounded-xl border-2 border-gold-500/40 bg-gold-500/10 text-gold-600 dark:text-gold-400 flex items-center justify-center active:scale-95 transition-all"
                                            title="Tambah ke Keranjang">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Button Beli Langsung (Hero Button with dynamic subtotal) --}}
                                <form action="{{ route('member.produk_gym.buy_now', $product->id) }}" method="POST" class="flex-1 min-w-0">
                                    @csrf
                                    <input type="hidden" name="quantity" :value="qty">
                                    <button type="submit"
                                            class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-400 via-gold-500 to-amber-500 text-brand-nav font-black text-xs uppercase tracking-wider shadow-md flex items-center justify-between gap-1 active:scale-98 transition-all">
                                        <span class="flex items-center gap-1 truncate">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0">
                                                <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
                                            </svg>
                                            <span class="truncate text-[11px]">Beli Langsung</span>
                                        </span>
                                        <span class="font-mono font-bold text-[11px] bg-black/15 px-2 py-0.5 rounded-lg flex-shrink-0" x-text="formatRupiah(qty * unitPrice)"></span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="lg:hidden h-20"></div>
                    @endif
                </div>
            </div>

            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
                <div class="border-t border-brand-borderSoft/30 pt-20 mt-20 animate-slide-up" style="animation-delay: 0.2s">
                    <div class="flex items-end justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-xl font-display font-bold t-main mb-1">ITEM SEJENIS</h3>
                            <p class="t-muted text-xs">Lengkapi perlengkapan latihan Anda.</p>
                        </div>
                        <a href="{{ route('member.produk_gym.index') }}" class="group flex items-center gap-2 text-gold-600 text-xs font-bold hover:text-gold-500 transition-colors flex-shrink-0">
                            LIHAT SEMUA
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $related)
                            @php
                                $rSlug = $related->id . '-' . Str::slug($related->nama ?? 'produk');
                                $rImg = $imgUrl($related->foto, $related->nama);
                                $rReady = ((int)$related->stok) > 0;
                            @endphp

                            <a href="{{ route('member.produk_gym.show', $rSlug) }}"
                               class="group rounded-3xl overflow-hidden surface-card flex flex-col">

                                <div class="relative w-full aspect-[3/4] related-media-bg overflow-hidden">
                                    <img src="{{ $rImg }}"
                                         alt="{{ $related->nama }}"
                                         class="w-full h-full object-cover object-center brightness-95
                                                group-hover:brightness-110 group-hover:scale-[1.06]
                                                transition duration-700"
                                         loading="lazy">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>

                                    @if(!$rReady)
                                        <div class="absolute top-3 right-3 px-2 py-1 bg-red-600/90 backdrop-blur text-white text-[9px] font-bold uppercase tracking-wider rounded">
                                            Habis
                                        </div>
                                    @endif

                                    @if($related->kategori)
                                        <div class="absolute top-3 left-3">
                                            <span class="px-2 py-1 bg-black/60 backdrop-blur text-white text-[9px] font-bold uppercase tracking-wider rounded border border-white/10">
                                                {{ $related->kategori }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4 flex flex-col flex-1 border-t border-brand-borderSoft/30">
                                    <h4 class="t-main font-bold text-sm line-clamp-2 min-h-[2.5rem] group-hover:text-gold-600 transition-colors">
                                        {{ $related->nama }}
                                    </h4>

                                    <div class="mt-3 pt-3 border-t border-brand-borderSoft/30 flex items-center justify-between gap-2">
                                        <p class="t-main font-bold font-display text-sm">
                                            Rp {{ number_format((int)$related->harga, 0, ',', '.') }}
                                        </p>

                                        @if($rReady)
                                            <span class="text-[9px] font-bold text-green-700 bg-green-500/10 px-2 py-0.5 rounded border border-green-500/15">
                                                Stok {{ $related->stok }}
                                            </span>
                                        @else
                                            <span class="text-[9px] font-bold text-red-700 bg-red-500/10 px-2 py-0.5 rounded border border-red-500/15">
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
