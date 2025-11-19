@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js; 

    $pageTitle = $pageTitle ?? 'Riwayat Penjualan Produk';
    
    // LOGIKA UNTUK MEMBUKA MODAL CREATE JIKA ADA VALIDASI ERROR DARI STORE
    $openCreateOnLoad = ($errors->any() && (old('_method') !== 'PUT')) ? 'true' : 'false';

    // LOGIKA UNTUK MEMBUKA MODAL EDIT JIKA ADA VALIDASI ERROR DARI UPDATE
    $openEditOnLoad = ($errors->any() && old('_method') === 'PUT' && old('id')) ? 'true' : 'false';
    $oldEditId = old('_method') === 'PUT' ? (old('id') ?? 'null') : 'null';
    
    // Data untuk AlpineJS
    $produks = $produks ?? collect();
    $metodePembayaran = $metodePembayaran ?? [];
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
            openEdit: {{ $openEditOnLoad }}, // <-- Data Modal Edit
            
            detailPenjualan: null, 
            editPenjualan: null, 
            
            // Data Create
            selectedProductId: {{ old('produk_id') ?? 'null' }}, 
            produkData: {{ Js::from($produks) }},
            selectedProductObject: null,
            jumlahBeli: {{ old('jumlah') ?? 1 }},
            
            // Data Edit (untuk form)
            editForm: {
                id: {{ $oldEditId }}, 
                produk_id: {{ old('produk_id') ?? 'null' }},
                jumlah: {{ old('jumlah') ?? 'null' }},
                metode_pembayaran: '{{ old('metode_pembayaran') }}',
                keterangan: '{{ old('keterangan') }}',
                harga_satuan: null, 
                stok_awal: null, 
                jumlah_awal: null, 
            },
            
            updateProductObject() {
                this.selectedProductObject = this.produkData.find(p => p.id == this.selectedProductId);
            },
            
            // Fungsi untuk membuka modal detail
            showDetail(penjualan) {
                this.detailPenjualan = penjualan;
                this.openDetail = true;
            },
            
            // Fungsi untuk membuka modal Edit
            showEdit(penjualan) {
                const produk = this.produkData.find(p => p.id === penjualan.produk.id) || null; 
                
                if (!produk) {
                    alert('Produk tidak ditemukan atau sudah dihapus. Tidak dapat mengedit.');
                    return;
                }

                this.editPenjualan = penjualan;

                // Isi data ke form untuk modal edit
                this.editForm.id = penjualan.id;
                this.editForm.produk_id = penjualan.produk.id;
                this.editForm.jumlah = penjualan.jumlah;
                this.editForm.metode_pembayaran = penjualan.metode_pembayaran;
                this.editForm.keterangan = penjualan.keterangan;
                this.editForm.harga_satuan = produk.harga;
                this.editForm.stok_awal = produk.stok;
                this.editForm.jumlah_awal = penjualan.jumlah;
                
                this.openEdit = true;
            },
            
            // Hitung total harga edit
            calculateEditTotal() {
                if (this.editForm.jumlah && this.editForm.harga_satuan) {
                    return this.editForm.jumlah * this.editForm.harga_satuan;
                }
                return 0;
            },
            
            // Hitung stok maksimal untuk input jumlah di modal edit
            calculateMaxStockEdit() {
                 // Stok saat ini + Jumlah yang dibeli sebelumnya
                 // Catatan: Asumsi produk.stok adalah stok produk SAAT INI (setelah transaksi ini dikurangi)
                 return (this.editForm.stok_awal + this.editForm.jumlah_awal);
            },

            // Helper untuk format tanggal
            formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },
            
            // Helper untuk format mata uang
            formatRupiah(number) {
                return 'Rp ' + number.toLocaleString('id-ID');
            }
        }" 
        x-init="
            updateProductObject();
            // Data penjualan dari PHP untuk Alpine.js
            // Pastikan data ini di-load dengan relasi 'produk'
            window.penjualanData = {{ Js::from($daftar_penjualan) }};

            // Logika untuk mengisi data edit jika modal edit dibuka karena validasi error
            if (openEdit) {
                // Mencari data transaksi yang gagal divalidasi
                const failedPenjualan = window.penjualanData.data.find(p => p.id == editForm.id);
                if (failedPenjualan) {
                    const produk = produkData.find(p => p.id === failedPenjualan.produk.id) || null;
                    if (produk) {
                        editForm.harga_satuan = produk.harga;
                        editForm.stok_awal = produk.stok;
                        editForm.jumlah_awal = failedPenjualan.jumlah; 
                    }
                    editPenjualan = failedPenjualan;
                } else {
                    openEdit = false;
                }
            }
        "
    > 
        {{-- HEADER HALAMAN (Sama seperti Produk) --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar transaksi penjualan produk yang pernah terjadi."
        >
            {{-- TOMBOL CATAT PENJUALAN (Struktur dan Warna Sama Persis) --}}
            <x-ui.button-primary type="button" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Catat Penjualan
            </x-ui.button-primary>
        </x-ui.section-header>

        {{-- CARD TABEL PENJUALAN (Sama seperti Produk) --}}
        <x-ui.card
            title="Daftar Riwayat Penjualan"
            subtitle="Transaksi terbaru dan detailnya."
            class="border-brand-borderSoft"
        >
            {{-- Perbaikan: Mengurangi min-w tabel agar tidak selalu melebihi wadah pada layar yang lebih kecil. --}}
            <div class="overflow-x-auto custom-scrollbar">
                {{-- Menggunakan min-w dan w-full collapse --}}
                {{-- Menurunkan min-w dari 900px menjadi 800px atau menyesuaikan lebar kolom di bawah --}}
                <table class="w-full border-collapse min-w-[800px] text-sm"> 
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- PENYESUAIAN KOLOM agar tampil proporsional --}}
                            {{-- Sesuaikan persentase dan min-width: --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%] min-w-[150px]">Tanggal</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%] min-w-[200px]">Produk</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[70px]">Jumlah</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%] min-w-[120px]">Total Harga</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%] min-w-[120px]">Pembayaran</th>
                            <th class="p-3 text-left text-[12px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[100px]">Keterangan</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[80px]">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($daftar_penjualan as $penjualan)
                            @php
                                $tanggal = Carbon::parse($penjualan->tanggal_transaksi)->locale('id')->isoFormat('D MMM YYYY, HH:mm');
                                $namaProduk = $penjualan->produk->nama ?? 'Produk Dihapus';
                                // Siapkan data lengkap penjualan untuk AlpineJS (pastikan relasi produk juga dimuat)
                                $penjualan_data_js = [
                                    'id' => $penjualan->id,
                                    'tanggal_transaksi' => $penjualan->tanggal_transaksi,
                                    'produk' => $penjualan->produk ? ['id' => $penjualan->produk->id, 'nama' => $penjualan->produk->nama, 'harga' => $penjualan->produk->harga] : ['id' => 0, 'nama' => 'Produk Dihapus', 'harga' => 0],
                                    'jumlah' => $penjualan->jumlah,
                                    'total_harga' => $penjualan->total_harga,
                                    'metode_pembayaran' => $penjualan->metode_pembayaran,
                                    'keterangan' => $penjualan->keterangan,
                                    'created_at' => $penjualan->created_at,
                                    'updated_at' => $penjualan->updated_at,
                                ];
                            @endphp
                            
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                
                                {{-- TANGGAL --}}
                                {{-- Menggunakan lebar yang sama dengan header --}}
                                <td class="p-3 align-middle w-[18%] min-w-[150px]"> 
                                    <div class="text-xs text-text-muted">
                                        {{ $tanggal }}
                                    </div>
                                </td>

                                {{-- PRODUK --}}
                                <td class="p-3 align-middle w-[20%] min-w-[200px]">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ $namaProduk }}
                                    </div>
                                </td>

                                {{-- JUMLAH --}}
                                <td class="p-3 align-middle text-center w-[10%] min-w-[70px]">
                                    <div class="text-sm font-bold text-primary-dark">
                                        {{ $penjualan->jumlah }}
                                    </div>
                                </td>
                                
                                {{-- TOTAL HARGA --}}
                                <td class="p-3 align-middle w-[15%] min-w-[120px]">
                                    <div class="text-sm font-semibold text-success">
                                        {{ 'Rp ' . number_format($penjualan->total_harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- METODE PEMBAYARAN --}}
                                <td class="p-3 align-middle w-[15%] min-w-[120px]">
                                    <span class="text-xs font-medium text-text-main whitespace-nowrap">
                                        {{ $penjualan->metode_pembayaran }}
                                    </span>
                                </td>
                                
                                {{-- KETERANGAN --}}
                                <td class="p-3 align-middle w-[12%] min-w-[100px]"> 
                                    <div class="text-xs text-text-muted max-w-full">
                                        {{ $penjualan->keterangan ? Str::limit($penjualan->keterangan, 30) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle w-[10%] min-w-[80px]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        
                                        {{-- DETAIL ICON --}}
                                        <button 
                                            type="button" 
                                            @click="showDetail({{ Js::from($penjualan_data_js) }})" 
                                            title="Lihat Detail"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="eye" class="w-6 h-6"></i>
                                        </button>

                                        {{-- EDIT ICON --}}
                                        <button 
                                            type="button" 
                                            @click="showEdit({{ Js::from($penjualan_data_js) }})" 
                                            title="Edit Transaksi"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="square-pen" class="w-6 h-6"></i>
                                        </button>
                                        
                                        {{-- HAPUS / BATALKAN --}}
                                        <form
                                            id="delete-penjualan-{{ $penjualan->id }}"
                                            action="{{ route('admin.penjualan_produk.destroy', $penjualan) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Batalkan Transaksi"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                onclick="confirmDeletePenjualan({{ $penjualan->id }}, '{{ $namaProduk }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-6 h-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic"> 
                                    Belum ada riwayat transaksi penjualan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $daftar_penjualan->links() }}
            </div>
        </x-ui.card>

        {{-- ======================== --}}
        {{-- MODAL CATAT PENJUALAN BARU (CREATE) --}}
        {{-- ======================== --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openCreate = false"
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">
                            Catat Penjualan Baru
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5">
                            Masukkan detail transaksi penjualan produk baru.
                        </p>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openCreate = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4">
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

                        <div class="space-y-4">
                            <div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    {{-- PRODUK ID --}}
                                    <div>
                                        <x-ui.label for="produk_id_modal">Pilih Produk</x-ui.label>
                                        <select 
                                            id="produk_id_modal" 
                                            name="produk_id"
                                            x-model="selectedProductId" 
                                            x-on:change="updateProductObject()"
                                            required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('produk_id') border-danger ring-danger-soft @enderror"
                                        >
                                            <option value="" disabled :selected="selectedProductId === null">-- Pilih Produk --</option>
                                            @foreach ($produks as $produk)
                                                <option 
                                                    value="{{ $produk->id }}" 
                                                    {{ old('produk_id') == $produk->id ? 'selected' : '' }}
                                                >
                                                    {{ $produk->nama }} (Stok: {{ $produk->stok }}) - Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('produk_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- JUMLAH --}}
                                    <div x-data="{ jumlah: {{ old('jumlah') ?? 1 }} }">
                                        <x-ui.label for="jumlah_modal">Jumlah Beli</x-ui.label>
                                        <input 
                                            type="number" 
                                            id="jumlah_modal" 
                                            name="jumlah"
                                            x-model.number="jumlahBeli" 
                                            value="{{ old('jumlah') ?? 1 }}" 
                                            min="1"
                                            :max="selectedProductObject ? selectedProductObject.stok : 9999" 
                                            required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah') border-danger ring-danger-soft @enderror"
                                        >
                                        @error('jumlah')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        <p x-show="selectedProductObject" class="text-xs text-text-muted mt-1">Stok tersedia: <span x-text="selectedProductObject.stok"></span></p>
                                    </div>
                                </div>
                                
                                {{-- Detail Perhitungan Harga --}}
                                <div x-show="selectedProductObject" class="mt-4 p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                                    <h3 class="font-semibold text-text-main">Detail Harga:</h3>
                                    <p class="text-sm text-text-muted">Harga Satuan: 
                                        <span class="font-medium text-text-main" x-text="'Rp ' + (selectedProductObject.harga ? selectedProductObject.harga.toLocaleString('id-ID') : '0')"></span>
                                    </p>
                                    <p class="text-lg font-bold text-success">Total Bayar: 
                                        <span x-text="'Rp ' + (selectedProductObject ? (selectedProductObject.harga * jumlahBeli).toLocaleString('id-ID') : '0')"></span>
                                    </p>
                                </div>
                                
                            </div>

                            {{-- METODE PEMBAYARAN --}}
                            <div>
                                <x-ui.label for="metode_pembayaran_modal">Metode Pembayaran</x-ui.label>
                                <select 
                                    id="metode_pembayaran_modal" 
                                    name="metode_pembayaran" 
                                    required
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('metode_pembayaran') border-danger ring-danger-soft @enderror"
                                >
                                    <option value="" disabled {{ old('metode_pembayaran') == null ? 'selected' : '' }}>-- Pilih Metode --</option>
                                    @foreach ($metodePembayaran as $metode)
                                        <option value="{{ $metode }}" {{ old('metode_pembayaran') == $metode ? 'selected' : '' }}>
                                            {{ $metode }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('metode_pembayaran')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>
                            
                            {{-- KETERANGAN --}}
                            <div>
                                <x-ui.label for="keterangan_modal">Keterangan (Opsional)</x-ui.label>
                                <textarea 
                                    id="keterangan_modal" 
                                    name="keterangan" 
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('keterangan') border-danger ring-danger-soft @enderror"
                                >{{ old('keterangan') }}</textarea>
                                @error('keterangan')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-ui.button-secondary type="button" @click="openCreate = false" class="mr-2">
                                Batal
                            </x-ui.button-secondary>
                            <x-ui.button-primary type="submit">
                                Catat Transaksi
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- ======================== --}}
        {{-- MODAL DETAIL PENJUALAN (SHOW) --}}
        {{-- ======================== --}}
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
                        <h2 class="text-xl font-semibold text-text-main">
                            Detail Transaksi Penjualan
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5">
                            Informasi lengkap mengenai transaksi produk.
                        </p>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openDetail = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4" x-if="detailPenjualan">
                    
                    <div class="space-y-4">
                        
                        {{-- Ringkasan Transaksi --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                            <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                                <span class="text-sm font-medium text-text-muted">Tanggal Transaksi:</span>
                                <span class="font-semibold text-sm text-text-main" x-text="formatDate(detailPenjualan.tanggal_transaksi)"></span>
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
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-text-muted">Harga Satuan:</span>
                                <span class="text-sm font-medium text-text-main" x-text="formatRupiah(detailPenjualan.produk.harga)"></span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-text-muted">Jumlah Beli:</span>
                                <span class="font-bold text-sm text-primary-dark" x-text="detailPenjualan.jumlah + ' unit'"></span>
                            </div>
                            
                        </div>

                        {{-- Total Harga --}}
                        <div class="p-4 border border-success/50 rounded-xl bg-success-soft/30">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-text-main">Total Bayar:</span>
                                <span class="text-xl font-extrabold text-success" x-text="formatRupiah(detailPenjualan.total_harga)"></span>
                            </div>
                        </div>
                        
                        {{-- Keterangan --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                            <h3 class="text-sm font-medium text-text-muted">Keterangan:</h3>
                            <p class="text-sm text-text-main mt-1 italic" x-text="detailPenjualan.keterangan || '-'"></p>
                        </div>
                        
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-ui.button-secondary type="button" @click="openDetail = false">
                            Tutup
                        </x-ui.button-secondary>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- MODAL EDIT PENJUALAN (UPDATE) --}}
        {{-- ======================== --}}
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
                        <h2 class="text-xl font-semibold text-text-main">
                            Edit Transaksi Penjualan
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5" x-text="'ID Transaksi: ' + (editPenjualan ? editPenjualan.id : '')">
                            Ubah detail transaksi penjualan produk.
                        </p>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openEdit = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4" x-if="editPenjualan">
                    {{-- Form UPDATE --}}
                    <form :action="'{{ route('admin.penjualan_produk.index') }}/' + editForm.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT') 
                        
                        {{-- Hidden ID untuk dikirim saat validasi gagal --}}
                        <input type="hidden" name="id" :value="editForm.id">
                        
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

                        <div class="space-y-4">
                            <div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    {{-- PRODUK ID (Disabled agar tidak diubah) --}}
                                    <div>
                                        <x-ui.label for="produk_id_modal_edit">Produk Dijual</x-ui.label>
                                        <input 
                                            type="text" 
                                            :value="editPenjualan ? editPenjualan.produk.nama : ''" 
                                            disabled
                                            class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft"
                                        >
                                        {{-- Produk ID dikirim sebagai hidden field --}}
                                        <input type="hidden" name="produk_id" :value="editForm.produk_id"> 
                                        <p class="text-xs text-text-muted mt-1">Produk tidak dapat diubah.</p>
                                    </div>

                                    {{-- JUMLAH --}}
                                    <div>
                                        <x-ui.label for="jumlah_modal_edit">Jumlah Beli</x-ui.label>
                                        <input 
                                            type="number" 
                                            id="jumlah_modal_edit" 
                                            name="jumlah"
                                            x-model.number="editForm.jumlah" 
                                            min="1"
                                            :max="calculateMaxStockEdit()" 
                                            required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah') border-danger ring-danger-soft @enderror"
                                        >
                                        @error('jumlah')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        <p class="text-xs text-text-muted mt-1">Stok Maksimal: <span x-text="calculateMaxStockEdit()"></span></p>
                                    </div>
                                </div>
                                
                                {{-- Detail Perhitungan Harga Edit --}}
                                <div class="mt-4 p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                                    <h3 class="font-semibold text-text-main">Perhitungan Harga:</h3>
                                    <p class="text-sm text-text-muted">Harga Satuan: 
                                        <span class="font-medium text-text-main" x-text="formatRupiah(editForm.harga_satuan)"></span>
                                    </p>
                                    <p class="text-lg font-bold text-success">Total Bayar Baru: 
                                        <span x-text="formatRupiah(calculateEditTotal())"></span>
                                        <input type="hidden" name="total_harga" :value="calculateEditTotal()">
                                    </p>
                                </div>
                                
                            </div>

                            {{-- METODE PEMBAYARAN --}}
                            <div>
                                <x-ui.label for="metode_pembayaran_modal_edit">Metode Pembayaran</x-ui.label>
                                <select 
                                    id="metode_pembayaran_modal_edit" 
                                    name="metode_pembayaran" 
                                    x-model="editForm.metode_pembayaran"
                                    required
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('metode_pembayaran') border-danger ring-danger-soft @enderror"
                                >
                                    <option value="" disabled>-- Pilih Metode --</option>
                                    @foreach ($metodePembayaran as $metode)
                                        <option :value="'{{ $metode }}'">
                                            {{ $metode }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('metode_pembayaran')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>
                            
                            {{-- KETERANGAN --}}
                            <div>
                                <x-ui.label for="keterangan_modal_edit">Keterangan (Opsional)</x-ui.label>
                                <textarea 
                                    id="keterangan_modal_edit" 
                                    name="keterangan" 
                                    rows="3"
                                    x-model="editForm.keterangan"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('keterangan') border-danger ring-danger-soft @enderror"
                                ></textarea>
                                @error('keterangan')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-ui.button-secondary type="button" @click="openEdit = false" class="mr-2">
                                Batal
                            </x-ui.button-secondary>
                            {{-- MENGGANTI x-ui.button-warning dengan x-ui.button-primary --}}
                            <x-ui.button-primary type="submit">
                                Simpan Perubahan
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div> {{-- Penutup div x-data besar --}}

    {{-- SCRIPT KONFIRMASI HAPUS (Pembatalan) dan CUSTOM STYLE --}}
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
    
    <style>
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
    </style>
</x-layouts.admin>