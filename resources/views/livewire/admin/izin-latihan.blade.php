{{-- resources/views/livewire/admin/izin-latihan.blade.php --}}

@php
    use Illuminate\Support\Str;

    $pageTitle = 'Permintaan Izin Baru';

    // Data member untuk searchable dropdown di modal (dikirim dari Livewire: $membersForSelect)
    $memberOptions = collect($membersForSelect ?? [])->map(fn($m) => [
        'id'    => $m->id,
        'label' => $m->nama . ' (' . $m->username . ')',
    ])->toArray();
@endphp

<div
    x-data="{
        // STATE GLOBAL MODAL
        openCreate: false,
        openDetailId: null,
        openApproveId: null,

        // Search global (sinkron sama Livewire $search)
        searchTerm: @js($this->search),

        // DATA MEMBER UNTUK DROPDOWN (CREATE)
        members: @js($memberOptions),
        memberSearch: '',
        openMemberDropdown: false,

        // PREVIEW FILE
        fileUrl: null,
        fileName: '',
        fileType: '',

        resetCreateForm() {
            this.memberSearch       = '';
            this.openMemberDropdown = false;
            this.fileUrl            = null;
            this.fileName           = '';
            this.fileType           = '';

            if (this.$refs.buktiInput) {
                this.$refs.buktiInput.value = null;
            }

            // reset state Livewire form juga (kalau mau lebih rapi)
            if (this.$wire) {
                this.$wire.member_id     = null;
                this.$wire.jumlah_hari   = 1;
                this.$wire.tanggal_mulai = '{{ now()->toDateString() }}';
                this.$wire.alasan        = null;
                this.$wire.bukti_alasan  = null;
            }
        },
        filteredMembers() {
            if (!this.memberSearch) return this.members;
            const s = this.memberSearch.toLowerCase();
            return this.members.filter(m => m.label.toLowerCase().includes(s));
        },
        selectMember(m) {
            this.memberSearch = m.label;
            this.openMemberDropdown = false;
            if (this.$wire) {
                this.$wire.member_id = m.id;
            }
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
            const mime  = file.type || '';
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
    }"
    x-on:izin-manual-saved.window="resetCreateForm(); openCreate = false"
    x-effect="
        const main = document.querySelector('main');
        const html = document.documentElement;
        const body = document.body;
        const locked = openCreate || openDetailId || openApproveId;

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
    {{-- HEADER SECTION --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Permintaan izin yang belum diproses."
    />

    <hr class="border-t border-brand-borderSoft mb-6">

    {{-- SEARCH + FILTER + ACTION --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

        {{-- SEARCH + FILTER --}}
        <div
            class="relative w-full max-w-md"
            x-data="{ showFilter: false }"
        >
            <div class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm h-[42px]">
                <div class="pl-4 text-text-muted">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </div>

                <input
                    type="text"
                    placeholder="Cari nama member..."
                    class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full"
                    @keydown.enter.prevent
                    x-model="searchTerm"
                    wire:model.debounce.300ms="search"
                >

                <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                {{-- BUTTON FILTER --}}
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
                        <button
                            type="button"
                            class="text-xs text-danger hover:underline"
                            wire:click="$set('sort', 'newest')"
                        >
                            Reset
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                            Urutkan berdasarkan
                        </label>
                        <select
                            wire:model="sort"
                            class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2"
                        >
                            <option value="newest">
                                Waktu pengajuan · Terbaru
                            </option>
                            <option value="oldest">
                                Waktu pengajuan · Terlama
                            </option>
                            <option value="days_max">
                                Hari diajukan · Terbanyak
                            </option>
                            <option value="days_min">
                                Hari diajukan · Tersedikit
                            </option>
                        </select>

                        <p class="text-[10px] text-text-muted mt-1">
                            Pilih apakah ingin lihat izin terbaru, terlama, atau berdasarkan banyaknya hari yang diajukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex items-center gap-2">
            <x-ui.button-primary
                type="button"
                @click="resetCreateForm(); openCreate = true"
            >
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

    {{-- TABLE IZIN PENDING --}}
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
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                            No
                        </th>
                        <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Member
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Hari Diajukan
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Tgl Mulai
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Status
                        </th>
                        <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Alasan
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-brand-borderSoft/80">
                    @forelse ($daftar_izin as $izin)
                        <tr
                            x-data="{
                                memberName: @js($izin->member?->nama ?? ''),
                                memberUsername: @js($izin->member?->username ?? ''),
                            }"
                            x-show="
                                !searchTerm
                                || memberName.toLowerCase().startsWith(searchTerm.toLowerCase())
                                || memberUsername.toLowerCase().startsWith(searchTerm.toLowerCase())
                            "
                            class="hover:bg-brand-surface-50 transition-colors duration-150 h-24"
                        >
                            <td class="p-3 text-center align-middle text-xs text-text-muted">
                                {{ $loop->iteration + ($daftar_izin->currentPage() - 1) * $daftar_izin->perPage() }}
                            </td>

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

                            <td class="p-3 text-center align-middle">
                                <span class="text-sm font-semibold text-text-main">
                                    {{ $izin->jumlah_hari }} Hari
                                </span>
                            </td>

                            <td class="p-3 text-center align-middle">
                                <span class="text-sm text-text-muted">
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                                </span>
                            </td>

                            <td class="p-3 text-center align-middle">
                                <x-ui.badge variant="warning">
                                    Pending
                                </x-ui.badge>
                            </td>

                            <td class="p-3 text-left align-middle max-w-[220px]">
                                <div class="text-xs text-text-muted line-clamp-1 overflow-hidden text-ellipsis whitespace-nowrap">
                                    {{ $izin->alasan ? Str::limit($izin->alasan, 80) : '-' }}
                                </div>
                            </td>

                            <td class="px-3 py-4 align-middle w-[16%] min-w-[140px]">
                                <div class="flex items-center justify-center gap-4 h-full">
                                    {{-- DETAIL --}}
                                    <div class="relative group flex items-center justifycenter">
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
                                        {{-- SETUJUI (masih pakai modal/form lama kalau ada) --}}
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

    {{-- MODAL TAMBAH IZIN MANUAL --}}
    @include('admin.izin_latihan.modals.create')

    {{-- Kalau masih pakai modal detail/approve lama --}}
    @foreach ($daftar_izin as $izin)
        @include('admin.izin_latihan.modals.detail', ['izin' => $izin])
        @include('admin.izin_latihan.modals.approve_form', ['izin' => $izin])
    @endforeach

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
</div>
