{{-- resources/views/admin/produk/index.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
    
    $pageTitle       = $pageTitle ?? 'Manajemen Produk';
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];
    
    $search         = request('q', '');
    $filterKategori = request('kategori', '');
    $startDate      = request('start_date', '');
    $endDate        = request('end_date', '');
    $sort           = request('sort', 'newest');
    
    $hasActiveFilter  = $filterKategori || $startDate || $endDate;
    $openCreateOnLoad = ($errors->any() && old('_method') !== 'PUT') ? 'true' : 'false';

    /** @var \Illuminate\Pagination\LengthAwarePaginator $produks */
    $currentPage = $produks->currentPage() ?? 1;
    $perPage     = $produks->perPage() ?? 15;
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Kelola data produk yang tersedia di BETA GYM berdasarkan skema database."
>
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
    
    <div
        x-data="{
            openCreate: {{ $openCreateOnLoad }},
            createImageUrl: null,
            search: '{{ $search }}',
            filterKategori: '{{ $filterKategori }}',
            hasActiveFilter: {{ $hasActiveFilter ? 'true' : 'false' }},

            resetCreateForm() {
                this.$refs.createForm?.reset();
                this.createImageUrl = null;
                const hargaInput = document.getElementById('harga_create_formatted');
                if (hargaInput) hargaInput.value = '';
            },
        }"
    > 
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar produk aktif dan pengelolaan datanya."
        />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>
        
        {{-- BARIS: SEARCH + TOMBOL AKSI --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full max-w-md z-30" x-data="{ showFilter: false }">
                <form action="{{ route('admin.produk.index') }}" method="GET">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">

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
                            :class="(showFilter || hasActiveFilter) ? 'text-gold-600 bg-brand-surface-50' : ''"
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
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5"
                    >
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter &amp; Urutan</h4>
                                <a href="{{ route('admin.produk.index') }}" class="text-xs text-danger hover:underline">Reset</a>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_kategori">Kategori</label>
                                <select 
                                    name="kategori" 
                                    id="filter_kategori"
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

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_sort">Urutan</label>
                                <select 
                                    name="sort" 
                                    id="filter_sort"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                >
                                    <option value="newest"     {{ $sort == 'newest'     ? 'selected' : '' }}>Terbaru (Default)</option>
                                    <option value="oldest"     {{ $sort == 'oldest'     ? 'selected' : '' }}>Terlama</option>
                                    <option value="name_asc"   {{ $sort == 'name_asc'   ? 'selected' : '' }}>Nama (A-Z)</option>
                                    <option value="name_desc"  {{ $sort == 'name_desc'  ? 'selected' : '' }}>Nama (Z-A)</option>
                                    <option value="price_asc"  {{ $sort == 'price_asc'  ? 'selected' : '' }}>Harga Termurah</option>
                                    <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Harga Termahal</option>
                                </select>
                            </div>

                            <input type="hidden" name="q" :value="search">

                            <button type="submit" class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <x-ui.button-primary type="button" @click="resetCreateForm(); openCreate = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Produk Baru
                </x-ui.button-primary>
            </div>
        </div>
        
        {{-- CARD TABEL PRODUK --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Produk</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua produk yang terdaftar dalam sistem.
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $produks->total() }} Produk
                    </span>
                </div>
            </div>

            <div class="w-full">
                <table class="table-fixed w-full border-collapse text-xs md:text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[4%]">
                                No.
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[9%]">
                                Foto
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[22%]">
                                Nama Produk
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Kategori
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Harga
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[6%]">
                                Stok
                            </th>
                            {{-- width deskripsi diperkecil sedikit --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[25%]">
                                Deskripsi
                            </th>
                            {{-- width aksi diperbesar --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php
                            $no = ($currentPage - 1) * $perPage + 1;
                        @endphp

                        @forelse ($produks as $produk)
                            @php
                                $currentFotoPath = $produk->foto ?? null;
                                $currentFotoUrl  = $currentFotoPath
                                    ? Storage::url($currentFotoPath)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';

                                $initialImageUrl = ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT')
                                    ? (old('foto_preview') ?? $currentFotoUrl)
                                    : $currentFotoUrl;

                                $openEditOnLoad = ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT') ? 'true' : 'false';

                                $namaLower = strtolower($produk->nama);
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
                                        @js($produk->kategori) === filterKategori
                                    )
                                "
                                x-data="{
                                    openDetail: false,
                                    openEdit: {{ $openEditOnLoad }},
                                    imageUrl: '{{ $initialImageUrl }}',
                                    originalImageUrl: '{{ $currentFotoUrl }}',
                                    originalData: {
                                        nama: '{{ $produk->nama }}',
                                        kategori: '{{ $produk->kategori }}',
                                        harga: '{{ (int) $produk->harga }}',
                                        deskripsi: '{{ $produk->deskripsi ?? '' }}',
                                    },
                                    resetEditForm() {
                                        document.getElementById('nama_{{ $produk->id }}').value = this.originalData.nama;
                                        document.getElementById('kategori_{{ $produk->id }}').value = this.originalData.kategori;
                                        
                                        const hargaInputRaw = document.getElementById('harga_{{ $produk->id }}');
                                        const hargaInputFormatted = document.getElementById('harga_formatted_{{ $produk->id }}');
                                        
                                        if (hargaInputFormatted) {
                                            hargaInputFormatted.value = new Intl.NumberFormat('id-ID').format(this.originalData.harga);
                                        }
                                        if (hargaInputRaw) {
                                            hargaInputRaw.value = this.originalData.harga;
                                        }
                                        
                                        document.getElementById('deskripsi_{{ $produk->id }}').value = this.originalData.deskripsi;
                                        
                                        const fileInput = document.getElementById('foto_{{ $produk->id }}');
                                        if (fileInput) {
                                            fileInput.value = '';
                                        }
                                        this.imageUrl = this.originalImageUrl;
                                    },
                                }"
                            >
                                <td class="p-3 align-middle text-center text-text-muted text-sm font-semibold w-[4%]">
                                    {{ $no++ }}
                                </td>

                                <td class="p-3 align-middle w-[9%]">
                                    <div class="w-12 h-12 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                        <img
                                            src="{{ $currentFotoUrl }}"
                                            alt="Foto {{ $produk->nama }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';"
                                        >
                                    </div>
                                </td>

                                <td class="p-3 align-middle w-[22%]">
                                    <div class="text-sm font-semibold text-text-main max-w-[200px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $produk->nama }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle w-[10%]">
                                    <div class="text-xs font-medium text-primary-dark">
                                        {{ ucwords($produk->kategori) }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle w-[10%]">
                                    <div class="text-sm text-text-main">
                                        {{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle text-center w-[6%]">
                                    <div class="text-sm text-text-main font-bold">
                                        {{ $produk->stok }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle w-[25%]">
                                    <div class="text-[12px] text-text-muted block max-w-full overflow-hidden text-ellipsis whitespace-nowrap pr-6">
                                        {{ $produk->deskripsi ? $produk->deskripsi : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4 align-middle w-[14%]">
                                    <div class="flex items-center justify-center gap-3 h-full mr-7">
                                        <button 
                                            type="button"
                                            title="Lihat Detail Produk"
                                            class="relative group p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150"
                                            @click.stop="openDetail = true"
                                        >
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-info
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150"
                                            >
                                                Detail
                                            </span>
                                        </button>

                                        <button 
                                            type="button"
                                            title="Edit Produk"
                                            class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                                            @click.stop="resetEditForm(); openEdit = true"
                                        >
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-yellow-600
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150"
                                            >
                                                Edit
                                            </span>
                                        </button>

                                        <form
                                            id="delete-product-{{ $produk->id }}"
                                            action="{{ route('admin.produk.destroy', $produk) }}"
                                            method="POST"
                                            class="inline-block"
                                            @click.stop
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Hapus Produk"
                                                class="relative group p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                onclick="confirmDeleteProduct({{ $produk->id }}, '{{ $produk->nama }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150"
                                                >
                                                    Hapus
                                                </span>
                                            </button>
                                        </form>

                                        @include('admin.produk.modals.detail')
                                        @include('admin.produk.modals.edit')
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-text-muted italic">
                                    @if ($filterKategori || $startDate || $endDate || $search)
                                        Tidak ada produk yang ditemukan dengan kombinasi pencarian / filter tersebut.
                                    @else
                                        Belum ada data produk yang tersimpan.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $produks->appends([
                    'q'          => request('q'),
                    'kategori'   => $filterKategori,
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'sort'       => $sort,
                ])->links() }}
            </div>
        </x-ui.card>
        
        @include('admin.produk.modals.create')
        
        <script>
            function formatRupiahInput(input) {
                let value = input.value.replace(/\D/g, '');
                if (value) {
                    let formatted = new Intl.NumberFormat('id-ID').format(value);
                    input.value = formatted;

                    let rawInputId = '';
                    if (input.id.startsWith('harga_create_formatted')) {
                        rawInputId = 'harga_create';
                    } else if (input.id.startsWith('harga_formatted_')) {
                        rawInputId = 'harga_' + input.id.substring('harga_formatted_'.length);
                    }

                    const rawInput = document.getElementById(rawInputId);
                    if (rawInput) {
                        rawInput.value = value;
                    }
                } else {
                    input.value = '';
                    let rawInputId = '';
                    if (input.id.startsWith('harga_create_formatted')) {
                        rawInputId = 'harga_create';
                    } else if (input.id.startsWith('harga_formatted_')) {
                        rawInputId = 'harga_' + input.id.substring('harga_formatted_'.length);
                    }
                    const rawInput = document.getElementById(rawInputId);
                    if (rawInput) {
                        rawInput.value = '';
                    }
                }
            }
            
            function confirmDeleteProduct(productId, productName) {
                if (typeof Swal === 'undefined') {
                    if (confirm(`Yakin ingin menghapus produk ${productName}?`)) {
                        document.getElementById('delete-product-' + productId).submit();
                    }
                    return;
                }
                Swal.fire({
                    title: 'Hapus Produk?',
                    text: `Anda yakin ingin menghapus data produk ${productName}? Tindakan ini tidak dapat dibatalkan.`,
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
                        document.getElementById('delete-product-' + productId).submit();
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
        </style>
    </div>
</x-layouts.admin>
