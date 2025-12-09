{{-- resources/views/member/produk_gym/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Js;
    use Illuminate\Support\Facades\Auth;

    $pageTitle    = $pageTitle    ?? 'Marketplace Gym';
    $pageSubtitle = 'Suplemen, gear, dan kebutuhan latihan terbaik untukmu.';

    // Nomor WA admin dari controller
    $adminNumber = app(\App\Http\Controllers\Member\ProdukGymController::class)->getAdminNumber();

    // State filter dari request
    $search   = $search   ?? request('search', '');
    $kategori = $kategori ?? request('kategori', 'all');
    $sort     = request('sort', 'popular');

    $kategoriOptions = $kategoriOptions ?? ['minuman', 'suplemen', 'lainnya'];

    // Data member untuk identitas di WhatsApp
    $member           = Auth::user();
    $memberName       = $member->name ?? 'Member';
    $memberIdentifier = "{$memberName}";
@endphp

<x-layouts.member
    :pageTitle="$pageTitle"
    :pageSubtitle="$pageSubtitle"
>
    <div class="max-w-7xl mx-auto space-y-8 pb-10">

        {{-- ========================================================= --}}
        {{-- 1. HERO BANNER --}}
        {{-- ========================================================= --}}
        <div class="relative w-full rounded-3xl overflow-hidden shadow-card-strong bg-brand-nav">
            {{-- Pattern --}}
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(#D4A757 1px, transparent 1px); background-size: 20px 20px;">
            </div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between p-8 md:p-10 gap-6">
                <div class="space-y-4 max-w-lg">
                    <span class="inline-block px-3 py-1 rounded-full bg-gold-500/15 text-gold-500 text-xs font-bold tracking-widest uppercase border border-gold-500/25">
                        Official Store Member
                    </span>
                    <h2 class="text-3xl md:text-5xl font-display font-bold text-brand-white leading-tight">
                        FUEL YOUR
                        <br>
                        <span class="text-transparent bg-clip-text bg-brand-gold">TRAINING.</span>
                    </h2>
                    <p class="text-brand-silver text-sm md:text-base">
                        Dapatkan suplemen original dan gear berkualitas. Harga khusus dan stok prioritas untuk member aktif.
                    </p>
                </div>

                <div class="hidden md:flex items-center justify-center text-gold-500/70">
                    <i data-lucide="shopping-bag" class="w-32 h-32"></i>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 2. SEARCH + KATEGORI + SORT --}}
        {{-- ========================================================= --}}
        <div
            x-data="{
                activeKategori: '{{ $kategori }}',
                showSort: false,
                applyKategori(k) {
                    this.activeKategori = k;
                    const form = document.getElementById('produkFilterForm');
                    if (form) form.submit();
                }
            }"
            class="sticky top-20 z-30 bg-brand-bg/95 backdrop-blur-sm py-4 border-b border-brand-borderSoft/40"
        >
            <form
                id="produkFilterForm"
                action="{{ route('member.produk_gym.index') }}"
                method="GET"
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <input type="hidden" name="kategori" x-model="activeKategori">

                {{-- SEARCH --}}
                <div class="relative w-full lg:max-w-xl flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-5 h-5 text-brand-textSoft"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari suplemen, whey, atau aksesoris…"
                        class="block w-full pl-10 pr-4 py-3 bg-brand-card border border-brand-borderSoft rounded-2xl
                               text-sm text-brand-text placeholder-brand-textSoft/60
                               focus:ring-2 focus:ring-gold-500 focus:border-transparent
                               shadow-sm transition-all"
                    >
                </div>

                {{-- KATEGORI + SORT --}}
                <div class="w-full lg:w-auto flex flex-wrap items-center gap-3 justify-between lg:justify-end">
                    {{-- Kategori pills --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="applyKategori('all')"
                            class="px-5 py-2 rounded-full text-xs font-bold border transition-all duration-200"
                            :class="activeKategori === 'all'
                                ? 'bg-brand-nav text-gold-500 border-brand-nav'
                                : 'bg-brand-card text-brand-textSoft border-brand-borderSoft hover:border-gold-500 hover:text-brand-text'"
                        >
                            Semua
                        </button>

                        @foreach($kategoriOptions as $opt)
                            <button
                                type="button"
                                @click="applyKategori('{{ $opt }}')"
                                class="px-5 py-2 rounded-full text-xs font-bold border transition-all duration-200"
                                :class="activeKategori === '{{ $opt }}'
                                    ? 'bg-brand-nav text-gold-500 border-brand-nav'
                                    : 'bg-brand-card text-brand-textSoft border-brand-borderSoft hover:border-gold-500 hover:text-brand-text'"
                            >
                                {{ ucfirst($opt) }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Sort --}}
                    <div class="relative">
                        <button
                            type="button"
                            @click="showSort = !showSort"
                            @click.outside="showSort = false"
                            class="flex items-center gap-2 px-4 py-3 bg-brand-card border border-brand-borderSoft rounded-2xl
                                   text-sm font-medium text-brand-text hover:bg-brand-surface-50 transition-colors"
                        >
                            <i data-lucide="arrow-up-down" class="w-4 h-4 text-gold-600"></i>
                            <span>Urutkan</span>
                        </button>

                        <div
                            x-show="showSort"
                            x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-48 bg-brand-card rounded-xl shadow-card-strong border border-brand-borderSoft z-40 overflow-hidden"
                        >
                            @php
                                $sortOptions = [
                                    'popular'   => 'Paling Populer',
                                    'newest'    => 'Terbaru',
                                    'price_low' => 'Harga Terendah',
                                    'price_high'=> 'Harga Tertinggi',
                                ];
                            @endphp

                            @foreach($sortOptions as $key => $label)
                                <button
                                    type="submit"
                                    name="sort"
                                    value="{{ $key }}"
                                    class="w-full text-left px-4 py-3 text-xs font-medium transition-colors
                                           {{ $sort === $key ? 'bg-brand-surface-50 text-gold-600' : 'text-brand-text hover:bg-brand-surface-100' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <x-ui.toast type="success" class="mt-2">
                {{ session('success') }}
            </x-ui.toast>
        @endif

        @if (session('error'))
            <x-ui.toast type="danger" class="mt-2">
                {{ session('error') }}
            </x-ui.toast>
        @endif

        {{-- ========================================================= --}}
        {{-- 3. CONTENT --}}
        {{-- ========================================================= --}}
        @if($produks->isEmpty())
            {{-- EMPTY STATE --}}
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-24 h-24 bg-brand-surface-100 rounded-full flex items-center justify-center mb-6 border border-brand-borderSoft">
                    <i data-lucide="search-x" class="w-10 h-10 text-brand-textSoft"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-text" style="text-shadow:none;">
                    Produk Tidak Ditemukan
                </h3>
                <p class="text-brand-textSoft mt-2 max-w-md mx-auto">
                    Coba ubah kata kunci atau pilih kategori yang berbeda.
                </p>
                <a
                    href="{{ route('member.produk_gym.index') }}"
                    class="mt-6 px-6 py-2 bg-brand-nav text-gold-500 text-sm font-bold rounded-full hover:bg-brand-sidebar transition-colors"
                >
                    Reset Filter
                </a>
            </div>
        @else

            {{-- A. PALING DICARI (SLIDER) – hanya jika tanpa search & kategori = all --}}
            @php
                $featuredProducts = $produks instanceof \Illuminate\Contracts\Pagination\Paginator
                    ? $produks->take(5)
                    : $produks->take(5);
            @endphp

            @if($featuredProducts->isNotEmpty() && $search === '' && $kategori === 'all')
                <section
                    x-data="{
                        scrollNext() { const c = this.$refs.track; c.scrollBy({left: c.clientWidth*0.7, behavior:'smooth'}); },
                        scrollPrev() { const c = this.$refs.track; c.scrollBy({left: -c.clientWidth*0.7, behavior:'smooth'}); },
                    }"
                    class="space-y-4"
                >
                    <div class="flex items-center gap-2">
                        <i data-lucide="flame" class="w-5 h-5 text-accent-500"></i>
                        <h3 class="text-lg font-bold text-brand-text uppercase tracking-wide"
                            style="text-shadow:none;">
                            Paling Dicari
                        </h3>
                    </div>

                    <div class="relative">
                        <div
                            x-ref="track"
                            class="flex gap-4 overflow-x-auto pb-3 scroll-smooth custom-scrollbar"
                        >
                            @foreach($featuredProducts as $produk)
                                @php
                                    $imageUrl = $produk->foto
                                        ? Storage::url($produk->foto)
                                        : 'https://placehold.co/300x300/F5E6D6/A67C39?text=BETA+GYM';
                                @endphp

                                <article
                                    class="min-w-[180px] md:min-w-[220px] bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden
                                           hover:border-gold-500 hover:shadow-md transition-all duration-200 flex flex-col cursor-pointer"
                                    onclick="window.bukaModalProduk && window.bukaModalProduk({{ Js::from($produk->nama) }}, {{ $produk->harga }}, {{ Js::from($imageUrl) }})"
                                >
                                    <div class="relative aspect-[4/3] overflow-hidden bg-brand-surface-50">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $produk->nama }}"
                                            class="w-full h-full object-cover transition-transform duration-500"
                                            loading="lazy"
                                        >
                                        <span class="absolute top-2 right-2 bg-accent-500 text-white text-[10px] font-bold px-2 py-1 rounded-md">
                                            HOT
                                        </span>
                                    </div>
                                    <div class="p-3 space-y-1">
                                        <h4 class="text-xs md:text-sm font-semibold text-brand-text line-clamp-1">
                                            {{ $produk->nama }}
                                        </h4>
                                        <p class="text-sm md:text-base font-bold text-accent-600"
                                           style="text-shadow:none;">
                                            Rp{{ number_format($produk->harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        {{-- Arrows overlay (desktop & mobile) --}}
                        <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between">
                            <button
                                type="button"
                                @click="scrollPrev()"
                                class="pointer-events-auto ml-1 md:ml-2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-brand-bg/90 border border-brand-borderSoft flex items-center justify-center text-brand-textSoft hover:bg-brand-surface-100 hover:text-brand-text transition-colors"
                            >
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button
                                type="button"
                                @click="scrollNext()"
                                class="pointer-events-auto mr-1 md:mr-2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-brand-bg/90 border border-brand-borderSoft flex items-center justify-center text-brand-textSoft hover:bg-brand-surface-100 hover:text-brand-text transition-colors"
                            >
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </section>
            @endif

            {{-- B. GRID KATALOG LENGKAP --}}
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-brand-text uppercase tracking-wide"
                        style="text-shadow:none;">
                        {{ $kategori === 'all' ? 'Katalog Lengkap' : 'Kategori: ' . ucfirst($kategori) }}
                    </h3>
                    <span class="text-xs text-brand-textSoft bg-brand-surface-100 px-2 py-0.5 rounded-md font-bold border border-brand-borderSoft">
                        {{ $produks->total() ?? $produks->count() }} Item
                    </span>
                </div>

                {{-- Grid: semua card tinggi seragam --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                    @foreach ($produks as $produk)
                        @php
                            $imageUrl     = $produk->foto ? Storage::url($produk->foto) : 'https://placehold.co/400x400/F5E6D6/A67C39?text=BETA+GYM';
                            $isOutOfStock = $produk->stok <= 0;
                            $isLowStock   = $produk->stok > 0 && $produk->stok <= 5;
                        @endphp

                        <article
                            class="group relative bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden
                                   hover:shadow-card-strong hover:border-gold-500/60 transition-all duration-300 flex flex-col h-full"
                        >
                            {{-- IMAGE: ukuran fix --}}
                            <div class="relative w-full aspect-[3/4] overflow-hidden bg-brand-surface-50">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $produk->nama }}"
                                    class="w-full h-full object-cover transition-transform duration-500 {{ $isOutOfStock ? 'grayscale opacity-70' : 'group-hover:scale-105' }}"
                                    loading="lazy"
                                >

                                {{-- Badges stok --}}
                                <div class="absolute top-2 left-2 flex flex-col gap-1">
                                    @if($isOutOfStock)
                                        <span class="px-2 py-1 bg-brand-nav/90 text-brand-white text-[10px] font-bold uppercase rounded-md">
                                            Habis
                                        </span>
                                    @elseif($isLowStock)
                                        <span class="px-2 py-1 bg-accent-500 text-white text-[10px] font-bold uppercase rounded-md">
                                            Sisa {{ $produk->stok }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Badge kategori (di gambar, bukan di atas nama) --}}
                                <span class="absolute top-2 right-2 px-2 py-1 bg-brand-bg/90 text-brand-text text-[10px] font-bold rounded-md border border-brand-borderSoft">
                                    {{ strtoupper($produk->kategori) }}
                                </span>
                            </div>

                            {{-- CONTENT --}}
                            <div class="p-3 md:p-4 flex flex-col flex-1">
                                {{-- kategori di atas nama DIHAPUS sesuai permintaan --}}

                                <h4 class="text-sm font-semibold text-brand-text leading-snug line-clamp-2 mb-2 min-h-[2.5em]">
                                    {{ $produk->nama }}
                                </h4>

                                <div class="mt-auto pt-2 border-t border-brand-borderSoft/40">
                                    {{-- Harga (tanpa bayangan / hover) --}}
                                    <p class="text-base md:text-lg font-bold text-brand-text"
                                       style="text-shadow:none;">
                                        Rp{{ number_format($produk->harga, 0, ',', '.') }}
                                    </p>

                                    <div class="flex items-center gap-1 mt-1 opacity-80">
                                        <i data-lucide="star" class="w-3 h-3 text-gold-500 fill-gold-500"></i>
                                        <span class="text-[10px] text-brand-textSoft">4,9</span>
                                        <span class="text-[10px] text-brand-borderStrong mx-1">•</span>
                                        <span class="text-[10px] text-brand-textSoft">Terjual 10+</span>
                                    </div>
                                </div>
                            </div>

                            {{-- ACTION BUTTON: hover profesional --}}
                            <div class="p-3 md:p-4 pt-0">
                                <button
                                    type="button"
                                    onclick="window.bukaModalProduk && window.bukaModalProduk({{ Js::from($produk->nama) }}, {{ $produk->harga }}, {{ Js::from($imageUrl) }})"
                                    @disabled($isOutOfStock)
                                    class="w-full py-2.5 rounded-xl text-xs font-semibold flex items-center justify-center gap-2
                                           focus:outline-none transition-all duration-200
                                           {{ $isOutOfStock
                                                ? 'bg-brand-surface-200 text-brand-textSoft cursor-not-allowed'
                                                : 'bg-red-600 text-white shadow-sm hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/20 focus:ring-2 focus:ring-red-500/40 active:scale-95' }}"
                                >
                                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                    {{ $isOutOfStock ? 'Stok Habis' : '+ Keranjang' }}
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- PAGINATION --}}
                @if(method_exists($produks, 'links'))
                    <div class="mt-8">
                        {{ $produks->appends(['search' => $search, 'kategori' => $kategori, 'sort' => $sort])->links() }}
                    </div>
                @endif
            </section>
        @endif
    </div>

    {{-- MODAL KONFIRMASI WHATSAPP --}}
    @include('member.produk_gym.modals.whatsapp_modal', [
        'adminNumber'      => $adminNumber,
        'memberIdentifier' => $memberIdentifier,
    ])

    {{-- CSS tambahan --}}
    <style>
        /* Hilangkan scrollbar horizontal tapi tetap bisa scroll untuk slider */
        .custom-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .custom-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</x-layouts.member>
