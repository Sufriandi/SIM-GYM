{{-- resources/views/admin/coach/modals/edit.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    // URL Foto saat ini (jika ada)
    $currentFotoPath = $coach->foto ?? null;
    $currentFotoUrl = $currentFotoPath ? Storage::url($currentFotoPath) : null;

    // Logika auto-open jika ada error validasi pada item ini
    $openEditOnLoad = $errors->any() && old('_method') === 'PUT' && (int) old('coach_id') === (int) $coach->id;
@endphp

<div x-show="openEditId === {{ $coach->id }} || {{ $openEditOnLoad ? 'true' : 'false' }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @keydown.escape.window="openEditId = null" @click.self="openEditId = null">

    {{-- x-data: inisialisasi imageUrl dengan foto lama (jika ada) --}}
    <div class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell flex flex-col max-h-[90vh]"
        x-data="{ imageUrl: @js($currentFotoUrl) }">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80 shrink-0">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Data Coach</h2>
                <p class="text-sm text-text-muted mt-0.5">Perbarui informasi profil coach.</p>
            </div>
            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openEditId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- ISI MODAL --}}
        <div class="px-6 pb-6 pt-4 overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.coaches.update', $coach) }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="coach_id" value="{{ $coach->id }}">

                @if ($openEditOnLoad)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50 mb-2">
                        <ul class="list-disc list-inside text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- PANEL KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            {{-- Container Foto Persegi --}}
                            <div
                                class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden relative">
                                <template x-if="imageUrl">
                                    <img :src="imageUrl" alt="Foto Coach" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imageUrl">
                                    <div class="flex flex-col items-center text-text-muted">
                                        <span class="text-[10px] text-center px-2">Belum ada foto</span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">Foto saat ini.</p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_{{ $coach->id }}">Nama Coach</x-ui.label>
                                    <input type="text" id="nama_{{ $coach->id }}" name="nama"
                                        value="{{ old('nama', $coach->nama) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                        required>
                                </div>
                                <div>
                                    <x-ui.label for="no_hp_{{ $coach->id }}">Nomor HP</x-ui.label>
                                    <input type="text" id="no_hp_{{ $coach->id }}" name="no_hp"
                                        value="{{ old('no_hp', $coach->no_hp) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                        required>
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="alamat_{{ $coach->id }}">Alamat</x-ui.label>
                                <textarea id="alamat_{{ $coach->id }}" name="alamat" rows="2"
                                    class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark"
                                    required>{{ old('alamat', $coach->alamat) }}</textarea>
                            </div>

                            <div>
                                <x-ui.label for="deskripsi_{{ $coach->id }}">Deskripsi / Keahlian</x-ui.label>
                                <textarea id="deskripsi_{{ $coach->id }}" name="deskripsi" rows="2"
                                    class="w-full rounded-xl border bg-brand-shell text-sm px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark">{{ old('deskripsi', $coach->deskripsi) }}</textarea>
                            </div>

                            <div>
                                <x-ui.label for="foto_{{ $coach->id }}">Ganti Foto (opsional)</x-ui.label>
                                <input type="file" id="foto_{{ $coach->id }}" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gold-600 file:text-white hover:file:bg-gold-700"
                                    @change="const file = $event.target.files[0]; if(file){ const reader = new FileReader(); reader.onload = (e) => { imageUrl = e.target.result; }; reader.readAsDataURL(file); }">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft mt-4">
                    <x-ui.button-secondary type="button" @click="openEditId = null">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Perubahan</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
