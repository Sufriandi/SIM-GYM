{{-- resources/views/admin/rekening/modals/detail.blade.php --}}
<div x-show="openDetail" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetail = false" @keydown.escape.window="openDetail = false">

    <div
        class="relative w-full max-w-3xl rounded-3xl shadow-2xl border border-brand-borderSoft
                bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Detail</h2>
                <p class="text-sm text-text-muted mt-0.5"
                    x-text="detailTab === 'rekening' ? 'Detail rekening bank.' : 'Detail QRIS.'"></p>
            </div>

            <button type="button" class="p-2 rounded-xl hover:bg-brand-surface-50" @click="openDetail = false">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 py-5 space-y-4">
            {{-- DETAIL REKENING --}}
            <div x-show="detailTab === 'rekening'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Nama Bank</div>
                        <div class="mt-1 text-sm font-semibold text-text-main" x-text="detailItem?.nama_bank ?? '-'">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Nomor Rekening
                        </div>
                        <div class="mt-1 text-sm font-semibold text-text-main"
                            x-text="detailItem?.nomor_rekening ?? '-'"></div>
                    </div>

                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell md:col-span-2">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Atas Nama</div>
                        <div class="mt-1 text-sm font-semibold text-text-main" x-text="detailItem?.nama_pemilik ?? '-'">
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAIL QRIS --}}
            <div x-show="detailTab === 'qris'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell md:col-span-2">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Nama QRIS</div>
                        <div class="mt-1 text-sm font-semibold text-text-main" x-text="detailItem?.nama_qris ?? '-'">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Gambar</div>

                        <template x-if="detailItem?.path_gambar">
                            <img class="mt-2 w-full max-w-[260px] rounded-xl border border-brand-borderSoft bg-white/10"
                                :src="`{{ asset('storage') }}/${detailItem.path_gambar}`" alt="QRIS">
                        </template>

                        <template x-if="!detailItem?.path_gambar">
                            <div class="mt-2 text-sm text-text-muted">-</div>
                        </template>
                    </div>

                    <div class="p-4 rounded-2xl border border-brand-borderSoft bg-brand-shell">
                        <div class="text-[11px] uppercase tracking-wide text-text-muted font-semibold">Keterangan</div>
                        <div class="mt-2 text-sm text-text-main whitespace-pre-line"
                            x-text="detailItem?.keterangan ?? '-'"></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" class="px-5 py-2 rounded-xl border border-brand-borderSoft text-sm"
                    @click="openDetail = false">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
