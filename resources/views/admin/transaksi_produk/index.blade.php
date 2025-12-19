{{-- resources/views/admin/transaksi_produk/index.blade.php --}}
@php
    $pageTitle = 'Transaksi Kasir';
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
                            class="w-full pl-10 pr-4 py-2 bg-brand-card border border-brand-borderSoft rounded-xl text-sm focus:ring-1 focus:ring-gold-500 focus:border-gold-500 outline-none transition-all">
                    </div>
                </div>

                {{-- Grid Produk --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($produks as $produk)
                        <template x-if="matchesSearch('{{ strtolower($produk->nama) }}')">
                            <button type="button"
                                @click="addItem({{ $produk->id }}, '{{ $produk->nama }}', {{ $produk->harga }}, {{ $produk->stok }}, '{{ asset('storage/' . $produk->foto) }}')"
                                class="group bg-brand-card border border-brand-borderSoft rounded-2xl overflow-hidden hover:border-gold-500/50 hover:shadow-gold-glow/20 transition-all duration-300 text-left flex flex-col h-full"
                                :class="{{ $produk->stok }} <= 0 ? 'opacity-60 cursor-not-allowed' : ''"
                                :disabled="{{ $produk->stok }} <= 0">
                                {{-- Thumbnail --}}
                                <div class="aspect-square relative overflow-hidden bg-brand-shell/50">
                                    <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama }}"
                                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
                                        onerror="this.src='https://placehold.co/400x400/21160F/F5E6D6?text=No+Image'">
                                    @if ($produk->stok <= 5 && $produk->stok > 0)
                                        <span
                                            class="absolute top-2 right-2 bg-accent-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Stok
                                            Tipis</span>
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
                                            class="w-7 h-7 bg-brand-shell rounded-full flex items-center justify-center border border-brand-borderSoft group-hover:bg-gold-500 group-hover:border-gold-500 transition-colors">
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
                    <div class="sticky top-24 space-y-6">

                        {{-- CARD: INFORMASI PEMBELI --}}
                        <x-ui.card class="p-5 border-brand-borderSoft">
                            <div
                                class="flex items-center gap-2 mb-4 text-gold-500 font-bold text-xs uppercase tracking-wider">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                <span>Informasi Pembeli</span>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-text-muted uppercase mb-1">Pilih
                                        Member</label>
                                    <select name="buyer_member_id"
                                        class="w-full bg-brand-shell border border-brand-borderSoft rounded-xl px-4 py-2.5 text-sm text-text-main focus:ring-1 focus:ring-gold-500 outline-none">
                                        <option value="">Tamu (Bukan Member)</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-text-muted uppercase mb-1">Metode
                                        Pembayaran</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach ($metodeLabels as $key => $label)
                                            <label class="relative cursor-pointer group">
                                                <input type="radio" name="metode_pembayaran"
                                                    value="{{ $key }}" class="peer sr-only" required
                                                    {{ $loop->first ? 'checked' : '' }}>
                                                <div
                                                    class="bg-brand-shell border border-brand-borderSoft peer-checked:border-gold-500 peer-checked:bg-gold-500/10 rounded-xl py-2 text-center transition-all">
                                                    <span
                                                        class="text-[11px] font-bold text-text-muted peer-checked:text-gold-400 group-hover:text-text-main">{{ $label }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>

                        {{-- CARD: KERANJANG --}}
                        <x-ui.card class="p-5 border-brand-borderSoft flex flex-col min-h-[300px]">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="flex items-center gap-2 text-gold-500 font-bold text-xs uppercase tracking-wider">
                                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                    <span>Item Belanja</span>
                                </div>
                                <button type="button" @click="resetCart()"
                                    class="text-[10px] font-bold text-danger uppercase hover:underline">Hapus
                                    Semua</button>
                            </div>

                            {{-- List Items --}}
                            <div class="flex-1 space-y-3 overflow-y-auto max-h-80 pr-2 custom-scrollbar">
                                <template x-for="(item, index) in Object.values(cart)" :key="item.id">
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-xl bg-brand-shell/50 border border-brand-borderSoft/50 group">
                                        <img :src="item.foto"
                                            class="w-10 h-10 rounded-lg object-cover border border-brand-borderSoft">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-text-main truncate" x-text="item.nama"></p>
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

                                        {{-- Hidden Inputs for Laravel --}}
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
                            <div class="mt-4 pt-4 border-t border-brand-borderSoft">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-xs font-bold text-text-muted uppercase">Subtotal</span>
                                    <span class="text-xl font-black text-gold-500"
                                        x-text="formatRupiah(totalPrice)"></span>
                                </div>
                                <x-ui.button-primary type="submit" class="w-full justify-center py-3"
                                    ::disabled="Object.keys(cart).length === 0">
                                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Konfirmasi Bayar
                                </x-ui.button-primary>
                            </div>
                        </x-ui.card>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function kasirSystem() {
                return {
                    cart: {},
                    searchQuery: '',

                    matchesSearch(nama) {
                        if (!this.searchQuery) return true;
                        return nama.includes(this.searchQuery.toLowerCase());
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
                        if (delta > 0 && item.qty < item.stok) {
                            item.qty++;
                        } else if (delta < 0 && item.qty > 1) {
                            item.qty--;
                        } else if (delta < 0 && item.qty === 1) {
                            this.removeItem(id);
                        }
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
