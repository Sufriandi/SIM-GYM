{{-- resources/views/admin/izin_latihan/index.blade.php --}}

@php
    use App\Models\Member;

    $pageTitle = $pageTitle ?? 'Permintaan Izin Baru';
    $openCreateOnLoad = $errors->hasBag('izin_manual') && $errors->izin_manual->any() ? 'true' : 'false';

    $sourceMembers = isset($membersForSelect)
        ? $membersForSelect
        : Member::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'member');
            })
            ->orderBy('nama')
            ->get(['id', 'nama', 'user_id']);

    $memberOptions = $sourceMembers->map(function ($m) {
        $nama = trim((string) ($m->user?->name ?? $m->nama ?? ''));
        $username = $m->user?->username ?? '';

        return [
            'id'       => $m->id,
            'nama'     => $nama,
            'username' => $username,
            'label'    => trim($nama . ($username ? ' (' . $username . ')' : '')),
        ];
    })->values()->toArray();

    $oldMemberId = old('member_id');
    $oldMemberLabel = '';

    if ($oldMemberId) {
        $found = collect($memberOptions)->firstWhere('id', (int) $oldMemberId);
        $oldMemberLabel = $found['label'] ?? '';
    }
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Permintaan izin yang belum diproses."
>
    {{-- MAIN WRAPPER (ALPINE ROOT) --}}
    <div
        x-data="{
            // === STATE UTAMA ===
            openCreate: {{ $openCreateOnLoad }},
            openDetailId: null,
            openApproveId: null,
            openRejectId: null,

            // Search realtime (frontend)
            searchTerm: @js(request('q')),

            // === STATE MODAL CREATE ===
            members: @js($memberOptions),

            create: {
                memberSearch: @js($oldMemberLabel),
                memberId: @js($oldMemberId),
                jumlahHari: {{ json_encode(old('jumlah_hari', 1)) }},
                tanggalMulai: '{{ old('tanggal_mulai', now()->toDateString()) }}',
                alasan: @js(old('alasan')),
            },

            dropdownOpen: false,

            fileUrl: null,
            fileName: '',
            fileType: '',

            // === FUNCTIONS ===
            openCreateModal() {
                this.resetCreateForm();
                this.openCreate = true;
            },

            closeCreateModal() {
                this.openCreate = false;
                this.resetCreateForm();
            },

            resetCreateForm() {
                this.create.memberSearch = '';
                this.create.memberId = null;
                this.create.jumlahHari = 1;
                this.create.tanggalMulai = '{{ now()->toDateString() }}';
                this.create.alasan = '';

                this.dropdownOpen = false;

                this.fileUrl = null;
                this.fileName = '';
                this.fileType = '';

                if (this.$refs.buktiInput) {
                    this.$refs.buktiInput.value = null;
                }
            },

            matchMember(m) {
                if (!this.create.memberSearch) return true;
                return (m.label || '').toLowerCase().includes(this.create.memberSearch.toLowerCase());
            },

            selectMember(m) {
                this.create.memberId = m.id;
                this.create.memberSearch = m.label;
                this.dropdownOpen = false;
            },

            handleFileChange(e) {
                const file = e.target.files[0];
                if (!file) {
                    this.fileUrl = null;
                    this.fileName = '';
                    this.fileType = '';
                    return;
                }

                this.fileName = file.name;
                const mime = file.type || '';
                const lower = file.name.toLowerCase();

                if (mime.startsWith('image/')) {
                    this.fileType = 'image';
                } else if (mime === 'application/pdf' || lower.endsWith('.pdf')) {
                    this.fileType = 'pdf';
                } else {
                    this.fileType = 'other';
                }

                if (this.fileType === 'image' || this.fileType === 'pdf') {
                    const reader = new FileReader();
                    reader.onload = (ev) => { this.fileUrl = ev.target.result; };
                    reader.readAsDataURL(file);
                } else {
                    this.fileUrl = null;
                }
            },
        }"
        x-on:open-izin-manual.window="openCreateModal()"
        x-effect="
            const main = document.querySelector('main');
            const html = document.documentElement;
            const body = document.body;

            const locked = openCreate || openDetailId || openApproveId || openRejectId;
            const targets = [html, body, main].filter(Boolean);

            if (locked) {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY === undefined) {
                        el.dataset.prevOverflowY = el.style.overflowY || '';
                    }
                    el.style.overflowY = 'hidden';
                });
            } else {
                targets.forEach((el) => {
                    if (el.dataset.prevOverflowY !== undefined) {
                        el.style.overflowY = el.dataset.prevOverflowY;
                        delete el.dataset.prevOverflowY;
                    } else {
                        el.style.removeProperty('overflow-y');
                    }
                });
            }
        "
    >
        <x-ui.section-header :title="$pageTitle" subtitle="Permintaan izin yang belum diproses." />

        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- SEARCH + FILTER + ACTION --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH + FILTER --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.izin_latihan.index') }}" method="GET" x-ref="searchForm">
                    <div class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input
                            type="text"
                            name="q"
                            x-model="searchTerm"
                            placeholder="Cari nama member..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full"
                        >

                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                        <button
                            type="button"
                            @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-text-muted hover:text-text-main mr-1 rounded-full hover:bg-brand-surface-50"
                        >
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                    </div>

                    {{-- FILTER DROPDOWN --}}
                    <div
                        x-show="showFilter"
                        x-cloak
                        @click.outside="showFilter = false"
                        class="absolute top-[48px] left-0 w-full bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-10"
                    >
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter & Urutan</h4>
                                <a href="{{ route('admin.izin_latihan.index') }}" class="text-xs text-danger hover:underline">
                                    Reset
                                </a>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                    Urutkan berdasarkan
                                </label>

                                <select name="sort" class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2">
                                    <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>
                                        Waktu pengajuan · Terbaru
                                    </option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>
                                        Waktu pengajuan · Terlama
                                    </option>
                                    <option value="days_max" {{ request('sort') == 'days_max' ? 'selected' : '' }}>
                                        Hari diajukan · Terbanyak
                                    </option>
                                    <option value="days_min" {{ request('sort') == 'days_min' ? 'selected' : '' }}>
                                        Hari diajukan · Tersedikit
                                    </option>
                                </select>

                                <p class="text-[10px] text-text-muted mt-1">
                                    Pilih izin terbaru, terlama, atau berdasarkan banyaknya hari.
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-primary-dark text-white text-sm font-medium py-2 rounded-lg">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center gap-2">
                <x-ui.button-primary type="button" @click="openCreateModal()">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                    Tambah Manual
                </x-ui.button-primary>

                <a href="{{ route('admin.izin_latihan.history') }}">
                    <x-ui.button-secondary>
                        <i data-lucide="history" class="w-4 h-4 mr-1"></i>
                        Riwayat
                    </x-ui.button-secondary>
                </a>
            </div>
        </div>

        {{-- TABLE --}}
        <x-ui.card class="border-brand-borderSoft">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Permintaan Izin</h3>
                    <p class="text-xs text-text-muted mt-0.5">Semua permintaan izin yang masih menunggu tindakan.</p>
                </div>

                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $daftar_izin->total() }} Pending
                    </span>
                </div>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[800px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">No</th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">Member</th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">Hari Diajukan</th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">Tgl Mulai</th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">Status</th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">Alasan</th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($daftar_izin as $izin)
                            <tr
                                x-data="{ memberName: @js($izin->member?->nama ?? '') }"
                                x-show="!searchTerm || memberName.toLowerCase().includes(searchTerm.toLowerCase())"
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-24"
                            >
                                <td class="p-3 text-center align-middle text-xs text-text-muted">
                                    {{ $loop->iteration + ($daftar_izin->currentPage() - 1) * $daftar_izin->perPage() }}
                                </td>

                                <td class="p-3 text-left align-middle">
                                    
                                        @if ($izin->member->user?->name)
                                            <div class="text-base font-bold text-text-main">
                                                {{ $izin->member->user->name }}
                                            </div>
                                        
                                    @else
                                        <div class="text-sm text-danger italic">[Member Dihapus]</div>
                                    @endif
                                </td>

                                <td class="p-3 text-center align-middle">
                                    <span class="text-sm font-semibold text-text-main">
                                        {{ $izin->jumlah_hari }}
                                    </span>
                                    <span class="ml-1 text-xs md:text-[13px] text-text-muted align-middle">
                                            Hari
                                        </span>
                                </td>

                                <td class="p-3 text-center align-middle">
                                    <span class="text-sm text-text-muted">
                                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                                    </span>
                                </td>

                                <td class="p-3 text-center align-middle">
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                </td>

                                <td class="p-3 text-left align-middle max-w-[220px]">
                                    <div class="text-xs text-text-muted line-clamp-1 overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $izin->alasan ? \Illuminate\Support\Str::limit($izin->alasan, 80) : '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 align-middle w-[16%] min-w-[140px]">
                                    <div class="flex items-center justify-center gap-4 h-full">

                                        {{-- DETAIL --}}
                                        <div class="relative group flex items-center justify-center">
                                            <button
                                                type="button"
                                                @click="openDetailId = {{ $izin->id }}"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                            >
                                                <i data-lucide="eye" class="w-6 h-6"></i>
                                            </button>

                                            <span class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                text-[10px] font-medium text-info opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                Detail
                                            </span>
                                        </div>

                                        @if ($izin->member)
                                            {{-- SETUJUI --}}
                                            <div class="relative group flex items-center justify-center">
                                                <button
                                                    type="button"
                                                    @click="openApproveId = {{ $izin->id }}"
                                                    class="p-2 rounded-full text-success hover:bg-success-soft/50 transition-colors duration-150"
                                                >
                                                    <i data-lucide="circle-check" class="w-6 h-6"></i>
                                                </button>

                                                <span class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                    text-[10px] font-medium text-success opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                    Setujui
                                                </span>
                                            </div>

                                            {{-- TOLAK --}}
                                            <div class="relative group flex items-center justify-center">
                                                <button
                                                    type="button"
                                                    @click="openRejectId = {{ $izin->id }}"
                                                    class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                >
                                                    <i data-lucide="circle-x" class="w-6 h-6"></i>
                                                </button>

                                                <span class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                    text-[10px] font-medium text-danger opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                                    Tolak
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-[11px] italic text-danger">Aksi diblokir</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Tidak ada permintaan izin baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $daftar_izin->onEachSide(1)->links() }}
            </div>
        </x-ui.card>

        {{-- ============================ --}}
        {{-- MODAL TAMBAH IZIN MANUAL     --}}
        {{-- ============================ --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
            @click.self="closeCreateModal()"
            @keydown.escape.window="closeCreateModal()"
            @wheel.self.prevent
@touchmove.self.prevent
        >
            <div class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">
                {{-- HEADER MODAL --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Tambah Izin Manual</h2>
                        <p class="text-sm text-text-muted mt-0.5">Catat izin yang diajukan langsung di tempat.</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="closeCreateModal()">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                {{-- ISI MODAL --}}
                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <form method="POST" action="{{ route('admin.izin_latihan.store.manual') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- PANEL KIRI: PREVIEW FILE --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col gap-3">
                                    <div class="w-full aspect-square border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                        <img x-show="fileUrl && fileType === 'image'" :src="fileUrl" alt="Preview Bukti Izin" class="object-cover w-full h-full">
                                        <embed x-show="fileUrl && fileType === 'pdf'" :src="fileUrl" type="application/pdf" class="w-full h-full" />

                                        <div x-show="fileName && fileType === 'other'" class="flex flex-col items-center justify-center p-4 text-center">
                                            <div class="w-10 h-10 rounded-full border border-brand-borderSoft flex items-center justify-center mb-2">
                                                <span class="text-[11px] font-semibold text-text-main">DOC</span>
                                            </div>
                                            <p class="text-xs text-text-main break-all" x-text="fileName"></p>
                                            <p class="text-[11px] text-text-muted mt-1">
                                                File dokumen, preview tidak tersedia tapi file akan tersimpan.
                                            </p>
                                        </div>

                                        <span x-show="!fileName" class="text-xs text-text-muted text-center p-2">
                                            Gambar & PDF ditampilkan di sini. Dokumen lain akan menampilkan nama file.
                                        </span>
                                    </div>

                                    <p class="text-[11px] text-text-muted text-center">
                                        Format didukung: Gambar (JPG/PNG), PDF, dokumen (DOC/DOCX). Maks 10MB.
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

    <div class="relative" @click.outside="dropdownOpen = false">
        <input
            type="text"
            id="member_search"
            x-model="create.memberSearch"
            @focus="dropdownOpen = true"
            @input="dropdownOpen = true"
            @keydown.escape.stop="dropdownOpen = false"
            placeholder="Cari nama / username..."
            autocomplete="off"
            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
        >

        <input type="hidden" name="member_id" :value="create.memberId ?? ''">

        <div
            x-show="dropdownOpen"
            x-cloak
            class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto overscroll-contain rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg custom-scrollbar"
        >
            <template x-for="m in members" :key="m.id">
                <button
                    type="button"
                    x-show="matchMember(m)"
                    class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                    @mousedown.prevent
                    @click="selectMember(m)"
                    x-text="m.label"
                ></button>
            </template>

            <div x-show="members.filter(m => matchMember(m)).length === 0" class="px-3 py-2 text-xs text-text-muted">
                Member tidak ditemukan.
            </div>
        </div>
    </div>

    @error('member_id', 'izin_manual')
        <p class="text-xs text-danger mt-1">{{ $message }}</p>
    @enderror
</div>


                                        {{-- JUMLAH HARI --}}
                                        <div>
                                            <x-ui.label for="jumlah_hari_create">Jumlah Hari</x-ui.label>
                                            <input
                                                type="number"
                                                id="jumlah_hari_create"
                                                name="jumlah_hari"
                                                x-model.number="create.jumlahHari"
                                                min="1"
                                                max="30"
                                                required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            >
                                            @error('jumlah_hari', 'izin_manual')
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
                                                name="tanggal_mulai"
                                                x-model="create.tanggalMulai"
                                                required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            >
                                            @error('tanggal_mulai', 'izin_manual')
                                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- BUKTI / FILE --}}
                                        <div class="space-y-2">
                                            <x-ui.label for="bukti_alasan_create">Bukti / File (opsional)</x-ui.label>
                                            <input
                                                type="file"
                                                id="bukti_alasan_create"
                                                name="bukti_alasan"
                                                accept="image/*,.pdf,.doc,.docx"
                                                x-ref="buktiInput"
                                                class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700"
                                                @change="handleFileChange($event)"
                                            >
                                            @error('bukti_alasan', 'izin_manual')
                                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                            @enderror
                                            <p class="text-[11px] text-text-muted mt-1">
                                                Maksimal 10MB. Gambar (JPG/PNG), PDF, atau dokumen (DOC/DOCX).
                                            </p>
                                        </div>
                                    </div>

                                    {{-- ALASAN --}}
                                    <div>
                                        <x-ui.label for="alasan_create">Alasan</x-ui.label>
                                        <textarea
                                            id="alasan_create"
                                            name="alasan"
                                            rows="3"
                                            x-model="create.alasan"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            placeholder="Contoh: Izin karena sakit, melampirkan surat dokter."
                                        ></textarea>
                                        @error('alasan', 'izin_manual')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <x-ui.button-secondary type="button" @click="closeCreateModal()">Batal</x-ui.button-secondary>
                            <x-ui.button-primary type="submit">Simpan Izin</x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL, APPROVE & REJECT (SATU PER IZIN) --}}
        @foreach ($daftar_izin as $izin)
            @include('admin.izin_latihan.modals.detail', ['izin' => $izin])
            @include('admin.izin_latihan.modals.approve_form', ['izin' => $izin])
            @include('admin.izin_latihan.modals.reject_form', ['izin' => $izin])
        @endforeach

    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.admin>
