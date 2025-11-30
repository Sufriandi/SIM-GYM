{{-- resources/views/admin/stok_produk/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js; 
    
    $pageTitle = 'Daftar Stok Produk'; 

    // Ambil parameter dari request (untuk form dan view)
    $search = request('q', ''); 
    $filterKategori = request('kategori', '');
    $sort = request('sort', 'newest');
    
    $produks = $produks ?? collect(); 
    
    $openEditOnLoad = session('modal_edit_open', false);
    
    $currentPage = $produks->currentPage() ?? 1;
    $perPage = $produks->perPage() ?? 15;
    
    $totalRiwayatStok = $totalRiwayatStok ?? 0;
    
    // ASUMSI: Daftar kategori produk yang valid
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya']; 
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar stok produk yang tersedia di gudang."
>
    {{-- TAMPILKAN PESAN FLASH --}}
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
            // STATE PENCARIAN CLIENT-SIDE (Live Search)
            searchQuery: '{{ $search }}', 
            
            // ... (Fungsi Alpine.js lainnya)
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
                this.editStokForm.id = data.id;
                this.editStokForm.nama = data.nama;
                this.editStokForm.stok_lama = data.stok;
                this.editStokForm.stok_baru = data.stok;
                this.editStokForm.keterangan = data.deskripsi ?? '';

                this.openEditStok = true;
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
        >
        </x-ui.section-header>

        {{-- garis dibawah judul --}}
        <hr class="border-t border-brand-borderSoft mb-6 mt-2">

        {{-- FITUR PENCARIAN & FILTER + TOMBOL AKSI --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH BAR + FILTER (LIVE SEARCH + SERVER FILTER) --}}
            <div class="relative w-full max-w-md z-30" x-data="{ 
                showFilter: false, 
                filterKategori: '{{ $filterKategori }}', 
                filterSort: '{{ $sort }}',
                
                // Fungsi submit hanya untuk filter lanjutan dan reset
                submitFilter() {
                    this.$refs.filterForm.submit();
                }
            }">
                <form action="{{ route('admin.stok_produk.index') }}" method="GET" x-ref="filterForm">
                    {{-- Input Hidden untuk Filter Server Side --}}
                    {{-- Nilai kategori/sort dikirim dari x-model di dropdown --}}
                    
                    <input type="hidden" name="q" :value="searchQuery"> {{-- KIRIM QUERY LIVE SEARCH SAAT SUBMIT FILTER --}}

                    {{-- Container Input Gabungan --}}
                    <div class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">
                        {{-- Icon Search --}}
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        {{-- Input Text (Terikat ke $parent.searchQuery untuk Live Search) --}}
                        <input
                            type="text"
                            x-model.debounce.150ms="searchQuery" 
                            placeholder="Cari nama produk / kategori..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                            autocomplete="off"
                            {{-- HILANGKAN name="q" di sini agar tidak ada parameter di URL saat mengetik, 
                                 dan gunakan hidden input di atas untuk submit q. --}}
                        >

                        {{-- Divider Vertical --}}
                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>
                        
                        {{-- Tombol Filter Toggle --}}
                        <button
                            type="button"
                            @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors mr-1 rounded-full hover:bg-brand-surface-50"
                            :class="showFilter || filterKategori || filterSort !== 'newest' ? 'text-primary-dark bg-brand-surface-50' : ''"
                            title="Filter Lanjutan"
                        >
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

                        {{-- Tombol Reset Search Live (Hanya muncul jika ada input live search) --}}
                        <template x-if="searchQuery">
                            <button type="button" 
                                @click="searchQuery = ''; submitFilter()" {{-- Reset search dan submit filter --}}
                                class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition duration-150 mr-1" 
                                title="Reset Pencarian">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </button>
                        </template>

                    </div>

                    {{-- POPUP DROPDOWN FILTER (SERVER-SIDE) --}}
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
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5"
                    >
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter Lanjutan</h4>
                                {{-- Link reset akan mengarahkan ke URL index tanpa query params --}}
                                <a href="{{ route('admin.stok_produk.index') }}" class="text-xs text-danger hover:underline">Reset</a>
                            </div>
                            
                            {{-- Filter Kategori Produk --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_kategori_select">Kategori Produk</label>
                                <select 
                                    name="kategori" {{-- <-- PENTING: name='kategori' untuk sinkronisasi controller --}}
                                    id="filter_kategori_select"
                                    x-model="filterKategori"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                >
                                    <option value="" {{ $filterKategori == '' ? 'selected' : '' }}>Semua Kategori</option>
                                    @foreach ($kategoriOptions as $option)
                                        <option value="{{ $option }}" {{ $filterKategori == $option ? 'selected' : '' }}>
                                            {{ ucwords($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Urutan --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_sort_select">Urutan</label>
                                <select 
                                    name="sort" {{-- <-- PENTING: name='sort' untuk sinkronisasi controller --}}
                                    id="filter_sort_select"
                                    x-model="filterSort"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                >
                                    <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Tanggal Dibuat Terbaru</option>
                                    <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Tanggal Dibuat Terlama</option>
                                    <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>Nama Produk (A-Z)</option>
                                    <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>Nama Produk (Z-A)</option>
                                    <option value="stok_asc" {{ $sort == 'stok_asc' ? 'selected' : '' }}>Stok Terendah</option>
                                    <option value="stok_desc" {{ $sort == 'stok_desc' ? 'selected' : '' }}>Stok Tertinggi</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TOMBOL AKSI KANAN (TIDAK BERUBAH) --}}
            <div class="flex items-center gap-2 justify-end md:justify-start w-full md:w-auto mt-3 md:mt-0">
                <x-ui.button-primary type="button" @click="openTambahStok = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Stok Produk
                </x-ui.button-primary>

                {{-- TOMBOL RIWAYAT --}}
                <a href="{{ route('admin.stok_produk.history') }}"> 
                    <x-ui.button-secondary>
                        <i data-lucide="history" class="w-4 h-4 mr-1"></i> 
                        Riwayat 
                        @if ($totalRiwayatStok > 0)
                            <span class="ml-1 bg-brand-surface-300 px-2 py-0.5 rounded-full text-xs font-semibold">{{ $totalRiwayatStok }}</span>
                        @endif
                    </x-ui.button-secondary>
                </a>
            </div>
        </div>
        
        <x-ui.card
            title="Daftar Stok Produk"
            subtitle="Stok yang tersedia saat ini di database."
            class="border-brand-borderSoft"
        >
            <div>
                <table class="w-full border-collapse min-w-[900px] text-sm"> 
                    <thead>
                        {{-- ... (Header Tabel Tetap) ... --}}
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[3%] min-w-[30px]">No.</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%] min-w-[100px]">Tanggal Dibuat</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[24%] min-w-[150px]">Produk</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%] min-w-[100px]">Kategori</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[9%] min-w-[70px]">Stok</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[25%] min-w-[150px]">Keterangan</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[11%] min-w-[90px]">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no_urut = ($currentPage - 1) * $perPage + 1; @endphp
                        
                        @forelse ($produks as $produk)
                            @php
                                $kategori = $produk->kategori ?? '-';
                                $tanggalDisplay = Carbon::parse($produk->created_at)->locale('id')->isoFormat('D MMM YYYY');
                                $stokAkhir = $produk->stok; 
                                $namaProduk = $produk->nama ?? 'Produk Dihapus';
                                
                                $produk_data_js = [
                                    'id' => $produk->id,
                                    'nama' => $namaProduk,
                                    'kategori' => ucwords($kategori),
                                    'stok' => $stokAkhir,
                                    'tanggal_dibuat' => $tanggalDisplay,
                                    'deskripsi' => $produk->deskripsi,
                                ];
                                
                                // Gabungkan data yang dapat dicari menjadi satu string huruf kecil
                                $search_data = strtolower($namaProduk . ' ' . $kategori . ' ' . $produk->deskripsi);
                            @endphp
                            
                            <tr 
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                {{-- FUNGSI LIVE SEARCH UTAMA --}}
                                x-show="!searchQuery || '{{ $search_data }}'.includes(searchQuery.toLowerCase())"
                                x-ref="row_{{ $produk->id }}"
                            >
                                
                                {{-- NO (3%) --}}
                                <td class="p-3 align-middle text-center text-text-muted w-[3%] min-w-[30px]">{{ $no_urut++ }}</td>
                                
                                {{-- TANGGAL DIBUAT (14%) --}}
                                <td class="p-3 align-middle w-[14%] min-w-[100px]"> 
                                    <div class="text-xs text-text-muted">{{ $tanggalDisplay }}</div>
                                </td>
                                
                                {{-- PRODUK (24%) --}}
                                <td class="p-3 align-middle w-[24%] min-w-[150px]">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1">{{ $namaProduk }}</div>
                                </td>
                                
                                {{-- KATEGORI (14%) --}}
                                <td class="p-3 align-middle w-[14%] min-w-[100px]">
                                    <div class="text-xs font-medium text-primary-dark">
                                        {{ ucwords($kategori) }}
                                    </div>
                                </td>

                                {{-- JUMLAH (STOK AKHIR) (9%) --}}
                                <td class="p-3 align-middle text-center w-[9%] min-w-[70px]">
                                    <div class="text-sm font-bold {{ $stokAkhir > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $stokAkhir }}
                                    </div>
                                </td>
                                
                                {{-- KETERANGAN (Menggunakan deskripsi singkat produk) (25%) --}}
                                <td class="p-3 align-middle w-[25%] min-w-[150px]"> 
                                    <div class="text-xs text-text-muted max-w-full line-clamp-1">
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 50) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI (DETAIL, HAPUS) --}}
                                <td class="p-3 align-middle w-[11%] min-w-[90px]">
                                    <div class="flex items-center justify-center gap-1.5 h-full">
                                        
                                        {{-- 1. DETAIL ICON --}}
                                        <div x-data="{ viewing: false }" class="relative flex flex-col items-center justify-start h-full">
                                            <button 
                                                type="button" 
                                                @mouseenter="viewing = true"
                                                @mouseleave="viewing = false"
                                                @click="showDetail({{ Js::from($produk_data_js) }})" 
                                                title="Lihat Detail Produk"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="eye" class="w-6 h-6"></i>
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
                                        
                                        {{-- 2. HAPUS PRODUK PERMANEN --}}
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
                                                    <i data-lucide="trash-2" class="w-6 h-6"></i>
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
                            <tr x-show="!searchQuery" x-cloak>
                                <td colspan="7" class="p-6 text-center text-text-muted italic"> 
                                    @if ($filterKategori || $sort != 'newest')
                                        Tidak ada produk yang ditemukan dengan kriteria filter saat ini.
                                    @else
                                        Belum ada data produk.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        
                        {{-- Empty State untuk Live Search --}}
                        <template x-if="searchQuery && Array.from($refs).filter(ref => ref.tagName === 'TR' && ref.style.display !== 'none').length === 0">
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Tidak ada produk yang cocok dengan pencarian **'<span x-text="searchQuery"></span>'** di halaman ini.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{-- Pagination menyertakan semua parameter filter server-side dan pencarian --}}
                {{ $produks->appends([
                    'q' => request('q'), 
                    'kategori' => $filterKategori,
                    'sort' => $sort,
                ])->links() }}
            </div>
        </x-ui.card>
        
        {{-- MODAL DETAIL PRODUK (TIDAK BERUBAH) --}}
        {{-- ... (Kode Modal Detail) ... --}}
        <div
            x-show="openDetail"
            x-cloak
            x-transition
            @click.self="openDetail = false"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openDetail = false"
                class="relative w-full max-w-2xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Detail Produk</h2>
                        <p class="text-sm text-text-muted mt-0.5" x-text="detailProduk ? detailProduk.nama : ''"></p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openDetail = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar" x-if="detailProduk">
                    
                    <div class="space-y-4">
                        
                        {{-- Ringkasan Produk --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                            <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                                <span class="text-sm font-medium text-text-muted">Tanggal Dibuat:</span>
                                <span class="font-semibold text-sm text-text-main" x-text="detailProduk.tanggal_dibuat"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-text-muted">Kategori:</span>
                                <span class="font-semibold text-sm text-primary-dark" x-text="detailProduk.kategori"></span>
                            </div>
                        </div>

                        {{-- Detail Jumlah (Stok Akhir) --}}
                        <div :class="{
                            'border-success/50 bg-success-soft/30': detailProduk && detailProduk.stok > 0,
                            'border-danger/50 bg-danger-soft/30': detailProduk && detailProduk.stok <= 0
                        }" class="p-4 border rounded-xl">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-text-main">Stok Akhir Produk:</span>
                                <span :class="{
                                    'text-success': detailProduk && detailProduk.stok > 0,
                                    'text-danger': detailProduk && detailProduk.stok <= 0
                                }" class="text-xl font-extrabold" x-text="detailProduk.stok + ' unit'"></span>
                            </div>
                        </div>
                        
                        {{-- Keterangan / Deskripsi --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                            <h3 class="text-sm font-medium text-text-muted">Deskripsi:</h3>
                            <p class="text-sm text-text-main mt-1 italic" x-text="detailProduk.deskripsi || 'Tidak ada deskripsi.'"></p>
                        </div>
                        
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-ui.button-secondary type="button" @click="openDetail = false">Tutup</x-ui.button-secondary>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL TAMBAH STOK PRODUK BARU (TIDAK BERUBAH) --}}
        {{-- ... (Kode Modal Tambah Stok) ... --}}
        <div
            x-show="openTambahStok"
            x-cloak
            x-transition
            @click.self="openTambahStok = false"
            class="fixed inset-0 z-[999] flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openTambahStok = false"
                class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft 
                    bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <form action="{{ route('admin.stok_produk.store') }}" method="POST">
                    @csrf

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                        <div>
                            <h2 class="text-xl font-semibold text-text-main">Tambah Stok Produk</h2>
                            <p class="text-sm text-text-muted mt-0.5">Isi detail produk yang akan ditambahkan ke stok.</p>
                        </div>
                        <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openTambahStok = false">
                            <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                        </button>
                    </div>

                    {{-- BODY --}}
                    <div class="px-6 pb-6 pt-4 space-y-4">

                        {{-- PRODUK (Dropdown dengan Panah) --}}
                        <div>
                            <x-ui.label for="produk_id">Produk <span class="text-danger">*</span></x-ui.label>
                            <div class="relative">
                                <select name="produk_id" id="produk_id" required
                                    class="custom-select w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($allProduk as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        {{-- STOK --}}
                        <div>
                            <x-ui.label for="stok">Jumlah Stok Masuk <span class="text-danger">*</span></x-ui.label>
                            <input type="number" name="stok" id="stok" required min="1"
                                class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft">
                        </div>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan">Keterangan (opsional)</x-ui.label>
                            <textarea name="keterangan" id="keterangan"
                                class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft"
                                rows="3"></textarea>
                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="flex justify-end gap-2 px-6 pb-5">
                        <x-ui.button-secondary type="button" @click="openTambahStok = false">Batal</x-ui.button-secondary>
                        <x-ui.button-primary type="submit">Simpan</x-ui.button-primary>
                    </div>

                </form>
            </div>
        </div>
        
        {{-- MODAL EDIT STOK PRODUK (SNIPPET 1) (TIDAK BERUBAH) --}}
        {{-- ... (Kode Modal Edit Stok) ... --}}
        <div
            x-show="openEditStok"
            x-cloak
            x-transition
            @click.self="openEditStok = false"
            class="fixed inset-0 z-[999] flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openEditStok = false"
                class="relative w-full max-w-xl rounded-3xl shadow-2xl border border-brand-borderSoft 
                        bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <form :action="`{{ url('admin/stok_produk') }}/${editStokForm.id}`" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- HEADER --}}
                    <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                        <div>
                            <h2 class="text-xl font-semibold text-text-main">Edit/Penyesuaian Stok Produk</h2>
                            <p class="text-sm text-text-muted mt-0.5" x-text="editStokForm.nama"></p>
                        </div>
                        <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openEditStok = false">
                            <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                        </button>
                    </div>

                    {{-- BODY --}}
                    <div class="px-6 pb-6 pt-4 space-y-4">

                        {{-- STOK LAMA --}}
                        <div>
                            <x-ui.label>Stok Lama</x-ui.label>
                            <input type="number" x-model="editStokForm.stok_lama" readonly
                                class="w-full rounded-xl border bg-brand-surface-50 text-sm px-3 py-2 border-brand-borderSoft cursor-not-allowed">
                            <input type="hidden" name="stok_lama" :value="editStokForm.stok_lama">
                        </div>

                        {{-- STOK BARU --}}
                        <div>
                            <x-ui.label for="stok_baru">Stok Baru (Stok Akhir yang Benar) <span class="text-danger">*</span></x-ui.label>
                            <input type="number" name="stok_baru" id="stok_baru" required min="0"
                                x-model="editStokForm.stok_baru"
                                class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft">
                        </div>

                        {{-- KETERANGAN --}}
                        <div>
                            <x-ui.label for="keterangan_edit">Keterangan Penyesuaian (wajib jika ada perubahan)</x-ui.label>
                            <textarea name="keterangan" id="keterangan_edit" rows="3"
                                x-model="editStokForm.keterangan"
                                class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft"></textarea>
                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="flex justify-end gap-2 px-6 pb-5">
                        <x-ui.button-secondary type="button" @click="openEditStok = false">
                            Batal
                        </x-ui.button-secondary>
                        <x-ui.button-primary type="submit">
                            Perbarui Stok
                        </x-ui.button-primary>
                    </div>

                </form>
            </div>
        </div>
        
    </div> {{-- Penutup div x-data besar --}}

    {{-- SCRIPT KONFIRMASI HAPUS PRODUK (TIDAK BERUBAH) --}}
    <script>
        // FUNGSI KONFIRMASI HAPUS PRODUK (TETAP)
        function confirmDeleteProduct(productId, productName) {
            if (typeof Swal === 'undefined') {
                // Fallback jika SweetAlert tidak terdefinisi
                if (confirm(`Yakin ingin menghapus produk ${productName} (stok akan hilang permanen)?`)) {
                    document.getElementById('delete-produk-' + productId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Produk Permanen?',
                html: `Anda yakin ingin menghapus produk **${productName}**? <br> Penghapusan ini akan menghapus produk, stok, dan semua riwayat yang terkait.`,
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
    
    {{-- CUSTOM SCROLLBAR & DROPDOWN STYLING (TIDAK BERUBAH) --}}
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F5E6D6; /* brand.shell */
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D4A757; /* gold-500 */
            border-radius: 999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #A67C39; /* gold-700 */
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