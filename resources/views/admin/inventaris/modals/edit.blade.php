{{-- MODAL EDIT INVENTARIS (per alat, dipanggil dari dalam x-data di index) --}}
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
                <h2 class="text-xl font-semibold text-text-main">
                    Edit Inventaris Alat
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Perbarui informasi alat dan kondisinya.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEdit = false"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form
                method="POST"
                action="{{ route('admin.inventaris.update', $alat) }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="previewEditUrl">
                                    <img
                                        :src="previewEditUrl"
                                        alt="Foto alat"
                                        class="w-full h-full object-cover"
                                    >
                                </template>
                                <template x-if="!previewEditUrl">
                                    <span class="text-[11px] text-text-muted">
                                        Belum ada foto
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih foto baru untuk mengganti gambar alat.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- NAMA --}}
                                <div>
                                    <x-ui.label for="nama_{{ $alat->id }}">Nama Alat</x-ui.label>
                                    <input
                                        type="text"
                                        id="nama_{{ $alat->id }}"
                                        name="nama"
                                        value="{{ old('nama', $alat->nama) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required
                                    >
                                </div>

                                {{-- KONDISI --}}
                                <div>
                                    <x-ui.label for="kondisi_{{ $alat->id }}">Kondisi</x-ui.label>
                                    <select
                                        id="kondisi_{{ $alat->id }}"
                                        name="kondisi"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required
                                    >
                                        <option value="" disabled>
                                            -- Pilih Kondisi --
                                        </option>
                                        <option value="Baik" {{ old('kondisi', $alat->kondisi) === 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Maintenance" {{ old('kondisi', $alat->kondisi) === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="Rusak" {{ old('kondisi', $alat->kondisi) === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                    </select>
                                </div>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div>
                                <x-ui.label for="deskripsi_{{ $alat->id }}">Deskripsi</x-ui.label>
                                <textarea
                                    id="deskripsi_{{ $alat->id }}"
                                    name="deskripsi"
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                >{{ old('deskripsi', $alat->deskripsi) }}</textarea>
                            </div>

                            {{-- FOTO --}}
                            <div class="space-y-2">
                                <x-ui.label for="foto_{{ $alat->id }}">Foto (opsional)</x-ui.label>
                                <input
                                    type="file"
                                    id="foto_{{ $alat->id }}"
                                    name="foto"
                                    accept="image/*"
                                    @change="setEditPreview($event)"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700"
                                >
                                <p class="text-[11px] text-text-muted">
                                    Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openEdit = false">
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
