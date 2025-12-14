{{-- resources/views/admin/rekening/index.blade.php --}}
@php
    $pageTitle = 'Rekening & QRIS';

    $rekenings = $rekenings ?? collect();
    $qrises = $qrises ?? collect();

    $totalRekening = method_exists($rekenings, 'total') ? $rekenings->total() : $rekenings->count();
    $totalQris = method_exists($qrises, 'total') ? $qrises->total() : $qrises->count();

    $startRek = method_exists($rekenings, 'currentPage') ? ($rekenings->currentPage() - 1) * $rekenings->perPage() : 0;
    $startQrs = method_exists($qrises, 'currentPage') ? ($qrises->currentPage() - 1) * $qrises->perPage() : 0;

    $search = $search ?? request('search', '');
    $sort = $sort ?? request('sort', 'newest');
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Kelola rekening donasi dan QRIS yang akan ditampilkan di halaman publik.">

    <div x-data="{
        search: @js($search),
        sort: @js($sort),
        sortDraft: @js($sort),
    
        openCreate: false,
        createTab: 'rekening',
    
        openDetail: false,
        detailTab: 'rekening',
        detailItem: null,
    
        openEditRekening: false,
        editRekening: null,
    
        openEditQris: false,
        editQris: null,
        editQrisKeterangan: '',
    }"
        x-on:open-detail="
            detailTab = $event.detail.tab;
            detailItem = $event.detail.item;
            openDetail = true;
        "
        x-on:open-edit-rekening="
            editRekening = $event.detail;
            openEditRekening = true;
        "
        x-on:open-edit-qris="
            editQris = $event.detail;
            editQrisKeterangan = (editQris && editQris.keterangan) ? editQris.keterangan : '';
            openEditQris = true;
        "
        x-effect="
            const main  = document.querySelector('main');
            const html  = document.documentElement;
            const body  = document.body;
            const locked = openCreate || openDetail || openEditRekening || openEditQris;

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
        <x-ui.section-header :title="$pageTitle" subtitle="Manajemen rekening bank dan QRIS dalam satu halaman." />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- SEARCH + FILTER + TAMBAH --}}
        <div class="mt-6 mb-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.rekening.index') }}" method="GET">
                    <input type="hidden" name="sort" :value="sortDraft">

                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" name="search" x-model="search" value="{{ $search }}"
                            placeholder="Cari bank / nomor / pemilik / QRIS..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-2 rounded-l-full
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
                                <a href="{{ route('admin.rekening.index') }}"
                                    class="text-xs text-danger hover:underline">
                                    Reset
                                </a>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">Urutkan berdasarkan</p>

                                <div class="relative">
                                    <select x-model="sortDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                               appearance-none">
                                        <option value="newest">Terbaru dulu</option>
                                        <option value="oldest">Terlama dulu</option>
                                    </select>

                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>

                                <p class="text-[11px] text-text-muted leading-snug">
                                    Sorting diterapkan untuk daftar Rekening &amp; QRIS.
                                </p>
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

            {{-- tombol hanya satu (seperti sebelumnya) --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true; createTab = 'rekening'">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Data
            </x-ui.button-primary>
        </div>

        {{-- CARD REKENING --}}
        <x-ui.card class="border-brand-borderSoft mb-6 overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Rekening</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Data rekening bank.
                        @if ($search)
                            <span class="font-semibold text-gold-700">
                                &nbsp;Hasil untuk "{{ $search }}" ({{ $totalRekening }} data)
                            </span>
                        @endif
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">{{ $totalRekening }} Data</span>
                </div>
            </div>

            {{-- seperti memberships: scrollbar hanya jika perlu --}}
            <div class="w-full overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[760px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No</th>
                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[26%]">
                                Nama Bank</th>
                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[24%]">
                                Nomor Rekening</th>
                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[30%]">
                                Atas Nama</th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[14%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($rekenings as $item)
                            <tr x-show="
                                    !search
                                    || @js(strtolower($item->nama_bank ?? '')).includes(search.trim().toLowerCase())
                                    || @js(strtolower($item->nomor_rekening ?? '')).includes(search.trim().toLowerCase())
                                    || @js(strtolower($item->nama_pemilik ?? '')).includes(search.trim().toLowerCase())
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-16">
                                <td class="p-3 text-center align-middle text-sm font-semibold text-text-muted">
                                    {{ $loop->iteration + $startRek }}
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate">
                                        {{ $item->nama_bank }}
                                    </div>
                                </td>

                                {{-- biar tidak memaksa lebar (no nowrap) --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-mono text-text-main truncate">
                                        {{ $item->nomor_rekening }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main truncate">
                                        {{ $item->nama_pemilik }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-3 h-full">
                                        <button type="button" title="Detail"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60"
                                            @click="$dispatch('open-detail', { tab: 'rekening', item: @js($item->toArray()) })">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        <button type="button" title="Edit"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60"
                                            @click="$dispatch('open-edit-rekening', @js($item->toArray()))">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        <form id="delete-rekening-{{ $item->id }}"
                                            action="{{ route('admin.info-rekening.destroy', $item) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60"
                                                onclick="confirmDeleteRekening({{ $item->id }}, @js($item->nama_bank))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-text-muted italic">
                                    Belum ada data rekening.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($rekenings, 'links'))
                <div class="mt-6">
                    {{ $rekenings->appends(['search' => $search, 'sort' => $sort])->onEachSide(1)->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- CARD QRIS --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar QRIS</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Data QRIS.
                        @if ($search)
                            <span class="font-semibold text-gold-700">
                                &nbsp;Hasil untuk "{{ $search }}" ({{ $totalQris }} data)
                            </span>
                        @endif
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">{{ $totalQris }} Data</span>
                </div>
            </div>

            {{-- seperti memberships: scrollbar hanya jika perlu --}}
            <div class="w-full overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[820px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No</th>
                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[22%]">
                                Gambar</th>
                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[26%]">
                                Nama QRIS</th>

                            <th
                                class="p-3 text-left   text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[32%]">
                                Keterangan</th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[14%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($qrises as $item)
                            <tr x-show="
                                    !search
                                    || @js(strtolower($item->nama_qris ?? '')).includes(search.trim().toLowerCase())
                                    || @js(strtolower($item->keterangan ?? '')).includes(search.trim().toLowerCase())
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-20">
                                <td class="p-3 text-center align-middle text-sm font-semibold text-text-muted">
                                    {{ $loop->iteration + $startQrs }}
                                </td>

                                {{-- batasi ukuran gambar supaya tidak bikin overflow --}}
                                <td class="p-3 align-middle">
                                    @if ($item->path_gambar)
                                        <div
                                            class="w-16 h-16 border-brand-borderSoft bg-white/10 overflow-hidden flex items-center justify-center">
                                            <img src="{{ asset('storage/' . $item->path_gambar) }}"
                                                alt="QRIS {{ $item->nama_qris }}"
                                                class="w-full h-full object-contain">
                                        </div>
                                    @else
                                        <span class="text-xs text-text-muted">-</span>
                                    @endif
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate">
                                        {{ $item->nama_qris }}
                                    </div>
                                </td>



                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main line-clamp-2">
                                        {{ $item->keterangan ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-3 h-full">
                                        <button type="button" title="Detail"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60"
                                            @click="$dispatch('open-detail', { tab: 'qris', item: @js($item->toArray()) })">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        <button type="button" title="Edit"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60"
                                            @click="$dispatch('open-edit-qris', @js($item->toArray()) )">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        <form id="delete-qris-{{ $item->id }}"
                                            action="{{ route('admin.info-qris.destroy', $item) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60"
                                                onclick="confirmDeleteQris({{ $item->id }}, @js($item->nama_qris))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-text-muted italic">
                                    Belum ada data QRIS.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($qrises, 'links'))
                <div class="mt-6">
                    {{ $qrises->appends(['search' => $search, 'sort' => $sort])->onEachSide(1)->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- MODALS --}}
        @include('admin.rekening.modals.create')
        @include('admin.rekening.modals.edit')
        @include('admin.rekening.modals.detail')

        <style>
            [x-cloak] {
                display: none !important;
            }

            /* sama seperti sebelumnya; hanya bekerja saat overflow terjadi */
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
        function confirmDeleteRekening(id, bankName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus rekening ${bankName}?`)) {
                    document.getElementById('delete-rekening-' + id).submit();
                }
                return;
            }
            Swal.fire({
                title: 'Hapus Rekening?',
                html: `Anda yakin ingin menghapus rekening <strong>${bankName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-rekening-' + id).submit();
            });
        }

        function confirmDeleteQris(id, name) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus QRIS ${name}?`)) {
                    document.getElementById('delete-qris-' + id).submit();
                }
                return;
            }
            Swal.fire({
                title: 'Hapus QRIS?',
                html: `Anda yakin ingin menghapus QRIS <strong>${name}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-qris-' + id).submit();
            });
        }
    </script>
</x-layouts.admin>
