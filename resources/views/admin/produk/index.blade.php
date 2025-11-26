{{-- resources/views/admin/produk/index.blade.php --}}

@php
    use Illuminate\Support\Str; 
    use Illuminate\Support\Facades\Storage; 
    use Illuminate\Support\Js; 

    $pageTitle = $pageTitle ?? 'Manajemen Produk';
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];
    
    // Pastikan openCreateOnLoad juga mereset createImageUrl jika ada validasi gagal
    $openCreateOnLoad = ($errors->any() && old('_method') !== 'PUT') ? 'true' : 'false';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Kelola data produk yang tersedia di BETA GYM berdasarkan skema database."
>
    {{-- TAMPILKAN PESAN FLASH (SUCCESS/ERROR) --}}
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
    
    {{-- STATE UTAMA UNTUK MODAL CREATE --}}
    <div x-data="{ 
        openCreate: {{ $openCreateOnLoad }}, 
        createImageUrl: null,
        resetCreateForm() {
            this.$refs.createForm.reset();
            this.createImageUrl = null; 
        }
    }"> 

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar produk aktif dan pengelolaan datanya."
        >
        </x-ui.section-header>

        {{-- garis dibawah judul --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        <div class="mt-6 mb-4 flex justify-end">
            <x-ui.button-primary type="button" @click="resetCreateForm(); openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Produk Baru
            </x-ui.button-primary>
        </div>
        

        {{-- CARD TABEL PRODUK --}}
        <x-ui.card
            title="Daftar Produk"
            subtitle="Semua produk yang terdaftar dalam sistem."
            class="border-brand-borderSoft"
        >
            <div class="overflow-x-auto custom-scrollbar">
                {{-- MINIMUM WIDTH DIKURANGI AGAR LAYOUT TIDAK TERLALU LEBAR DI LAYAR KECIL --}}
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]"> 
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            {{-- FOTO (8%) --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[8%] min-w-[70px]">Foto</th>
                            
                            {{-- NAMA PRODUK (22%) --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[22%] min-w-[180px]">Nama Produk</th>
                            
                            {{-- KATEGORI (13%) --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[13%] min-w-[100px]">Kategori</th>
                            
                            {{-- HARGA (13%) --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[13%]">Harga</th>
                            
                            {{-- STOK (7%) --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[7%]">Stok</th>
                            
                            {{-- DESKRIPSI (25%) --}}
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[25%] min-w-[250px]">Deskripsi</th>
                            
                            {{-- AKSI (12%) --}}
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[100px]">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($produks as $produk)
                            @php
                                $currentFotoPath = $produk->foto ?? null;
                                $currentFotoUrl = $currentFotoPath ? Storage::url($currentFotoPath) : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';
                                
                                // Jika validasi gagal, gunakan old URL, jika tidak, gunakan current URL
                                $initialImageUrl = ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT') 
                                    ? (old('foto_preview') ?? $currentFotoUrl) 
                                    : $currentFotoUrl;

                                $openEditOnLoad = ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT') ? 'true' : 'false';
                            @endphp
                            
                            {{-- State AlpineJS untuk modal edit dan preview foto --}}
                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ 
                                    openEdit: {{ $openEditOnLoad }}, 
                                    imageUrl: '{{ $initialImageUrl }}',
                                    originalImageUrl: '{{ $currentFotoUrl }}',
                                    // Data asli untuk reset
                                    originalData: {
                                        nama: '{{ $produk->nama }}',
                                        kategori: '{{ $produk->kategori }}',
                                        // HARGA SEBAGAI NOMINAL MURNI UNTUK EDIT
                                        harga: '{{ (int) $produk->harga }}', 
                                        deskripsi: '{{ $produk->deskripsi ?? '' }}'
                                    },
                                    resetEditForm() {
                                        // Reset semua input ke nilai asli
                                        document.getElementById('nama_{{ $produk->id }}').value = this.originalData.nama;
                                        document.getElementById('kategori_{{ $produk->id }}').value = this.originalData.kategori;
                                        // Reset harga ke nominal murni
                                        document.getElementById('harga_{{ $produk->id }}').value = this.originalData.harga; 
                                        document.getElementById('deskripsi_{{ $produk->id }}').value = this.originalData.deskripsi;
                                        
                                        // Reset file input
                                        const fileInput = document.getElementById('foto_{{ $produk->id }}');
                                        if (fileInput) {
                                            fileInput.value = '';
                                        }
                                        
                                        // Reset preview foto ke foto asli
                                        this.imageUrl = this.originalImageUrl;
                                    }
                                }"
                            >
                                {{-- FOTO (8%) --}}
                                <td class="p-3 align-middle w-[8%] min-w-[70px]">
                                    <img
                                        src="{{ $currentFotoUrl }}"
                                        alt="Foto {{ $produk->nama }}"
                                        class="w-12 h-12 rounded object-cover border border-brand-borderSoft shadow-sm"
                                        onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';"
                                    >
                                </td>

                                {{-- NAMA (22%) --}}
                                <td class="p-3 align-middle w-[22%] min-w-[180px]">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ $produk->nama }}
                                    </div>
                                </td>

                                {{-- KATEGORI (13%) --}}
                                <td class="p-3 align-middle w-[13%] min-w-[100px]">
                                    <div class="text-xs font-medium text-primary-dark">
                                        {{ ucwords($produk->kategori) }}
                                    </div>
                                </td>

                                {{-- HARGA (13%) --}}
                                <td class="p-3 align-middle w-[13%]">
                                    <div class="text-sm text-text-main">
                                        {{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- STOK (7%) --}}
                                <td class="p-3 align-middle text-center w-[7%]">
                                    <div class="text-sm text-text-main font-bold">
                                        {{ $produk->stok }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI (25%) --}}
                                <td class="p-3 align-middle w-[25%] min-w-[250px]">
                                    <div class="text-xs text-text-muted max-w-full">
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI (12%) - Diubah untuk efek hover dan posisi teks --}}
                                <td class="p-3 align-middle w-[12%] min-w-[100px]">
                                    {{-- Menggunakan h-full untuk mengisi tinggi baris tabel --}}
                                    <div class="flex items-center justify-center gap-1.5 h-full">
                                        
                                        {{-- WRAPPER EDIT ICON (Posisi Teks Disesuaikan) --}}
                                        <div x-data="{ editing: false }" class="relative flex flex-col items-center justify-start h-full">
                                            <button 
                                                type="button"
                                                @mouseenter="editing = true"
                                                @mouseleave="editing = false"
                                                @click.stop="resetEditForm(); openEdit = true" 
                                                title="Edit Produk"
                                                class="p-2 rounded-full text-primary-dark hover:bg-primary-soft/50 transition-colors duration-150 z-10"
                                            >
                                                <i data-lucide="square-pen" class="w-6 h-6"></i>
                                            </button>
                                            {{-- Teks Edit: Posisi bottom diubah menjadi lebih ke atas, transisi opacity & transform untuk animasi halus --}}
                                            <span x-show="editing" 
                                                  x-cloak 
                                                  x-transition:enter="transition ease-out duration-300"
                                                  x-transition:enter-start="opacity-0 translate-y-2"
                                                  x-transition:enter-end="opacity-100 translate-y-0"
                                                  x-transition:leave="transition ease-in duration-200"
                                                  x-transition:leave-start="opacity-100 translate-y-0"
                                                  x-transition:leave-end="opacity-0 translate-y-2"
                                                  class="absolute top-[33px] text-[10px] font-medium text-primary-dark whitespace-nowrap z-0">
                                                Edit
                                            </span>
                                        </div>

                                        {{-- WRAPPER HAPUS ICON (Posisi Teks Disesuaikan) --}}
                                        <form
                                            id="delete-product-{{ $produk->id }}"
                                            action="{{ route('admin.produk.destroy', $produk) }}"
                                            method="POST"
                                            class="inline-block"
                                            @click.stop
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <div x-data="{ deleting: false }" class="relative flex flex-col items-center justify-start h-full">
                                                <button
                                                    type="button"
                                                    @mouseenter="deleting = true"
                                                    @mouseleave="deleting = false"
                                                    title="Hapus Produk"
                                                    class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150 z-10"
                                                    onclick="confirmDeleteProduct({{ $produk->id }}, '{{ $produk->nama }}')"
                                                >
                                                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                                                </button>
                                                {{-- Teks Hapus: Posisi bottom diubah menjadi lebih ke atas, transisi opacity & transform untuk animasi halus --}}
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

                                    {{-- ======================= --}}
                                    {{-- MODAL EDIT DATA PRODUK --}}
                                    {{-- ======================= --}}
                                    <div
                                        x-show="openEdit"
                                        x-cloak
                                        x-transition
                                        @click.self="resetEditForm(); openEdit = false"
                                        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
                                    >
                                        <div
                                            @click.stop
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                                        >
                                            {{-- HEADER MODAL --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                                                <div>
                                                    <h2 class="text-xl font-semibold text-text-main">Edit Produk</h2>
                                                    <p class="text-sm text-text-muted mt-0.5">{{ $produk->nama }}</p>
                                                </div>
                                                <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="resetEditForm(); openEdit = false">
                                                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                                                </button>
                                            </div>

                                            {{-- ISI MODAL EDIT --}}
                                            <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.produk.update', $produk) }}"
                                                    enctype="multipart/form-data"
                                                    class="space-y-5"
                                                >
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                                    <input type="hidden" name="foto_preview" :value="imageUrl">
                                                    
                                                    {{-- TAMPILAN ERROR VALIDASI UPDATE --}}
                                                    @if ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT')
                                                         <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4">
                                                             <p class="text-sm font-semibold">Ada kesalahan input saat mengedit:</p>
                                                             <ul class="list-disc list-inside text-xs mt-1">
                                                                     @foreach ($errors->all() as $error)
                                                                         <li>{{ $error }}</li>
                                                                     @endforeach
                                                             </ul>
                                                         </div>
                                                    @endif

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        {{-- PANEL KIRI: FOTO + INFO SINGKAT --}}
                                                        <div class="md:col-span-1">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                                                <div class="w-full aspect-square border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                                                    <img :src="imageUrl" alt="Preview Foto Produk" class="object-cover w-full h-full" :style="{ display: imageUrl.includes('No+Foto') ? 'none' : 'block' }">
                                                                    <span x-show="imageUrl.includes('No+Foto')" class="text-xs text-text-muted text-center p-2">Tidak ada foto</span>
                                                                </div>
                                                                <p class="text-[11px] text-text-muted text-center">Foto saat ini. Upload foto baru di kolom form kanan untuk mengganti.</p>
                                                            </div>
                                                        </div>

                                                        {{-- PANEL KANAN: FORM --}}
                                                        <div class="md:col-span-2">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    {{-- NAMA --}}
                                                                    <div>
                                                                        <x-ui.label for="nama_{{ $produk->id }}">Nama Produk</x-ui.label>
                                                                        <input type="text" id="nama_{{ $produk->id }}" name="nama"
                                                                            value="{{ old('nama', $produk->nama) }}" required
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror">
                                                                        @error('nama')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    {{-- KATEGORI --}}
                                                                    <div>
                                                                        <x-ui.label for="kategori_{{ $produk->id }}">Kategori</x-ui.label>
                                                                        <select id="kategori_{{ $produk->id }}" name="kategori" required
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('kategori') border-danger ring-danger-soft @enderror">
                                                                            @foreach ($kategoriOptions as $option)
                                                                                <option value="{{ $option }}" {{ old('kategori', $produk->kategori) == $option ? 'selected' : '' }}>
                                                                                    {{ ucwords($option) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('kategori')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                    </div>
                                                                </div>

                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    {{-- HARGA --}}
                                                                    <div>
                                                                        <x-ui.label for="harga_{{ $produk->id }}">Harga (Rp)</x-ui.label>
                                                                        <input type="number" id="harga_{{ $produk->id }}" name="harga"
                                                                            value="{{ old('harga', (int) $produk->harga) }}" required
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('harga') border-danger ring-danger-soft @enderror">
                                                                        @error('harga')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                    </div>
                                                                    {{-- STOK (DISABLED) --}}
                                                                    <div>
                                                                        <x-ui.label for="stok_{{ $produk->id }}">Stok (Otomatis)</x-ui.label>
                                                                        <input type="text" 
                                                                            value="{{ $produk->stok }}" 
                                                                            disabled 
                                                                            class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft">
                                                                        <p class="text-xs text-text-muted mt-1">Stok dikelola melalui Riwayat Stok Produk.</p>
                                                                    </div>
                                                                </div>

                                                                {{-- DESKRIPSI --}}
                                                                <div>
                                                                    <x-ui.label for="deskripsi_{{ $produk->id }}">Deskripsi (opsional)</x-ui.label>
                                                                    <textarea id="deskripsi_{{ $produk->id }}" name="deskripsi" rows="1"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft 
                                                                        focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror"
                                                                    >{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                                                                    @error('deskripsi')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                </div>

                                                                {{-- FOTO --}}
                                                                <div class="space-y-2">
                                                                    <x-ui.label for="foto_{{ $produk->id }}">Foto Produk (opsional)</x-ui.label>
                                                                    <input type="file" id="foto_{{ $produk->id }}" name="foto" accept="image/*"
                                                                        class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700 @error('foto') border-danger ring-danger-soft @enderror"
                                                                        @change="
                                                                            const file = $event.target.files[0];
                                                                            if (file) {
                                                                                const reader = new FileReader();
                                                                                reader.onload = (e) => { imageUrl = e.target.result; };
                                                                                reader.readAsDataURL(file);
                                                                            } else {
                                                                                imageUrl = originalImageUrl;
                                                                            }
                                                                        ">
                                                                    @error('foto')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                    <p class="text-[11px] text-text-muted mt-1">Maksimal 2MB. Format yang didukung: JPG, PNG, dll.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-3">
                                                        <x-ui.button-secondary type="button" @click="resetEditForm(); openEdit = false">Batal</x-ui.button-secondary>
                                                        <x-ui.button-primary type="submit">Simpan Perubahan</x-ui.button-primary>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic"> 
                                    Belum ada data produk yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $produks->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL TAMBAH PRODUK (CREATE) --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="if (!{{ $openCreateOnLoad }}) openCreate = false" 
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Tambah Produk</h2>
                        <p class="text-sm text-text-muted mt-0.5">Data Produk Baru</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openCreate = false; resetCreateForm()">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <form
                        x-ref="createForm" 
                        method="POST"
                        action="{{ route('admin.produk.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        {{-- MENAMPILKAN ERROR VALIDASI UNTUK CREATE --}}
                        @php $openCreateFormErrors = $errors->any() && old('_method') !== 'PUT'; @endphp
                        @if ($openCreateFormErrors)
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
                            {{-- PANEL KIRI: PREVIEW FOTO BARU --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                    <div class="w-full aspect-square border-2 border-dashed border-brand-borderSoft rounded-lg overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                        <img x-show="createImageUrl" :src="createImageUrl" alt="Preview Foto Produk Baru" class="object-cover w-full h-full">
                                        <span x-show="!createImageUrl" class="text-xs text-text-muted text-center p-2">Preview Foto Produk</span>
                                    </div>
                                    <p class="text-[11px] text-text-muted text-center">Foto yang akan diupload.</p>
                                </div>
                            </div>

                            {{-- PANEL KANAN: FORM --}}
                            <div class="md:col-span-2">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- NAMA --}}
                                        <div>
                                            <x-ui.label for="nama_create">Nama Produk</x-ui.label>
                                            <input type="text" id="nama_create" name="nama"
                                                value="{{ old('nama') }}" required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror">
                                            @error('nama')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        </div>

                                        {{-- KATEGORI --}}
                                        <div>
                                            <x-ui.label for="kategori_create">Kategori</x-ui.label>
                                            <select id="kategori_create" name="kategori" required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('kategori') border-danger ring-danger-soft @enderror">
                                                <option value="" disabled {{ old('kategori') == null ? 'selected' : '' }}>Pilih Kategori</option>
                                                @foreach ($kategoriOptions as $option)
                                                    <option value="{{ $option }}" {{ old('kategori') == $option ? 'selected' : '' }}>
                                                        {{ ucwords($option) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kategori')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- HARGA --}}
                                        <div>
                                            <x-ui.label for="harga_create">Harga (Rp)</x-ui.label>
                                            <input type="number" id="harga_create" name="harga"
                                                value="{{ old('harga') ? (int) old('harga') : '' }}" required
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('harga') border-danger ring-danger-soft @enderror">
                                            @error('harga')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        </div>

                                        {{-- STOK (DISABLED) --}}
                                        <div>
                                            <x-ui.label for="stok_create">Stok Awal</x-ui.label>
                                            <input type="text" value="0" disabled
                                                class="w-full rounded-xl border bg-brand-surface-50 text-sm text-text-muted px-3 py-2 border-brand-borderSoft">
                                            <input type="hidden" name="stok" value="0">
                                            <p class="text-xs text-text-muted mt-1">Stok diinisialisasi 0. Tambah stok awal melalui Riwayat Stok.</p>
                                        </div>
                                    </div>

                                    {{-- DESKRIPSI --}}
                                    <div>
                                        <x-ui.label for="deskripsi">Deskripsi (opsional)</x-ui.label>
                                        <textarea id="deskripsi" name="deskripsi" rows="1"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft 
                                            focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror"
                                        >{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- FOTO --}}
                                    <div class="space-y-2">
                                        <x-ui.label for="foto_create">Foto Produk (opsional)</x-ui.label>
                                        <input type="file" id="foto_create" name="foto" accept="image/*"
                                            class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700 @error('foto') border-danger ring-danger-soft @enderror"
                                            @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { createImageUrl = e.target.result; };
                                                    reader.readAsDataURL(file);
                                                } else {
                                                    createImageUrl = null;
                                                }
                                            ">
                                        @error('foto')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                        <p class="text-[11px] text-text-muted mt-1">Maksimal 2MB. Format yang didukung: JPG, PNG, dll.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <x-ui.button-secondary type="button" @click="openCreate = false; resetCreateForm()">Batal</x-ui.button-secondary>
                            <x-ui.button-primary type="submit">Simpan Produk</x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SCRIPT KONFIRMASI HAPUS DAN STYLE TETAP --}}
        <script>
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
            /* Menambahkan z-index pada script agar teks hover tidak tertutup */
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
        </style>
    </div>
</x-layouts.admin>