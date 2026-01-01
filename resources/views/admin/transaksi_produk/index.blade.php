{{-- resources/views/admin/transaksi_produk/index.blade.php --}}
@php
    $pageTitle = 'Transaksi Kasir';

    // Build options seperti di transaksi membership (label: "Nama (username)")
    $memberOptions = ($members ?? collect())
        ->map(function ($m) {
            $name = $m->user?->name ?? '';
            $username = $m->user?->username ? ' (' . $m->user->username . ')' : '';
            return [
                'id' => (int) $m->id,
                'label' => trim($name . $username),
            ];
        })
        ->values()
        ->all();

    $oldBuyerId = old('buyer_member_id'); // bisa null / '' untuk tamu
    $oldBuyerLabel = '';
    if (!empty($oldBuyerId)) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldBuyerId);
        $oldBuyerLabel = $found['label'] ?? '';
    }
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Sistem kasir untuk penjualan produk kepada member atau tamu.">

    <div x-data="kasirSystem()" class="pb-20">

        {{-- HEADER SECTION --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Pilih produk untuk memulai transaksi baru." />
        <div class="mt-2 mb-6 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- GRID UTAMA --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- ================= KOLOM KIRI: KATALOG PRODUK ================= --}}
            <div class="lg:col-span-8">

                {{-- Search & Filter Mini --}}
                <div class="mb-6 flex gap-4">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-text-muted">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama produk..."
                            class="w-full pl-10 pr-4 py-2 bg-brand-card border border-brand-borderSoft rounded-xl text-sm
                                   focus:ring-1 focus:ring-gold-500 focus:border-gold-500 outline-none transition-all">
                    </div>

                    <div class="w-44 relative" @click.outside="kategoriOpen = false">
                        <button type="button" @click="kategoriOpen = !kategoriOpen"
                            class="w-full px-3 py-2 bg-brand-card border border-brand-borderSoft rounded-2xl text-sm text-text-main
               focus:ring-1 focus:ring-gold-500 focus:border-gold-500 outline-none transition-all
               flex items-center justify-between">
                            <span class="truncate" x-text="kategoriLabel(selectedKategori)"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted transition-transform"
                                :class="kategoriOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="kategoriOpen" x-cloak
                            class="absolute z-50 mt-2 w-full rounded-2xl border border-brand-borderSoft bg-brand-shell shadow-lg overflow-hidden">
                            <div class="max-h-56 overflow-y-auto custom-scrollbar">
                                <button type="button"
                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                    @click="setKategori('')">
                                    Semua
                                </button>
                                <div class="h-px bg-brand-borderSoft/70"></div>

                                <button type="button"
                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                    @click="setKategori('minuman')">
                                    Minuman
                                </button>
                                <button type="button"
                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                    @click="setKategori('suplemen')">
                                    Suplemen
                                </button>
                                <button type="button"
                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                    @click="setKategori('lainnya')">
                                    Lainnya
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Grid Produk --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($produks as $produk)
                        <template x-if="matchesFilter(@js($produk->nama), @js($produk->kategori))">
                            <button type="button"
                                @click="addItem(
                                    {{ $produk->id }},
                                    @js($produk->nama),
                                    {{ $produk->harga }},
                                    {{ $produk->stok }},
                                    @js($produk->foto ? asset('storage/' . $produk->foto) : 'https://placehold.co/400x400/21160F/F5E6D6?text=No+Image')
                                )"
                                class="group bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden
                                       hover:border-gold-500/50 hover:shadow-gold-glow/20 transition-all duration-300
                                       text-left flex flex-col h-full"
                                :class="{{ $produk->stok }} <= 0 ? 'opacity-60 cursor-not-allowed' : ''"
                                :disabled="{{ $produk->stok }} <= 0">

                                {{-- Thumbnail --}}
                                <div class="aspect-square relative overflow-hidden bg-brand-shell/50">
                                    <img src="{{ $produk->foto ? asset('storage/' . $produk->foto) : 'https://placehold.co/400x400/21160F/F5E6D6?text=No+Image' }}"
                                        alt="{{ $produk->nama }}"
                                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">

                                    @if ($produk->stok <= 5 && $produk->stok > 0)
                                        <span
                                            class="absolute top-2 right-2 bg-accent-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                            Stok Tipis
                                        </span>
                                    @elseif($produk->stok <= 0)
                                        <div
                                            class="absolute inset-0 bg-brand-black/60 flex items-center justify-center">
                                            <span
                                                class="text-white text-xs font-bold uppercase tracking-widest">Habis</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Body --}}
                                <div class="p-4 flex-1 flex flex-col justify-between">
                                    <div>
                                        <p
                                            class="text-sm font-bold text-text-main line-clamp-1 group-hover:text-gold-400 transition-colors">
                                            {{ $produk->nama }}
                                        </p>
                                        <p class="text-xs text-text-muted mt-1 font-medium">
                                            Stok: {{ $produk->stok }}
                                        </p>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-sm font-black text-gold-500">
                                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                        </span>

                                        <div
                                            class="w-7 h-7 bg-brand-shell rounded-full flex items-center justify-center border border-brand-borderSoft
                                                    group-hover:bg-gold-500 group-hover:border-gold-500 transition-colors">
                                            <i data-lucide="plus"
                                                class="w-4 h-4 text-text-muted group-hover:text-brand-black"></i>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </template>
                    @endforeach
                </div>
            </div>

            {{-- ================= KOLOM KANAN: RINGKASAN BELANJA ================= --}}
            <div class="lg:col-span-4">
                <form action="{{ route('admin.transaksi_produk.store') }}" method="POST" id="form-transaksi">
                    @csrf

                    {{-- 
                        FIX: Gunakan sticky biasa dengan top-24. 
                        Hapus class h-screen atau flex-grow yang bikin layout rusak.
                    --}}
                    <div class="lg:sticky lg:top-24 space-y-6">

                        {{-- CARD: INFORMASI PEMBELI --}}
                        <x-ui.card class="p-5 border-brand-borderSoft !overflow-visible relative z-30">
                            <div
                                class="flex items-center gap-2 mb-4 text-gold-500 font-bold text-xs uppercase tracking-wider">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                <span>Informasi Pembeli</span>
                            </div>

                            <div class="space-y-4">
                                {{-- INPUT PENCARIAN MEMBER --}}
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-text-muted uppercase mb-1">
                                        Pilih Member
                                    </label>
                                    <div class="relative" @click.outside="buyerDropdownOpen = false">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-text-muted">
                                            <i data-lucide="search" class="w-4 h-4"></i>
                                        </span>
                                        <input type="text" x-model="buyerSearch" @focus="buyerDropdownOpen = true"
                                            @input="buyerDropdownOpen = true"
                                            placeholder="Cari nama / username member..." autocomplete="off"
                                            class="w-full pl-10 pr-3 py-2.5 bg-brand-shell border rounded-2xl text-sm text-text-main
                                                   focus:ring-1 focus:ring-gold-500 outline-none
                                                   @error('buyer_member_id') border-danger ring-danger-soft @else border-brand-borderSoft @enderror">

                                        <input type="hidden" name="buyer_member_id" :value="buyerId ?? ''">

                                        {{-- Dropdown Result --}}
                                        <div x-show="buyerDropdownOpen" x-cloak
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                                            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 transform scale-95 -translate-y-2"
                                            class="absolute z-50 mt-2 w-full rounded-2xl border border-brand-borderSoft bg-brand-shell shadow-xl shadow-black/20 overflow-hidden ring-1 ring-black/5">

                                            <div class="max-h-56 overflow-y-auto custom-scrollbar scrollbar-gutter bg-brand-shell"
                                                @wheel.stop @touchmove.stop>
                                                <button type="button"
                                                    class="w-full text-left px-3 py-2.5 text-sm text-text-main hover:bg-brand-surface-50 transition-colors border-l-2 border-transparent hover:border-gold-500"
                                                    @click="selectGuest()">
                                                    <span class="font-medium">Tamu (Bukan Member)</span>
                                                </button>
                                                <div class="h-px bg-brand-borderSoft/70 mx-2 my-1"></div>
                                                <template x-for="m in filteredMembers(buyerSearch)"
                                                    :key="'buyer-' + m.id">
                                                    <button type="button"
                                                        class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50 transition-colors border-l-2 border-transparent hover:border-gold-500"
                                                        @click="selectBuyer(m)" x-text="m.label"></button>
                                                </template>
                                                <div x-show="filteredMembers(buyerSearch).length === 0"
                                                    class="px-3 py-4 text-xs text-text-muted text-center italic">
                                                    Member tidak ditemukan.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('buyer_member_id')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-text-muted">Kosongkan untuk transaksi tamu.</p>
                                </div>

                                {{-- METODE PEMBAYARAN --}}
                                <div>
                                    <label class="block text-[10px] font-bold text-text-muted uppercase mb-1">
                                        Metode Pembayaran
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach ($metodeLabels as $key => $label)
                                            <label class="relative cursor-pointer group">
                                                <input type="radio" name="metode_pembayaran"
                                                    value="{{ $key }}" class="peer sr-only" required
                                                    {{ old('metode_pembayaran') ? (old('metode_pembayaran') === $key ? 'checked' : '') : ($loop->first ? 'checked' : '') }}>
                                                <div
                                                    class="bg-brand-shell border border-brand-borderSoft peer-checked:border-gold-500 peer-checked:bg-gold-500/10 rounded-xl py-2 text-center transition-all">
                                                    <span
                                                        class="text-[11px] font-bold text-text-muted peer-checked:text-gold-400 group-hover:text-text-main">
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>

                        {{-- CARD: KERANJANG --}}
                        <x-ui.card class="border-brand-borderSoft relative z-20">

                            {{-- Header Keranjang --}}
                            <div class="p-5 pb-2 flex items-center justify-between">
                                <div
                                    class="flex items-center gap-2 text-gold-500 font-bold text-xs uppercase tracking-wider">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                    <span>Item Belanja</span>
                                </div>
                                <button type="button" @click="resetCart()"
                                    class="text-[10px] font-bold text-danger uppercase hover:underline">
                                    Hapus Semua
                                </button>
                            </div>

                            {{-- 
                                FIX UTAMA DISINI:
                                Gunakan max-h-[350px] agar list item tidak memanjang tanpa batas.
                                Jika item banyak, scrollbar akan muncul DI SINI, bukan di body.
                                Ini menjamin tombol bayar di bawahnya tetap terlihat.
                            --}}
                            <div class="max-h-[350px] overflow-y-auto custom-scrollbar px-5 py-2 space-y-3">
                                <template x-for="(item, index) in Object.values(cart)" :key="item.id">
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-xl bg-brand-shell/50 border border-brand-borderSoft/50 group">
                                        <img :src="item.foto"
                                            class="w-10 h-10 rounded-lg object-cover border border-brand-borderSoft">

                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-text-main truncate" x-text="item.nama">
                                            </p>
                                            <p class="text-[10px] text-gold-500 font-medium"
                                                x-text="formatRupiah(item.harga * item.qty)"></p>
                                        </div>

                                        <div
                                            class="flex items-center bg-brand-card border border-brand-borderSoft rounded-lg overflow-hidden">
                                            <button type="button" @click="changeQty(item.id, -1)"
                                                class="px-2 py-1 hover:bg-brand-shell text-text-muted transition-colors">−</button>
                                            <span
                                                class="px-2 text-[11px] font-bold text-text-main border-x border-brand-borderSoft"
                                                x-text="item.qty"></span>
                                            <button type="button" @click="changeQty(item.id, 1)"
                                                class="px-2 py-1 hover:bg-brand-shell text-text-muted transition-colors">+</button>
                                        </div>

                                        <button type="button" @click="removeItem(item.id)"
                                            class="p-1 text-text-muted hover:text-danger transition-colors">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <input type="hidden" :name="'items[' + index + '][produk_id]'"
                                            :value="item.id">
                                        <input type="hidden" :name="'items[' + index + '][qty]'"
                                            :value="item.qty">
                                    </div>
                                </template>

                                <div x-show="Object.keys(cart).length === 0" class="py-10 text-center">
                                    <i data-lucide="shopping-bag"
                                        class="w-10 h-10 text-brand-borderSoft mx-auto mb-2"></i>
                                    <p class="text-xs text-text-muted italic">Keranjang masih kosong</p>
                                </div>
                            </div>

                            {{-- Footer Keranjang --}}
                            <div class="p-5 pt-4 mt-2 border-t border-brand-borderSoft bg-brand-card rounded-b-2xl">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-xs font-bold text-text-muted uppercase">Subtotal</span>
                                    <span class="text-xl font-black text-gold-500"
                                        x-text="formatRupiah(totalPrice)"></span>
                                </div>

                                <x-ui.button-primary type="submit" class="w-full justify-center py-3"
                                    ::disabled="Object.keys(cart).length === 0">
                                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                                    Konfirmasi Bayar
                                </x-ui.button-primary>
                            </div>

                        </x-ui.card>
                    </div>
                </form>
            </div>

        </div> {{-- end grid utama --}}
    </div> {{-- end x-data wrapper --}}

    @push('scripts')
        <script>
            function kasirSystem() {
                return {
                    // ====== Member dropdown data ======
                    members: @js($memberOptions),

                    buyerDropdownOpen: false,
                    buyerSearch: @js($oldBuyerLabel),
                    buyerId: @js($oldBuyerId ? (int) $oldBuyerId : null),

                    filteredMembers(text) {
                        const q = (text || '').toLowerCase().trim();
                        if (!q) return this.members;
                        return this.members.filter(m => (m.label || '').toLowerCase().includes(q));
                    },

                    selectBuyer(m) {
                        this.buyerId = m.id;
                        this.buyerSearch = m.label;
                        this.buyerDropdownOpen = false;
                    },

                    selectGuest() {
                        this.buyerId = null;
                        this.buyerSearch = '';
                        this.buyerDropdownOpen = false;
                    },

                    // ====== Kasir logic ======
                    cart: {},
                    searchQuery: '',
                    selectedKategori: '',

                    kategoriOpen: false,

                    kategoriLabel(val) {
                        const map = {
                            '': 'Semua',
                            'minuman': 'Minuman',
                            'suplemen': 'Suplemen',
                            'lainnya': 'Lainnya',
                        };
                        return map[(val || '').toLowerCase()] ?? 'Semua';
                    },

                    setKategori(val) {
                        this.selectedKategori = (val || '').toLowerCase();
                        this.kategoriOpen = false;
                        this.$nextTick(() => lucide.createIcons());
                    },


                    matchesFilter(nama, kategori) {
                        const q = (this.searchQuery || '').toLowerCase().trim();
                        const k = (this.selectedKategori || '').toLowerCase().trim();

                        const namaVal = String(nama || '').toLowerCase();
                        const katVal = String(kategori || '').toLowerCase().trim();

                        const namaOk = !q || namaVal.includes(q);
                        const kategoriOk = !k || katVal === k;

                        return namaOk && kategoriOk;
                    },

                    addItem(id, nama, harga, stok, foto) {
                        if (this.cart[id]) {
                            if (this.cart[id].qty < stok) {
                                this.cart[id].qty++;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Stok Terbatas',
                                    text: 'Maksimal pembelian untuk ' + nama + ' adalah ' + stok,
                                    background: '#21160F',
                                    color: '#F8F2E7'
                                });
                            }
                        } else {
                            this.cart[id] = {
                                id,
                                nama,
                                harga,
                                stok,
                                foto,
                                qty: 1
                            };
                        }
                        this.$nextTick(() => lucide.createIcons());
                    },

                    removeItem(id) {
                        delete this.cart[id];
                    },

                    changeQty(id, delta) {
                        const item = this.cart[id];
                        if (!item) return;

                        if (delta > 0 && item.qty < item.stok) item.qty++;
                        else if (delta < 0 && item.qty > 1) item.qty--;
                        else if (delta < 0 && item.qty === 1) this.removeItem(id);
                    },

                    resetCart() {
                        this.cart = {};
                    },

                    get totalPrice() {
                        return Object.values(this.cart).reduce((sum, item) => sum + (item.harga * item.qty), 0);
                    },

                    formatRupiah(amount) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(amount);
                    }
                }
            }
        </script>
    @endpush

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #3A2D2A;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #D4A757;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

</x-layouts.admin>
