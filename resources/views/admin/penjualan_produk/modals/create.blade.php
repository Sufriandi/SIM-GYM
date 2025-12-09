{{-- resources/views/admin/penjualan_produk/modals/create.blade.php --}}

<div
    x-show="openCreate"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false"
    @keydown.escape.window="openCreate = false"
>
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Catat Penjualan Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Tambahkan transaksi penjualan produk baru.
                </p>
            </div>

            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4">
            <form action="{{ route('admin.penjualan_produk.store') }}" method="POST">
                @csrf

                {{-- PILIH PEMBELI --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-text-muted uppercase mb-1">
                        Pembeli (Member / Umum)
                    </label>

                    {{-- contoh input dengan search pakai memberSearchQuery & createForm.member_id --}}
                    <div class="relative">
                        <input
                            type="text"
                            x-model="memberSearchQuery"
                            @focus="isMemberDropdownFocused = true"
                            placeholder="Cari nama member, atau kosongkan untuk Umum"
                            class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-3 py-2 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-primary-dark"
                            autocomplete="off"
                        >

                        <div
                            x-show="isMemberDropdownFocused"
                            x-cloak
                            @click.outside="isMemberDropdownFocused = false"
                            class="absolute z-20 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft bg-brand-card shadow-lg custom-scrollbar"
                        >
                            <button
                                type="button"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-brand-surface-50"
                                @click="selectMember(null, 'Umum (Tidak Terdaftar)')"
                            >
                                Umum (Tidak Terdaftar)
                            </button>

                            <template x-for="member in filteredMembers()" :key="member.id">
                                <button
                                    type="button"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-brand-surface-50"
                                    @click="selectMember(member.id, member.name)"
                                    x-text="member.name"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <input type="hidden" name="member_id" :value="createForm.member_id">
                    @error('member_id')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- METODE PEMBAYARAN --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-text-muted uppercase mb-1">
                        Metode Pembayaran
                    </label>
                    <select
                        name="metode_pembayaran"
                        x-model="createForm.metode_pembayaran"
                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-shell px-3 py-2 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-primary-dark"
                    >
                        <option value="">Pilih Metode</option>
                        <template x-for="metode in metodePembayaranOptions" :key="metode">
                            <option :value="metode" x-text="metode"></option>
                        </template>
                    </select>
                    @error('metode_pembayaran')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DAFTAR PRODUK (cartItems) --}}
                <div class="mb-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-text-main">Produk yang Dijual</h3>
                        <button
                            type="button"
                            class="text-xs font-semibold text-primary-dark hover:underline"
                            @click="addCartItem()"
                        >
                            + Tambah Produk
                        </button>
                    </div>

                    <template x-for="(item, index) in cartItems" :key="index">
                        <div class="rounded-2xl border border-brand-borderSoft bg-brand-shell/70 px-3 py-3 space-y-2">
                            <div class="grid grid-cols-12 gap-2">
                                {{-- Produk --}}
                                <div class="col-span-7">
                                    <label class="block text-[11px] text-text-muted mb-0.5">Produk</label>
                                    <select
                                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-card px-3 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                        :name="`produks[${index}][produk_id]`"
                                        x-model.number="item.produk_id"
                                        @change="updateCartItem(index)"
                                    >
                                        <option value="">Pilih Produk</option>
                                        <template x-for="produk in produkData" :key="produk.id">
                                            <option :value="produk.id" x-text="produk.nama"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Jumlah --}}
                                <div class="col-span-3">
                                    <label class="block text-[11px] text-text-muted mb-0.5">Jumlah</label>
                                    <input
                                        type="number"
                                        min="1"
                                        class="w-full rounded-xl border border-brand-borderSoft bg-brand-card px-2 py-1.5 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                        :name="`produks[${index}][jumlah]`"
                                        x-model.number="item.jumlah"
                                        @input="updateCartItem(index)"
                                    >
                                    <p class="mt-0.5 text-[10px] text-text-muted" x-text="`Stok: ${item.stok_tersedia}`"></p>
                                </div>

                                {{-- Hapus baris --}}
                                <div class="col-span-2 flex items-end justify-end">
                                    <button
                                        type="button"
                                        class="p-2 rounded-full text-danger hover:bg-danger-soft/40"
                                        @click="removeCartItem(index)"
                                        x-show="cartItems.length > 1"
                                    >
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-text-muted mt-1">
                                <span x-text="item.harga_satuan ? `Harga: Rp ${item.harga_satuan.toLocaleString('id-ID')}` : 'Harga: -'"></span>
                                <span x-text="`Subtotal: Rp ${(item.jumlah * item.harga_satuan).toLocaleString('id-ID')}`"></span>
                            </div>
                        </div>
                    </template>

                    {{-- info duplicate produk --}}
                    <p class="mt-1 text-[11px] text-danger" x-show="cartItems.some((c, i) => isProductDuplicate(c.produk_id, i))">
                        Ada produk yang sama di lebih dari satu baris.
                    </p>
                </div>

                {{-- KETERANGAN --}}
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-text-muted uppercase mb-1">
                        Keterangan
                    </label>
                    <textarea
                        name="keterangan"
                        x-model="createForm.keterangan"
                        rows="3"
                        class="w-full rounded-2xl border border-brand-borderSoft bg-brand-shell px-3 py-2 text-sm text-text-main focus:outline-none focus:ring-1 focus:ring-primary-dark"
                        placeholder="Catatan tambahan (opsional)..."
                    ></textarea>
                    @error('keterangan')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- GRAND TOTAL --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold text-text-muted">Total Bayar</span>
                    <span class="text-lg font-extrabold text-success" x-text="formatRupiah(calculateGrandTotal())"></span>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex items-center justify-end gap-2 border-t border-brand-borderSoft pt-4">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm rounded-xl bg-brand-surface-50 text-text-main hover:bg-brand-surface-100"
                        @click="openCreate = false; resetCreateForm();"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm rounded-xl bg-primary-dark text-white hover:bg-primary-dark/90"
                    >
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
