@php
    use Illuminate\Support\Js;

    // dari controller: $members = Member::orderBy('nama')->get(['id','nama','username']);
    $openCreateOnLoad = $errors->any() ? 'true' : 'false';

    // Siapkan data member untuk Alpine (id + label)
    $memberOptions = ($members ?? collect())->map(function ($m) {
        return [
            'id' => $m->id,
            'label' => trim($m->nama . ($m->username ? ' (' . $m->username . ')' : '')),
        ];
    });
@endphp

<div x-data="{
    openCreate: {{ $openCreateOnLoad }},
    members: {{ Js::from($memberOptions) }},
    search: '',
    selectedId: {{ old('member_id') ? (int) old('member_id') : 'null' }},
    selectedLabel: '',
    filePreviewUrl: null,
    filePreviewType: null,
    fileName: '',

    init() {
        if (this.selectedId) {
            const found = this.members.find(m => m.id == this.selectedId);
            if (found) this.selectedLabel = found.label;
        }
    },

    get filteredMembers() {
        if (!this.search) return this.members;
        const s = this.search.toLowerCase();
        return this.members.filter(m => m.label.toLowerCase().includes(s));
    },

    selectMember(m) {
        this.selectedId = m.id;
        this.selectedLabel = m.label;
    },

    onFileChange(event) {
        const file = event.target.files[0];
        if (!file) {
            this.filePreviewUrl = null;
            this.filePreviewType = null;
            this.fileName = '';
            return;
        }

        this.fileName = file.name;
        const ext = file.name.split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            this.filePreviewType = 'image';
            this.filePreviewUrl = URL.createObjectURL(file);
        } else if (ext === 'pdf') {
            this.filePreviewType = 'pdf';
            this.filePreviewUrl = URL.createObjectURL(file);
        } else {
            this.filePreviewType = 'other';
            this.filePreviewUrl = null;
        }
    },

    resetCreateModal() {
        this.search = '';
        this.selectedId = null;
        this.selectedLabel = '';
        this.filePreviewUrl = null;
        this.filePreviewType = null;
        this.fileName = '';
        if (this.$refs.createForm) {
            this.$refs.createForm.reset();
        }
    }
}" x-init="init()">
    {{-- TOMBOL TAMBAH IZIN MANUAL --}}
    <div class="mb-8 flex justify-end gap-2">
        <x-ui.button-primary type="button" @click="resetCreateModal(); openCreate = true">
            + Tambah Izin Manual
        </x-ui.button-primary>

        <a href="{{ route('admin.izin_latihan.history') }}">
            <x-ui.button-secondary>
                Riwayat Persetujuan
            </x-ui.button-secondary>
        </a>
    </div>

    {{-- ============================ --}}
    {{-- MODAL TAMBAH IZIN MANUAL     --}}
    {{-- ============================ --}}
    <div x-show="openCreate" x-cloak x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">
        <div @click.away="resetCreateModal(); openCreate = false"
            class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
            {{-- HEADER MODAL --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                <div>
                    <h2 class="text-xl font-semibold text-text-main">Tambah Izin Manual</h2>
                    <p class="text-sm text-text-muted mt-0.5">Catat izin yang diajukan langsung di tempat.</p>
                </div>
                <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                    @click="resetCreateModal(); openCreate = false">
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>

            {{-- ISI MODAL --}}
            <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                <form x-ref="createForm" method="POST" action="{{ route('admin.izin_latihan.store.manual') }}"
                    enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- ERROR VALIDASI CREATE (JIKA ADA) --}}
                    @if ($errors->any())
                        <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                            <p class="text-sm font-semibold">Ada kesalahan input:</p>
                            <ul class="list-disc list-inside text-xs mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- PANEL KIRI: PREVIEW BUKTI --}}
                        <div class="md:col-span-1">
                            <div
                                class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                <div
                                    class="w-full aspect-[4/3] border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                    {{-- Gambar --}}
                                    <template x-if="filePreviewType === 'image'">
                                        <img :src="filePreviewUrl" alt="Preview Bukti Izin"
                                            class="object-cover w-full h-full">
                                    </template>

                                    {{-- PDF --}}
                                    <template x-if="filePreviewType === 'pdf'">
                                        <iframe :src="filePreviewUrl" class="w-full h-full"></iframe>
                                    </template>

                                    {{-- Dokumen lain --}}
                                    <template x-if="filePreviewType === 'other'">
                                        <div class="flex flex-col items-center gap-1">
                                            <i data-lucide="file-text" class="w-8 h-8 text-text-muted"></i>
                                            <span class="text-[11px] text-text-muted" x-text="fileName"></span>
                                        </div>
                                    </template>

                                    {{-- Belum ada file --}}
                                    <template x-if="!filePreviewType">
                                        <span class="text-xs text-text-muted text-center px-3">
                                            Preview bukti izin (gambar / PDF) akan muncul di sini setelah memilih file.
                                        </span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-text-muted text-center">
                                    Lokasi simpan: <span
                                        class="font-mono text-[10px]">public/images/uploads/bukti_izin</span>
                                </p>
                            </div>
                        </div>

                        {{-- PANEL KANAN: FORM --}}
                        <div class="md:col-span-2">
                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                                {{-- MEMBER (SEARCHABLE) --}}
                                <div>
                                    <x-ui.label for="member_search">Member</x-ui.label>

                                    <div class="space-y-2">
                                        <input type="text" id="member_search" x-model="search"
                                            placeholder="Cari nama / username member..."
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">

                                        <input type="hidden" name="member_id" :value="selectedId">

                                        @error('member_id')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror

                                        {{-- LIST MEMBER --}}
                                        <div class="max-h-40 rounded-xl border border-brand-borderSoft/70 bg-brand-shell overflow-y-auto custom-scrollbar"
                                            x-show="filteredMembers.length">
                                            <template x-for="m in filteredMembers" :key="m.id">
                                                <button type="button"
                                                    class="w-full text-left px-3 py-1.5 text-xs hover:bg-brand-surface-50 flex justify-between items-center"
                                                    @click="selectMember(m)">
                                                    <span x-text="m.label"></span>
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full border"
                                                        :class="selectedId === m.id ?
                                                            'border-primary text-primary-dark bg-primary-soft/40' :
                                                            'border-transparent text-text-muted'">
                                                        <span x-show="selectedId === m.id">Dipilih</span>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>

                                        <p class="text-[11px] text-text-muted">
                                            Dipilih:
                                            <span class="font-medium"
                                                x-text="selectedLabel || 'Belum ada member dipilih'"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- JUMLAH HARI & TANGGAL MULAI --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- JUMLAH HARI --}}
                                    <div>
                                        <x-ui.label for="jumlah_hari">Jumlah Hari Izin</x-ui.label>
                                        <input type="number" name="jumlah_hari" id="jumlah_hari"
                                            value="{{ old('jumlah_hari', 1) }}" min="1" max="30" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah_hari') border-danger ring-danger-soft @enderror">
                                        @error('jumlah_hari')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- TANGGAL MULAI --}}
                                    <div>
                                        <x-ui.label for="tanggal_mulai">Tanggal Mulai Izin</x-ui.label>
                                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                            value="{{ old('tanggal_mulai', now()->toDateString()) }}" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('tanggal_mulai') border-danger ring-danger-soft @enderror">
                                        @error('tanggal_mulai')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ALASAN --}}
                                <div>
                                    <x-ui.label for="alasan">Alasan Izin</x-ui.label>
                                    <textarea name="alasan" id="alasan" rows="3"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('alasan') border-danger ring-danger-soft @enderror"
                                        placeholder="Contoh: Izin karena sakit, melampirkan surat dokter.">{{ old('alasan') }}</textarea>
                                    @error('alasan')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- BUKTI / DOKUMEN --}}
                                <div class="space-y-2">
                                    <x-ui.label for="bukti_alasan">Bukti / Dokumen (opsional)</x-ui.label>
                                    <input type="file" name="bukti_alasan" id="bukti_alasan"
                                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                                        class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700 @error('bukti_alasan') border-danger ring-danger-soft @enderror"
                                        @change="onFileChange($event)">
                                    @error('bukti_alasan')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-text-muted mt-1">
                                        Maksimal 4MB. Format: JPG, PNG, PDF, DOC/X, XLS/X.
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3">
                        <x-ui.button-secondary type="button" @click="resetCreateModal(); openCreate = false">
                            Batal
                        </x-ui.button-secondary>
                        <x-ui.button-primary type="submit">
                            Simpan Izin
                        </x-ui.button-primary>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
