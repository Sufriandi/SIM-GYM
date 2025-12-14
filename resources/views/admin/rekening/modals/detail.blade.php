{{-- resources/views/admin/rekening/modals/detail.blade.php --}}
<div x-show="openDetail" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openDetail = false" @keydown.escape.window="openDetail = false">
    <div class="relative w-full rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        :class="detailTab === 'qris' ? 'max-w-4xl' : 'max-w-3xl'">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main"
                    x-text="detailTab === 'rekening' ? 'Detail Rekening' : 'Detail QRIS'"></h2>
                <p class="text-sm text-text-muted mt-0.5"
                    x-text="detailTab === 'rekening' ? 'Detail rekening bank.' : 'Detail QRIS.'"></p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openDetail = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">

            {{-- DETAIL REKENING --}}
            <div x-show="detailTab === 'rekening'">
                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-[11px] font-bold uppercase text-text-muted mb-1">Nama Bank</div>
                            <div class="rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm text-text-main font-semibold"
                                x-text="detailItem?.nama_bank ?? '—'"></div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold uppercase text-text-muted mb-1">Nomor Rekening</div>
                            <div class="rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm text-text-main font-semibold"
                                x-text="detailItem?.nomor_rekening ?? '—'"></div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-[11px] font-bold uppercase text-text-muted mb-1">Atas Nama</div>
                            <div class="rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm text-text-main font-semibold"
                                x-text="detailItem?.nama_pemilik ?? '—'"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAIL QRIS (preview kiri seperti create) --}}
            <div x-show="detailTab === 'qris'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PREVIEW --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-32 h-32 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="detailItem?.path_gambar">
                                    <img :src="`{{ asset('storage') }}/${detailItem.path_gambar}`" alt="Preview QRIS"
                                        class="w-full h-full object-cover">
                                </template>

                                <template x-if="!detailItem?.path_gambar">
                                    <span class="text-[11px] text-text-muted">Tidak ada gambar</span>
                                </template>
                            </div>

                            <p class="text-[11px] text-text-muted text-center">
                                Preview QRIS yang tersimpan.
                            </p>
                        </div>
                    </div>

                    {{-- INFO --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-5 space-y-4">
                            <div>
                                <div class="text-[11px] font-bold uppercase text-text-muted mb-1">Nama QRIS</div>
                                <div class="rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm text-text-main font-semibold"
                                    x-text="detailItem?.nama_qris ?? '—'"></div>
                            </div>

                            <div>
                                <div class="text-[11px] font-bold uppercase text-text-muted mb-1">Keterangan</div>
                                <div class="rounded-xl border border-brand-borderSoft bg-brand-shell px-4 py-2 text-sm text-text-main whitespace-pre-line min-h-[44px]"
                                    x-text="detailItem?.keterangan ?? '—'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTION --}}
            <div class="flex items-center justify-end gap-2 pt-5">
                <x-ui.button-secondary type="button" @click="openDetail = false">
                    Tutup
                </x-ui.button-secondary>
            </div>
        </div>
    </div>
</div>
