{{-- resources/views/admin/stok_produk/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js;

    $pageTitle = 'Daftar Stok Produk';

    // Ambil parameter dari request (untuk form dan view)
    $search         = request('q', '');
    $filterKategori = request('kategori', '');
    $sort           = request('sort', 'newest');

    /** @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $produks */
    $produks = $produks ?? collect();

    // Modal edit stok otomatis terbuka jika dari session
    $openEditOnLoad = session('modal_edit_open', false);

    // PASTIKAN $currentPage dan $perPage SELALU ADA
    $isPaginator = $produks instanceof \Illuminate\Pagination\LengthAwarePaginator;

    $currentPage = $isPaginator ? $produks->currentPage() : 1;
    $perPage     = $isPaginator ? $produks->perPage() : max($produks->count(), 1);

    $totalRiwayatStok = $totalRiwayatStok ?? 0;

    // ASUMSI: Daftar kategori produk yang valid
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar stok produk yang tersedia di gudang."
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
    @if (session('info'))
        <div class="bg-info-soft border border-info text-info-dark px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <div
        x-data="{
            openDetail: false,
            detailProduk: null,
            openEditStok: {{ Js::from($openEditOnLoad) }},
            openTambahStok: false,

            // STATE PENCARIAN & FILTER (ala INVENTARIS)
            search: '{{ $search }}',
            filterKategori: '{{ $filterKategori }}',
            filterKategoriDraft: '{{ $filterKategori }}',
            filterSort: '{{ $sort }}',
            filterSortDraft: '{{ $sort }}',

            editStokForm: {
                id: '{{ old('id') ?? '' }}',
                nama: '',
                stok_lama: {{ old('stok_lama') ?? 0 }},
                stok_baru: {{ old('stok_baru') ?? 0 }},
                keterangan: '{{ old('keterangan') ?? '' }}',
            },

            showDetail(produk) {
                this.detailProduk = produk;
                this.openDetail = true;
            },

            showEditStok(data) {
                this.editStokForm.id         = data.id;
                this.editStokForm.nama       = data.nama;
                this.editStokForm.stok_lama  = data.stok;
                this.editStokForm.stok_baru  = data.stok;
                this.editStokForm.keterangan = data.deskripsi ?? '';
                this.openEditStok            = true;
            },

            formatDateDisplay(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }"
    >
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar produk dan stok akhirnya. Gunakan tombol edit untuk menyesuaikan stok."
        />
        <hr class="border-t border-brand-borderSoft mb-6 mt-2">

        {{-- BARIS: SEARCH + FILTER + AKSI --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH + FILTER (logika ala INVENTARIS) --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.stok_produk.index') }}" method="GET">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]"
                    >
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input
                            type="text"
                            name="q"
                            x-model="search"
                            value="{{ $search }}"
                            placeholder="Cari nama produk..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-2 rounded-l-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off"
                        >

                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                        <button
                            type="button"
                            @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium
                                   text-text-muted hover:text-text-main transition-colors mr-1
                                   rounded-full hover:bg-brand-surface-50"
                            :class="showFilter ? 'text-gold-600 bg-brand-surface-50' : ''"
                        >
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

                        <button type="submit" class="hidden"></button>
                    </div>

                    {{-- POPUP FILTER --}}
                    <div
                        x-show="showFilter"
                        x-cloak
                        @click.outside="showFilter = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card
                               border border-brand-borderSoft rounded-2xl shadow-xl p-5"
                    >
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">
                                    Filter &amp; Urutan
                                </h4>
                                <a
                                    href="{{ route('admin.stok_produk.index') }}"
                                    class="text-xs text-danger hover:underline"
                                >
                                    Reset
                                </a>
                            </div>

                            {{-- KATEGORI --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">
                                    Filter berdasarkan kategori
                                </p>

                                <div class="relative">
                                    <select
                                        x-model="filterKategoriDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                               appearance-none"
                                    >
                                        <option value="">Semua kategori</option>
                                        @foreach ($kategoriOptions as $option)
                                            <option value="{{ $option }}">{{ ucwords($option) }}</option>
                                        @endforeach
                                    </select>

                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>

                                <p class="text-[11px] text-text-muted leading-snug">
                                    Pilih kategori produk yang ingin ditampilkan di daftar stok.
                                </p>
                            </div>

                            {{-- SORT --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">
                                    Urutkan berdasarkan
                                </p>

                                <div class="relative">
                                    <select
                                        x-model="filterSortDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                               appearance-none"
                                    >
                                        <option value="newest">Tanggal Dibuat Terbaru</option>
                                        <option value="oldest">Tanggal Dibuat Terlama</option>
                                        <option value="name_asc">Nama Produk (A-Z)</option>
                                        <option value="name_desc">Nama Produk (Z-A)</option>
                                        <option value="stok_asc">Stok Terendah</option>
                                        <option value="stok_desc">Stok Tertinggi</option>
                                    </select>

                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>
                            </div>

                            <input type="hidden" name="kategori" :value="filterKategoriDraft">
                            <input type="hidden" name="sort" :value="filterSortDraft">
                            <input type="hidden" name="q" :value="search">

                            <button
                                type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white
                                       text-sm font-semibold py-2 rounded-lg transition shadow-md"
                            >
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

        {{-- CARD TABEL STOK PRODUK – TANPA overflow-x-auto supaya tidak ada scrollbar --}}
        <x-ui.card
            title="Daftar Stok Produk"
            subtitle="Stok yang tersedia saat ini di database."
            class="border-brand-borderSoft"
        >
            <div class="w-full">
                <table class="table-fixed w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[6%]">
                                No.
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[16%]">
                                Tanggal Dibuat
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[24%]">
                                Produk
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Kategori
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Stok
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%]">
                                Keterangan
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no_urut = ($currentPage - 1) * $perPage + 1; @endphp

                        @forelse ($produks as $produk)
                            @php
                                $kategori       = $produk->kategori ?? '-';
                                $tanggalDisplay = Carbon::parse($produk->created_at)->locale('id')->isoFormat('D MMM YYYY');
                                $stokAkhir      = $produk->stok;
                                $namaProduk     = $produk->nama ?? 'Produk Dihapus';

                                $namaLower = strtolower($namaProduk);

                                $produk_data_js = [
                                    'id'             => $produk->id,
                                    'nama'           => $namaProduk,
                                    'kategori'       => ucwords($kategori),
                                    'stok'           => $stokAkhir,
                                    'tanggal_dibuat' => $tanggalDisplay,
                                    'deskripsi'      => $produk->deskripsi,
                                ];
                            @endphp

                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-20"
                                x-show="
                                    (
                                        !search ||
                                        @js($namaLower).startsWith(search.trim().toLowerCase())
                                    )
                                    &&
                                    (
                                        !filterKategori ||
                                        @js($kategori) === filterKategori
                                    )
                                "
                            >
                                {{-- NO --}}
                                <td class="p-3 align-middle text-center text-text-muted text-sm font-semibold">
                                    {{ $no_urut++ }}
                                </td>

                                {{-- TANGGAL DIBUAT --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted whitespace-nowrap">
                                        {{ $tanggalDisplay }}
                                    </div>
                                </td>

                                {{-- PRODUK --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main max-w-[260px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $namaProduk }}
                                    </div>
                                </td>

                                {{-- KATEGORI --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs font-medium text-primary-dark">
                                        {{ ucwords($kategori) }}
                                    </div>
                                </td>

                                {{-- STOK --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="text-sm font-bold {{ $stokAkhir > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $stokAkhir }}
                                    </div>
                                </td>

                                {{-- KETERANGAN --}}
                                <td class="p-3 align-middle">
                                    <div class="text-[12px] text-text-muted max-w-[320px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 60) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4 align-middle w-[10%] min-w-[120px]">
                                    <div class="flex items-center justify-center gap-3 h-full">
                                        {{-- DETAIL --}}
                                        <div x-data="{ viewing: false }" class="relative flex flex-col items-center justify-start h-full">
                                            <button
                                                type="button"
                                                @mouseenter="viewing = true"
                                                @mouseleave="viewing = false"
                                                @click="showDetail({{ Js::from($produk_data_js) }})"
                                                title="Lihat Detail Produk"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </button>
                                            <span x-show="viewing"
                                                  x-cloak
                                                  x-transition:enter="transition ease-out duration-300"
                                                  x-transition:enter-start="opacity-0 translate-y-2"
                                                  x-transition:enter-end="opacity-100 translate-y-0"
                                                  x-transition:leave="transition ease-in duration-200"
                                                  x-transition:leave-start="opacity-100 translate-y-0"
                                                  x-transition:leave-end="opacity-0 translate-y-2"
                                                  class="absolute top-[33px] text-[10px] font-medium text-info whitespace-nowrap z-0">
                                                Detail
                                            </span>
                                        </div>

                                        {{-- HAPUS --}}
                                        <form
                                            id="delete-produk-{{ $produk->id }}"
                                            action="{{ route('admin.stok_produk.destroy', $produk->id) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <div x-data="{ deleting: false }" class="relative flex flex-col items-center justify-start h-full">
                                                <button
                                                    type="button"
                                                    @mouseenter="deleting = true"
                                                    @mouseleave="deleting = false"
                                                    title="Hapus Produk Permanen"
                                                    class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150 z-10"
                                                    onclick="confirmDeleteProduct({{ $produk->id }}, '{{ $namaProduk }}')"
                                                >
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                                <span x-show="deleting"
                                                      x-cloak
                                                      x-transition:enter="transition ease-out duration-300"
                                                      x-transition:enter-start="opacity-0 translate-y-2"
                                                      x-transition:enter-end="opacity-100 translate-y-0"
                                                      x-transition:leave="transition ease-in duration-200"
                                                      x-transition:leave-start="opacity-100 translate-y-0"
                                                      x-transition:leave-end="opacity-0 translate-y-2"
                                                      class="absolute top-[33px] text-[10px] font-medium text-danger whitespace-nowrap z-0">
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
                    'q'        => request('q'),
                    'kategori' => $filterKategori,
                    'sort'     => $sort,
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
        [x-cloak] { display: none !important; }
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
