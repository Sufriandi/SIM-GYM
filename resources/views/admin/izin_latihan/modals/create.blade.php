{{-- resources/views/admin/izin_latihan/modals/create.blade.php --}}

<div
    x-show="openCreate"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="resetCreateForm(); openCreate = false"
    @wheel.prevent
    @touchmove.prevent
>
    <div
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
    >
        {{-- HEADER MODAL --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Izin Manual</h2>
                <p class="text-sm text-text-muted mt-0.5">Catat izin yang diajukan langsung di tempat.</p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="resetCreateForm(); openCreate = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form
                x-ref="createForm"
                wire:submit.prevent="saveManual"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FILE --}}
                    <div class="md:col-span-1">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col gap-3">
                            <div class="w-full aspect-square border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                {{-- IMAGE --}}
                                <img
                                    x-show="fileUrl && fileType === 'image'"
                                    :src="fileUrl"
                                    alt="Preview Bukti Izin"
                                    class="object-cover w-full h-full"
                                >
                                {{-- PDF --}}
                                <embed
                                    x-show="fileUrl && fileType === 'pdf'"
                                    :src="fileUrl"
                                    type="application/pdf"
                                    class="w-full h-full"
                                />
                                {{-- OTHER DOC --}}
                                <div
                                    x-show="fileName && fileType === 'other'"
                                    class="flex flex-col items-center justify-center p-4 text-center"
                                >
                                    <div class="w-10 h-10 rounded-full border border-brand-borderSoft flex items-center justify-center mb-2">
                                        <span class="text-[11px] font-semibold text-text-main">
                                            DOC
                                        </span>
                                    </div>
                                    <p class="text-xs text-text-main break-all" x-text="fileName"></p>
                                    <p class="text-[11px] text-text-muted mt-1">
                                        File dokumen, preview tidak tersedia tapi file akan tersimpan.
                                    </p>
                                </div>
                                {{-- PLACEHOLDER --}}
                                <span
                                    x-show="!fileName"
                                    class="text-xs text-text-muted text-center p-2"
                                >
                                    Gambar & PDF ditampilkan di sini. Dokumen lain akan menampilkan nama file.
                                </span>
                            </div>

                            <p class="text-[11px] text-text-muted text-center">
                                Format didukung: Gambar (JPG/PNG), PDF, dokumen (DOC/DOCX).
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- MEMBER (SEARCHABLE) --}}
                                <div class="space-y-2">
                                    <x-ui.label for="member_search">Member</x-ui.label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="member_search"
                                            x-model="memberSearch"
                                            @focus="openMemberDropdown = true"
                                            @input="openMemberDropdown = true"
                                            placeholder="Cari nama / username member..."
                                            autocomplete="off"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        >

                                        {{-- Livewire simpan id member di properti member_id --}}
                                        <input type="hidden" wire:model="member_id">

                                        {{-- DROPDOWN MEMBER --}}
                                        <div
                                            x-show="openMemberDropdown"
                                            x-cloak
                                            class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg custom-scrollbar"
                                        >
                                            <template x-for="m in filteredMembers()" :key="m.id">
                                                <button
                                                    type="button"
                                                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50 flex justify-between items-center"
                                                    @click="selectMember(m)"
                                                >
                                                    <span x-text="m.label"></span>
                                                </button>
                                            </template>
                                            <div
                                                x-show="filteredMembers().length === 0"
                                                class="px-3 py-2 text-xs text-text-muted"
                                            >
                                                Member tidak ditemukan.
                                            </div>
                                        </div>
                                    </div>
                                    @error('member_id')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-text-muted">
                                        Ketik sebagian nama / username lalu pilih salah satu hasil.
                                    </p>
                                </div>

                                {{-- JUMLAH HARI --}}
                                <div>
                                    <x-ui.label for="jumlah_hari_create">Jumlah Hari</x-ui.label>
                                    <input
                                        type="number"
                                        id="jumlah_hari_create"
                                        min="1"
                                        max="30"
                                        wire:model.defer="jumlah_hari"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah_hari') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('jumlah_hari')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- TANGGAL MULAI --}}
                                <div>
                                    <x-ui.label for="tanggal_mulai_create">Tanggal Mulai</x-ui.label>
                                    <input
                                        type="date"
                                        id="tanggal_mulai_create"
                                        wire:model.defer="tanggal_mulai"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('tanggal_mulai') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('tanggal_mulai')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- BUKTI / FILE --}}
                                <div class="space-y-2">
                                    <x-ui.label for="bukti_alasan_create">Bukti / File (opsional)</x-ui.label>
                                    <input
                                        type="file"
                                        id="bukti_alasan_create"
                                        accept="image/*,.pdf,.doc,.docx"
                                        x-ref="buktiInput"
                                        wire:model="bukti_alasan"
                                        class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700 @error('bukti_alasan') border-danger ring-danger-soft @enderror"
                                        @change="handleFileChange($event)"
                                    >
                                    @error('bukti_alasan')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-text-muted mt-1">
                                        Maksimal 2MB. Gambar (JPG/PNG), PDF, atau dokumen (DOC/DOCX).
                                    </p>
                                </div>
                            </div>

                            {{-- ALASAN (KOLOM alasan DI DB) --}}
                            <div>
                                <x-ui.label for="alasan_create">Alasan Izin</x-ui.label>
                                <textarea
                                    id="alasan_create"
                                    rows="3"
                                    wire:model.defer="alasan"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('alasan') border-danger ring-danger-soft @enderror"
                                    placeholder="Contoh: Izin karena sakit, melampirkan surat dokter."
                                ></textarea>
                                @error('alasan')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary
                        type="button"
                        @click="resetCreateForm(); openCreate = false"
                    >
                        Batal
                    </x-ui.button-secondary>
                    <x-ui.button-primary
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveManual,bukti_alasan"
                    >
                        <span wire:loading wire:target="saveManual,bukti_alasan" class="animate-pulse">
                            Menyimpan...
                        </span>
                        <span wire:loading.remove wire:target="saveManual,bukti_alasan">
                            Simpan Izin
                        </span>
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
