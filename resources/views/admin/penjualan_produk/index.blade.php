{{-- resources/views/admin/penjualan_produk/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js;
    use Illuminate\Support\Facades\Session;

    $pageTitle = $pageTitle ?? 'Penjualan Produk';
    
    // --- VARIABEL UNTUK FILTER (Diambil dari Controller) ---
    $search        = $search ?? request('q', '');
    $filterMetode  = $filterMetode ?? request('metode_pembayaran', '');
    $filterProduk  = $filterProduk ?? request('produk_id', '');

    // Opsi untuk filter Metode Pembayaran
    $metodePembayaranOptions = $metodePembayaran ?? ['Cash', 'Transfer', 'QRIS'];

    // LOGIKA UNTUK MEMBUKA MODAL CREATE JIKA ADA VALIDASI ERROR DARI STORE
    $openCreateOnLoad = ($errors->any() && (old('_method') !== 'PUT')) ? 'true' : 'false';

    // LOGIKA UNTUK MEMBUKA MODAL EDIT JIKA ADA VALIDASI ERROR DARI UPDATE
    $errorsEdit    = Session::get('errors') ? Session::get('errors')->getBag('default') : null;
    $oldEditId     = old('_method') === 'PUT' ? (old('id') ?? 'null') : 'null';
    $openEditOnLoad = ($errorsEdit && old('_method') === 'PUT' && $oldEditId != 'null') ? 'true' : 'false';
    
    // Data untuk AlpineJS
    $produks = $produks ?? collect();
    $members = $members ?? collect();
    
    // Pagination
    $currentPage = $daftar_penjualan->currentPage() ?? 1;
    $perPage     = $daftar_penjualan->perPage() ?? 15;
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Mencatat dan meninjau riwayat transaksi penjualan produk."
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
            openCreate: {{ $openCreateOnLoad }},
            openDetail: false,
            openEdit: {{ $openEditOnLoad }},
            
            detailPenjualan: null,
            editPenjualan: null,
            
            // STATE PENCARIAN CLIENT-SIDE
            searchQuery: '{{ $search }}',
            
            // DATA UNTUK FORM
            produkData: {{ Js::from($produks) }},
            membersData: {{ Js::from($members) }},
            metodePembayaranOptions: {{ Js::from($metodePembayaranOptions) }},
            
            memberSearchQuery: '',
            isMemberDropdownFocused: false,
            cartItems: [
                { produk_id: null, jumlah: 1, max_stok: 9999, harga_satuan: 0, stok_tersedia: 0, nama_produk: '' }
            ],

            createForm: {
                member_id: '{{ old('member_id') ?? '' }}',
                metode_pembayaran: '{{ old('metode_pembayaran') ?? '' }}',
                keterangan: '{{ old('keterangan') ?? '' }}',
            },

            editForm: {
                id: {{ $oldEditId }},
                produk_id: {{ old('produk_id') ?? 'null' }},
                member_id: '{{ old('member_id') ?? 'null' }}',
                jumlah: {{ old('jumlah') ?? 'null' }},
                metode_pembayaran: '{{ old('metode_pembayaran') }}',
                keterangan: '{{ old('keterangan') }}',
                harga_satuan: null,
                stok_awal: null,
                jumlah_awal: null,
            },

            // --- FUNCTIONS ---
            filteredMembers() {
                if (this.memberSearchQuery.length < 1) { return this.membersData; }
                const query = this.memberSearchQuery.toLowerCase();
                return this.membersData.filter(member => member.name.toLowerCase().includes(query));
            },
            selectMember(memberId, memberName) {
                this.createForm.member_id = memberId;
                this.memberSearchQuery = memberName;
                this.isMemberDropdownFocused = false;
            },
            getMemberName(memberId) {
                if (!memberId || memberId === 'null') return 'Umum (Tidak Terdaftar)';
                const member = this.membersData.find(m => m.id == memberId);
                return member ? member.name : 'Umum (Tidak Terdaftar)';
            },
            resetCreateForm() {
                this.createForm.member_id = '';
                this.createForm.metode_pembayaran = '';
                this.createForm.keterangan = '';
                this.memberSearchQuery = '';
                this.isMemberDropdownFocused = false;
                this.cartItems = [
                    { produk_id: null, jumlah: 1, max_stok: 9999, harga_satuan: 0, stok_tersedia: 0, nama_produk: '' }
                ];
            },
            getProdukById(id) { return this.produkData.find(p => p.id == id); },
            updateCartItem(index) {
                const item = this.cartItems[index];
                if (item.produk_id) {
                    const produk = this.getProdukById(item.produk_id);
                    if (produk) {
                        item.stok_tersedia = produk.stok;
                        item.harga_satuan = produk.harga;
                        item.max_stok = produk.stok;
                        item.nama_produk = produk.nama;

                        if (item.jumlah > item.max_stok) {
                            item.jumlah = item.max_stok;
                        } else if (item.jumlah < 1 || !item.jumlah) {
                            item.jumlah = 1;
                        }
                    }
                } else {
                    item.stok_tersedia = 0;
                    item.harga_satuan = 0;
                    item.max_stok = 9999;
                    item.nama_produk = '';
                    item.jumlah = 1;
                }
            },
            addCartItem() {
                this.cartItems.push({ produk_id: null, jumlah: 1, max_stok: 9999, harga_satuan: 0, stok_tersedia: 0, nama_produk: '' });
            },
            removeCartItem(index) {
                if (this.cartItems.length > 1) {
                    this.cartItems.splice(index, 1);
                }
            },
            calculateGrandTotal() {
                return this.cartItems.reduce((sum, item) => sum + (item.jumlah * item.harga_satuan), 0);
            },
            isProductDuplicate(produk_id, currentIndex) {
                if (!produk_id) return false;
                return this.cartItems.some((item, index) => index !== currentIndex && item.produk_id == produk_id);
            },
            showDetail(penjualan) {
                this.detailPenjualan = penjualan;
                this.openDetail = true;
            },
            showEdit(penjualan) {
                const produk = this.produkData.find(p => p.id === penjualan.produk.id) || null;
                if (!produk) { console.error('Produk tidak ditemukan atau sudah dihapus. Tidak dapat mengedit.'); return; }
                this.editPenjualan = penjualan;
                this.editForm.id = penjualan.id;
                this.editForm.produk_id = penjualan.produk.id;
                this.editForm.member_id = penjualan.member_id;
                this.editForm.jumlah = penjualan.jumlah;
                this.editForm.metode_pembayaran = penjualan.metode_pembayaran;
                this.editForm.keterangan = penjualan.keterangan;
                this.editForm.harga_satuan = produk.harga;
                this.editForm.stok_awal = produk.stok + penjualan.jumlah;
                this.editForm.jumlah_awal = penjualan.jumlah;
                this.openEdit = true;
            },
            calculateEditTotal() {
                if (this.editForm.jumlah && this.editForm.harga_satuan) { return this.editForm.jumlah * this.editForm.harga_satuan; }
                return 0;
            },
            calculateMaxStockEdit() {
                return (this.editForm.stok_awal);
            },
            formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
            },
            formatRupiah(number) {
                let num = parseInt(number);
                if (isNaN(num)) return 'Rp 0';
                return 'Rp ' + num.toLocaleString('id-ID');
            }
        }" 
        x-init="
            memberSearchQuery = getMemberName(createForm.member_id);

            @if(old('produks'))
                cartItems = {{ Js::from(old('produks')) }}.map(item => {
                    const produk = getProdukById(item.produk_id);
                    return {
                        produk_id: item.produk_id,
                        jumlah: parseInt(item.jumlah) || 1,
                        max_stok: produk ? produk.stok : 9999,
                        harga_satuan: produk ? produk.harga : 0,
                        stok_tersedia: produk ? produk.stok : 0,
                        nama_produk: produk ? produk.nama : ''
                    };
                });
            @endif

            window.penjualanData = {{ Js::from($daftar_penjualan) }};

            if (openEdit) {
                const failedPenjualan = window.penjualanData.data.find(p => p.id == editForm.id);
                if (failedPenjualan) {
                    const produk = produkData.find(p => p.id === failedPenjualan.produk.id) || null;
                    if (produk) {
                        editForm.harga_satuan = produk.harga;
                        editForm.stok_awal = produk.stok + failedPenjualan.jumlah;
                        editForm.jumlah_awal = failedPenjualan.jumlah;
                    }
                    editForm.member_id = '{{ old('member_id') }}' || failedPenjualan.member_id;
                    editForm.jumlah = {{ old('jumlah') ?? 'null' }};
                    editForm.metode_pembayaran = '{{ old('metode_pembayaran') }}';
                    editForm.keterangan = '{{ old('keterangan') }}';
                    editPenjualan = failedPenjualan;
                } else {
                    openEdit = false;
                }
            }
        "
        class="min-h-screen pb-20"
    >
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar transaksi penjualan produk yang pernah terjadi."
        />
        <hr class="border-t border-brand-borderSoft mb-6 mt-2">
        
        {{-- BARIS: SEARCH + FILTER + TOMBOL AKSI --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH + FILTER ala inventaris --}}
            <div class="relative w-full max-w-md z-30" 
                 x-data="{ 
                    showFilter: false, 
                    filterMetode: '{{ $filterMetode }}', 
                    filterProduk: '{{ $filterProduk }}',
                }">
                <form action="{{ route('admin.penjualan_produk.index') }}" method="GET" x-ref="filterForm">
                    {{-- search dikirim via hidden --}}
                    <input type="hidden" name="q" :value="searchQuery"> 

                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]"
                    >
                        {{-- Icon Search --}}
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        {{-- Input Text (live client-side) --}}
                        <input
                            type="text"
                            x-model.debounce.150ms="searchQuery" 
                            placeholder="Cari produk atau pembeli..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-2 rounded-l-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off"
                        >

                        {{-- Divider --}}
                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>
                        
                        {{-- Tombol Filter Toggle --}}
                        <button
                            type="button"
                            @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium
                                   text-text-muted hover:text-text-main transition-colors mr-1
                                   rounded-full hover:bg-brand-surface-50"
                            :class="showFilter || filterMetode || filterProduk ? 'text-primary-dark bg-brand-surface-50' : ''"
                            title="Filter Lanjutan"
                        >
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
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
                                <h4 class="text-sm font-semibold text-text-main">Filter Lanjutan</h4>
                                <a href="{{ route('admin.penjualan_produk.index') }}" class="text-xs text-danger hover:underline">
                                    Reset
                                </a>
                            </div>
                            
                            {{-- Filter Produk --}}
                            <div>
                                <p class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                    Filter Produk
                                </p>
                                <div class="relative">
                                    <select 
                                        name="produk_id"
                                        x-model="filterProduk"
                                        class="custom-select w-full rounded-lg border bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8 border-brand-borderSoft
                                               focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                    >
                                        <option value="">Semua Produk</option>
                                        @foreach ($produks as $produk)
                                            <option value="{{ $produk->id }}">
                                                {{ $produk->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down"
                                       class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>

                            {{-- Filter Metode Pembayaran --}}
                            <div>
                                <p class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                    Metode Pembayaran
                                </p>
                                <div class="relative">
                                    <select 
                                        name="metode_pembayaran"
                                        x-model="filterMetode"
                                        class="custom-select w-full rounded-lg border bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8 border-brand-borderSoft
                                               focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                    >
                                        <option value="">Semua Metode</option>
                                        @foreach ($metodePembayaranOptions as $option)
                                            <option value="{{ $option }}">{{ ucwords($option) }}</option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down"
                                       class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white
                                       text-sm font-medium py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- TOMBOL CATAT PENJUALAN --}}
            <x-ui.button-primary type="button" @click="resetCreateForm(); openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Catat Penjualan
            </x-ui.button-primary>
        </div>
        
        {{-- CARD TABEL PENJUALAN (struktur ala inventaris) --}}
        <x-ui.card
            title="Daftar Penjualan Produk"
            subtitle="Transaksi terbaru dan detailnya."
            class="border-brand-borderSoft"
        >
            {{-- wrapper tabel: scroll horizontal hanya di layar kecil --}}
            <div class="w-full overflow-x-auto md:overflow-x-visible custom-scrollbar">
                <table class="table-fixed w-full border-collapse text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- total persentase kolom ≈ 100% --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[13%]">
                                Tanggal Transaksi
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Pembeli
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%]">
                                Produk
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[7%]">
                                Jumlah
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Total Harga
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Pembayaran
                            </th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Keterangan
                            </th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no = ($currentPage - 1) * $perPage + 1; @endphp
                        @forelse ($daftar_penjualan as $penjualan)
                            @php
                                $namaProduk = $penjualan->produk->nama ?? 'Produk Dihapus';
                                $namaMember = $penjualan->member->name ?? 'Umum';

                                $penjualan_data_js = [
                                    'id' => $penjualan->id,
                                    'tanggal_transaksi' => $penjualan->tanggal_transaksi,
                                    'member_id' => $penjualan->member_id,
                                    'member_name' => $namaMember,
                                    'produk' => $penjualan->produk
                                        ? ['id' => $penjualan->produk->id, 'nama' => $penjualan->produk->nama, 'harga' => $penjualan->produk->harga, 'stok' => $penjualan->produk->stok]
                                        : ['id' => 0, 'nama' => 'Produk Dihapus', 'harga' => 0, 'stok' => 0],
                                    'jumlah' => $penjualan->jumlah,
                                    'total_harga' => $penjualan->total_harga,
                                    'metode_pembayaran' => $penjualan->metode_pembayaran,
                                    'keterangan' => $penjualan->keterangan,
                                    'created_at' => $penjualan->created_at,
                                    'updated_at' => $penjualan->updated_at,
                                ];

                                $search_data = strtolower($namaProduk . ' ' . $namaMember . ' ' . $penjualan->keterangan);
                            @endphp

                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150 h-16"
                                x-show="!searchQuery || @js($search_data).includes(searchQuery.toLowerCase())"
                            >
                                {{-- NOMOR --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="text-sm font-medium text-text-muted">{{ $no++ }}</div>
                                </td>

                                {{-- TANGGAL --}}
                                <td class="p-3 align-middle">
                                    <span class="text-sm text-text-muted whitespace-nowrap">
                                        {{ Carbon::parse($penjualan->tanggal_transaksi)->locale('id')->isoFormat('D MMM YYYY') }}
                                    </span>
                                </td>

                                {{-- MEMBER --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-medium text-text-main max-w-[180px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $namaMember }}
                                    </div>
                                </td>

                                {{-- PRODUK --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main max-w-[220px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $namaProduk }}
                                    </div>
                                </td>

                                {{-- JUMLAH --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="text-sm font-bold text-primary-dark">
                                        {{ $penjualan->jumlah }}
                                    </div>
                                </td>

                                {{-- TOTAL HARGA --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-success whitespace-nowrap">
                                        {{ 'Rp' . number_format($penjualan->total_harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- METODE --}}
                                <td class="p-3 align-middle">
                                    <span class="text-xs font-medium text-text-main whitespace-nowrap">
                                        {{ $penjualan->metode_pembayaran }}
                                    </span>
                                </td>

                                {{-- KETERANGAN --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted max-w-[260px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $penjualan->keterangan ? Str::limit($penjualan->keterangan, 50) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- DETAIL --}}
                                        <div x-data="{ viewing: false }" class="relative flex flex-col items-center justify-start">
                                            <button
                                                type="button"
                                                @mouseenter="viewing = true"
                                                @mouseleave="viewing = false"
                                                @click="showDetail({{ Js::from($penjualan_data_js) }})"
                                                title="Lihat Detail"
                                                class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </button>
                                            <span
                                                x-show="viewing"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-2"
                                                class="absolute top-[33px] text-[10px] font-medium text-info whitespace-nowrap z-0"
                                            >
                                                Detail
                                            </span>
                                        </div>

                                        {{-- EDIT --}}
                                        <div x-data="{ editing: false }" class="relative flex flex-col items-center justify-start">
                                            <button
                                                type="button"
                                                @mouseenter="editing = true"
                                                @mouseleave="editing = false"
                                                @click="showEdit({{ Js::from($penjualan_data_js) }})"
                                                title="Edit Transaksi"
                                                class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            </button>
                                            <span
                                                x-show="editing"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-2"
                                                class="absolute top-[33px] text-[10px] font-medium text-yellow-600 whitespace-nowrap z-0"
                                            >
                                                Edit
                                            </span>
                                        </div>

                                        {{-- HAPUS --}}
                                        <form
                                            id="delete-penjualan-{{ $penjualan->id }}"
                                            action="{{ route('admin.penjualan_produk.destroy', $penjualan) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <div x-data="{ deleting: false }" class="relative flex flex-col items-center justify-start">
                                                <button
                                                    type="button"
                                                    @mouseenter="deleting = true"
                                                    @mouseleave="deleting = false"
                                                    title="Batalkan Transaksi"
                                                    class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150 z-10"
                                                    onclick="confirmDeletePenjualan({{ $penjualan->id }}, @js($namaProduk))"
                                                >
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                                <span
                                                    x-show="deleting"
                                                    x-cloak
                                                    x-transition:enter="transition ease-out duration-300"
                                                    x-transition:enter-start="opacity-0 translate-y-2"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-in duration-200"
                                                    x-transition:leave-start="opacity-100 translate-y-0"
                                                    x-transition:leave-end="opacity-0 translate-y-2"
                                                    class="absolute top-[33px] text-[10px] font-medium text-danger whitespace-nowrap z-0"
                                                >
                                                    Batalkan
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-6 text-center text-text-muted italic">
                                    @if ($search || $filterMetode || $filterProduk)
                                        Tidak ada transaksi yang ditemukan dengan kriteria filter/pencarian saat ini.
                                    @else
                                        Belum ada riwayat transaksi penjualan.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $daftar_penjualan->appends([
                    'q' => $search,
                    'metode_pembayaran' => $filterMetode,
                    'produk_id' => $filterProduk
                ])->links() }}
            </div>
        </x-ui.card>


        {{-- MODALS DIPISAH (ala inventaris) --}}
        @include('admin.penjualan_produk.modals.create')
        @include('admin.penjualan_produk.modals.detail')
        @include('admin.penjualan_produk.modals.edit')

    </div> {{-- penutup x-data besar --}}

    {{-- SCRIPT KONFIRMASI HAPUS --}}
    <script>
        function confirmDeletePenjualan(penjualanId, productName) {
            // Fallback jika SweetAlert belum tersedia
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin membatalkan transaksi produk ${productName}? Stok akan dikembalikan.`)) {
                    document.getElementById('delete-penjualan-' + penjualanId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Batalkan Transaksi?',
                html: `Anda yakin ingin membatalkan transaksi produk <strong>${productName}</strong>? <br> Stok produk yang dijual akan dikembalikan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#C73527',
                cancelButtonColor: '#6C5A46',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Tutup',
                background: '#21160F',
                color: '#F8F2E7',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-penjualan-' + penjualanId).submit();
                }
            });
        }
    </script>
    
    {{-- CUSTOM SCROLLBAR & SELECT --}}
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
