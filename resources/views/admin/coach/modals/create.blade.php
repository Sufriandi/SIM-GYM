{{-- resources/views/admin/coach/modals/create.blade.php --}}

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">
    <div @click.away="openCreate = false"
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{ createImageUrl: null }">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Data Coach</h2>
                <p class="text-sm text-text-muted mt-0.5">Masukkan informasi coach baru.</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            {{-- PERBAIKAN: space-y dikurangi jadi 4 --}}
            <form method="POST" action="{{ route('admin.coaches.store') }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

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

                {{-- PERBAIKAN: gap dikurangi jadi 5 --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- PANEL KIRI: PREVIEW FOTO (TETAP PERSEGI SEPERTI AWAL) --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            {{-- Ukuran tetap w-28 h-28 --}}
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="createImageUrl">
                                    <img :src="createImageUrl" alt="Preview Foto Coach"
                                        class="w-full h-full object-cover">
                                </template>
                                <template x-if="!createImageUrl">
                                    <span class="text-[11px] text-text-muted text-center px-2">Foto Coach</span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih gambar untuk menambahkan foto coach.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        {{-- PERBAIKAN: space-y dikurangi jadi 3 --}}
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_create">Nama Coach</x-ui.label>
                                    <input type="text" id="nama_create" name="nama" value="{{ old('nama') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required>
                                </div>
                                <div>
                                    <x-ui.label for="no_hp_create">Nomor HP</x-ui.label>
                                    <input type="text" id="no_hp_create" name="no_hp" value="{{ old('no_hp') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required>
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="alamat_create">Alamat</x-ui.label>
                                {{-- PERBAIKAN: rows dikurangi jadi 2 --}}
                                <textarea id="alamat_create" name="alamat" rows="2"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                    required>{{ old('alamat') }}</textarea>
                            </div>

                            <div>
                                <x-ui.label for="deskripsi_create">Deskripsi / Keahlian</x-ui.label>
                                {{-- PERBAIKAN: rows dikurangi jadi 2 --}}
                                <textarea id="deskripsi_create" name="deskripsi" rows="2"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">{{ old('deskripsi') }}</textarea>
                            </div>

                            <div>
                                <x-ui.label for="foto_create">Foto Profil (opsional)</x-ui.label>
                                <input type="file" id="foto_create" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700"
                                    @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => { createImageUrl = e.target.result; }; reader.readAsDataURL(file); } else { createImageUrl = null; }">
                                <p class="text-[11px] text-text-muted mt-1">
                                    Maksimal 2MB. Format yang didukung: JPG, JPEG, PNG.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Coach</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
