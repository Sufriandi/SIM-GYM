{{-- resources/views/admin/produk/index.blade.php --}}

@php
    use Illuminate\Support\Str; 
    use Illuminate\Support\Facades\Storage; 
    use Illuminate\Support\Js; 

    $pageTitle = $pageTitle ?? 'Manajemen Produk';
    $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];
    
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
    <div x-data="{ openCreate: {{ $openCreateOnLoad }} }"> 

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar produk aktif dan pengelolaan datanya."
        >
        </x-ui.section-header>

        {{-- garis dibawah judul --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        <div class="mt-6 mb-4 flex justify-end">
            <x-ui.button-primary type="button" @click="openCreate = true">
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
                <table class="w-full border-collapse min-w-[900px] text-sm"> 
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%] min-w-[50px]">Foto</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%] min-w-[150px]">Nama Produk</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[100px]">Kategori</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%] min-w-[100px]">Harga</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[80px]">Stok</th>
                            <th class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[33%] min-w-[300px]">Deskripsi</th>
                            <th class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%] min-w-[120px]">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($produks as $produk)
                            @php
                                $currentFotoPath = $produk->foto ?? null;
                                $currentFotoUrl = $currentFotoPath ? Storage::url($currentFotoPath) : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';
                                
                                $openEditOnLoad = ($errors->any() && old('produk_id') == $produk->id && old('_method') === 'PUT') ? 'true' : 'false';
                            @endphp
                            
                            {{-- State AlpineJS untuk modal edit dan preview foto --}}
                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ openEdit: {{ $openEditOnLoad }}, imageUrl: '{{ $currentFotoUrl }}' }"
                            >
                                {{-- FOTO --}}
                                <td class="p-3 align-middle w-[5%] min-w-[80px]">
                                    <img
                                        src="{{ $currentFotoUrl }}"
                                        alt="Foto {{ $produk->nama }}"
                                        class="w-12 h-12 rounded object-cover border border-brand-borderSoft shadow-sm"
                                        onerror="this.onerror=null; this.src='https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';"
                                    >
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle w-[18%] min-w-[150px]">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ $produk->nama }}
                                    </div>
                                </td>

                                {{-- KATEGORI --}}
                                <td class="p-3 align-middle w-[12%] min-w-[100px]">
                                    <div class="text-xs font-medium text-primary-dark">
                                        {{ ucwords($produk->kategori) }}
                                    </div>
                                </td>

                                {{-- HARGA --}}
                                <td class="p-3 align-middle w-[12%] min-w-[100px]">
                                    <div class="text-sm text-text-main">
                                        {{ 'Rp ' . number_format($produk->harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- STOK (Hanya Display) --}}
                                <td class="p-3 align-middle text-center w-[10%] min-w-[80px]">
                                    <div class="text-sm text-text-main font-bold">
                                        {{ $produk->stok }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle w-[33%] min-w-[300px]">
                                    <div class="text-xs text-text-muted max-w-full">
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle w-[10%] min-w-[120px]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        
                                        {{-- EDIT ICON --}}
                                        <button 
                                            type="button"
                                            @click="openEdit = true"
                                            title="Edit Produk"
                                            class="p-2 rounded-full text-primary-dark hover:bg-primary-soft/50 transition-colors duration-150"
                                        >
                                            <i data-lucide="square-pen" class="w-6 h-6"></i>
                                        </button>

                                        {{-- HAPUS ICON --}}
                                        <form
                                            id="delete-product-{{ $produk->id }}"
                                            action="{{ route('admin.produk.destroy', $produk) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Hapus Produk"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/50 transition-colors duration-150"
                                                onclick="confirmDeleteProduct({{ $produk->id }}, '{{ $produk->nama }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-6 h-6"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- ======================= --}}
                                    {{-- MODAL EDIT DATA PRODUK --}}
                                    {{-- ======================= --}}
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
                                            {{-- HEADER MODAL --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                                                <div>
                                                    <h2 class="text-xl font-semibold text-text-main">Edit Produk</h2>
                                                    <p class="text-sm text-text-muted mt-0.5">{{ $produk->nama }}</p>
                                                </div>
                                                <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openEdit = false">
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
                                                    {{-- Input hidden untuk identifikasi produk pada saat validasi gagal --}}
                                                    <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                                    
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
                                                        {{-- PANEL KIRI: FOTO + INFO SINGKAT (TETAP) --}}
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
                                                                            value="{{ old('harga', $produk->harga) }}" required
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
                                                                    <x-ui.label for="deskripsi_{{ $produk->id }}">Deskripsi</x-ui.label>
                                                                    <textarea id="deskripsi_{{ $produk->id }}" name="deskripsi" rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror"
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
                                                                                imageUrl = '{{ $currentFotoUrl }}';
                                                                            }
                                                                        ">
                                                                    @error('foto')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                                                                    <p class="text-[11px] text-text-muted mt-1">Maksimal 2MB. Format yang didukung: JPG, PNG, dll.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-3">
                                                        <x-ui.button-secondary type="button" @click="openEdit = false">Batal</x-ui.button-secondary>
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
                @click.away="openCreate = false"
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                x-data="{ createImageUrl: null }"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">Tambah Produk</h2>
                        <p class="text-sm text-text-muted mt-0.5">Data Produk Baru</p>
                    </div>
                    <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition" @click="openCreate = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <form
                        method="POST"
                        action="{{ route('admin.produk.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        {{-- MENAMPILKAN ERROR VALIDASI UNTUK CREATE --}}
                        @if ($errors->any() && old('_method') !== 'PUT')
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
                                                value="{{ old('harga') }}" required
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
                                        <x-ui.label for="deskripsi_create">Deskripsi</x-ui.label>
                                        <textarea id="deskripsi_create" name="deskripsi" rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror"
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
                            <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
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