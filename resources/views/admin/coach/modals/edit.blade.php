{{-- resources/views/admin/coach/modals/edit.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $currentFotoPath = $coach->foto ?? null;
    $currentFotoUrl = $currentFotoPath
        ? Storage::url($currentFotoPath)
        : 'https://placehold.co/200x200/3A2D2A/F5E6D6?text=No+Foto';
@endphp

<div
    x-show="openEditId === {{ $coach->id }}"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
>
    <div
        @click.away="openEditId = null"
        class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{ imageUrl: '{{ $currentFotoUrl }}' }"
    >
        {{-- HEADER MODAL (mirip inventaris) --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Edit Data Coach
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Perbarui informasi profil coach.
                </p>
            </div>
            <button
                type="button"
                class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditId = null"
            >
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form
                method="POST"
                action="{{ route('admin.coaches.update', $coach) }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FOTO (match inventaris) --}}
                    <div class="md:col-span-1">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                <template x-if="imageUrl && !imageUrl.includes('No+Foto')">
                                    <img
                                        :src="imageUrl"
                                        alt="Foto {{ $coach->nama }}"
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
                                Pilih foto baru untuk mengganti gambar coach.
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM (layout sama seperti inventaris) --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- NAMA --}}
                                <div>
                                    <x-ui.label for="nama_{{ $coach->id }}">Nama Coach</x-ui.label>
                                    <input
                                        type="text"
                                        id="nama_{{ $coach->id }}"
                                        name="nama"
                                        value="{{ old('nama', $coach->nama) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required
                                    >
                                </div>

                                {{-- NO HP --}}
                                <div>
                                    <x-ui.label for="no_hp_{{ $coach->id }}">Nomor HP</x-ui.label>
                                    <input
                                        type="text"
                                        id="no_hp_{{ $coach->id }}"
                                        name="no_hp"
                                        value="{{ old('no_hp', $coach->no_hp) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        required
                                    >
                                </div>
                            </div>

                            {{-- ALAMAT --}}
                            <div>
                                <x-ui.label for="alamat_{{ $coach->id }}">Alamat</x-ui.label>
                                <textarea
                                    id="alamat_{{ $coach->id }}"
                                    name="alamat"
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                    required
                                >{{ old('alamat', $coach->alamat) }}</textarea>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div>
                                <x-ui.label for="deskripsi_{{ $coach->id }}">Deskripsi / Keahlian</x-ui.label>
                                <textarea
                                    id="deskripsi_{{ $coach->id }}"
                                    name="deskripsi"
                                    rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                >{{ old('deskripsi', $coach->deskripsi) }}</textarea>
                            </div>

                            {{-- FOTO --}}
                            <div class="space-y-2">
                                <x-ui.label for="foto_{{ $coach->id }}">Foto Profil (opsional)</x-ui.label>

                                <input
                                    type="file"
                                    id="foto_{{ $coach->id }}"
                                    name="foto"
                                    accept="image/*"
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
                                            imageUrl = '{{ $currentFotoUrl }}';
                                        }
                                    "
                                >
                                <p class="text-[11px] text-text-muted">
                                    Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openEditId = null">
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
