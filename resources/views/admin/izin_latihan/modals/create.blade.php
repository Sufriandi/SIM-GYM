@php
    use Illuminate\Support\Js;

    $openCreateOnLoad = $errors->hasBag('izin_manual') && $errors->izin_manual->any() ? 'true' : 'false';

    $sourceMembers = $membersForSelect ?? collect();

    $memberOptions = $sourceMembers->map(function ($m) {
        $nama = trim((string) ($m->user?->name ?? $m->nama ?? ''));
        $username = trim((string) ($m->user?->username ?? $m->username ?? ''));

        return [
            'id'       => $m->id,
            'nama'     => $nama,
            'username' => $username,
            // tanpa '@'
            'label'    => trim($nama . ($username ? ' (' . $username . ')' : '')),
        ];
    })->values()->toArray();

    $oldMemberId = old('member_id', old('member_id', null));
    $oldMemberLabel = '';

    if ($oldMemberId) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldMemberId);
        $oldMemberLabel = $found['label'] ?? '';
    }
@endphp

<div
    x-data="{
        openCreate: {{ $openCreateOnLoad }},
        members: {{ Js::from($memberOptions) }},

        search: '',
        dropdownOpen: false,

        selectedId: {{ old('member_id') ? (int) old('member_id') : 'null' }},
        selectedLabel: '',

        filePreviewUrl: null,
        filePreviewType: null,
        fileName: '',
        _objectUrl: null,

        init() {
            if (this.selectedId) {
                const found = this.members.find(m => m.id == this.selectedId);
                if (found) {
                    this.selectedLabel = found.label;
                    this.search = found.label; // tampilkan label di input
                }
            }
        },

        get filteredMembers() {
            const s = (this.search || '').toLowerCase().trim();
            if (!s) return this.members;

            return this.members.filter(m => {
                const nama = (m.nama || '').toLowerCase();
                const username = (m.username || '').toLowerCase();
                const label = (m.label || '').toLowerCase();
                return nama.includes(s) || username.includes(s) || label.includes(s);
            });
        },

        openDropdown() { this.dropdownOpen = true; },
        closeDropdown() { this.dropdownOpen = false; },

        selectMember(m) {
            this.selectedId = m.id;
            this.selectedLabel = m.label;
            this.search = m.label;
            this.dropdownOpen = false;
        },

        onFileChange(event) {
            const file = event.target.files[0];

            if (this._objectUrl) {
                URL.revokeObjectURL(this._objectUrl);
                this._objectUrl = null;
            }

            if (!file) {
                this.filePreviewUrl = null;
                this.filePreviewType = null;
                this.fileName = '';
                return;
            }

            this.fileName = file.name;
            const ext = file.name.split('.').pop().toLowerCase();

            if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                this.filePreviewType = 'image';
                this._objectUrl = URL.createObjectURL(file);
                this.filePreviewUrl = this._objectUrl;
            } else if (ext === 'pdf') {
                this.filePreviewType = 'pdf';
                this._objectUrl = URL.createObjectURL(file);
                this.filePreviewUrl = this._objectUrl;
            } else {
                this.filePreviewType = 'other';
                this.filePreviewUrl = null;
            }
        },

        resetCreateModal() {
            this.search = '';
            this.dropdownOpen = false;
            this.selectedId = null;
            this.selectedLabel = '';

            if (this._objectUrl) {
                URL.revokeObjectURL(this._objectUrl);
                this._objectUrl = null;
            }

            this.filePreviewUrl = null;
            this.filePreviewType = null;
            this.fileName = '';

            if (this.$refs.createForm) this.$refs.createForm.reset();
        }
    }"
    x-init="init()"
>
    <div class="mb-8 flex justify-end gap-2">
        <x-ui.button-primary type="button" @click="resetCreateModal(); openCreate = true">
            + Tambah Izin Manual
        </x-ui.button-primary>

        <a href="{{ route('admin.izin_latihan.history') }}">
            <x-ui.button-secondary>Riwayat Persetujuan</x-ui.button-secondary>
        </a>
    </div>

    <div
        x-show="openCreate" x-cloak x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        @keydown.escape.window="resetCreateModal(); openCreate = false"
    >
        <div
            @click.away="resetCreateModal(); openCreate = false"
            class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        >
            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                <div>
                    <h2 class="text-xl font-semibold text-text-main">Tambah Izin Manual</h2>
                    <p class="text-sm text-text-muted mt-0.5">Catat izin yang diajukan langsung di tempat.</p>
                </div>
                <button
                    type="button"
                    class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                    @click="resetCreateModal(); openCreate = false"
                >
                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                </button>
            </div>

            <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                <form
                    x-ref="createForm"
                    method="POST"
                    action="{{ route('admin.izin_latihan.store.manual') }}"
                    enctype="multipart/form-data"
                    class="space-y-5"
                >
                    @csrf

                    @if ($errors->hasBag('izin_manual') && $errors->izin_manual->any())
    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
        <p class="text-sm font-semibold">Ada kesalahan input:</p>
        <ul class="list-disc list-inside text-xs mt-1">
            @foreach ($errors->izin_manual->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- PANEL KIRI: PREVIEW BUKTI --}}
                        <div class="md:col-span-1">
                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                <div class="w-full aspect-[4/3] border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                    <template x-if="filePreviewType === 'image'">
                                        <img :src="filePreviewUrl" alt="Preview Bukti Izin" class="object-cover w-full h-full">
                                    </template>

                                    <template x-if="filePreviewType === 'pdf'">
                                        <iframe :src="filePreviewUrl" class="w-full h-full"></iframe>
                                    </template>

                                    <template x-if="filePreviewType === 'other'">
                                        <div class="flex flex-col items-center gap-1">
                                            <i data-lucide="file-text" class="w-8 h-8 text-text-muted"></i>
                                            <span class="text-[11px] text-text-muted" x-text="fileName"></span>
                                        </div>
                                    </template>

                                    <template x-if="!filePreviewType">
                                        <span class="text-xs text-text-muted text-center px-3">
                                            Preview bukti izin (gambar / PDF) akan muncul di sini setelah memilih file.
                                        </span>
                                    </template>
                                </div>

                                <p class="text-[11px] text-text-muted text-center">
                                    Format: JPG/PNG, PDF, DOC/DOCX. Maks 2MB.
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
                                        <div class="relative" @click.away="closeDropdown()">
                                            <input
                                                type="text"
                                                id="member_search"
                                                x-model="search"
                                                placeholder="Cari nama / username..."
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                @focus="openDropdown()"
                                                @click="openDropdown()"
                                                @input="openDropdown()"
                                                @keydown.escape.prevent="closeDropdown()"
                                                autocomplete="off"
                                            >

                                            <input type="hidden" name="member_id" :value="selectedId ?? ''">

                                            {{-- DROPDOWN: stabil + scroll --}}
                                            <div
                                                x-show="dropdownOpen"
                                                x-cloak
                                                class="absolute z-50 mt-2 w-full max-h-56 overflow-y-auto overscroll-contain
                                                       rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg custom-scrollbar"
                                                @click.stop
                                                @wheel.stop
                                                @mousedown.prevent
                                            >
                                                <template x-if="filteredMembers.length === 0">
                                                    <div class="px-3 py-2 text-xs text-text-muted">
                                                        Tidak ada member yang cocok.
                                                    </div>
                                                </template>

                                                <template x-for="m in filteredMembers" :key="m.id">
                                                    <button
                                                        type="button"
                                                        class="w-full text-left px-3 py-2 hover:bg-brand-surface-50"
                                                        @click="selectMember(m)"
                                                    >
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="leading-tight">
                                                                <div class="text-sm font-semibold text-text-main" x-text="m.nama || '-'"></div>
                                                                <div class="text-[11px] text-text-muted" x-text="m.username ? '(' + m.username + ')' : ''"></div>
                                                            </div>

                                                            <span
                                                                class="text-[10px] px-2 py-0.5 rounded-full border"
                                                                :class="selectedId === m.id
                                                                    ? 'border-primary text-primary-dark bg-primary-soft/40'
                                                                    : 'border-transparent text-text-muted'"
                                                            >
                                                                <span x-show="selectedId === m.id">Dipilih</span>
                                                            </span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        @error('member_id', 'izin_manual')
  <p class="text-xs text-danger mt-1">{{ $message }}</p>
@enderror


                                        <p class="text-[11px] text-text-muted">
                                            Dipilih:
                                            <span class="font-medium" x-text="selectedLabel || 'Belum ada member dipilih'"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- JUMLAH HARI & TANGGAL MULAI --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-ui.label for="jumlah_hari">Jumlah Hari Izin</x-ui.label>
                                        <input type="number" name="jumlah_hari" id="jumlah_hari"
                                            value="{{ old('jumlah_hari', 1) }}" min="1" max="30" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent {{ ($errors->hasBag('izin_manual') && $errors->izin_manual->has('jumlah_hari')) ? 'border-danger ring-danger-soft' : '' }}">
                                        @error('jumlah_hari', 'izin_manual')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label for="tanggal_mulai">Tanggal Mulai Izin</x-ui.label>
                                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                            value="{{ old('tanggal_mulai', now()->toDateString()) }}" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent {{ ($errors->hasBag('izin_manual') && $errors->izin_manual->has('tanggal_mulai')) ? 'border-danger ring-danger-soft' : '' }}">
                                        @error('tanggal_mulai', 'izin_manual')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- ALASAN --}}
                                <div>
                                    <x-ui.label for="alasan">Alasan Izin</x-ui.label>
                                    <textarea name="alasan" id="alasan" rows="3"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent {{ ($errors->hasBag('izin_manual') && $errors->izin_manual->has('alasan')) ? 'border-danger ring-danger-soft' : '' }}"
                                        placeholder="Contoh: Izin karena sakit, melampirkan surat dokter.">{{ old('alasan') }}</textarea>
                                    @error('alasan', 'izin_manual')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- BUKTI --}}
                                <div class="space-y-2">
                                    <x-ui.label for="bukti_alasan">Bukti / Dokumen (opsional)</x-ui.label>
                                    <input type="file" name="bukti_alasan" id="bukti_alasan"
                                        accept="image/*,.pdf,.doc,.docx"
                                        class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700 {{ ($errors->hasBag('izin_manual') && $errors->izin_manual->has('bukti_alasan')) ? 'border-danger ring-danger-soft' : '' }}"
                                        @change="onFileChange($event)">
                                    @error('bukti_alasan', 'izin_manual')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-text-muted mt-1">
                                        Maksimal 2MB. Format: JPG/PNG, PDF, DOC/DOCX.
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
