@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Js; 
    
    // Asumsi data: $riwayat_stok (StokProduk::paginate), $produks (Produk::all())
    $pageTitle = 'Riwayat Stok Produk';
    
    // LOGIKA UNTUK MEMBUKA MODAL CREATE/EDIT JIKA ADA VALIDASI ERROR
    $openCreateOnLoad = ($errors->any() && (old('_method') !== 'PUT')) ? 'true' : 'false';
    $openEditOnLoad = ($errors->any() && old('_method') === 'PUT' && old('id')) ? 'true' : 'false';
    $oldEditId = old('_method') === 'PUT' ? (old('id') ?? 'null') : 'null';
    
    // Data untuk AlpineJS
    $produks = $produks ?? collect();
    $riwayat_stok = $riwayat_stok ?? collect(); // Pastikan variabel ini ada
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Mencatat dan meninjau riwayat penambahan dan pengurangan stok produk."
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
            
            detailStok: null, 
            editStok: null, 
            
            // Data Create
            selectedProductId: {{ old('produk_id') ?? 'null' }}, 
            produkData: {{ Js::from($produks) }},
            selectedProductObject: null,
            
            // Data Edit (untuk form)
            editForm: {
                id: {{ $oldEditId }}, 
                produk_id: {{ old('produk_id') ?? 'null' }},
                jumlah: {{ old('jumlah') ?? 'null' }},
                tanggal: '{{ old('tanggal') }}',
                keterangan: '{{ old('keterangan') }}',
                stok_saat_ini: null, 
                jumlah_awal: null, 
            },
            
            updateProductObject() {
                this.selectedProductObject = this.produkData.find(p => p.id == this.selectedProductId);
            },
            
            // Fungsi untuk membuka modal detail
            showDetail(stok) {
                this.detailStok = stok;
                this.openDetail = true;
            },
            
            // Fungsi untuk membuka modal Edit
            showEdit(stok) {
                const produk = this.produkData.find(p => p.id === stok.produk.id) || null; 
                
                if (!produk) {
                    alert('Produk tidak ditemukan. Tidak dapat mengedit.');
                    return;
                }

                this.editStok = stok;

                // Isi data ke form untuk modal edit
                this.editForm.id = stok.id;
                this.editForm.produk_id = stok.produk.id;
                this.editForm.jumlah = stok.jumlah;
                this.editForm.tanggal = stok.tanggal_input_format; // YYYY-MM-DD
                this.editForm.keterangan = stok.keterangan;
                
                // Data untuk perhitungan (Stok produk saat ini di DB)
                this.editForm.stok_saat_ini = produk.stok;
                this.editForm.jumlah_awal = stok.jumlah;
                
                this.openEdit = true;
            },
            
            // Helper untuk format tanggal untuk tampilan (DIGUNAKAN DI MODAL DETAIL)
            formatDateDisplay(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                
                // PERBAIKAN: Hapus opsi jam dan menit
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }
        }" 
        x-init="
            updateProductObject();
        "
    > 
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar riwayat penambahan atau penyesuaian stok produk."
        >
        </x-ui.section-header>

        {{-- garis dibawah judul --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- TOMBOL TAMBAH STOK --}}
        <div class="mt-6 mb-4 flex justify-end">
            <x-ui.button-primary type="button" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Stok
            </x-ui.button-primary>
        </div>
            
        {{-- CARD TABEL RIWAYAT STOK --}}
        <x-ui.card
            title="Riwayat Stok Masuk"
            subtitle="Pencatatan penambahan stok terbaru."
            class="border-brand-borderSoft"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[900px] text-sm"> 
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- KOLOM HEADERS --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%] min-w-[150px]">Tanggal Input</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[30%] min-w-[250px]">Produk</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[80px]">Jumlah</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%] min-w-[250px]">Keterangan</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[80px]">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($riwayat_stok as $stok)
                            @php
                                // PERBAIKAN: Hapus format jam (HH:mm) untuk tampilan tabel
                                $tanggalDisplay = Carbon::parse($stok->tanggal)->locale('id')->isoFormat('D MMM YYYY');
                                
                                // FIX: Menggunakan format date-only (Y-m-d) agar kompatibel dengan input type="date"
                                $tanggalInputFormat = Carbon::parse($stok->tanggal)->format('Y-m-d'); 
                                
                                $namaProduk = $stok->produk->nama ?? 'Produk Dihapus';
                                
                                $stok_data_js = [
                                    'id' => $stok->id,
                                    'produk' => $stok->produk ? ['id' => $stok->produk->id, 'nama' => $stok->produk->nama, 'stok' => $stok->produk->stok] : ['id' => 0, 'nama' => 'Produk Dihapus', 'stok' => 0],
                                    'jumlah' => $stok->jumlah,
                                    'tanggal' => $stok->tanggal, 
                                    'tanggal_input_format' => $tanggalInputFormat, 
                                    'keterangan' => $stok->keterangan,
                                ];
                            @endphp
                            
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                
                                {{-- TANGGAL INPUT --}}
                                <td class="p-3 align-middle w-[20%] min-w-[150px]"> 
                                    <div class="text-xs text-text-muted">{{ $tanggalDisplay }}</div>
                                </td>
                                
                                {{-- PRODUK --}}
                                <td class="p-3 align-middle w-[30%] min-w-[250px]">
                                    <div class="text-sm font-semibold text-text-main">{{ $namaProduk }}</div>
                                </td>

                                {{-- JUMLAH --}}
                                <td class="p-3 align-middle text-center w-[10%] min-w-[80px]">
                                    <div class="text-sm font-bold text-success">{{ $stok->jumlah }}</div>
                                </td>
                                
                                {{-- KETERANGAN --}}
                                <td class="p-3 align-middle w-[20%] min-w-[250px]"> 
                                    <div class="text-xs text-text-muted max-w-full">
                                        {{ $stok->keterangan ? Str::limit($stok->keterangan, 50) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle w-[10%] min-w-[80px]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        
                                        {{-- DETAIL ICON --}}
                                        <button 
                                            type="button" 
                                            @click="showDetail({{ Js::from($stok_data_js) }})" 
                                            title="Lihat Detail"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="eye" class="w-6 h-6"></i>
                                        </button>

                                        {{-- EDIT ICON --}}
                                        <button 
                                            type="button" 
                                            @click="showEdit({{ Js::from($stok_data_js) }})" 
                                            title="Edit Penambahan Stok"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="square-pen" class="w-6 h-6"></i>
                                        </button>
                                        
                                        {{-- HAPUS / BATALKAN STOK --}}
                                        <form
                                            id="delete-stok-{{ $stok->id }}"
                                            action="{{ route('admin.stok_produk.destroy', $stok) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Batalkan Penambahan Stok"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                onclick="confirmDeleteStok({{ $stok->id }}, '{{ $namaProduk }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-6 h-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-text-muted italic"> 
                                    Belum ada riwayat penambahan stok produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $riwayat_stok->links() }}
            </div>
        </x-ui.card>

        {{-- ======================== --}}
        {{-- MODAL TAMBAH STOK BARU (CREATE) --}}
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
                        <h2 class="text-xl font-semibold text-text-main">Tambah Stok Produk</h2>
                        <p class="text-sm text-text-muted mt-0.5">Masukkan detail penambahan stok produk.</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openCreate = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar"> 
                    <form action="{{ route('admin.stok_produk.store') }}" method="POST" class="space-y-6">
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
                                                {{ $produk->nama }} (Stok Saat Ini: {{ $produk->stok }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('produk_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                </div>

                                {{-- JUMLAH STOK --}}
                                <div>
                                    <x-ui.label for="jumlah_modal">Jumlah Stok Masuk</x-ui.label>
                                    <input 
                                        type="number" 
                                        id="jumlah_modal" 
                                        name="jumlah"
                                        value="{{ old('jumlah') ?? 1 }}" 
                                        min="1"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('jumlah')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                    <p x-show="selectedProductObject" class="text-xs text-text-muted mt-1">Stok produk ini saat ini: <span x-text="selectedProductObject.stok"></span></p>
                                </div>
                            </div>
                            
                            {{-- TANGGAL MASUK --}}
                            <div>
                                <x-ui.label for="tanggal_modal">Tanggal Input Stok</x-ui.label>
                                <input 
                                    type="date" 
                                    id="tanggal_modal" 
                                    name="tanggal"
                                    value="{{ old('tanggal') ?? now()->format('Y-m-d') }}" 
                                    required
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('tanggal') border-danger ring-danger-soft @enderror"
                                >
                                @error('tanggal')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- KETERANGAN --}}
                            <div>
                                <x-ui.label for="keterangan_modal">Keterangan (Sumber/Catatan)</x-ui.label>
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
                                Catat Stok
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- MODAL DETAIL STOK (SHOW) --}}
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
                        <h2 class="text-xl font-semibold text-text-main">Detail Riwayat Stok</h2>
                        <p class="text-sm text-text-muted mt-0.5">Informasi lengkap penambahan stok.</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openDetail = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar" x-if="detailStok">
                    
                    <div class="space-y-4">
                        
                        {{-- Ringkasan Stok --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50 space-y-2">
                            <div class="flex justify-between items-center border-b border-brand-borderSoft pb-2">
                                <span class="text-sm font-medium text-text-muted">Tanggal Input:</span>
                                {{-- PERBAIKAN: Gunakan helper formatDateDisplay yang sudah dimodifikasi untuk hanya tampilkan tanggal --}}
                                <span class="font-semibold text-sm text-text-main" x-text="formatDateDisplay(detailStok.tanggal)"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-text-muted">Produk:</span>
                                <span class="font-semibold text-sm text-primary-dark" x-text="detailStok.produk.nama"></span>
                            </div>
                        </div>

                        {{-- Detail Jumlah --}}
                        <div class="p-4 border border-success/50 rounded-xl bg-success-soft/30">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-text-main">Jumlah Stok Masuk:</span>
                                <span class="text-xl font-extrabold text-success" x-text="detailStok.jumlah + ' unit'"></span>
                            </div>
                        </div>
                        
                        {{-- Stok Saat Ini di Produk (Optional info) --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                             <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-text-muted">Stok Produk Saat Ini:</span>
                                <span class="font-semibold text-sm text-text-main" x-text="detailStok.produk.stok + ' unit'"></span>
                            </div>
                        </div>
                        
                        {{-- Keterangan --}}
                        <div class="p-4 border border-brand-borderSoft rounded-xl bg-brand-surface-50">
                            <h3 class="text-sm font-medium text-text-muted">Keterangan:</h3>
                            <p class="text-sm text-text-main mt-1 italic" x-text="detailStok.keterangan || '-'"></p>
                        </div>
                        
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-ui.button-secondary type="button" @click="openDetail = false">Tutup</x-ui.button-secondary>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================== --}}
        {{-- MODAL EDIT STOK (UPDATE) --}}
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
                        <h2 class="text-xl font-semibold text-text-main">Edit Penambahan Stok</h2>
                        <p class="text-sm text-text-muted mt-0.5" x-text="'ID Riwayat Stok: ' + (editStok ? editStok.id : '')">
                            Ubah detail entri stok produk.
                        </p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openEdit = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar" x-if="editStok">
                    <form :action="'{{ route('admin.stok_produk.index') }}/' + editForm.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT') 
                        
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- PRODUK ID (DISABLED) --}}
                                <div>
                                    <x-ui.label for="produk_id_modal_edit">Produk</x-ui.label>
                                    <input 
                                        type="text" 
                                        :value="editStok ? editStok.produk.nama : ''" 
                                        disabled
                                        class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft"
                                    >
                                    <input type="hidden" name="produk_id" :value="editForm.produk_id"> 
                                    <p class="text-xs text-text-muted mt-1">Produk tidak dapat diubah.</p>
                                </div>

                                {{-- JUMLAH STOK --}}
                                <div>
                                    <x-ui.label for="jumlah_modal_edit">Jumlah Stok Masuk</x-ui.label>
                                    <input 
                                        type="number" 
                                        id="jumlah_modal_edit" 
                                        name="jumlah"
                                        x-model.number="editForm.jumlah" 
                                        min="1"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('jumlah') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('jumlah')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                    <p class="text-xs text-text-muted mt-1">
                                        Stok akan disesuaikan. Jumlah awal: <span x-text="editForm.jumlah_awal"></span>
                                    </p>
                                </div>
                            </div>
                            
                            {{-- TANGGAL MASUK --}}
                            <div>
                                <x-ui.label for="tanggal_modal_edit">Tanggal Input Stok</x-ui.label>
                                <input 
                                    type="date" {{-- Benar: type="date" untuk menghilangkan waktu --}}
                                    id="tanggal_modal_edit" 
                                    name="tanggal"
                                    x-model="editForm.tanggal"
                                    required
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('tanggal') border-danger ring-danger-soft @enderror"
                                >
                                @error('tanggal')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- KETERANGAN --}}
                            <div>
                                <x-ui.label for="keterangan_modal_edit">Keterangan (Sumber/Catatan)</x-ui.label>
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
                            <x-ui.button-primary type="submit">
                                Simpan Perubahan
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div> {{-- Penutup div x-data besar --}}

    {{-- SCRIPT KONFIRMASI HAPUS (Pembatalan Stok) --}}
    <script>
        function confirmDeleteStok(stokId, productName) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin membatalkan penambahan stok untuk produk ${productName}? Stok produk akan dikurangi.`)) {
                    document.getElementById('delete-stok-' + stokId).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Batalkan Penambahan Stok?',
                html: `Anda yakin ingin membatalkan entri stok untuk produk <strong>${productName}</strong>? <br> Jumlah stok di produk akan **dikurangi** sesuai entri ini.`,
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
                    document.getElementById('delete-stok-' + stokId).submit();
                }
            });
        }
    </script>
    
    {{-- CUSTOM SCROLLBAR (TETAP) --}}
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