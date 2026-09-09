{{-- resources/views/member/produk_gym/cart.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fmt = fn($v) => number_format((float) $v, 0, ',', '.');

    $totalTagihan = $total ?? 0;
    $orderId = $orderId ?? 'ORD-' . strtoupper(Str::random(9));

    $qrisImg = !empty($qris?->path_gambar) ? Storage::url($qris->path_gambar) : '';

    $waAdmin = $waAdmin ?? null;
    $merchantName = $merchantName ?? 'BETA GYM';
    $merchantLogo = $merchantLogo ?? asset('images/logo.webp');
@endphp

<x-layouts.member :pageTitle="'Keranjang & Checkout – BETA GYM'" :pageSubtitle="''">

    {{-- BACKDROP: Light mode putih, cards cream mengikuti CSS vars (dark mode ikut .dark vars) --}}
    <div class="fixed inset-0 pointer-events-none z-0 bg-brand-bg">
        <div class="absolute -top-24 -right-24 w-[520px] h-[520px] bg-gold-500/10 rounded-full blur-[170px]"></div>
        <div class="absolute -bottom-24 -left-24 w-[520px] h-[520px] bg-brand-shell/60 rounded-full blur-[180px]"></div>
        <div class="absolute inset-0 opacity-[0.06] dark:opacity-[0.03]"
            style="background-image:
                radial-gradient(circle at 18% 18%, rgba(212,167,87,0.16), transparent 55%),
                radial-gradient(circle at 86% 80%, rgba(199,53,39,0.08), transparent 60%);">
        </div>
    </div>

    <section class="relative z-10 pb-16" x-data="cartPage()" x-init="init()"
        @keydown.escape.window="closeAll()">

        <div class="container mx-auto px-4 sm:px-6 max-w-6xl">

            {{-- HEADER --}}
            <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8">
                <a href="{{ route('member.produk_gym.index') }}" class="icon-btn" aria-label="Kembali ke Marketplace">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="text-text-muted">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>

                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-heading font-bold text-text-main truncate">
                        Keranjang Belanja
                    </h1>
                    <p class="text-xs sm:text-sm text-text-muted">
                        Review item, lalu pilih metode pembayaran
                        <span class="text-text-muted/80" x-show="hasItems" x-text="'• ' + itemsCount + ' item'"></span>
                    </p>
                </div>
            </div>

            {{-- HAS ITEMS --}}
            <div x-show="hasItems" x-transition.opacity>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                    {{-- ITEMS LIST --}}
                    <div class="lg:col-span-8 space-y-4">
                        @foreach ($cart ?? [] as $id => $details)
                            @php
                                $img = trim((string) ($details['photo'] ?? ''));
                                if ($img !== '' && !Str::startsWith($img, ['http://', 'https://'])) {
                                    $img = Storage::url($img);
                                }
                                if ($img === '') {
                                    $img = 'https://placehold.co/600x600/F8F2E7/A67C39?text=ITEM';
                                }

                                $qty = (int) ($details['quantity'] ?? 1);
                                $price = (float) ($details['price'] ?? 0);
                                $name = (string) ($details['name'] ?? 'Produk');
                                $cat = (string) ($details['category'] ?? 'ITEM');
                            @endphp

                            <div class="card-surface p-4 sm:p-5" x-show="items['{{ (string) $id }}']" x-cloak
                                x-transition.opacity>

                                <div class="flex gap-4">
                                    <div
                                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-brand-shell flex-shrink-0 border border-brand-borderSoft/60">
                                        <img src="{{ $img }}" class="w-full h-full object-cover"
                                            alt="Item">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p
                                                    class="text-[10px] text-text-muted uppercase tracking-wider mb-1 truncate">
                                                    {{ $cat }}
                                                </p>

                                                <h4
                                                    class="font-heading font-bold text-text-main text-base leading-tight truncate">
                                                    {{ $name }}
                                                </h4>

                                                <p class="text-xs text-text-muted mt-1">
                                                    Harga:
                                                    <span class="text-text-main font-semibold whitespace-nowrap">
                                                        Rp {{ $fmt($price) }}
                                                    </span>
                                                </p>
                                            </div>

                                            <button type="button" @click="removeItem('{{ (string) $id }}')"
                                                :disabled="loading['{{ (string) $id }}']"
                                                class="p-2 rounded-lg text-text-muted hover:text-red-600 hover:bg-brand-surface-50 transition disabled:opacity-40"
                                                aria-label="Hapus item">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18" />
                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="changeQty('{{ (string) $id }}','dec')"
                                                    :disabled="loading['{{ (string) $id }}']"
                                                    class="w-9 h-9 rounded-xl bg-brand-surface-50 border border-brand-borderSoft/60 text-text-main hover:bg-brand-surface-100 transition disabled:opacity-40">
                                                    -
                                                </button>

                                                <input type="number" min="1"
                                                    class="w-16 h-9 rounded-xl bg-brand-surface-50 border border-brand-borderSoft/60 text-text-main text-center
                                                              focus:outline-none focus:ring-2 focus:ring-gold-500/30"
                                                    :value="items['{{ (string) $id }}'] ? items['{{ (string) $id }}']
                                                        .quantity : {{ $qty }}"
                                                    @input.debounce.450ms="setQty('{{ (string) $id }}', $event.target.value)"
                                                    :disabled="loading['{{ (string) $id }}']">

                                                <button type="button" @click="changeQty('{{ (string) $id }}','inc')"
                                                    :disabled="loading['{{ (string) $id }}']"
                                                    class="w-9 h-9 rounded-xl bg-brand-surface-50 border border-brand-borderSoft/60 text-text-main hover:bg-brand-surface-100 transition disabled:opacity-40">
                                                    +
                                                </button>

                                                <span
                                                    class="text-xs text-text-muted hidden sm:inline whitespace-nowrap">
                                                    Update subtotal otomatis
                                                </span>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-[11px] text-text-muted">Subtotal</p>
                                                <p class="text-gold-600 font-heading font-bold text-base whitespace-nowrap"
                                                    x-text="idr(lineTotal('{{ (string) $id }}'))"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- SUMMARY --}}
                    <div class="lg:col-span-4">
                        <div class="card-premium p-5 sm:p-6 rounded-3xl lg:sticky lg:top-28">
                            <h3 class="font-heading font-bold text-text-main mb-5 text-lg">
                                Rincian Biaya
                            </h3>

                            <div class="space-y-3 mb-6 pb-6 border-b border-brand-borderSoft/60">
                                <div class="flex justify-between text-text-muted text-sm">
                                    <span>Total Harga</span>
                                    <span class="text-text-main font-semibold whitespace-nowrap"
                                        x-text="idr(subtotal)"></span>
                                </div>
                                <div class="flex justify-between text-text-muted text-sm">
                                    <span>Biaya Layanan</span>
                                    <span class="text-text-main font-semibold whitespace-nowrap"
                                        x-text="idr(adminFee)"></span>
                                </div>
                            </div>

                            <div class="flex justify-between mb-6 items-end">
                                <span class="text-text-muted font-heading font-bold">Total Tagihan</span>
                                <span class="text-2xl font-heading font-bold text-gold-600 whitespace-nowrap"
                                    x-text="idr(total)"></span>
                            </div>

                            <a href="{{ route('member.produk_gym.payment') }}"
                                :class="{ 'opacity-50 pointer-events-none cursor-not-allowed': !hasItems }"
                                class="btn-primary w-full py-4 rounded-xl flex items-center justify-center gap-2.5 shadow-lg shadow-gold-500/25 hover:shadow-gold-500/40 hover:-translate-y-0.5 transition-all font-extrabold text-sm uppercase tracking-wider">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <line x1="2" x2="22" y1="10" y2="10" />
                                </svg>
                                <span>Lanjut ke Pembayaran</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </a>

                            <p class="text-[11px] text-text-muted mt-3 text-center">
                                Pilih metode pembayaran (Transfer Bank / QRIS) di laman pembayaran.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- EMPTY STATE --}}
            <div x-show="!hasItems" x-transition.opacity x-cloak
                class="text-center py-24 border border-dashed border-brand-borderSoft/70 rounded-3xl bg-brand-card/70">
                <p class="text-text-muted">Keranjang kosong</p>
                <a href="{{ route('member.produk_gym.index') }}"
                    class="text-gold-600 font-semibold hover:underline mt-2 inline-flex items-center gap-1">
                    Belanja Dulu
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </a>
            </div>

        </div>

        {{-- Toast Notifikasi Keranjang --}}
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 pointer-events-none"
             x-show="toast.show"
             x-cloak
             x-transition.opacity>
            <div class="bg-black/90 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-2xl border border-white/10 flex items-center gap-2">
                <svg class="w-4 h-4 text-gold-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <span x-text="toast.text"></span>
            </div>
        </div>

        <style>
            .custom-scrollbar-light::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar-light::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .custom-scrollbar-light::-webkit-scrollbar-thumb {
                background: #d7d7d7;
                border-radius: 999px;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>

        <script>
            function cartPage() {
                const qtyUrlTpl = @js(route('member.produk_gym.cart.qty', ['id' => '__ID__']));
                const rmUrlTpl = @js(route('member.produk_gym.cart.remove', ['id' => '__ID__']));

                return {
                    items: @js($cart ?? []),
                    subtotal: Number(@js((int) ($subtotal ?? 0))),
                    adminFee: Number(@js((int) ($adminFee ?? 0))), // selalu 0
                    total: Number(@js((int) ($total ?? 0))),
                    loading: {},
                    csrf: @js(csrf_token()),

                    get hasItems() {
                        return Object.keys(this.items || {}).length > 0;
                    },
                    get itemsCount() {
                        let c = 0;
                        for (const k in (this.items || {})) c += Number(this.items[k]?.quantity || 0);
                        return c;
                    },
                    idr(n) {
                        const num = Number(n || 0);
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                    },
                    lineTotal(id) {
                        const it = (this.items || {})[id];
                        if (!it) return 0;
                        return Number(it.price || 0) * Number(it.quantity || 0);
                    },
                    syncTotals(payload) {
                        this.subtotal = Number(payload.subtotal || 0);
                        this.adminFee = Number(payload.admin_fee || 0);
                        this.total = Number(payload.total || 0);
                    },
                    async postJson(url, body) {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(body || {})
                        });

                        let data = null;
                        try {
                            data = await res.json();
                        } catch (e) {}

                        if (!res.ok || !data) throw (data || {
                            message: 'Request gagal.'
                        });
                        return data;
                    },

                    async changeQty(id, op) {
                        if (!this.items[id]) return;

                        this.loading[id] = true;
                        try {
                            const url = qtyUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, {
                                op
                            });

                            if (!data.ok) {
                                this.showToast(data.message || 'Gagal update qty');
                                return;
                            }

                            if (data.removed) delete this.items[id];
                            else this.items[id].quantity = Number(data.quantity || this.items[id].quantity || 1);

                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal update qty');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    async setQty(id, qty) {
                        if (!this.items[id]) return;

                        const val = Math.max(1, parseInt(qty || 1, 10));
                        this.loading[id] = true;

                        try {
                            const url = qtyUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, {
                                qty: val
                            });

                            if (!data.ok) {
                                this.showToast(data.message || 'Gagal update qty');
                                return;
                            }

                            if (data.removed) delete this.items[id];
                            else this.items[id].quantity = Number(data.quantity || val);

                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal update qty');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    async removeItem(id) {
                        if (!this.items[id]) return;

                        this.loading[id] = true;
                        try {
                            const url = rmUrlTpl.replace('__ID__', id);
                            const data = await this.postJson(url, {});

                            if (!data.ok) {
                                this.showToast(data.message || 'Gagal hapus item');
                                return;
                            }

                            delete this.items[id];
                            this.syncTotals(data);
                            if (data.message) this.showToast(data.message);
                            if (!this.hasItems) this.closeAll();
                        } catch (e) {
                            this.showToast(e?.message || 'Gagal hapus item');
                        } finally {
                            this.loading[id] = false;
                        }
                    },

                    toast: {
                        show: false,
                        text: ''
                    },
                    toastTimer: null,

                    init() {},

                    showToast(t) {
                        this.toast.text = t;
                        this.toast.show = true;
                        if (this.toastTimer) clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(() => {
                            this.toast.show = false
                        }, 1800);
                    },
                }
            }
        </script>

    </section>

</x-layouts.member>
