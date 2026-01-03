{{-- resources/views/member/produk_gym/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle   = $pageTitle ?? 'Produk Gym';
    $products    = $products ?? ($produks ?? collect()); // kompatibel kalau controller pakai $produks
    $categories  = $categories ?? collect();

    $currentQ    = $currentQ ?? request('q', '');
    $currentKat  = $currentKat ?? request('kategori', 'all');

    // hitung total item (bukan cuma jumlah baris)
    $cartRaw = session('cart', []);
    $cartCount = 0;
    foreach ($cartRaw as $it) {
        $cartCount += (int)($it['quantity'] ?? 1);
    }

    $imgUrl = function ($path, $fallbackText = 'PRODUK') {
        $path = trim((string) $path);
        if ($path === '') {
            return 'https://placehold.co/1000x1000/f6f7fb/111827?text=' . urlencode($fallbackText) . '&font=raleway';
        }
        if (Str::startsWith($path, ['http://','https://'])) return $path;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) return url('/' . $path);
        if (Str::startsWith($path, 'public/')) $path = Str::after($path, 'public/');

        return Storage::url($path);
    };

    // Hero image (pakai yang Anda sudah pakai di guest, kalau ada)
    $heroImg = $heroImg ?? asset('images/marketplace-hero.jpg'); // silakan sesuaikan file image Anda
@endphp

<x-layouts.member :title="$pageTitle . ' – BETA GYM'">

    {{-- HERO (light) --}}
    <section class="relative overflow-hidden rounded-3xl border border-brand-borderSoft/40 bg-white shadow-sm">
        <div class="absolute inset-0">
            {{-- background image --}}
            <div class="absolute inset-0 bg-center bg-cover"
                 style="background-image:url('{{ $heroImg }}');"></div>

            {{-- overlay: bukan gelap full, hanya untuk kontras teks --}}
            <div class="absolute inset-0 bg-white/60"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-white/70 via-white/40 to-white"></div>
        </div>

        <div class="relative px-6 py-10 lg:px-10 lg:py-14">
            <div class="flex flex-col lg:flex-row gap-8 lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gold-500/30 bg-white/80">
                        <span class="w-2 h-2 rounded-full bg-gold-500"></span>
                        <span class="text-[11px] font-bold tracking-[0.18em] uppercase text-brand-nav">
                            Official Store Member
                        </span>
                    </div>

                    <h1 class="mt-5 text-4xl md:text-5xl font-display font-black tracking-tight text-brand-nav leading-[1.0]">
                        Elite Gear <span class="text-gold-500">for Elite</span> Performance
                    </h1>

                    <p class="mt-4 text-sm md:text-base text-brand-nav/70 max-w-xl">
                        Suplemen original, gear berkualitas, dan kebutuhan latihan lainnya. Stok terupdate dan checkout cepat.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="#katalog"
                           class="px-6 py-3 rounded-full bg-brand-nav text-white font-bold shadow-sm hover:opacity-95 transition">
                            Lihat Katalog
                        </a>
                        <a href="{{ route('member.produk_gym.cart') }}"
                           class="px-6 py-3 rounded-full bg-white border border-brand-borderSoft/40 text-brand-nav font-bold hover:bg-brand-shell/50 transition">
                            Keranjang
                            @if($cartCount > 0)
                                <span class="ml-2 inline-flex items-center justify-center text-[10px] font-black bg-gold-500 text-brand-nav w-6 h-6 rounded-full">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- benefit card (light) --}}
                <div class="w-full lg:max-w-md">
                    <div class="bg-white/85 backdrop-blur rounded-3xl border border-brand-borderSoft/40 shadow-sm overflow-hidden">
                        <div class="p-6">
                            <p class="text-[12px] font-bold tracking-[0.2em] uppercase text-brand-nav/60">
                                Member Benefit
                            </p>
                            <h3 class="mt-2 text-2xl font-display font-black text-brand-nav">
                                Harga & stok prioritas
                            </h3>
                            <p class="mt-3 text-sm text-brand-nav/70">
                                Produk favorit lebih cepat tersedia untuk member.
                            </p>
                        </div>
                        <div class="h-2 bg-gold-500"></div>
                    </div>
                </div>
            </div>

            {{-- FILTER BAR (light) --}}
            <div id="katalog" class="mt-10 pt-8 border-t border-brand-borderSoft/30">
                <form method="GET" action="{{ route('member.produk_gym.index') }}"
                      class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 bg-white border border-brand-borderSoft/40 rounded-full px-4 py-3 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="text-brand-nav/50">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text" name="q" value="{{ $currentQ }}"
                                   placeholder="Cari whey, creatine, strap, dll..."
                                   class="w-full bg-transparent outline-none text-brand-nav placeholder:text-brand-nav/40">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 items-center justify-end">
                        <div class="bg-white border border-brand-borderSoft/40 rounded-full px-4 py-3 shadow-sm flex items-center gap-2">
                            <span class="text-sm font-bold text-brand-nav">Kategori</span>
                            <span class="text-brand-nav/30">•</span>
                            <select name="kategori"
                                    class="bg-transparent outline-none text-sm font-bold text-gold-600">
                                <option value="all" @selected(($currentKat ?? 'all') === 'all')>Semua</option>
                                @foreach(($categories ?? collect()) as $kat)
                                    <option value="{{ $kat }}" @selected(($currentKat ?? '') === $kat)>
                                        {{ Str::upper($kat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                                class="bg-white border border-brand-borderSoft/40 rounded-full px-5 py-3 shadow-sm font-bold text-brand-nav hover:bg-brand-shell/50 transition">
                            Terapkan
                        </button>

                        <a href="{{ route('member.produk_gym.cart') }}"
                           class="w-12 h-12 rounded-full bg-brand-nav text-white flex items-center justify-center shadow-sm hover:opacity-95 transition relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                            </svg>

                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-gold-500 text-brand-nav text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-black border-2 border-white">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- KATALOG --}}
    <section class="mt-10">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-display font-black text-brand-nav">Katalog Produk</h2>
                <p class="text-sm text-brand-nav/60">Stok terupdate dan siap checkout.</p>
            </div>

            <div class="text-right">
                <p class="text-[11px] font-bold tracking-[0.25em] uppercase text-brand-nav/40">Total</p>
                <p class="text-3xl font-display font-black text-brand-nav">
                    {{ method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse(($products ?? []) as $p)
                @php
                    $slug = $p->id . '-' . Str::slug($p->nama ?? 'produk');
                    $img  = $imgUrl($p->foto ?? '', $p->nama ?? 'PRODUK');
                    $stok = (int)($p->stok ?? 0);
                    $ready = $stok > 0;
                @endphp

                <a href="{{ route('member.produk_gym.show', $slug) }}"
                   class="group bg-white rounded-3xl border border-brand-borderSoft/40 overflow-hidden shadow-sm hover:shadow-md transition">
                    <div class="relative aspect-[4/5] bg-brand-shell">
                        <img src="{{ $img }}" alt="{{ $p->nama }}"
                             class="w-full h-full object-cover object-center group-hover:scale-[1.03] transition duration-500"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/0 to-white/0 opacity-80 pointer-events-none"></div>

                        @if(!empty($p->kategori))
                            <div class="absolute top-3 left-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                             bg-white/90 border border-brand-borderSoft/40 text-brand-nav">
                                    {{ $p->kategori }}
                                </span>
                            </div>
                        @endif

                        <div class="absolute top-3 right-3">
                            @if($ready)
                                <span class="px-2 py-1 rounded-full text-[10px] font-black
                                             bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Ready • {{ $stok }}
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-[10px] font-black
                                             bg-rose-50 text-rose-700 border border-rose-200">
                                    Habis
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-brand-nav line-clamp-2 min-h-[2.5rem] group-hover:text-gold-600 transition">
                            {{ $p->nama }}
                        </h3>

                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-base font-display font-black text-brand-nav">
                                Rp {{ number_format((int)($p->harga ?? 0), 0, ',', '.') }}
                            </p>

                            <span class="text-[11px] font-bold text-brand-nav/45">
                                SKU {{ str_pad((int)$p->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <div class="bg-white border border-brand-borderSoft/40 rounded-3xl p-10 text-center text-brand-nav/60">
                        Produk tidak ditemukan.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if(method_exists($products, 'links'))
            <div class="mt-10">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

</x-layouts.member>
