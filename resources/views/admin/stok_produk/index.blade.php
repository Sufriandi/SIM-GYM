{{-- resources/views/admin/stok_produk/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js;

    $pageTitle = 'Daftar Stok Produk';

    // Ambil parameter dari request
    $search = request('q', '');
    $filterKategori = request('kategori', '');
    $sort = request('sort', 'newest');

    /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $produks */
    $produks = $produks ?? collect();

    $openEditOnLoad = session('modal_edit_open', false);
    $isPaginator = $produks instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $currentPage = $isPaginator ? $produks->currentPage() : 1;
    $perPage = $isPaginator ? $produks->perPage() : max($produks->count(), 1);
    $totalRiwayatStok = $totalRiwayatStok ?? 0;

    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    // Badge kategori (kontras)
    $kategoriVariantMap = [
        'minuman' => 'info', // biru
        'suplemen' => 'danger', // merah
        'lainnya' => 'success', // hijau
    ];

    /**
     * Stok 3 warna saja (tanpa badge):
     * 0 = merah, 1-5 = kuning, >5 = hijau
     */
    $stokTextClass = function ($stok) {
        $stok = (int) ($stok ?? 0);
        if ($stok <= 0) {
            return 'text-danger';
        }
        if ($stok <= 5) {
            return 'text-warning';
        }
        return 'text-success';
    };
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Daftar stok produk yang tersedia di gudang.">

    {{-- FLASH MESSAGE SUDAH DIHAPUS (Notifikasi via SweetAlert) --}}

    <div x-data="{
        openDetail: false,
        detailProduk: null,
        openEditStok: {{ Js::from($openEditOnLoad) }},
        openTambahStok: false,
    
        // STATE PENCARIAN & FILTER
        search: @js($search),
        filterKategori: @js($filterKategori),
        filterKategoriDraft: @js($filterKategori),
        filterSort: @js($sort),
        filterSortDraft: @js($sort),
    
        editStokForm: {
            id: @js(old('id') ?? ''),
            nama: '',
            stok_lama: @js(old('stok_lama') ?? 0),
            stok_baru: @js(old('stok_baru') ?? 0),
            keterangan: @js(old('keterangan') ?? ''),
        },
    
        showDetail(produk) {
            this.detailProduk = produk;
            this.openDetail = true;
        },
    
        showEditStok(data) {
            this.editStokForm.id = data.id;
            this.editStokForm.nama = data.nama;
            this.editStokForm.stok_lama = data.stok;
            this.editStokForm.stok_baru = data.stok;
            // Keterangan dikosongkan agar admin isi alasan baru
            this.editStokForm.keterangan = '';
            this.openEditStok = true;
        }
    }">
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle"
            subtitle="Daftar produk dan stok akhirnya. Gunakan tombol edit untuk menyesuaikan stok." />
        <hr class="border-t border-brand-borderSoft mb-6 mt-2">

        {{-- BARIS: SEARCH + FILTER + AKSI --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH + FILTER --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.stok_produk.index') }}" method="GET">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" name="q" x-model="search" placeholder="Cari nama produk..."
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

                    {{-- POPUP FILTER --}}
                    <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card z-20
                               border border-brand-borderSoft rounded-2xl shadow-xl p-5">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter &amp; Urutan</h4>
                                <a href="{{ route('admin.stok_produk.index') }}"
                                    class="text-xs text-danger hover:underline">Reset</a>
                            </div>

                            {{-- KATEGORI --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">Filter berdasarkan kategori
                                </p>
                                <div class="relative">
                                    <select x-model="filterKategoriDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8 custom-select
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark">
                                        <option value="">Semua kategori</option>
                                        @foreach ($kategoriOptions as $option)
                                            <option value="{{ $option }}">{{ ucwords($option) }}</option>
                                        @endforeach
                                    </select>
                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>
                            </div>

                            {{-- SORT --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">Urutkan berdasarkan</p>
                                <div class="relative">
                                    <select x-model="filterSortDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8 custom-select
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark">
                                        <option value="newest">Tanggal Dibuat Terbaru</option>
                                        <option value="oldest">Tanggal Dibuat Terlama</option>
                                        <option value="name_asc">Nama Produk (A-Z)</option>
                                        <option value="name_desc">Nama Produk (Z-A)</option>
                                        <option value="stok_asc">Stok Terendah</option>
                                        <option value="stok_desc">Stok Tertinggi</option>
                                    </select>
                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>
                            </div>

                            <input type="hidden" name="kategori" :value="filterKategoriDraft">
                            <input type="hidden" name="sort" :value="filterSortDraft">
                            <input type="hidden" name="q" :value="search">

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white
                                       text-sm font-semibold py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TOMBOL AKSI KANAN --}}
            <div class="flex items-center gap-2 justify-end md:justify-start w-full md:w-auto mt-3 md:mt-0">
                <x-ui.button-primary type="button" @click="openTambahStok = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Stok Produk
                </x-ui.button-primary>

                <a href="{{ route('admin.stok_produk.history') }}">
                    <x-ui.button-secondary>
                        <i data-lucide="history" class="w-4 h-4 mr-1"></i>
                        Riwayat
                        @if ($totalRiwayatStok > 0)
                            <span class="ml-1 bg-brand-surface-300 px-2 py-0.5 rounded-full text-xs font-semibold">
                                {{ $totalRiwayatStok }}
                            </span>
                        @endif
                    </x-ui.button-secondary>
                </a>
            </div>
        </div>

        {{-- WRAPPER TABEL --}}
        <x-ui.card title="Daftar Stok Produk" subtitle="Stok yang tersedia saat ini di database."
            class="border-brand-borderSoft">

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full table-fixed border-collapse text-xs md:text-sm min-w-[800px] md:min-w-full">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No.</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Tanggal</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%]">
                                Produk</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Kategori</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%]">
                                Stok</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[25%]">
                                Keterangan</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no_urut = ($currentPage - 1) * $perPage + 1; @endphp

                        @forelse ($produks as $produk)
                            @php
                                $kategori = $produk->kategori ?? '-';
                                $tanggalDisplay = Carbon::parse($produk->created_at)
                                    ->locale('id')
                                    ->isoFormat('D MMM YYYY');
                                $stokAkhir = (int) ($produk->stok ?? 0);
                                $namaProduk = $produk->nama ?? 'Produk Dihapus';
                                $namaLower = strtolower($namaProduk);

                                // kategori badge
                                $kategoriKey = strtolower((string) ($kategori ?? ''));
                                $kategoriBadgeVariant = $kategoriVariantMap[$kategoriKey] ?? 'neutral';
                                $kategoriBadgeLabel =
                                    $kategoriKey !== '' && $kategoriKey !== '-' ? ucwords($kategoriKey) : '—';

                                // stok warna
                                $stokClass = $stokTextClass($stokAkhir);

                                $produk_data_js = [
                                    'id' => $produk->id,
                                    'nama' => $namaProduk,
                                    'kategori' => ucwords($kategori),
                                    'stok' => $stokAkhir,
                                    'tanggal_dibuat' => $tanggalDisplay,
                                    'deskripsi' => $produk->deskripsi,
                                ];
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 h-16"
                                x-show="(!search || @js($namaLower).startsWith(search.trim().toLowerCase())) && (!filterKategori || @js($kategori) === filterKategori)">

                                {{-- NO --}}
                                <td class="p-3 align-middle text-center text-text-muted text-sm font-semibold">
                                    {{ $no_urut++ }}
                                </td>

                                {{-- TANGGAL --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted whitespace-nowrap">
                                        {{ $tanggalDisplay }}
                                    </div>
                                </td>

                                {{-- PRODUK --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate"
                                        title="{{ $namaProduk }}">
                                        {{ $namaProduk }}
                                    </div>
                                </td>

                                {{-- KATEGORI: BADGE --}}
                                <td class="p-3 align-middle">
                                    <x-ui.badge :variant="$kategoriBadgeVariant">
                                        {{ $kategoriBadgeLabel }}
                                    </x-ui.badge>
                                </td>

                                {{-- STOK: ANGKA 3 WARNA (tanpa badge) --}}
                                <td class="p-3 align-middle text-center">
                                    <span class="text-sm font-bold tabular-nums {{ $stokClass }}">
                                        {{ $stokAkhir }}
                                    </span>
                                </td>

                                {{-- KETERANGAN --}}
                                <td class="p-3 align-middle">
                                    <div class="text-[12px] text-text-muted truncate"
                                        title="{{ $produk->deskripsi }}">
                                        {{ $produk->deskripsi ? $produk->deskripsi : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4 align-middle">
                                    <div class="flex items-center justify-center gap-2 h-full">

                                        {{-- 1. TOMBOL DETAIL --}}
                                        <div x-data="{ viewing: false }" class="relative">
                                            <button type="button" @mouseenter="viewing = true"
                                                @mouseleave="viewing = false"
                                                @click="showDetail({{ Js::from($produk_data_js) }})"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </button>
                                            <span x-show="viewing" x-cloak
                                                class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-[10px] font-medium text-info bg-brand-shell border border-brand-borderSoft px-2 py-0.5 rounded shadow z-10">
                                                Detail
                                            </span>
                                        </div>

                                        {{-- 2. TOMBOL KOREKSI --}}
                                        <div x-data="{ editing: false }" class="relative">
                                            <button type="button" @mouseenter="editing = true"
                                                @mouseleave="editing = false"
                                                @click="showEditStok({{ Js::from($produk_data_js) }})"
                                                class="p-2 rounded-full text-warning hover:bg-warning/10 transition-colors duration-150">
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>
                                            <span x-show="editing" x-cloak
                                                class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-[10px] font-medium text-warning bg-brand-shell border border-brand-borderSoft px-2 py-0.5 rounded shadow z-10">
                                                Koreksi
                                            </span>
                                        </div>

                                        {{-- 3. TOMBOL HAPUS --}}
                                        <form id="delete-produk-{{ $produk->id }}"
                                            action="{{ route('admin.stok_produk.destroy', $produk->id) }}"
                                            method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <div x-data="{ deleting: false }" class="relative">
                                                <button type="button" @mouseenter="deleting = true"
                                                    @mouseleave="deleting = false"
                                                    class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                    onclick="confirmDeleteProduct({{ $produk->id }}, '{{ $namaProduk }}')">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                                <span x-show="deleting" x-cloak
                                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-[10px] font-medium text-danger bg-brand-shell border border-brand-borderSoft px-2 py-0.5 rounded shadow z-10">
                                                    Hapus
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Belum ada data produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $produks->appends([
                        'q' => request('q'),
                        'kategori' => $filterKategori,
                        'sort' => $sort,
                    ])->links() }}
            </div>
        </x-ui.card>

        {{-- MODALS --}}
        @include('admin.stok_produk.modals.detail')
        @include('admin.stok_produk.modals.create')
        @include('admin.stok_produk.modals.edit')
    </div>

    {{-- SCRIPT KONFIRMASI HAPUS PRODUK --}}
    <script>
        function confirmDeleteProduct(productId, productName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus produk ${productName} (stok akan hilang permanen)?`)) {
                    document.getElementById('delete-produk-' + productId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Produk Permanen?',
                html: `Anda yakin ingin menghapus produk <strong>${productName}</strong>? <br> Penghapusan ini akan menghapus produk, stok, dan semua riwayat yang terkait.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Tutup',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-produk-' + productId).submit();
                }
            });
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

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

        .custom-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 2.5rem !important;
            cursor: pointer;
        }
    </style>
</x-layouts.admin>
