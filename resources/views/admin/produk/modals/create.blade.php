{{-- resources/views/admin/produk/modals/create.blade.php --}}

@php
    $openCreateFormErrors = $errors->any() && old('_method') !== 'PUT';
@endphp

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-start justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @click.self="openCreate = false; resetCreateForm()" @keydown.escape.window="openCreate = false; resetCreateForm()"
    role="dialog" aria-modal="true" aria-labelledby="modal-produk-create-title">

    <div @click.stop
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               h-full max-h-[calc(100vh-48px)] flex flex-col overflow-hidden">

        {{-- HEADER (fixed) --}}
        <div
            class="flex items-center justify-between px-6 py-4 border-b-2 border-brand-borderSoft/80 shrink-0 bg-inherit z-10">
            <div>
                <h2 id="modal-produk-create-title" class="text-xl font-semibold text-text-main">Tambah Produk</h2>
                <p class="text-sm text-text-muted mt-0.5">Masukkan data produk baru.</p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false; resetCreateForm()" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- FORM WRAPPER --}}
        <form x-ref="createForm" method="POST" action="{{ route('admin.produk.store') }}" enctype="multipart/form-data"
            class="flex flex-col flex-1 min-h-0">

            @csrf

            {{-- BODY --}}
            {{-- Mobile: Scrollable (overflow-y-auto) --}}
            {{-- Desktop: No Scroll Body (md:overflow-hidden), layout diatur flex --}}
            <div class="flex-1 overflow-y-auto md:overflow-hidden custom-scrollbar px-6 py-4 overscroll-contain">

                {{-- ERROR VALIDASI --}}
                @if ($openCreateFormErrors)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-4 shrink-0">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- GRID UTAMA --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:h-full">

                    {{-- PANEL KIRI: PREVIEW FOTO (Sticky on Mobile, Static on Desktop) --}}
                    <div class="md:col-span-1 shrink-0">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden relative">
                                <template x-if="createImageUrl">
                                    <img :src="createImageUrl" alt="Preview Foto Produk"
                                        class="w-full h-full object-cover">
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

                    {{-- PANEL KANAN: FORM INPUT --}}
                    {{-- Desktop: Flex Column agar mengisi tinggi penuh --}}
                    <div class="md:col-span-2 md:h-full md:flex md:flex-col">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4 md:h-full md:flex md:flex-col md:space-y-3">

                            {{-- BAGIAN ATAS (Inputs) - Shrink 0 agar tidak gepeng --}}
                            <div class="shrink-0 space-y-4 md:space-y-3">
                                {{-- BARIS 1: NAMA & KATEGORI --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-ui.label for="nama_create">
                                            Nama Produk<span class="text-danger">*</span>
                                        </x-ui.label>
                                        <input type="text" id="nama_create" name="nama"
                                            value="{{ old('nama') }}" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                                   @error('nama') border-danger ring-danger-soft @enderror">
                                        @error('nama')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label for="kategori_create">
                                            Kategori<span class="text-danger">*</span>
                                        </x-ui.label>
                                        <select id="kategori_create" name="kategori" required
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                                   @error('kategori') border-danger ring-danger-soft @enderror">
                                            <option value="" disabled
                                                {{ old('kategori') == null ? 'selected' : '' }}>
                                                Pilih Kategori
                                            </option>
                                            @foreach ($kategoriOptions as $option)
                                                <option value="{{ $option }}"
                                                    {{ old('kategori') == $option ? 'selected' : '' }}>
                                                    {{ ucwords($option) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kategori')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- BARIS 2: HARGA --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-ui.label for="harga_create_formatted">
                                            Harga (Rp)<span class="text-danger">*</span>
                                        </x-ui.label>

                                        <input type="text" id="harga_create_formatted" x-ref="hargaCreateFormatted"
                                            value="{{ old('harga') ? number_format((int) old('harga'), 0, ',', '.') : '' }}"
                                            required x-on:input="formatRupiahInput($el)"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                                   @error('harga') border-danger ring-danger-soft @enderror">

                                        <input type="hidden" id="harga_create" name="harga"
                                            value="{{ old('harga') }}"
                                            :value="document.getElementById('harga_create_formatted').value.replace(/\./g, '')">

                                        @error('harga')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror

                                        <p class="text-[11px] text-text-muted mt-1">
                                            Stok tidak diinput di sini. Kelola stok di menu Stok Produk.
                                        </p>
                                    </div>
                                    <div class="hidden md:block"></div>
                                </div>
                            </div>

                            {{-- DESKRIPSI (Flexible Height) --}}
                            {{-- Desktop: Flex-1 dan min-h-0 agar mengisi sisa ruang dan scrollable --}}
                            <div class="flex-1 min-h-0 flex flex-col">
                                <x-ui.label for="deskripsi_create">Deskripsi (opsional)</x-ui.label>
                                <textarea id="deskripsi_create" name="deskripsi"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                           resize-none h-24 md:h-full md:flex-1 custom-scrollbar
                                           @error('deskripsi') border-danger ring-danger-soft @enderror"
                                    placeholder="Tuliskan deskripsi singkat produk...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FOTO PRODUK (Fixed Bottom) --}}
                            <div class="shrink-0 space-y-2 pt-1">
                                <x-ui.label for="foto_create">Foto Produk (opsional)</x-ui.label>
                                <input type="file" id="foto_create" name="foto" accept="image/*"
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
                                    ">
                                @error('foto')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-text-muted mt-1">
                                    Maksimal 5MB. Format: JPG, JPEG, PNG, WebP (Otomatis dikompres & dikonversi ke WebP HD).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER (Fixed at Bottom) --}}
            <div
                class="flex items-center justify-end gap-2 px-6 py-4 border-t border-brand-borderSoft/70 bg-inherit shrink-0 z-10">
                <x-ui.button-secondary type="button" @click="openCreate = false; resetCreateForm()">
                    Batal
                </x-ui.button-secondary>
                <x-ui.button-primary type="submit">
                    Simpan Produk
                </x-ui.button-primary>
            </div>
        </form>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</div>
