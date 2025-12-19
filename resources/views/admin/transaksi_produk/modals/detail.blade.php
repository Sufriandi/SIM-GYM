{{-- resources/views/admin/transaksi_produk/modals/detail.blade.php --}}
<div x-show="openDetailModal" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="close()" @keydown.escape.window="close()">
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar">
        {{-- HEADER --}}
        <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Detail Transaksi
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    No Nota:
                    <span class="font-semibold text-text-main" x-text="detail?.no_nota ?? '-'"></span>
                </p>
            </div>

            <button type="button" class="p-2 rounded-full hover:bg-brand-shell transition" @click="close()">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 py-5 space-y-6">
            {{-- META --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-shell p-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Tanggal</div>
                    <div class="mt-1 text-sm font-semibold text-text-main" x-text="detail?.tanggal_transaksi ?? '-'">
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-shell p-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Metode Pembayaran
                    </div>
                    <div class="mt-1">
                        <x-ui.badge variant="neutral">
                            <span x-text="detail?.metode_pembayaran ?? '-'"></span>
                        </x-ui.badge>
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-shell p-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Pembeli</div>
                    <div class="mt-1 text-sm font-semibold text-text-main" x-text="detail?.buyer ?? 'Tamu'"></div>
                </div>

                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-shell p-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Kasir</div>
                    <div class="mt-1 text-sm font-semibold text-text-main" x-text="detail?.kasir ?? '-'"></div>
                </div>

                <div class="md:col-span-2 rounded-2xl border border-brand-borderSoft/70 bg-brand-shell p-4">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Keterangan</div>
                    <div class="mt-1 text-sm text-text-main" x-text="detail?.keterangan ? detail.keterangan : '-'">
                    </div>
                </div>
            </div>

            {{-- ITEMS --}}
            <div class="rounded-2xl border border-brand-borderSoft/80 overflow-hidden">
                <div class="px-5 py-4 bg-brand-shell/70 border-b border-brand-borderSoft/70">
                    <h3 class="text-sm font-bold text-text-main">Item Produk</h3>
                    <p class="text-xs text-text-muted mt-0.5">Rincian item yang dibeli.</p>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full border-collapse text-xs md:text-sm md:min-w-[700px]">
                        <thead>
                            <tr class="border-b border-brand-borderSoft/70 bg-brand-shell/60">
                                <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">Produk
                                </th>
                                <th class="px-4 py-2 text-right text-[11px] font-semibold uppercase tracking-wide">Harga
                                </th>
                                <th class="px-4 py-2 text-center text-[11px] font-semibold uppercase tracking-wide">Qty
                                </th>
                                <th class="px-4 py-2 text-right text-[11px] font-semibold uppercase tracking-wide">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!detail?.items || detail.items.length === 0">
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-text-muted italic">
                                        Tidak ada item.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="it in (detail?.items || [])" :key="it.nama + '-' + it.qty">
                                <tr class="border-b border-brand-borderSoft/60 last:border-0">
                                    <td class="px-4 py-2 text-text-main" x-text="it.nama"></td>
                                    <td class="px-4 py-2 text-right text-text-main"
                                        x-text="formatRupiah(it.harga_satuan)"></td>
                                    <td class="px-4 py-2 text-center text-text-main" x-text="it.qty"></td>
                                    <td class="px-4 py-2 text-right font-bold text-text-main"
                                        x-text="formatRupiah(itemSubtotal(it))"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div
                    class="px-5 py-4 bg-brand-shell/60 border-t border-brand-borderSoft/70 flex items-center justify-between">
                    <div class="text-sm font-semibold text-text-muted">Total</div>
                    <div class="text-lg font-extrabold text-text-main" x-text="formatRupiah(detail?.total || 0)"></div>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button type="button" @click="close()"
                    class="px-5 py-2.5 rounded-2xl border border-brand-borderSoft/70 hover:bg-brand-shell transition text-sm font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
