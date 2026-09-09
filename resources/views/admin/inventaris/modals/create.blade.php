{{-- RESOURCES/VIEWS/ADMIN/INVENTARIS/MODALS/CREATE.BLADE.PHP --}}

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">

    {{-- TAMBAHKAN x-data DI SINI UNTUK MENGELOLA PREVIEW --}}
    <div @click.away="openCreate = false" x-data="{
        previewCreateUrl: null,
        setCreatePreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewCreateUrl = e.target.result; };
                reader.readAsDataURL(file);
            } else {
                this.previewCreateUrl = null;
            }
        }
    }"
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Inventaris Alat</h2>
                <p class="text-sm text-text-muted mt-0.5">Masukkan data alat baru beserta kondisinya.</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.inventaris.store') }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                {{-- Jika ada preview --}}
                                <template x-if="previewCreateUrl">
                                    <img :src="previewCreateUrl" alt="Preview foto alat"
                                        class="w-full h-full object-cover">
                                </template>

                                {{-- Jika TIDAK ada preview --}}
                                <template x-if="!previewCreateUrl">
                                    <span class="text-[11px] text-text-muted text-center px-2">Foto Alat</span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih gambar untuk menambahkan foto inventaris.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- NAMA --}}
                                <div>
                                    <x-ui.label for="nama_create">Nama Alat</x-ui.label>
                                    <input type="text" id="nama_create" name="nama" value="{{ old('nama') }}"
                                        placeholder="Contoh: Dumbbell 10kg"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                        required>
                                </div>

                                {{-- KONDISI --}}
                                <div>
                                    <x-ui.label for="kondisi_create">Kondisi</x-ui.label>
                                    <div class="relative">
                                        <select id="kondisi_create" name="kondisi"
                                            class="w-full appearance-none rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                            required>
                                            <option value="" disabled {{ old('kondisi') ? '' : 'selected' }}>--
                                                Pilih Kondisi --</option>
                                            <option value="Baik" {{ old('kondisi') === 'Baik' ? 'selected' : '' }}>
                                                Baik</option>
                                            <option value="Maintenance"
                                                {{ old('kondisi') === 'Maintenance' ? 'selected' : '' }}>Maintenance
                                            </option>
                                            <option value="Rusak" {{ old('kondisi') === 'Rusak' ? 'selected' : '' }}>
                                                Rusak</option>
                                        </select>
                                        <i data-lucide="chevron-down"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div>
                                <x-ui.label for="deskripsi_create">Deskripsi</x-ui.label>
                                <textarea id="deskripsi_create" name="deskripsi" rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark">{{ old('deskripsi') }}</textarea>
                            </div>

                            {{-- FOTO INPUT --}}
                            <div>
                                <x-ui.label for="foto_create">Foto (opsional)</x-ui.label>
                                <input type="file" id="foto_create" name="foto" accept="image/*"
                                    @change="setCreatePreview($event)"
                                    class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700">
                                <p class="text-[11px] text-text-muted mt-1">Maksimal 5MB. Format: JPG, JPEG, PNG, WebP (Otomatis dikompres & dikonversi ke WebP HD).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Alat</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
