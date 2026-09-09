{{-- resources/views/admin/produk/modals/edit.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;

    $currentFotoPath = $produk->foto ?? null;
    $currentFotoUrl = $currentFotoPath
        ? Storage::url($currentFotoPath)
        : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';

    $openEditOnLoad = $errors->any() && (int) old('produk_id') === (int) $produk->id && old('_method') === 'PUT';

    $initialImageUrl = $openEditOnLoad ? old('foto_preview') ?? $currentFotoUrl : $currentFotoUrl;

    $originalData = [
        'nama' => $produk->nama,
        'kategori' => $produk->kategori,
        'harga' => (int) $produk->harga,
        'deskripsi' => $produk->deskripsi ?? '',
    ];
@endphp

<div x-data="{
    imageUrl: @js($initialImageUrl),
    originalImageUrl: @js($currentFotoUrl),
    originalData: @js($originalData),

    resetEditForm() {
        // jangan reset kalau sedang open karena error (biarkan old() tampil)
        const isErrorOpen = {{ $openEditOnLoad ? 'true' : 'false' }};
        if (isErrorOpen) return;

        document.getElementById('nama_{{ $produk->id }}').value = this.originalData.nama;
        document.getElementById('kategori_{{ $produk->id }}').value = this.originalData.kategori;

        const hargaFormatted = document.getElementById('harga_formatted_{{ $produk->id }}');
        const hargaRaw = document.getElementById('harga_{{ $produk->id }}');
        if (hargaFormatted) hargaFormatted.value = new Intl.NumberFormat('id-ID').format(this.originalData.harga);
        if (hargaRaw) hargaRaw.value = this.originalData.harga;

        document.getElementById('deskripsi_{{ $produk->id }}').value = this.originalData.deskripsi;

        const fileInput = document.getElementById('foto_{{ $produk->id }}');
        if (fileInput) fileInput.value = '';

        this.imageUrl = this.originalImageUrl;
    },
}" x-init="if ({{ $openEditOnLoad ? 'true' : 'false' }}) {
    openEditId = {{ $produk->id }};
}"
    x-effect="
        if (openEditId === {{ $produk->id }}) {
            // saat dibuka via klik, rapikan ke nilai original
            resetEditForm();
        }
    "
    x-show="openEditId === {{ $produk->id }} || {{ $openEditOnLoad ? 'true' : 'false' }}" x-cloak x-transition
    @click.self="resetEditForm(); openEditId = null"
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm"
    @keydown.escape.window="resetEditForm(); openEditId = null" role="dialog" aria-modal="true">

    <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-4xl my-auto rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[92vh] flex flex-col overflow-hidden">

        {{-- HEADER (Fixed) --}}
        <div
            class="flex items-center justify-between px-6 py-4 border-b-2 border-brand-borderSoft/80 shrink-0 bg-inherit z-10">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui informasi data produk.</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="resetEditForm(); openEditId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- FORM WRAPPER --}}
        <form method="POST" action="{{ route('admin.produk.update', $produk) }}" enctype="multipart/form-data"
            class="flex flex-col flex-1 min-h-0">
            @csrf
            @method('PUT')

            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
            <input type="hidden" name="foto_preview" :value="imageUrl">

            {{-- BODY --}}
            {{-- Mobile: Scrollable | Desktop: No Body Scroll (Flex Layout) --}}
            <div class="flex-1 overflow-y-auto md:overflow-hidden custom-scrollbar px-6 py-4 overscroll-contain">

                @if ($openEditOnLoad)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4 shrink-0">
                        <p class="text-sm font-semibold">Ada kesalahan input saat mengedit:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:h-full">

                    {{-- PANEL KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1 shrink-0">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden relative">
                                <template x-if="imageUrl && !imageUrl.includes('No+Foto')">
                                    <img :src="imageUrl" alt="Preview Foto Produk"
                                        class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imageUrl || imageUrl.includes('No+Foto')">
                                    <span class="text-[11px] text-text-muted text-center px-2">Belum ada foto</span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">Pilih foto baru untuk mengganti gambar
                                produk.</p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM INPUT --}}
                    <div class="md:col-span-2 md:h-full md:flex md:flex-col">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4 md:h-full md:flex md:flex-col md:space-y-3">

                            {{-- BAGIAN ATAS (Inputs) --}}
                            <div class="shrink-0 space-y-4 md:space-y-3">
                                {{-- Baris 1 --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-ui.label for="nama_{{ $produk->id }}">Nama Produk<span
                                                class="text-danger">*</span></x-ui.label>
                                        <input type="text" id="nama_{{ $produk->id }}" name="nama"
                                            value="{{ old('nama', $produk->nama) }}" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror">
                                        @error('nama')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label for="kategori_{{ $produk->id }}">Kategori<span
                                                class="text-danger">*</span></x-ui.label>
                                        <select id="kategori_{{ $produk->id }}" name="kategori" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('kategori') border-danger ring-danger-soft @enderror">
                                            @foreach ($kategoriOptions as $option)
                                                <option value="{{ $option }}"
                                                    {{ old('kategori', $produk->kategori) == $option ? 'selected' : '' }}>
                                                    {{ ucwords($option) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kategori')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Baris 2 --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-ui.label for="harga_formatted_{{ $produk->id }}">Harga (Rp)<span
                                                class="text-danger">*</span></x-ui.label>
                                        <input type="text" id="harga_formatted_{{ $produk->id }}"
                                            value="{{ old('harga', $produk->harga) ? number_format((int) old('harga', $produk->harga), 0, ',', '.') : '' }}"
                                            required x-on:input="formatRupiahInput($el)"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('harga') border-danger ring-danger-soft @enderror">
                                        <input type="hidden" name="harga" id="harga_{{ $produk->id }}"
                                            value="{{ old('harga', (int) $produk->harga) }}">
                                        @error('harga')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Stok (Otomatis)</x-ui.label>
                                        <input type="text" value="{{ $produk->stok }}" disabled
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-muted px-3 py-2 border-brand-borderSoft">
                                        <p class="text-xs text-text-muted mt-1">Stok dikelola melalui menu Stok Produk.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- DESKRIPSI (Scrollable Area) --}}
                            <div class="flex-1 min-h-0 flex flex-col">
                                <x-ui.label for="deskripsi_{{ $produk->id }}">Deskripsi (opsional)</x-ui.label>
                                <textarea id="deskripsi_{{ $produk->id }}" name="deskripsi"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent 
                                           resize-none h-24 md:h-full md:flex-1 custom-scrollbar
                                           @error('deskripsi') border-danger ring-danger-soft @enderror"
                                    placeholder="Tuliskan deskripsi...">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FILE INPUT --}}
                            <div class="shrink-0 space-y-2 pt-1">
                                <x-ui.label for="foto_{{ $produk->id }}">Foto Produk (opsional)</x-ui.label>
                                <input type="file" id="foto_{{ $produk->id }}" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700"
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
                                @error('foto')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-text-muted mt-1">
                                    Kosongkan jika tidak ingin mengubah foto. Maksimal 5MB. Format: JPG, JPEG, PNG, WebP (Otomatis dikompres & dikonversi ke WebP HD).
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER (Fixed) --}}
            <div
                class="flex items-center justify-end gap-2 px-6 py-4 border-t border-brand-borderSoft/70 bg-inherit shrink-0 z-10">
                <x-ui.button-secondary type="button"
                    @click="resetEditForm(); openEditId = null">Batal</x-ui.button-secondary>
                <x-ui.button-primary type="submit">Simpan Perubahan</x-ui.button-primary>
            </div>
        </form>
    </div>
</div>
