{{-- resources/views/admin/members/index.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $pageTitle = $pageTitle ?? 'Daftar Member';
    $search = request('search');

    $status = $status ?? request('status', '');
    $sort = $sort ?? request('sort', 'name_asc');

    $statusOptions = [
        '' => 'Semua status',
        'aktif' => 'Aktif',
        'belum_aktif' => 'Belum aktif',
        'expired' => 'Expired',
    ];

    $sortOptions = [
        'name_asc' => 'Nama · A-Z',
        'name_desc' => 'Nama · Z-A',
        'daftar_newest' => 'Tanggal daftar · Terbaru',
        'daftar_oldest' => 'Tanggal daftar · Terlama',
    ];

    // modal create otomatis terbuka jika ada error validasi (bukan PUT)
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';

    // mapping status membership (dari accessor Member::status_membership)
    $statusMap = [
        'aktif' => ['variant' => 'success', 'label' => 'AKTIF'],
        'expired' => ['variant' => 'danger', 'label' => 'EXPIRED'],
        'belum_aktif' => ['variant' => 'warning', 'label' => 'BELUM AKTIF'],
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Kelola data member yang terdaftar di BETA GYM.">

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="mb-4 bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openEditId: null,
        openDetailId: null,
    
        search: @js($search ?? ''),
        status: @js($status ?? ''),
        sort: @js($sort ?? ''),
    
        statusDraft: @js($status ?? ''),
        sortDraft: @js($sort ?? ''),
    }"
        x-effect="
            const main = document.querySelector('main');
            const html = document.documentElement;
            const body = document.body;
            const locked = openCreate || !!openEditId || !!openDetailId;
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
        ">

        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Daftar member aktif dan histori keanggotaannya." />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- SEARCH + ADD --}}
        <div class="mt-6 mb-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.members.index') }}" method="GET">
                    <input type="hidden" name="status" :value="statusDraft">
                    <input type="hidden" name="sort" :value="sortDraft">

                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                       shadow-sm transition-all hover:border-brand-borderSoft/80
                       focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" name="search" x-model="search" value="{{ $search }}"
                            placeholder="Cari nama / username / email / no hp..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                           placeholder:text-text-muted/50 py-2 pl-3 pr-2
                           focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off">

                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                        <button type="button" @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium
                           text-text-muted hover:text-text-main transition-colors mr-1
                           rounded-full hover:bg-brand-surface-50"
                            :class="showFilter ? 'text-gold-600 bg-brand-surface-50' : ''">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

                        <button type="submit" class="hidden"></button>
                    </div>

                    <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft
                       rounded-2xl shadow-xl p-5 z-20">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter &amp; Urutan</h4>
                                <a href="{{ route('admin.members.index') }}"
                                    class="text-xs text-danger hover:underline">
                                    Reset
                                </a>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">Status</p>
                                <select x-model="statusDraft"
                                    class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                   text-xs text-text-main px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark">
                                    @foreach ($statusOptions as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">Urutkan</p>
                                <select x-model="sortDraft"
                                    class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                   text-xs text-text-main px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark">
                                    @foreach ($sortOptions as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-semibold
                               py-2 rounded-lg transition shadow-md"
                                @click="showFilter = false">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                Tambah Member
            </x-ui.button-primary>
        </div>


        {{-- CARD TABEL MEMBER --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Member</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua member yang terdaftar dalam sistem.
                        @if ($search)
                            <span class="font-semibold text-gold-700">
                                &nbsp;Hasil untuk "{{ $search }}" ({{ $members->total() }} member)
                            </span>
                        @endif
                    </p>
                </div>

                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $members->total() }} Member
                    </span>
                </div>
            </div>

            {{-- POLA SAMA SEPERTI REKENING: scroll hanya jika perlu --}}
            <div class="w-full overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[860px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No
                            </th>

                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[12%] hidden sm:table-cell">
                                Foto
                            </th>

                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[30%]">
                                Nama
                            </th>

                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[26%] hidden md:table-cell">
                                Paket Aktif
                            </th>

                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[12%]">
                                Status
                            </th>

                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[14%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($members as $member)
                            @php
                                $user = $member->user;
                                $displayName = $user?->name ?? '-';

                                $foto = !empty($user?->foto)
                                    ? Storage::url($user->foto)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';

                                $statusKey = $member->status_membership ?? 'belum_aktif';
                                $statusVariant = $statusMap[$statusKey]['variant'] ?? 'neutral';
                                $statusLabel =
                                    $statusMap[$statusKey]['label'] ?? Str::upper(str_replace('_', ' ', $statusKey));

                                $paketAktifNama = $member->nama_paket_aktif ?? null;

                                $tanggalDaftar = $member->tanggal_daftar
                                    ? Carbon::parse($member->tanggal_daftar)->format('d M Y')
                                    : '-';
                            @endphp

                            <tr x-show="!search || @js(strtolower($displayName)).includes(search.trim().toLowerCase())"
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-20">

                                {{-- NO --}}
                                <td class="p-3 text-center align-middle text-sm font-semibold text-text-muted">
                                    {{ $loop->iteration + ($members->currentPage() - 1) * $members->perPage() }}
                                </td>

                                {{-- FOTO (sm+) --}}
                                <td class="p-3 align-middle hidden sm:table-cell">
                                    <div
                                        class="w-14 h-14 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 overflow-hidden">
                                        <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';">
                                    </div>
                                </td>

                                {{-- NAMA + META + (paket untuk mobile) --}}
                                <td class="p-3 align-middle">
                                    <div class="flex items-center gap-3 min-w-0">
                                        {{-- avatar kecil untuk xs --}}
                                        <div
                                            class="sm:hidden w-10 h-10 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 overflow-hidden shrink-0">
                                            <img src="{{ $foto }}" alt="Foto {{ $displayName }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';">
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-semibold text-text-main truncate max-w-[220px] lg:max-w-[320px]">
                                                {{ $displayName }}
                                            </div>
                                            <div
                                                class="text-[11px] text-text-muted mt-0.5 truncate max-w-[260px] lg:max-w-[360px]">
                                                Terdaftar: {{ $tanggalDaftar }}
                                            </div>

                                            {{-- paket versi mobile --}}
                                            <div class="md:hidden mt-1">
                                                @if ($paketAktifNama)
                                                    <div
                                                        class="text-[11px] font-semibold text-gold-700 truncate max-w-[260px]">
                                                        {{ $paketAktifNama }}
                                                    </div>
                                                    <div class="text-[10px] text-text-muted truncate max-w-[260px]">
                                                        Sedang aktif
                                                    </div>
                                                @else
                                                    <div class="text-[11px] text-text-muted italic">—</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- PAKET (md+) --}}
                                <td class="p-3 align-middle hidden md:table-cell">
                                    @if ($paketAktifNama)
                                        <div class="text-sm font-semibold text-gold-700 truncate max-w-[320px]">
                                            {{ $paketAktifNama }}
                                        </div>
                                        <div class="text-[11px] text-text-muted mt-0.5 truncate max-w-[320px]">
                                            Sedang aktif
                                        </div>
                                    @else
                                        <div class="text-sm text-text-muted italic">—</div>
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td class="p-3 align-middle text-center">
                                    <x-ui.badge :variant="$statusVariant">
                                        {{ $statusLabel }}
                                    </x-ui.badge>
                                </td>

                                {{-- AKSI (JANGAN di-overflow-hidden, biar tidak kepotong) --}}
                                <td class="px-3 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-3 h-full">
                                        <button type="button" title="Detail"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60"
                                            @click="openDetailId = {{ $member->id }}">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        <button type="button" title="Edit"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60"
                                            @click="openEditId = {{ $member->id }}">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        <form id="delete-member-{{ $member->id }}"
                                            action="{{ route('admin.members.destroy', $member) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60"
                                                onclick="confirmDeleteMember({{ $member->id }}, @js($displayName))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-text-muted italic">
                                    Belum ada data member.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION (seperti rekening) --}}
            <div class="mt-6">
                {{ $members->appends(['search' => $search, 'status' => $status, 'sort' => $sort])->links() }}
            </div>
        </x-ui.card>

        {{-- MODALS --}}
        @include('admin.members.modals.create')
        @foreach ($members as $member)
            @include('admin.members.modals.detail', ['member' => $member])
            @include('admin.members.modals.edit', ['member' => $member])
        @endforeach

        <style>
            [x-cloak] {
                display: none !important;
            }

            /* scrollbar hanya terlihat saat overflow terjadi (sama seperti halaman lain) */
            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: #F5E6D6;
                border-radius: 999px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #D4A757;
                border-radius: 999px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #A67C39;
            }
        </style>
    </div>

    <script>
        function confirmDeleteMember(memberId, memberName) {
            if (typeof Swal === 'undefined') {
                if (confirm('Yakin ingin menghapus member ' + memberName + '?')) {
                    document.getElementById('delete-member-' + memberId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Member?',
                text: 'Anda yakin ingin menghapus data member ' + memberName +
                    '? Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-member-' + memberId).submit();
                }
            });
        }
    </script>
</x-layouts.admin>
