{{-- resources/views/admin/produk/index.blade.php --}}

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $pageTitle ?? 'Manajemen Produk';
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    $search = request('q', '');
    $filterKategori = request('kategori', '');
    $startDate = request('start_date', '');
    $endDate = request('end_date', '');
    $sort = request('sort', 'newest');

    $hasActiveFilter = $filterKategori || $startDate || $endDate;
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';

    /** @var \Illuminate\Pagination\LengthAwarePaginator $produks */
    $currentPage = $produks->currentPage() ?? 1;
    $perPage = $produks->perPage() ?? 15;

    // Badge variant mapping untuk kategori (sesuaikan jika mau)
    $kategoriVariantMap = [
        'minuman' => 'info', // biru
        'suplemen' => 'danger', // merah
        'lainnya' => 'success', // hijau
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Kelola data produk yang tersedia di BETA GYM berdasarkan skema database.">

    {{-- FLASH MESSAGE DIHAPUS (Notifikasi via SweetAlert) --}}

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openDetailId: null,
        openEditId: null,
    
        createImageUrl: null,
        searchQuery: @js($search ?? ''),
        filterKategori: @js($filterKategori ?? ''),
        hasActiveFilter: {{ $hasActiveFilter ? 'true' : 'false' }},
    
        resetCreateForm() {
            this.$refs.createForm?.reset();
            this.createImageUrl = null;
            const hargaInput = document.getElementById('harga_create_formatted');
            if (hargaInput) hargaInput.value = '';
        },
    }"
        x-effect="
            const html = document.documentElement;
            const body = document.body;
            const main = document.querySelector('main');
            const locked = openCreate || !!openDetailId || !!openEditId;
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
        @keydown.escape.window="
            openCreate = false;
            openDetailId = null;
            openEditId = null;
        ">

        <x-ui.section-header :title="$pageTitle" subtitle="Daftar produk aktif dan pengelolaan datanya." />
        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- ROW: SEARCH + TOMBOL TAMBAH --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- SEARCH + FILTER POPUP --}}
            <div class="relative w-full md:max-w-xl" x-data="{ showFilter: false }">
                <form action="{{ route('admin.produk.index') }}" method="GET"
                    class="flex-1 flex items-center rounded-full border border-brand-borderSoft bg-brand-card shadow-sm
                           focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">

                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">

                    <div class="pl-4 text-text-muted">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>

                    <input type="text" name="q" x-model.debounce.300ms="searchQuery"
                        value="{{ $search }}" placeholder="Cari nama produk..."
                        class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/60
                               focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                        autocomplete="off">

                    <div class="h-6 w-px bg-brand-borderSoft mx-2"></div>

                    <button type="button" @click="showFilter = !showFilter"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors mr-2
                               rounded-full hover:bg-brand-surface-50"
                        :class="(showFilter || hasActiveFilter) ? 'text-gold-600 bg-brand-surface-50' :
                        'text-text-muted hover:text-text-main'">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>

                    <button type="submit" class="hidden">Cari</button>
                </form>

                {{-- POPUP FILTER --}}
                <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-40">

                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                            <h4 class="text-sm font-semibold text-text-main">Filter &amp; Urutan</h4>
                            <a href="{{ route('admin.produk.index') }}"
                                class="text-xs text-danger hover:underline">Reset</a>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1"
                                for="filter_kategori">
                                Kategori
                            </label>
                            <select name="kategori" id="filter_kategori"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                <option value="" {{ $filterKategori == '' ? 'selected' : '' }}>Semua Kategori
                                </option>
                                @foreach ($kategoriOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $filterKategori == $option ? 'selected' : '' }}>
                                        {{ ucwords($option) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_sort">
                                Urutan
                            </label>
                            <select name="sort" id="filter_sort"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2
                                       border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Terbaru (Default)
                                </option>
                                <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>Nama (A-Z)
                                </option>
                                <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>Nama (Z-A)
                                </option>
                                <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>Harga Termurah
                                </option>
                                <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Harga Termahal
                                </option>
                            </select>
                        </div>

                        <input type="hidden" name="q" :value="searchQuery">

                        <button type="submit"
                            class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- TOMBOL TAMBAH --}}
            <div class="flex items-center justify-end">
                <x-ui.button-primary type="button" @click="resetCreateForm(); openCreate = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Produk Baru
                </x-ui.button-primary>
            </div>
        </div>

        {{-- CARD TABEL --}}
        <x-ui.card title="Daftar Produk" subtitle="Semua produk yang terdaftar dalam sistem."
            class="border-brand-borderSoft">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[860px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No.</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Foto</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[22%]">
                                Nama Produk</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Kategori</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Harga</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%]">
                                Stok</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[21%]">
                                Deskripsi</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no = ($currentPage - 1) * $perPage + 1; @endphp

                        @forelse ($produks as $produk)
                            @php
                                $namaLower = strtolower($produk->nama);
                                $currentFotoPath = $produk->foto ?? null;
                                $currentFotoUrl = $currentFotoPath
                                    ? Storage::url($currentFotoPath)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';
                                $hargaText = 'Rp ' . number_format((int) $produk->harga, 0, ',', '.');

                                $kategoriKey = strtolower((string) ($produk->kategori ?? ''));
                                $badgeVariant = $kategoriVariantMap[$kategoriKey] ?? 'neutral';
                                $kategoriLabel = $kategoriKey !== '' ? ucwords($kategoriKey) : '—';
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-show="(!searchQuery || @js($namaLower).includes(searchQuery.trim().toLowerCase()))">

                                <td class="p-3 text-center align-middle text-text-muted">
                                    {{ $no++ }}
                                </td>

                                <td class="p-3 align-middle">
                                    <div
                                        class="w-12 h-12 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 overflow-hidden">
                                        <img src="{{ $currentFotoUrl }}" alt="Foto {{ $produk->nama }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';">
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1">
                                        {{ $produk->nama }}
                                    </div>
                                </td>

                                {{-- KATEGORI: BADGE UI --}}
                                <td class="p-3 align-middle">
                                    <x-ui.badge :variant="$badgeVariant">
                                        {{ $kategoriLabel }}
                                    </x-ui.badge>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate"
                                        title="{{ $hargaText }}">
                                        Rp {{ number_format((int) $produk->harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle text-center">
                                    <div
                                        class="inline-flex items-center px-3 py-1 rounded-full bg-brand-surface-50 border border-brand-borderSoft">
                                        <span
                                            class="text-sm font-bold text-text-main">{{ (int) $produk->stok }}</span>
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2">
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 90) : '—' }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" title="Lihat Detail Produk"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors"
                                            @click.stop="openDetailId = {{ $produk->id }}">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        <button type="button" title="Edit Produk"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            @click.stop="openEditId = {{ $produk->id }}">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        <form id="delete-product-{{ $produk->id }}"
                                            action="{{ route('admin.produk.destroy', $produk) }}" method="POST"
                                            class="inline-block" @click.stop>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus Produk"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                onclick="confirmDeleteProduct({{ $produk->id }}, @js($produk->nama))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
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
                        'q' => request('q'),
                        'kategori' => $filterKategori,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'sort' => $sort,
                    ])->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL CREATE --}}
        @include('admin.produk.modals.create')

        {{-- MODALS DETAIL + EDIT --}}
        @foreach ($produks as $produk)
            @include('admin.produk.modals.detail', ['produk' => $produk])
            @include('admin.produk.modals.edit', [
                'produk' => $produk,
                'kategoriOptions' => $kategoriOptions,
            ])
        @endforeach

        <script>
            function formatRupiahInput(input) {
                let value = input.value.replace(/\D/g, '');
                if (value) {
                    input.value = new Intl.NumberFormat('id-ID').format(value);

                    let rawInputId = '';
                    if (input.id.startsWith('harga_create_formatted')) rawInputId = 'harga_create';
                    else if (input.id.startsWith('harga_formatted_')) rawInputId = 'harga_' + input.id.substring(
                        'harga_formatted_'.length);

                    const rawInput = document.getElementById(rawInputId);
                    if (rawInput) rawInput.value = value;
                } else {
                    input.value = '';
                    const rawInput = document.getElementById(input.id.startsWith('harga_create_formatted') ? 'harga_create' :
                        '');
                    if (rawInput) rawInput.value = '';
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
        </style>
    </div>
</x-layouts.admin>
