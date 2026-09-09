{{-- RESOURCES/VIEWS/ADMIN/INVENTARIS/MODALS/EDIT.BLADE.PHP --}}

@php
    $openEditOnLoad = $errors->any() && old('_method') === 'PUT' && (int) old('inventaris_id') === (int) $alat->id;
@endphp

<div {{-- PERBAIKAN: Gunakan ID unik --}} x-show="openEditId === {{ $alat->id }} || {{ $openEditOnLoad ? 'true' : 'false' }}"
    x-cloak x-transition
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm"
    @keydown.escape.window="openEditId = null" @click.self="openEditId = null">
    {{-- TAMBAHKAN x-data DI SINI UNTUK PREVIEW EDIT --}}
    <div x-data="{
        previewEditUrl: '{{ $alat->foto ? \Illuminate\Support\Facades\Storage::url($alat->foto) : null }}',
        setEditPreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.previewEditUrl = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }"
        class="relative w-full max-w-4xl my-auto rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Inventaris Alat</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui informasi alat dan kondisinya.</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.inventaris.update', $alat) }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="inventaris_id" value="{{ $alat->id }}">

                @if ($openEditOnLoad)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <ul class="list-disc list-inside text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                {{-- Jika ada preview (baik dari DB awal atau upload baru) --}}
                                <template x-if="previewEditUrl">
                                    <img :src="previewEditUrl" alt="Foto alat" class="w-full h-full object-cover">
                                </template>

                                {{-- Jika tidak ada foto --}}
                                <template x-if="!previewEditUrl">
                                    <span class="text-[11px] text-text-muted text-center px-2">Belum ada foto</span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">Foto saat ini.</p>
                        </div>
                    </div>

                    {{-- KANAN: FORM --}}
                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="nama_{{ $alat->id }}">Nama Alat</x-ui.label>
                                <input type="text" id="nama_{{ $alat->id }}" name="nama"
                                    value="{{ old('nama', $alat->nama) }}"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                    required>
                            </div>
                            <div>
                                <x-ui.label for="kondisi_{{ $alat->id }}">Kondisi</x-ui.label>
                                <div class="relative">
                                    <select id="kondisi_{{ $alat->id }}" name="kondisi"
                                        class="w-full appearance-none rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 pr-8 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                        required>
                                        <option value="Baik"
                                            {{ old('kondisi', $alat->kondisi) === 'Baik' ? 'selected' : '' }}>Baik
                                        </option>
                                        <option value="Maintenance"
                                            {{ old('kondisi', $alat->kondisi) === 'Maintenance' ? 'selected' : '' }}>
                                            Maintenance</option>
                                        <option value="Rusak"
                                            {{ old('kondisi', $alat->kondisi) === 'Rusak' ? 'selected' : '' }}>Rusak
                                        </option>
                                    </select>
                                    <i data-lucide="chevron-down"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-ui.label for="deskripsi_{{ $alat->id }}">Deskripsi</x-ui.label>
                            <textarea id="deskripsi_{{ $alat->id }}" name="deskripsi" rows="3"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark">{{ old('deskripsi', $alat->deskripsi) }}</textarea>
                        </div>

                        <div>
                            <x-ui.label for="foto_{{ $alat->id }}">Ganti Foto (opsional)</x-ui.label>
                            <input type="file" id="foto_{{ $alat->id }}" name="foto" accept="image/*"
                                @change="setEditPreview($event)"
                                class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft">
                    <x-ui.button-secondary type="button" @click="openEditId = null">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Perubahan</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
