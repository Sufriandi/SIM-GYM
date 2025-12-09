{{-- resources/views/admin/produk/modals/edit.blade.php --}}
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
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
    >
        {{-- HEADER MODAL (diseragamkan gaya dengan coach) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Edit Produk
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Perbarui informasi data produk.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="resetEditForm(); openEdit = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
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

                {{-- ERROR VALIDASI SAAT EDIT PRODUK --}}
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
                    {{-- PANEL KIRI: PREVIEW FOTO (disamakan dengan coach) --}}
                    <div class="md:col-span-1">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="imageUrl && !imageUrl.includes('No+Foto')">
                                    <img
                                        :src="imageUrl"
                                        alt="Preview Foto Produk"
                                        class="w-full h-full object-cover"
                                    >
                                </template>
                                <template x-if="!imageUrl || imageUrl.includes('No+Foto')">
                                    <span class="text-[11px] text-text-muted text-center px-2">
                                        Belum ada foto
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih foto baru untuk mengganti gambar produk.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM INPUT --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- NAMA PRODUK --}}
                                <div>
                                    <x-ui.label for="nama_{{ $produk->id }}">
                                        Nama Produk<span class="text-danger">*</span>
                                    </x-ui.label>
                                    <input
                                        type="text"
                                        id="nama_{{ $produk->id }}"
                                        name="nama"
                                        value="{{ old('nama', $produk->nama) }}"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('nama')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- KATEGORI --}}
                                <div>
                                    <x-ui.label for="kategori_{{ $produk->id }}">
                                        Kategori<span class="text-danger">*</span>
                                    </x-ui.label>
                                    <select
                                        id="kategori_{{ $produk->id }}"
                                        name="kategori"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('kategori') border-danger ring-danger-soft @enderror"
                                    >
                                        @foreach ($kategoriOptions as $option)
                                            <option
                                                value="{{ $option }}"
                                                {{ old('kategori', $produk->kategori) == $option ? 'selected' : '' }}
                                            >
                                                {{ ucwords($option) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kategori')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- HARGA --}}
                                <div>
                                    <x-ui.label for="harga_formatted_{{ $produk->id }}">
                                        Harga (Rp)<span class="text-danger">*</span>
                                    </x-ui.label>

                                    {{-- INPUT TERFORMAT --}}
                                    <input
                                        type="text"
                                        id="harga_formatted_{{ $produk->id }}"
                                        value="{{ old('harga', $produk->harga) ? number_format((int) old('harga', $produk->harga), 0, ',', '.') : '' }}"
                                        required
                                        x-on:input="formatRupiahInput($el)"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('harga') border-danger ring-danger-soft @enderror"
                                    >

                                    {{-- INPUT HIDDEN NILAI MURNI --}}
                                    <input
                                        type="hidden"
                                        name="harga"
                                        id="harga_{{ $produk->id }}"
                                        value="{{ old('harga', (int) $produk->harga) }}"
                                        :value="document.getElementById('harga_formatted_{{ $produk->id }}').value.replace(/\./g, '')"
                                    >

                                    @error('harga')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- STOK (READ-ONLY, DISAMAKAN WARNA) --}}
                                <div>
                                    <x-ui.label for="stok_{{ $produk->id }}">
                                        Stok (Otomatis)
                                    </x-ui.label>
                                    <input
                                        type="text"
                                        value="{{ $produk->stok }}"
                                        disabled
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-muted px-3 py-2
                                               border-brand-borderSoft"
                                    >
                                    <p class="text-xs text-text-muted mt-1">
                                        Stok dikelola melalui menu Stok Produk.
                                    </p>
                                </div>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div>
                                <x-ui.label for="deskripsi_{{ $produk->id }}">
                                    Deskripsi (opsional)
                                </x-ui.label>
                                <textarea
                                    id="deskripsi_{{ $produk->id }}"
                                    name="deskripsi"
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent @error('deskripsi') border-danger ring-danger-soft @enderror"
                                >{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FOTO PRODUK --}}
                            <div class="space-y-2">
                                <x-ui.label for="foto_{{ $produk->id }}">
                                    Foto Produk (opsional)
                                </x-ui.label>
                                <input
                                    type="file"
                                    id="foto_{{ $produk->id }}"
                                    name="foto"
                                    accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700 @error('foto') border-danger ring-danger-soft @enderror"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => { imageUrl = e.target.result; };
                                            reader.readAsDataURL(file);
                                        } else {
                                            imageUrl = originalImageUrl;
                                        }
                                    "
                                >
                                @error('foto')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-text-muted mt-1">
                                    Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB. Format: JPG, JPEG, PNG.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="resetEditForm(); openEdit = false">
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
