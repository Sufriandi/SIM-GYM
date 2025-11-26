{{-- resources/views/admin/izin_latihan/index.blade.php --}}

@php
    use App\Models\Member;
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Permintaan Izin Baru';

    // Modal create otomatis terbuka kalau ada error validasi
    $openCreateOnLoad = $errors->any() ? 'true' : 'false';

    /**
     * Sumber data member untuk searchable dropdown di modal
     * PRIORITAS: $membersForSelect dari controller (sudah difilter hanya role "user")
     * fallback: query langsung (jaga-jaga kalau dipakai di tempat lain).
     */
    $sourceMembers = isset($membersForSelect)
        ? $membersForSelect
        : Member::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'user');
            })
            ->orderBy('nama')
            ->get(['id', 'nama', 'username', 'user_id']);

    $memberOptions = $sourceMembers
        ->map(function ($m) {
            return [
                'id'    => $m->id,
                'label' => $m->nama . ' (' . $m->username . ')',
            ];
        })
        ->toArray();

    $oldMemberId    = old('member_id');
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
    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-danger-soft border border-danger text-danger px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- STATE UTAMA UNTUK MODAL CREATE + DETAIL + APPROVE --}}
    <div
        x-data="{
            openCreate: {{ $openCreateOnLoad }},
            openDetailId: null,  // ID izin yang sedang dibuka detailnya
            openApproveId: null, // ID izin yang sedang dibuka approve form-nya
        }"
        x-on:open-izin-manual.window="openCreate = true"
        x-on:close-izin-manual.window="openCreate = false"
    >
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Permintaan izin yang belum diproses."
        />

        {{-- GARIS PEMBATAS --}}
        <hr class="border-t border-brand-borderSoft mb-4">

        {{-- TOMBOL AKSI ATAS --}}
        <div class="mb-8 flex justify-end gap-2">
            <x-ui.button-primary type="button" @click="$dispatch('open-izin-manual')">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                Tambah Izin Manual
            </x-ui.button-primary>

            <a href="{{ route('admin.izin_latihan.history') }}">
                <x-ui.button-secondary>
                    Riwayat Persetujuan
                </x-ui.button-secondary>
            </a>
        </div>

        {{-- CARD UTAMA: TABEL PERMINTAAN IZIN PENDING --}}
        <x-ui.card
            title="Daftar Permintaan Izin Pending"
            subtitle="Semua permintaan izin yang masih menunggu tindakan Admin."
            class="border-brand-borderSoft"
        >
            <div class="w-full">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- NO --}}
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No
                            </th>
                            {{-- MEMBER --}}
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Member
                            </th>
                            {{-- HARI DIAJUKAN --}}
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Hari Diajukan
                            </th>
                            {{-- TGL MULAI --}}
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Tgl Mulai
                            </th>
                            {{-- STATUS --}}
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Status
                            </th>
                            {{-- ALASAN --}}
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Alasan
                            </th>
                            {{-- AKSI --}}
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($daftar_izin as $izin)
                            {{-- h-24 → baris sedikit lebih tinggi supaya tooltip tidak nempel garis --}}
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 h-24">
                                {{-- NO (global pagination) --}}
                                <td class="p-3 text-center align-middle text-xs text-text-muted">
                                    {{ $loop->iteration + ($daftar_izin->currentPage() - 1) * $daftar_izin->perPage() }}
                                </td>

                                {{-- MEMBER --}}
                                <td class="p-3 text-left align-middle">
                                    <div class="text-sm {{ $izin->member ? 'text-text-main' : 'text-danger italic' }}">
                                        {{ $izin->member?->nama ?? '[Member Dihapus]' }}
                                    </div>
                                    @if ($izin->member)
                                        <div class="text-[11px] text-text-muted">
                                            ID Member: {{ $izin->member->kode_member ?? '-' }}
                                        </div>
                                    @endif
                                </td>

                                {{-- HARI DIAJUKAN --}}
                                <td class="p-3 text-center align-middle">
                                    <span class="text-sm font-semibold text-text-main">
                                        {{ $izin->jumlah_hari }} Hari
                                    </span>
                                </td>

                                {{-- TANGGAL MULAI --}}
                                <td class="p-3 text-center align-middle">
                                    <span class="text-sm text-text-muted">
                                        {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                                    </span>
                                </td>

                                {{-- STATUS --}}
                                <td class="p-3 text-center align-middle">
                                    <x-ui.badge variant="warning">
                                        Pending
                                    </x-ui.badge>
                                </td>

                                {{-- ALASAN (1 baris, truncate, kalau kosong jadi '-') --}}
                                <td class="p-3 text-left align-middle max-w-[220px]">
                                    <div class="text-xs text-text-muted line-clamp-1 overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $izin->alasan ? Str::limit($izin->alasan, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
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

                                            <span
                                                class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-info
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150"
                                            >
                                                Detail
                                            </span>
                                        </div>

                                        @if ($izin->member)
                                            {{-- SETUJUI (BUKA MODAL APPROVE) --}}
                                            <div class="relative group flex items-center justify-center">
                                                <button
                                                    type="button"
                                                    @click="openApproveId = {{ $izin->id }}"
                                                    class="p-2 rounded-full text-success hover:bg-success-soft/50 transition-colors duration-150"
                                                >
                                                    <i data-lucide="circle-check" class="w-6 h-6"></i>
                                                </button>

                                                <span
                                                    class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-success
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150"
                                                >
                                                    Setujui
                                                </span>
                                            </div>

                                            {{-- TOLAK --}}
                                            <div class="relative group flex items-center justify-center">
                                                <form
                                                    id="reject-form-{{ $izin->id }}"
                                                    action="{{ route('admin.izin_latihan.reject', $izin->id) }}"
                                                    method="POST"
                                                >
                                                    @csrf
                                                    <button
                                                        type="button"
                                                        class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                        onclick="confirmReject({{ $izin->id }}, '{{ $izin->member?->nama ?? 'Member' }}')"
                                                    >
                                                        <i data-lucide="circle-x" class="w-6 h-6"></i>
                                                    </button>
                                                </form>

                                                <span
                                                    class="pointer-events-none absolute top-full mt-1 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150"
                                                >
                                                    Tolak
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-[11px] italic text-danger">
                                                Aksi diblokir
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Tidak ada permintaan izin baru yang perlu diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION (compact) --}}
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
            @click.self="$dispatch('close-izin-manual')"
        >
            <div
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                x-data="{
                    // member searchable
                    members: @js($memberOptions),
                    search: @js($oldMemberLabel),
                    selectedId: @js($oldMemberId),
                    dropdownOpen: false,

                    // file preview
                    fileUrl: null,
                    fileName: '',
                    fileType: '',

                    matchMember(m) {
                        if (!this.search) return true;
                        return m.label.toLowerCase().includes(this.search.toLowerCase());
                    },

                    selectMember(m) {
                        this.selectedId = m.id;
                        this.search = m.label;
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
                            reader.onload = (ev) => {
                                this.fileUrl = ev.target.result;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.fileUrl = null;
                        }
                    },

                    resetForm() {
                        this.search = '';
                        this.selectedId = null;
                        this.dropdownOpen = false;
                        this.fileUrl = null;
                        this.fileName = '';
                        this.fileType = '';
                    },

                    closeModal() {
                        this.resetForm();
                        this.$dispatch('close-izin-manual');
                    }
                }"
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
                        @click="closeModal()"
                    >
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                {{-- ISI MODAL --}}
                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <form
                        method="POST"
                        action="{{ route('admin.izin_latihan.store.manual') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

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
                                                    x-model="search"
                                                    @focus="dropdownOpen = true"
                                                    @input="dropdownOpen = true"
                                                    placeholder="Cari nama / username member..."
                                                    autocomplete="off"
                                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                >
                                                <input type="hidden" name="member_id" :value="selectedId ?? ''">

                                                {{-- DROPDOWN --}}
                                                <div
                                                    x-show="dropdownOpen"
                                                    x-cloak
                                                    class="absolute z-50 mt-1 w-full max-h-52 overflow-y-auto rounded-xl border border-brand-borderSoft bg-brand-shell shadow-lg custom-scrollbar"
                                                >
                                                    <template x-for="m in members" :key="m.id">
                                                        <button
                                                            type="button"
                                                            x-show="matchMember(m)"
                                                            class="w-full text-left px-3 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                                            @click="selectMember(m)"
                                                            x-text="m.label"
                                                        ></button>
                                                    </template>
                                                    <div
                                                        x-show="members.filter(m => matchMember(m)).length === 0"
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
                                                Ketik sebagian nama / username lalu klik salah satu hasil.
                                            </p>
                                        </div>

                                        {{-- JUMLAH HARI --}}
                                        <div>
                                            <x-ui.label for="jumlah_hari_create">Jumlah Hari</x-ui.label>
                                            <input
                                                type="number"
                                                id="jumlah_hari_create"
                                                name="jumlah_hari"
                                                value="{{ old('jumlah_hari', 1) }}"
                                                min="1"
                                                max="30"
                                                required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
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
                                                name="tanggal_mulai"
                                                value="{{ old('tanggal_mulai', now()->toDateString()) }}"
                                                required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
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
                                                name="bukti_alasan"
                                                accept="image/*,.pdf,.doc,.docx"
                                                class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700"
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

                                    {{-- KETERANGAN -> DISIMPAN KE KOLOM "alasan" --}}
                                    <div>
                                        <x-ui.label for="keterangan_create">Keterangan</x-ui.label>
                                        <textarea
                                            id="keterangan_create"
                                            name="keterangan"
                                            rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            placeholder="Contoh: Izin karena sakit, melampirkan surat dokter."
                                        >{{ old('keterangan') }}</textarea>
                                        @error('keterangan')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <x-ui.button-secondary type="button" @click="closeModal()">
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

        {{-- MODAL DETAIL & APPROVE (SATU PER IZIN, TAPI TANPA RELOAD) --}}
        @foreach ($daftar_izin as $izin)
            @include('admin.izin_latihan.modals.detail', ['izin' => $izin])
            @include('admin.izin_latihan.modals.approve_form', ['izin' => $izin])
        @endforeach

    </div> {{-- penutup div x-data --}}

    {{-- SCRIPT KONFIRMASI TOLAK --}}
    <script>
        function confirmReject(izinId, memberName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Anda yakin ingin menolak permintaan izin dari ${memberName}?`)) {
                    document.getElementById('reject-form-' + izinId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Tolak Izin?',
                text: `Anda yakin ingin menolak permintaan izin dari ${memberName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reject-form-' + izinId).submit();
                }
            });
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.admin>
