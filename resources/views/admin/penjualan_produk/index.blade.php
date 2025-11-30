{{-- resources/views/admin/penjualan_produk/index.blade.php --}}
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js; 
    use App\Models\User; 
    use Illuminate\Support\Facades\Session;

    $pageTitle = $pageTitle ?? 'Penjualan Produk';
    
    // --- VARIABEL UNTUK FILTER (Diambil dari Controller) ---
    $search = $search ?? request('q', ''); 
    $filterMetode = $filterMetode ?? request('metode_pembayaran', ''); 
    $filterProduk = $filterProduk ?? request('produk_id', ''); 

    // Opsi untuk filter Metode Pembayaran
    $metodePembayaranOptions = $metodePembayaran ?? ['Cash', 'Transfer', 'QRIS'];
    // ---------------------------------------------------

    // LOGIKA UNTUK MEMBUKA MODAL CREATE JIKA ADA VALIDASI ERROR DARI STORE
    $openCreateOnLoad = ($errors->any() && (old('_method') !== 'PUT')) ? 'true' : 'false';

    // LOGIKA UNTUK MEMBUKA MODAL EDIT JIKA ADA VALIDASI ERROR DARI UPDATE
    $errorsEdit = Session::get('errors') ? Session::get('errors')->getBag('default') : null; 
    $oldEditId = old('_method') === 'PUT' ? (old('id') ?? 'null') : 'null';
    $openEditOnLoad = ($errorsEdit && old('_method') === 'PUT' && $oldEditId != 'null') ? 'true' : 'false';
    
    // Data untuk AlpineJS
    $produks = $produks ?? collect();
    $members = $members ?? collect(); 
    
    // Ambil nomor halaman saat ini (default 1)
    $currentPage = $daftar_penjualan->currentPage() ?? 1;
    // Ambil jumlah item per halaman (default 15 jika tidak ada di env/config)
    $perPage = $daftar_penjualan->perPage() ?? 15;
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Mencatat dan meninjau riwayat transaksi penjualan produk."
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
            openCreate: {{ $openCreateOnLoad }},
            openDetail: false, 
            openEdit: {{ $openEditOnLoad }}, 
            
            detailPenjualan: null, 
            editPenjualan: null, 
            
            // STATE PENCARIAN CLIENT-SIDE (Live Search)
            searchQuery: '{{ $search }}', 
            
            // --- DATA CREATE MULTI-ITEM (Dipotong untuk fokus, aslinya tetap) ---
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
        >
        </x-ui.section-header>

        {{-- garis dibawah judul --}}
        <hr class="border-t border-brand-borderSoft mb-6 mt-2">
        
        {{-- FITUR PENCARIAN & FILTER + TOMBOL AKSI --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH BAR + FILTER (MENGGUNAKAN TAMPILAN CARD) --}}
            <div class="relative w-full max-w-md z-30" x-data="{ 
                showFilter: false, 
                filterMetode: '{{ $filterMetode }}', 
                filterProduk: '{{ $filterProduk }}',
                
                submitFilter() {
                    this.$refs.filterForm.submit();
                }
            }">
                <form action="{{ route('admin.penjualan_produk.index') }}" method="GET" x-ref="filterForm">
                    
                    <input type="hidden" name="q" :value="searchQuery"> 

                    {{-- Container Input Gabungan --}}
                    <div class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">
                        {{-- Icon Search --}}
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        {{-- Input Text (Live Search) --}}
                        <input
                            type="text"
                            x-model.debounce.150ms="searchQuery" 
                            placeholder="Cari produk atau pembeli..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                            autocomplete="off"
                        >

                        {{-- Divider Vertical --}}
                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>
                        
                        {{-- Tombol Filter Toggle --}}
                        <button
                            type="button"
                            @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-text-muted hover:text-text-main transition-colors mr-1 rounded-full hover:bg-brand-surface-50"
                            :class="showFilter || filterMetode || filterProduk ? 'text-primary-dark bg-brand-surface-50' : ''"
                            title="Filter Lanjutan"
                        >
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

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
                                <a href="{{ route('admin.penjualan_produk.index') }}" class="text-xs text-danger hover:underline">Reset</a>
                            </div>
                            
                            {{-- Filter Produk --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_produk_id">Filter Produk</label>
                                <div class="relative">
                                    <select 
                                        name="produk_id" 
                                        id="filter_produk_id"
                                        x-model="filterProduk"
                                        class="custom-select w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                    >
                                        <option value="" {{ $filterProduk == '' ? 'selected' : '' }}>Semua Produk</option>
                                        @foreach ($produks as $produk)
                                            <option value="{{ $produk->id }}" {{ $filterProduk == $produk->id ? 'selected' : '' }}>
                                                {{ $produk->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>

                            {{-- Filter Metode Pembayaran --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1" for="filter_metode">Metode Pembayaran</label>
                                <div class="relative">
                                    <select 
                                        name="metode_pembayaran" 
                                        id="filter_metode"
                                        x-model="filterMetode"
                                        class="custom-select w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark"
                                    >
                                        <option value="" {{ $filterMetode == '' ? 'selected' : '' }}>Semua Metode</option>
                                        @foreach ($metodePembayaranOptions as $option)
                                            <option value="{{ $option }}" {{ $filterMetode == $option ? 'selected' : '' }}>
                                                {{ ucwords($option) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
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
        
        {{-- CARD TABEL PENJUALAN --}}
        <x-ui.card
            title="Daftar Penjualan Produk"
            subtitle="Transaksi terbaru dan detailnya."
            class="border-brand-borderSoft"
        >
            <div>
                <table class="w-full border-collapse min-w-[900px] text-sm"> 
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- No: 4% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[4%] min-w-[30px]">No</th>
                            
                            {{-- Tanggal Transaksi: 12% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[100px]">Tanggal Transaksi</th>
                            
                            {{-- Pembeli: 15% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%] min-w-[130px]">Pembeli</th>
                            
                            {{-- Produk: 18% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%] min-w-[160px]">Produk</th>
                            
                            {{-- Jumlah: 6% --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[6%] min-w-[55px]">Jumlah</th>
                            
                            {{-- Total Harga: 11% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[11%] min-w-[95px]">Total Harga</th>
                            
                            {{-- Pembayaran: 10% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[90px]">Pembayaran</th>
                            
                            {{-- Keterangan: 16% --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[16%] min-w-[140px]">Keterangan</th>
                            
                            {{-- Aksi: 8% --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%] min-w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/80">
                        {{-- Inisialisasi nomor urut, dimulai dari (page - 1) * perPage + 1 --}}
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
                                    'produk' => $penjualan->produk ? ['id' => $penjualan->produk->id, 'nama' => $penjualan->produk->nama, 'harga' => $penjualan->produk->harga, 'stok' => $penjualan->produk->stok] : ['id' => 0, 'nama' => 'Produk Dihapus', 'harga' => 0, 'stok' => 0],
                                    'jumlah' => $penjualan->jumlah,
                                    'total_harga' => $penjualan->total_harga,
                                    'metode_pembayaran' => $penjualan->metode_pembayaran,
                                    'keterangan' => $penjualan->keterangan,
                                    'created_at' => $penjualan->created_at,
                                    'updated_at' => $penjualan->updated_at,
                                ];

                                // Data untuk pencarian client-side (Produk + Member + Keterangan)
                                $search_data = strtolower($namaProduk . ' ' . $namaMember . ' ' . $penjualan->keterangan);
                            @endphp
                            
                            <tr 
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                {{-- LOGIKA CLIENT-SIDE FILTERING --}}
                                x-show="!searchQuery || @js($search_data).includes(searchQuery.toLowerCase())"
                            >
                                
                                {{-- NOMOR URUT (4%) --}}
                                <td class="p-3 align-middle w-[4%] min-w-[30px]">
                                    <div class="text-sm font-medium text-text-muted text-center">{{ $no++ }}</div>
                                </td>
                                
                                {{-- TANGGAL (12%) - Diubah agar satu baris --}}
                                <td class="p-3 align-middle w-[12%] min-w-[100px]"> 
                                    <span class="text-sm text-text-muted whitespace-nowrap">
                                        {{ Carbon::parse($penjualan->tanggal_transaksi)->locale('id')->isoFormat('D MMM YYYY') }}
                                    </span>
                                </td>
                                
                                {{-- MEMBER (15%) --}}
                                <td class="p-3 align-middle w-[15%] min-w-[130px]">
                                    <div class="text-sm font-medium text-text-main">
                                        {{ $namaMember }}
                                    </div>
                                </td>

                                {{-- PRODUK (18%) --}}
                                <td class="p-3 align-middle w-[18%] min-w-[160px]">
                                    <div class="text-sm font-semibold text-text-main">{{ $namaProduk }}</div>
                                </td>

                                {{-- JUMLAH (6%) --}}
                                <td class="p-3 align-middle text-center w-[6%] min-w-[55px]">
                                    <div class="text-sm font-bold text-primary-dark">{{ $penjualan->jumlah }}</div>
                                </td>
                                
                                {{-- TOTAL HARGA (11%) --}}
                                <td class="p-3 align-middle w-[11%] min-w-[95px]">
                                    <div class="text-sm font-semibold text-success">
                                        {{ 'Rp' . number_format($penjualan->total_harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- METODE PEMBAYARAN (10%) --}}
                                <td class="p-3 align-middle w-[10%] min-w-[90px]">
                                    <span class="text-xs font-medium text-text-main whitespace-nowrap">
                                        {{ $penjualan->metode_pembayaran }}
                                    </span>
                                </td>
                                
                                {{-- KETERANGAN (16%) --}}
                                <td class="p-3 align-middle w-[16%] min-w-[140px]"> 
                                    <div class="text-xs text-text-muted max-w-full">
                                        {{ $penjualan->keterangan ? Str::limit($penjualan->keterangan, 30) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI (8%) --}}
                                <td class="p-3 align-middle w-[8%] min-w-[80px]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        
                                        {{-- WRAPPER DETAIL ICON (BIRU) --}}
                                        <div x-data="{ viewing: false }" class="relative flex flex-col items-center justify-start">
                                            <button 
                                                type="button" 
                                                @mouseenter="viewing = true"
                                                @mouseleave="viewing = false"
                                                @click="showDetail({{ Js::from($penjualan_data_js) }})" 
                                                title="Lihat Detail"
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

                                        {{-- WRAPPER EDIT ICON (Kuning/Primary) --}}
                                        <div x-data="{ editing: false }" class="relative flex flex-col items-center justify-start">
                                            <button 
                                                type="button" 
                                                @mouseenter="editing = true"
                                                @mouseleave="editing = false"
                                                @click="showEdit({{ Js::from($penjualan_data_js) }})" 
                                                title="Edit Transaksi"
                                                class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="square-pen" class="w-6 h-6"></i>
                                            </button>
                                            <span x-show="editing" 
                                                x-cloak 
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-2"
                                                class="absolute top-[33px] text-[10px] font-medium text-yellow-600 whitespace-nowrap z-0">
                                                Edit
                                            </span>
                                        </div>
                                        
                                        {{-- WRAPPER HAPUS / BATALKAN (Merah) --}}
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
                                                    onclick="confirmDeletePenjualan({{ $penjualan->id }}, '{{ $namaProduk }}')"
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
                                                    Batalkan
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Disesuaikan menjadi 9 kolom (termasuk No) --}}
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

            <div class="mt-6">
                {{-- PERUBAHAN: Menambahkan parameter filter ke tautan pagination --}}
                {{ $daftar_penjualan->appends(['q' => $search, 'metode_pembayaran' => $filterMetode, 'produk_id' => $filterProduk])->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL CATAT PENJUALAN BARU (CREATE) --}}
        {{-- ... (Kode Modal Create tidak berubah) ... --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openCreate = false"
                class="relative w-full max-w-5xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Catat Penjualan Produk</h2>
                        <p class="text-sm text-text-muted mt-0.5">Satu transaksi untuk beberapa jenis produk.</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openCreate = false; resetCreateForm()">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                {{-- KONTEN MODAL DENGAN SCROLL VERTICAL --}}
                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar"> 
                    <form action="{{ route('admin.penjualan_produk.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        {{-- Menampilkan error validasi dari Store --}}
                        @if ($errors->any() && (old('_method') !== 'PUT' || session('modal_create_open')))
                            <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                                <p class="text-sm font-semibold">Ada kesalahan input:</p>
                                <ul class="list-disc list-inside text-xs mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            {{-- KOLOM KIRI: DATA TRANSAKSI UTAMA --}}
                            <div class="md:col-span-1 space-y-4">
                                <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">Detail Pembeli & Pembayaran</h3>

                                {{-- MEMBER ID (SEARCHABLE DROPDOWN) --}}
                                <div x-data="{ 
                                        isFocused: false, 
                                        init() { this.memberSearchQuery = this.getMemberName(this.createForm.member_id); }
                                    }" 
                                    @click.away="isFocused = false" 
                                    class="relative">
                                    
                                    <x-ui.label for="member_id_modal">Pembeli (Member)</x-ui.label>
                                    
                                    {{-- INPUT UTAMA (Simulasi Dropdown) --}}
                                    <div class="relative">
                                        <input 
                                            type="text"
                                            x-ref="memberInput"
                                            x-model.debounce.150ms="memberSearchQuery"
                                            @focus="isFocused = true"
                                            @input="createForm.member_id = ''"
                                            @keydown.escape.prevent.stop="isFocused = false"
                                            x-bind:placeholder="createForm.member_id ? getMemberName(createForm.member_id) : 'Cari nama member...'"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main pl-3 pr-8 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent cursor-pointer"
                                            :value="createForm.member_id ? getMemberName(createForm.member_id) : memberSearchQuery"
                                        >
                                        
                                        {{-- Icon Dropdown/Clear --}}
                                        <i data-lucide="chevron-down" 
                                            class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 transition-transform duration-200"
                                            x-bind:class="{'rotate-180': isFocused}">
                                        </i>
                                    </div>

                                    <input type="hidden" name="member_id" :value="createForm.member_id">
                                    
                                    {{-- Dropdown Hasil Pencarian --}}
                                    <div 
                                        x-show="isFocused" 
                                        class="absolute z-20 w-full mt-1 bg-brand-card border border-brand-borderSoft rounded-xl shadow-lg max-h-48 overflow-y-auto"
                                        x-transition
                                    >
                                        <ul class="py-1">
                                            {{-- Opsi Umum --}}
                                            <li>
                                                <a href="#" @click.prevent="selectMember('', 'Umum (Tidak Terdaftar)')" 
                                                    class="block px-4 py-2 text-sm text-text-main hover:bg-brand-surface-50 font-semibold"
                                                    x-bind:class="{ 'bg-primary-soft/50': createForm.member_id === '' }">
                                                     -- Umum (Tidak Terdaftar) --
                                                </a>
                                            </li>

                                            <template x-for="member in filteredMembers()" :key="member.id">
                                                <li>
                                                    <a href="#" @click.prevent="selectMember(member.id, member.name)" 
                                                        class="block px-4 py-2 text-sm text-text-main hover:bg-brand-surface-50"
                                                        x-bind:class="{ 'bg-primary-soft/50': createForm.member_id == member.id }"
                                                        x-text="member.name"></a>
                                                </li>
                                            </template>
                                            <template x-if="filteredMembers().length === 0 && memberSearchQuery.length > 0">
                                                <div class="px-4 py-2 text-sm text-text-muted italic">Nama Member tidak ditemukan</div>
                                            </template>
                                        </ul>
                                    </div>
                                    
                                    @error('member_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>


                                {{-- METODE PEMBAYARAN (Standard Select, Mengikuti Style Pembeli) --}}
                                <div>
                                    <x-ui.label for="metode_pembayaran_modal">Metode Pembayaran<span class="text-danger">*</span></x-ui.label>
                                    <div class="relative">
                                        <select 
                                            id="metode_pembayaran_modal" 
                                            name="metode_pembayaran" 
                                            x-model="createForm.metode_pembayaran"
                                            required
                                            class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('metode_pembayaran') border-danger ring-danger-soft @enderror"
                                        >
                                            <option value="" disabled>-- Pilih Metode --</option>
                                            <template x-for="metode in metodePembayaranOptions" :key="metode">
                                                <option :value="metode" x-text="metode" :selected="createForm.metode_pembayaran == metode"></option>
                                            </template>
                                        </select>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                    </div>
                                    @error('metode_pembayaran')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                {{-- TOTAL AKHIR --}}
                                <div class="p-4 border border-primary-dark/50 rounded-xl bg-primary-soft/30">
                                    <h3 class="text-lg font-bold text-text-main">Total Keseluruhan:</h3>
                                    <p class="text-2xl font-extrabold text-primary-dark" x-text="formatRupiah(calculateGrandTotal())"></p>
                                </div>
                            </div>

                            {{-- KOLOM KANAN: DAFTAR PRODUK (KERANJANG) & KETERANGAN --}}
                            <div class="md:col-span-2 space-y-4">
                                <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">Daftar Produk Dibeli</h3>
                                
                                {{-- ITEM LIST / KERANJANG --}}
                                <div class="space-y-4 max-h-[300px] overflow-y-auto custom-scrollbar p-1">
                                    <template x-for="(item, index) in cartItems" :key="index">
                                        <div class="p-3 border border-brand-borderSoft rounded-xl bg-brand-surface-50 grid grid-cols-12 gap-3 items-center relative">
                                            
                                            <div class="col-span-7">
                                                <x-ui.label x-bind:for="'produk_id_' + index" class="text-xs font-medium">Produk</x-ui.label>
                                                <div class="relative">
                                                    <select 
                                                        x-bind:id="'produk_id_' + index" 
                                                        x-model="item.produk_id" 
                                                        x-on:change="updateCartItem(index)"
                                                        x-bind:name="'produks[' + index + '][produk_id]'" 
                                                        required
                                                        x-bind:class="{'border-danger ring-danger-soft': isProductDuplicate(item.produk_id, index) || item.produk_id === null}"
                                                        class="custom-select w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                                        
                                                        <option :value="null" disabled :selected="item.produk_id === null">--- Pilih Produk ---</option> 
                                                        <template x-for="produk in produkData" :key="produk.id">
                                                            <option 
                                                                x-bind:value="produk.id" 
                                                                x-text="produk.nama + ' (Stok: ' + produk.stok + ')'"
                                                                x-bind:disabled="isProductDuplicate(produk.id, index) || produk.stok === 0">
                                                            </option>
                                                        </template>
                                                    </select>
                                                    <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                                </div>
                                                <p x-show="isProductDuplicate(item.produk_id, index)" class="text-xs text-danger mt-1">Produk ini sudah ditambahkan.</p>
                                                <p class="text-[10px] text-text-muted mt-1" x-text="'Harga: ' + formatRupiah(item.harga_satuan)"></p>
                                            </div>
                                            
                                            <div class="col-span-4">
                                                <x-ui.label x-bind:for="'jumlah_' + index" class="text-xs font-medium">Jumlah</x-ui.label>
                                                <input 
                                                    type="number" 
                                                    x-bind:id="'jumlah_' + index" 
                                                    x-model.number="item.jumlah"
                                                    x-on:input="updateCartItem(index)"
                                                    x-bind:name="'produks[' + index + '][jumlah]'" 
                                                    min="1" 
                                                    x-bind:max="item.max_stok"
                                                    required
                                                    class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft">
                                                <p class="text-[10px] text-text-muted mt-1" x-text="'Subtotal: ' + formatRupiah(item.jumlah * item.harga_satuan)"></p>
                                            </div>
                                            
                                            <div class="col-span-1 flex justify-end">
                                                <button type="button" @click="removeCartItem(index)" class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition duration-150" title="Hapus Item" x-show="cartItems.length > 1">
                                                    <i data-lucide="minus-circle" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                {{-- TOMBOL TAMBAH ITEM --}}
                                <x-ui.button-secondary type="button" @click="addCartItem()" class="w-full">
                                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Item Produk
                                </x-ui.button-secondary>

                                {{-- KETERANGAN GLOBAL --}}
                                <div>
                                    <x-ui.label for="keterangan_modal">Keterangan Transaksi (Opsional)</x-ui.label>
                                    <textarea 
                                        id="keterangan_modal" 
                                        name="keterangan" 
                                        rows="3"
                                        x-model="createForm.keterangan"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft 
                                        focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('keterangan') border-danger ring-danger-soft @enderror"
                                    ></textarea>
                                    @error('keterangan')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end border-t border-brand-borderSoft pt-4">
                            <x-ui.button-secondary type="button" @click="openCreate = false; resetCreateForm()" class="mr-2">
                                Batal
                            </x-ui.button-secondary>
                            <x-ui.button-primary type="submit" x-bind:disabled="calculateGrandTotal() === 0">
                                Catat Transaksi (<span x-text="formatRupiah(calculateGrandTotal())"></span>)
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL (TIDAK BERUBAH) --}}
        {{-- ... (Kode Modal Detail) ... --}}
        <div
            x-show="openDetail"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openDetail = false"
                class="relative w-full max-w-2xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Detail Transaksi Penjualan</h2>
                        <p class="text-sm text-text-muted mt-0.5">Informasi lengkap mengenai transaksi produk.</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openDetail = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar" x-if="detailPenjualan">
                    
                    <div class="space-y-4">
                        
                        {{-- Ringkasan Transaksi --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                            <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                                <span class="text-sm font-medium text-text-muted">Tanggal Transaksi:</span>
                                <span class="font-semibold text-sm text-text-main" x-text="formatDate(detailPenjualan.tanggal_transaksi)"></span>
                            </div>
                            
                            {{-- MEMBER ID (DETAIL) --}}
                            <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                                <span class="text-sm font-medium text-text-muted">Pembeli:</span>
                                <span class="font-semibold text-sm text-primary-dark" x-text="detailPenjualan.member_name || 'Umum'"></span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-text-muted">Metode Pembayaran:</span>
                                <span class="font-semibold text-sm text-primary-dark" x-text="detailPenjualan.metode_pembayaran"></span>
                            </div>
                        </div>

                        {{-- Detail Produk --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                            <h3 class="text-base font-semibold text-text-main border-b border-brand-borderSoft pb-2 mb-2">Produk yang Dijual:</h3>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-text-muted">Nama Produk:</span>
                                <span class="font-semibold text-sm text-text-main" x-text="detailPenjualan.produk.nama"></span>
                            </div>
                            
                            <div 
                                x-data="{
                                    formatRupiah(angka) {
                                        if (!angka) return '0';
                                        let num = parseInt(angka); // hilangkan .00
                                        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                    }
                                }"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm text-text-muted">Harga Satuan:</span>
                                <span class="text-sm font-medium text-text-main" 
                                    x-text="'Rp ' + formatRupiah(detailPenjualan.produk.harga)">
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-text-muted">Jumlah Beli:</span>
                                <span class="font-bold text-sm text-primary-dark" x-text="detailPenjualan.jumlah + ' unit'"></span>
                            </div>
                            
                        </div>

                        {{-- Total Harga --}}
                        <div 
                            x-data="{
                                formatRupiah(angka) {
                                    if (!angka) return '0';
                                    let num = parseInt(angka); // hilangkan .00
                                    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                }
                            }"
                            class="p-4 border border-success/50 rounded-xl bg-success-soft/30"
                        >
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-text-main">Total Bayar:</span>
                                <span class="text-xl font-extrabold text-success" 
                                    x-text="'Rp ' + formatRupiah(detailPenjualan.total_harga)">
                                </span>
                            </div>
                        </div>
                        
                        {{-- Keterangan --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                            <h3 class="text-sm font-medium text-text-muted">Keterangan:</h3>
                            <p class="text-sm text-text-main mt-1 italic" x-text="detailPenjualan.keterangan || '-'"></p>
                        </div>
                        
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-ui.button-secondary type="button" @click="openDetail = false">Tutup</x-ui.button-secondary>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL EDIT PENJUALAN (UPDATE) --}}
        {{-- ... (Kode Modal Edit tidak berubah) ... --}}
        <div
            x-show="openEdit"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openEdit = false"
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Edit Item Transaksi</h2>
                        <p class="text-sm text-text-muted mt-0.5" x-text="'Produk: ' + (editPenjualan ? editPenjualan.produk.nama : '')">
                            Ubah detail item transaksi ini. Perubahan Member/Metode Pembayaran akan berlaku untuk satu batch transaksi.
                        </p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openEdit = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar" x-if="editPenjualan">
                    <form :action="'{{ url('admin/penjualan_produk') }}/' + editForm.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT') 
                        
                        <input type="hidden" name="id" :value="editForm.id">
                        <input type="hidden" name="produk_id" :value="editForm.produk_id"> 
                        
                        {{-- ERROR VALIDASI untuk PUT request --}}
                        @if ($errors->any() && old('_method') === 'PUT')
                            <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                                <p class="text-sm font-semibold">Ada kesalahan input saat mengedit:</p>
                                <ul class="list-disc list-inside text-xs mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">Detail Item & Jumlah</h3>
                                
                                {{-- PRODUK ID (Disabled) --}}
                                <div>
                                    <x-ui.label for="produk_id_modal_edit">Produk Dijual<span class="text-danger">*</span></x-ui.label>
                                    <input 
                                        type="text" 
                                        :value="editPenjualan ? editPenjualan.produk.nama : ''" 
                                        disabled
                                        class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft"
                                    >
                                    <p class="text-xs text-text-muted mt-1">Produk tidak dapat diubah.</p>
                                </div>

                                {{-- JUMLAH --}}
                                <div>
                                    <x-ui.label for="jumlah_modal_edit">Jumlah Beli<span class="text-danger">*</span></x-ui.label>
                                    <input 
                                        type="number" 
                                        id="jumlah_modal_edit" 
                                        name="jumlah"
                                        x-model.number="editForm.jumlah" 
                                        min="1"
                                        :max="calculateMaxStockEdit()" 
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('jumlah')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                    <p class="text-xs text-text-muted mt-1">Stok Tersedia Maksimal: <span x-text="calculateMaxStockEdit()"></span> (Stok saat ini + Jumlah lama)</p>
                                </div>

                                {{-- Detail Perhitungan Harga Edit --}}
                                <div 
                                    x-data="{
                                        formatRupiah(angka) {
                                            if (!angka) return '0';
                                            let num = parseInt(angka); // hilangkan .00
                                            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                        }
                                    }"
                                    x-show="editForm && editForm.harga_satuan"
                                    class="mt-4 p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2"
                                >
                                    <h3 class="font-semibold text-text-main">Detail Harga:</h3>

                                    <p class="text-sm text-text-muted">
                                        Harga Satuan: 
                                        <span class="font-medium text-text-main"
                                            x-text="'Rp ' + formatRupiah(editForm.harga_satuan)">
                                        </span>
                                    </p>
                                    <p class="text-lg font-bold text-success">Total Bayar Item Baru: 
                                        <span x-text="'Rp ' + formatRupiah(calculateEditTotal())"></span>
                                        <input type="hidden" name="total_harga" :value="calculateEditTotal()">
                                    </p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-text-main border-b border-brand-borderSoft pb-2">Data Transaksi Penjualan Produk</h3>
                                
                                {{-- MEMBER ID (EDIT) - Standard Select, Mengikuti Style Pembeli --}}
                                <div>
                                    <x-ui.label for="member_id_modal_edit">Pembeli<span class="text-danger">*</span></x-ui.label>
                                    <div class="relative">
                                        <select 
                                            id="member_id_modal_edit" 
                                            name="member_id" 
                                            x-model="editForm.member_id"
                                            class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('member_id') border-danger ring-danger-soft @enderror"
                                        >
                                            <option value="">-- Umum (Tidak Terdaftar) --</option>
                                            <template x-for="member in membersData" :key="member.id">
                                                <option :value="member.id">
                                                    <span x-text="member.name"></span>
                                                </option>
                                            </template>
                                        </select>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                    </div>
                                    @error('member_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>

                                {{-- METODE PEMBAYARAN (EDIT) - Standard Select, Mengikuti Style Pembeli --}}
                                <div>
                                    <x-ui.label for="metode_pembayaran_modal_edit">Metode Pembayaran<span class="text-danger">*</span></x-ui.label>
                                    <div class="relative">
                                        <select 
                                            id="metode_pembayaran_modal_edit" 
                                            name="metode_pembayaran" 
                                            x-model="editForm.metode_pembayaran"
                                            required
                                            class="custom-select w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('metode_pembayaran') border-danger ring-danger-soft @enderror"
                                        >
                                            <option value="" disabled>-- Pilih Metode --</option>
                                            @foreach ($metodePembayaranOptions as $metode)
                                                <option :value="'{{ $metode }}'" :selected="editForm.metode_pembayaran === '{{ $metode }}'">
                                                    {{ $metode }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"></i>
                                    </div>
                                    @error('metode_pembayaran')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                {{-- KETERANGAN --}}
                                <div>
                                    <x-ui.label for="keterangan_modal_edit">Keterangan (Opsional)</x-ui.label>
                                    <textarea 
                                        id="keterangan_modal_edit" 
                                        name="keterangan" 
                                        rows="6"
                                        x-model="editForm.keterangan"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('keterangan') border-danger ring-danger-soft @enderror"
                                    ></textarea>
                                    @error('keterangan')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end border-t border-brand-borderSoft pt-4">
                            <x-ui.button-secondary type="button" @click="openEdit = false" class="mr-2">
                                Batal
                            </x-ui.button-secondary>
                            <x-ui.button-primary type="submit">
                                Simpan Perubahan
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div> {{-- Penutup div x-data besar --}}

    {{-- SCRIPT KONFIRMASI HAPUS (TETAP) --}}
    <script>
        function confirmDeletePenjualan(penjualanId, productName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin membatalkan transaksi produk ${productName}? Stok akan dikembalikan.`)) {
                    document.getElementById('delete-penjualan-' + penjualanId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Batalkan Transaksi?',
                html: `Anda yakin ingin membatalkan transaksi produk <strong>${productName}</strong>? <br> Stok produk yang dijual akan **dikembalikan**.`,
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
    
    {{-- CUSTOM SCROLLBAR (TETAP) --}}
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
        
        /* CSS KHUSUS UNTUK MENIRU DROPDOWN (Menghilangkan panah default) */
        .custom-select {
            /* Menghilangkan panah default di kebanyakan browser */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            /* Memberi ruang di kanan untuk ikon panah Lucide */
            padding-right: 2.5rem !important; 
            /* Mengganti kursor ke pointer, seperti yang bisa diklik */
            cursor: pointer;
        }
    </style>
</x-layouts.admin>