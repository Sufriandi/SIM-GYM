{{-- resources/views/member/produk_gym/modals/whatsapp_modal.blade.php --}}

@php
    $adminNumber      = $adminNumber      ?? '6282390694731';
    $memberIdentifier = $memberIdentifier ?? 'Member';
@endphp

<div
    x-data="produkGymModal({
        adminNumber: '{{ $adminNumber }}',
        memberIdentifier: @js($memberIdentifier),
    })"
    x-init="init()"
>
    {{-- OVERLAY & CARD MODAL --}}
    <div
        x-show="open"
        x-cloak
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-4 bg-black/60 backdrop-blur-sm"
    >
        <div
            @click.away="close()"
            class="relative w-full max-w-md rounded-2xl border border-brand-borderSoft bg-brand-card shadow-2xl overflow-hidden"
        >
            {{-- HEADER --}}
            <div class="px-5 pt-5 pb-3 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-text-main">
                        Konfirmasi Pemesanan
                    </h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        Periksa kembali detail produk sebelum melanjutkan ke WhatsApp.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                    @click="close()"
                >
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>

            {{-- PREVIEW GAMBAR --}}
            <div
                class="h-48 w-full overflow-hidden bg-brand-surface-50 border-b border-brand-borderSoft
                       flex items-center justify-center p-4"
            >
                <img
                    :src="productImage"
                    alt="Produk"
                    class="max-h-full max-w-full object-contain rounded-xl"
                >
            </div>

            {{-- KONTEN DETAIL --}}
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-text-muted mb-1">
                        Produk yang akan dipesan
                    </p>
                    <p class="text-base font-semibold text-text-main" x-text="productName"></p>
                    <p class="text-sm font-bold text-success mt-0.5">
                        <span x-text="formatRupiah(productPrice)"></span>
                        <span class="text-[11px] text-text-muted font-normal">/ unit</span>
                    </p>
                </div>

                {{-- JUMLAH UNIT --}}
                <div>
                    <label class="block text-xs font-semibold text-text-main mb-1">
                        Jumlah unit
                    </label>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="w-9 h-9 flex items-center justify-center rounded-lg border border-brand-borderSoft
                                   bg-brand-shell text-text-main hover:bg-brand-surface-50 disabled:opacity-40
                                   disabled:cursor-not-allowed"
                            @click="decrement()"
                            :disabled="quantity <= 1"
                        >
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>

                        <input
                            type="number"
                            min="1"
                            x-model.number="quantity"
                            class="w-16 text-center rounded-lg border border-brand-borderSoft bg-brand-shell
                                   text-base font-semibold text-text-main py-1.5 focus:outline-none
                                   focus:ring-2 focus:ring-gold-500/60"
                        >

                        <button
                            type="button"
                            class="w-9 h-9 flex items-center justify-center rounded-lg border border-brand-borderSoft
                                   bg-brand-shell text-text-main hover:bg-brand-surface-50"
                            @click="increment()"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-text-muted">
                        Perkiraan total: <span class="font-semibold" x-text="formatRupiah(totalPrice())"></span>
                    </p>
                </div>

                <div class="text-[11px] text-text-muted bg-brand-shell/70 border border-brand-borderSoft rounded-xl p-3 leading-relaxed">
                    Pemesanan akan dikirim ke WhatsApp Admin atas nama
                    <span class="font-semibold" x-text="memberIdentifier"></span>.
                    Admin akan mengonfirmasi ketersediaan stok dan total pembayaran sebelum transaksi dilanjutkan.
                </div>
            </div>

            {{-- FOOTER AKSI --}}
            <div class="px-5 py-3 border-t border-brand-borderSoft bg-brand-shell flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="text-xs sm:text-sm font-medium text-text-muted hover:text-text-main transition-colors"
                    @click="close()"
                >
                    Batal
                </button>

                <a
                    :href="generateWhatsappLink()"
                    target="_blank"
                    @click="close()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                           bg-green-600 text-white text-xs sm:text-sm font-semibold
                           py-2 px-4 hover:bg-green-700 shadow-lg shadow-green-900/40
                           focus:outline-none focus:ring-2 focus:ring-green-500/60"
                >
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    Lanjut ke WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function produkGymModal(config) {
        return {
            open: false,
            productName: '',
            productPrice: 0,
            productImage: '',
            productStock: 0,
            quantity: 1,
            adminNumber: config.adminNumber,
            memberIdentifier: config.memberIdentifier,

            init() {
                // Fungsi global dipanggil dari tombol "Beli via WhatsApp"
                window.bukaModalProduk = (name, price, image, stock) => {
                    this.productName  = name;
                    this.productPrice = Number(price) || 0;
                    this.productImage = image;
                    this.productStock = Number(stock) || 0;
                    this.quantity     = 1;
                    this.open         = true;
                };
            },

            close() {
                this.open = false;
            },

            increment() {
                this.quantity += 1;
            },

            decrement() {
                if (this.quantity > 1) {
                    this.quantity -= 1;
                }
            },

            totalPrice() {
                return (this.productPrice || 0) * (this.quantity || 0);
            },

            formatRupiah(value) {
                const number = Number(value) || 0;
                return 'Rp ' + number.toLocaleString('id-ID');
            },

            generateWhatsappLink() {
                const lines = [
                    'Pemesanan Produk Gym:',
                    '',
                    `Identitas pemesan: ${this.memberIdentifier}`,
                    '',
                    'Detail pesanan:',
                    `Produk : ${this.productName}`,
                    `Harga  : ${this.formatRupiah(this.productPrice)} / unit`,
                    `Jumlah : ${this.quantity} unit`,
                    '',
                    `Perkiraan total: ${this.formatRupiah(this.totalPrice())}`,
                    '',
                    'Mohon konfirmasi ketersediaan stok dan total pembayaran.'
                ];

                const message = encodeURIComponent(lines.join('\n'));
                return `https://wa.me/${this.adminNumber}?text=${message}`;
            }
        };
    }
</script>
