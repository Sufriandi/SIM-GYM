{{-- resources/views/member/produk_gym/cart.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $fmt = fn($v) => number_format((float)$v, 0, ',', '.');

    $orderId = $orderId ?? ('ORD-' . strtoupper(Str::random(9)));
    $waAdmin = $waAdmin ?? '6281234567890';

    $qrisImg = !empty($qris?->path_gambar) ? Storage::url($qris->path_gambar) : '';
@endphp

<x-layouts.member :pageTitle="'Keranjang & Checkout – BETA GYM'" :pageSubtitle="''">

    {{-- Background: default LIGHT, support dark mode --}}
    <div class="fixed inset-0 -z-10 bg-gradient-to-b from-white via-gray-50 to-white dark:from-neutral-950 dark:via-neutral-950 dark:to-neutral-900">
        <div class="absolute -top-24 -right-24 h-[520px] w-[520px] rounded-full bg-gold-500/10 blur-[160px] dark:bg-gold-500/15"></div>
        <div class="absolute -bottom-24 -left-24 h-[520px] w-[520px] rounded-full bg-gray-900/5 blur-[170px] dark:bg-white/5"></div>
    </div>

    <section class="relative pb-16"
             x-data="cartPage()"
             x-init="init()"
             @keydown.escape.window="closePayment()">

        <div class="container mx-auto max-w-6xl px-4 sm:px-6">

            {{-- Header --}}
            <div class="mb-6 sm:mb-8 flex items-center gap-3 sm:gap-4">
                <a href="{{ route('member.produk_gym.index') }}"
                   class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white/70 p-2 text-gray-700 shadow-sm backdrop-blur hover:bg-white hover:text-gray-900 transition
                          dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10 dark:hover:text-white"
                   aria-label="Kembali ke marketplace">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>

                <div class="min-w-0">
                    <h1 class="truncate font-display text-xl font-bold text-gray-900 sm:text-2xl dark:text-white">
                        Keranjang Belanja
                    </h1>
                    <p class="text-xs text-gray-600 sm:text-sm dark:text-gray-300/80">
                        Review item, lalu pilih metode pembayaran
                        <span class="text-gray-500 dark:text-gray-400" x-show="hasItems" x-text="'• ' + itemsCount + ' item'"></span>
                    </p>
                </div>
            </div>

            {{-- HAS ITEMS --}}
            <div x-show="hasItems" x-transition.opacity>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">

                    {{-- Items list --}}
                    <div class="space-y-4 lg:col-span-8">
                        @foreach(($cart ?? []) as $id => $details)
                            @php
                                $img = trim((string)($details['photo'] ?? ''));
                                if ($img !== '' && !Str::startsWith($img, ['http://','https://'])) $img = Storage::url($img);
                                if ($img === '') $img = 'https://placehold.co/600x600/f8fafc/111827?text=ITEM';

                                $qty   = (int)($details['quantity'] ?? 1);
                                $price = (float)($details['price'] ?? 0);
                                $name  = (string)($details['name'] ?? 'Produk');
                                $cat   = (string)($details['category'] ?? 'ITEM');
                            @endphp

                            <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm backdrop-blur sm:p-5
                                        dark:border-white/10 dark:bg-white/5"
                                 x-show="items['{{ (string)$id }}']"
                                 x-cloak
                                 x-transition.opacity>

                                <div class="flex gap-4">
                                    <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 sm:h-24 sm:w-24
                                                dark:border-white/10 dark:bg-white/5">
                                        <img src="{{ $img }}" class="h-full w-full object-cover" alt="{{ $name }}">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    {{ $cat }}
                                                </p>

                                                <h4 class="truncate text-base font-bold leading-tight text-gray-900 dark:text-white">
                                                    {{ $name }}
                                                </h4>

                                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300/80">
                                                    Harga:
                                                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ $fmt($price) }}</span>
                                                </p>
                                            </div>

                                            <button type="button"
                                                    @click="removeItem('{{ (string)$id }}')"
                                                    :disabled="loading['{{ (string)$id }}']"
                                                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-600 transition
                                                           disabled:opacity-40 disabled:cursor-not-allowed
                                                           dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-red-400"
                                                    aria-label="Hapus item">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18"/>
                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        @click="changeQty('{{ (string)$id }}','dec')"
                                                        :disabled="loading['{{ (string)$id }}']"
                                                        class="h-10 w-10 rounded-xl border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-50 transition
                                                               disabled:opacity-40 disabled:cursor-not-allowed
                                                               dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
                                                        aria-label="Kurangi jumlah">
                                                    -
                                                </button>

                                                <input type="number" min="1" inputmode="numeric"
                                                       class="h-10 w-16 rounded-xl border border-gray-200 bg-white text-center text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold-500/40
                                                              dark:border-white/10 dark:bg-white/5 dark:text-white"
                                                       :value="items['{{ (string)$id }}'] ? items['{{ (string)$id }}'].quantity : {{ $qty }}"
                                                       @input.debounce.450ms="setQty('{{ (string)$id }}', $event.target.value)"
                                                       :disabled="loading['{{ (string)$id }}']"
                                                       aria-label="Jumlah item">

                                                <button type="button"
                                                        @click="changeQty('{{ (string)$id }}','inc')"
                                                        :disabled="loading['{{ (string)$id }}']"
                                                        class="h-10 w-10 rounded-xl border border-gray-200 bg-white text-gray-900 shadow-sm hover:bg-gray-50 transition
                                                               disabled:opacity-40 disabled:cursor-not-allowed
                                                               dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
                                                        aria-label="Tambah jumlah">
                                                    +
                                                </button>

                                                <span class="hidden text-xs text-gray-500 sm:inline dark:text-gray-400">
                                                    Subtotal terupdate otomatis
                                                </span>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Subtotal</p>
                                                <p class="text-base font-bold text-gold-600 dark:text-gold-500"
                                                   x-text="idr(lineTotal('{{ (string)$id }}'))"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Summary --}}
                    <div class="lg:col-span-4">
                        <div class="rounded-3xl border border-gray-200 bg-white/80 p-5 shadow-sm backdrop-blur sm:p-6 lg:sticky lg:top-28
                                    dark:border-white/10 dark:bg-white/5">
                            <h3 class="mb-5 text-lg font-bold text-gray-900 dark:text-white">
                                Rincian Biaya
                            </h3>

                            <div class="mb-6 space-y-3 border-b border-gray-200 pb-6 dark:border-white/10">
                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300/80">
                                    <span>Total Harga</span>
                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="idr(subtotal)"></span>
                                </div>

                                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300/80">
                                    <span>Biaya Layanan</span>
                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="idr(adminFee)"></span>
                                </div>
                            </div>

                            <div class="mb-6 flex items-end justify-between">
                                <span class="font-bold text-gray-700 dark:text-gray-200">Total Tagihan</span>
                                <span class="font-display text-2xl font-extrabold text-gold-600 dark:text-gold-500" x-text="idr(total)"></span>
                            </div>

                            <button type="button"
                                    @click="openPayment()"
                                    :disabled="!hasItems"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gold-500 py-4 font-bold text-brand-nav shadow-lg transition
                                           hover:bg-gold-400 disabled:cursor-not-allowed disabled:opacity-60">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" x2="22" y1="10" y2="10"/>
                                </svg>
                                Pilih Pembayaran
                            </button>

                            <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400">
                                Setelah bayar, klik “Saya Sudah Bayar” untuk konfirmasi.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- EMPTY STATE --}}
            <div x-show="!hasItems" x-transition.opacity x-cloak
                 class="rounded-3xl border border-dashed border-gray-300 bg-white/60 py-24 text-center shadow-sm backdrop-blur
                        dark:border-white/15 dark:bg-white/5">
                <p class="text-gray-600 dark:text-gray-300/80">Keranjang kosong</p>
                <a href="{{ route('member.produk_gym.index') }}"
                   class="mt-2 inline-block font-semibold text-gold-600 underline-offset-4 hover:underline dark:text-gold-500">
                    Belanja dulu
                </a>
            </div>

        </div>

        {{-- Include modal pembayaran (dipisah) --}}
        @include('member.produk_gym.partials.payment_modal', [
            'orderId'  => $orderId,
            'waAdmin'  => $waAdmin,
            'rekenings'=> $rekenings ?? [],
            'qris'     => $qris ?? null,
            'qrisImg'  => $qrisImg,
        ])

        @once
            <style>
                [x-cloak] { display: none !important; }
            </style>

            <script>
                function cartPage() {
                    const qtyUrlTpl = @js(route('member.produk_gym.cart.quantity', ['id' => '__ID__']));
                    const rmUrlTpl  = @js(route('member.produk_gym.cart.remove', ['id' => '__ID__']));

                    const fixedOrderId = @js($orderId);
                    const fixedWaAdmin = @js($waAdmin);

                    return {
                        items: @js($cart ?? []),
                        subtotal: Number(@js((int)($subtotal ?? 0))),
                        adminFee: Number(@js((int)($adminFee ?? 0))),
                        total: Number(@js((int)($total ?? 0))),
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
                            this.total    = Number(payload.total || 0);

                            // broadcast ke payment modal jika sedang terbuka
                            window.dispatchEvent(new CustomEvent('payment:update', {
                                detail: { total: this.total }
                            }));
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
                            try { data = await res.json(); } catch (e) {}

                            if (!res.ok || !data) throw (data || { message: 'Request gagal.' });
                            return data;
                        },

                        async changeQty(id, op) {
                            if (!this.items[id]) return;

                            this.loading[id] = true;
                            try {
                                const url = qtyUrlTpl.replace('__ID__', id);
                                const data = await this.postJson(url, { op });

                                if (!data.ok) return;

                                if (data.removed) delete this.items[id];
                                else this.items[id].quantity = Number(data.quantity || this.items[id].quantity || 1);

                                this.syncTotals(data);

                                if (!this.hasItems) this.closePayment();
                            } catch (e) {
                                // optional: toast kalau mau
                            } finally {
                                this.loading[id] = false;
                            }
                        },

                        async setQty(id, qty) {
                            if (!this.items[id]) return;

                            const parsed = parseInt(qty, 10);
                            const val = Number.isFinite(parsed) ? Math.max(1, parsed) : 1;

                            const current = Number(this.items[id]?.quantity || 1);
                            if (val === current) return;

                            this.loading[id] = true;
                            try {
                                const url = qtyUrlTpl.replace('__ID__', id);
                                const data = await this.postJson(url, { qty: val });

                                if (!data.ok) return;

                                if (data.removed) delete this.items[id];
                                else this.items[id].quantity = Number(data.quantity || val);

                                this.syncTotals(data);

                                if (!this.hasItems) this.closePayment();
                            } catch (e) {
                                // optional: toast kalau mau
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

                                if (!data.ok) return;

                                delete this.items[id];
                                this.syncTotals(data);

                                if (!this.hasItems) this.closePayment();
                            } catch (e) {
                                // optional: toast kalau mau
                            } finally {
                                this.loading[id] = false;
                            }
                        },

                        init() {
                            // sinkron awal modal bila diperlukan
                            window.dispatchEvent(new CustomEvent('payment:update', { detail: { total: this.total } }));
                        },

                        openPayment() {
                            if (!this.hasItems) return;

                            window.dispatchEvent(new CustomEvent('payment:open', {
                                detail: {
                                    total: this.total,
                                    orderId: fixedOrderId,
                                    waAdmin: fixedWaAdmin,
                                }
                            }));
                        },

                        closePayment() {
                            window.dispatchEvent(new CustomEvent('payment:close'));
                        },
                    }
                }
            </script>
        @endonce

    </section>

</x-layouts.member>
