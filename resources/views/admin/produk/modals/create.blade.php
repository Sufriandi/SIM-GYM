{{-- resources/views/admin/produk/modals/create.blade.php --}}

<div
    x-show="openCreate"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
>
    <div
        @click.away="openCreate = false"
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
    >
        {{-- HEADER (disamakan dengan coach) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Tambah Produk
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Masukkan data produk baru.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false; resetCreateForm()"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form
                x-ref="createForm"
                method="POST"
                action="{{ route('admin.produk.store') }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf

                @php
                    $openCreateFormErrors = $errors->any() && old('_method') !== 'PUT';
                @endphp

                {{-- ERROR VALIDASI UNTUK CREATE --}}
                @if ($openCreateFormErrors)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FOTO (match coach) --}}
                    <div class="md:col-span-1">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="createImageUrl">
                                    <img
                                        :src="createImageUrl"
                                        alt="Preview Foto Produk"
                                        class="w-full h-full object-cover"
                                    >
                                </template>
                                <template x-if="!createImageUrl">
                                    <span class="text-[11px] text-text-muted text-center px-2">
                                        Foto Produk
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih gambar untuk menambahkan foto produk.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM (layout sama seperti coach) --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            {{-- BARIS 1: NAMA & KATEGORI --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- NAMA PRODUK --}}
                                <div>
                                    <x-ui.label for="nama_create">
                                        Nama Produk<span class="text-danger">*</span>
                                    </x-ui.label>
                                    <input
                                        type="text"
                                        id="nama_create"
                                        name="nama"
                                        value="{{ old('nama') }}"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                               @error('nama') border-danger ring-danger-soft @enderror"
                                    >
                                    @error('nama')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- KATEGORI --}}
                                <div>
                                    <x-ui.label for="kategori_create">
                                        Kategori<span class="text-danger">*</span>
                                    </x-ui.label>
                                    <select
                                        id="kategori_create"
                                        name="kategori"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                               @error('kategori') border-danger ring-danger-soft @enderror"
                                    >
                                        <option value="" disabled {{ old('kategori') == null ? 'selected' : '' }}>
                                            Pilih Kategori
                                        </option>
                                        @foreach ($kategoriOptions as $option)
                                            <option
                                                value="{{ $option }}"
                                                {{ old('kategori') == $option ? 'selected' : '' }}
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

                            {{-- BARIS 2: HARGA & STOK (read-only) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- HARGA --}}
                                <div>
                                    <x-ui.label for="harga_create_formatted">
                                        Harga (Rp)<span class="text-danger">*</span>
                                    </x-ui.label>

                                    {{-- INPUT TERFORMAT --}}
                                    <input
                                        type="text"
                                        id="harga_create_formatted"
                                        x-ref="hargaCreateFormatted"
                                        name="harga_formatted"
                                        value="{{ old('harga') ? number_format((int) old('harga'), 0, ',', '.') : '' }}"
                                        required
                                        x-on:input="formatRupiahInput($el)"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                               @error('harga') border-danger ring-danger-soft @enderror"
                                    >

                                    {{-- INPUT HIDDEN UNTUK NILAI MURNI KE BACKEND --}}
                                    <input
                                        type="hidden"
                                        id="harga_create"
                                        name="harga"
                                        value="{{ old('harga') }}"
                                        :value="document.getElementById('harga_create_formatted').value.replace(/\./g, '')"
                                    >

                                    @error('harga')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- STOK (READ-ONLY) --}}
<div>
    <x-ui.label for="stok_create">
        Stok Awal
    </x-ui.label>
    <input
        type="text"
        value="0"
        disabled
        class="w-full rounded-xl border bg-brand-shell text-sm text-text-muted px-3 py-2
               border-brand-borderSoft"
    >
    <input type="hidden" name="stok" value="0">
    <p class="text-xs text-text-muted mt-1">
        Stok diinisialisasi 0. Tambah stok awal melalui menu Stok Produk.
    </p>
</div>

                            </div>

                            {{-- DESKRIPSI --}}
                            <div>
                                <x-ui.label for="deskripsi">
                                    Deskripsi (opsional)
                                </x-ui.label>
                                <textarea
                                    id="deskripsi"
                                    name="deskripsi"
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           @error('deskripsi') border-danger ring-danger-soft @enderror"
                                >{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FOTO PRODUK --}}
                            <div class="space-y-2">
                                <x-ui.label for="foto_create">
                                    Foto Produk (opsional)
                                </x-ui.label>
                                <input
                                    type="file"
                                    id="foto_create"
                                    name="foto"
                                    accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700
                                           @error('foto') border-danger ring-danger-soft @enderror"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => { createImageUrl = e.target.result; };
                                            reader.readAsDataURL(file);
                                        } else {
                                            createImageUrl = null;
                                        }
                                    "
                                >
                                @error('foto')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-text-muted mt-1">
                                    Maksimal 2MB. Format yang didukung: JPG, JPEG, PNG.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary
                        type="button"
                        @click="openCreate = false; resetCreateForm()"
                    >
                        Batal
                    </x-ui.button-secondary>

                    <x-ui.button-primary type="submit">
                        Simpan Produk
                    </x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
