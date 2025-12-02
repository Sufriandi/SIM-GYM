{{-- resources/views/admin/members/modals/edit.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;

    $foto = $member->foto ? Storage::url($member->foto) : 'https://placehold.co/150x150/3A2D2A/F5E6D6?text=No+Foto';

    $user = $member->user;
@endphp

<div x-show="openEditId === {{ $member->id }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm">
    <div @click.away="openEditId = null"
        class="w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell
               max-h-[90vh] overflow-y-auto custom-scrollbar relative"
        x-data="{ fotoUrl: '{{ $foto }}' }">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">
                    Edit Data Member
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Perbarui informasi profil dan keanggotaan member.
                </p>
            </div>
            <button type="button" class="p-1.5 rounded-full hover:bg-brand-surface-50 transition"
                @click="openEditId = null">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4">
            <form method="POST" action="{{ route('admin.members.update', $member) }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PANEL KIRI: PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4
                                   flex flex-col items-center gap-3">
                            <div
                                class="w-28 h-28 rounded-full border-2 border-dashed border-brand-borderSoft
                                       bg-brand-surface-50 overflow-hidden flex items-center justify-center">
                                <template x-if="fotoUrl && !fotoUrl.includes('No+Foto')">
                                    <img :src="fotoUrl" alt="Foto {{ $member->nama }}"
                                        class="w-full h-full object-cover">
                                </template>
                                <template x-if="!fotoUrl || fotoUrl.includes('No+Foto')">
                                    <span class="text-[11px] text-text-muted text-center px-2">
                                        Belum ada foto
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Pilih foto baru untuk mengganti foto profil member (opsional).
                            </p>
                        </div>
                    </div>

                    {{-- PANEL KANAN: FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            {{-- NAMA & NO HP --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_{{ $member->id }}">Nama Member</x-ui.label>
                                    <input id="nama_{{ $member->id }}" type="text" name="nama"
                                        value="{{ old('nama', $member->nama) }}" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="no_hp_{{ $member->id }}">Nomor HP</x-ui.label>
                                    <input id="no_hp_{{ $member->id }}" type="text" name="no_hp"
                                        value="{{ old('no_hp', $user->no_hp ?? '') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            {{-- TANGGAL MEMBERSHIP --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="tanggal_mulai_{{ $member->id }}">Mulai Membership</x-ui.label>
                                    <input id="tanggal_mulai_{{ $member->id }}" type="date" name="tanggal_mulai"
                                        value="{{ old('tanggal_mulai', $member->tanggal_mulai) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="tanggal_akhir_{{ $member->id }}">Akhir Membership</x-ui.label>
                                    <input id="tanggal_akhir_{{ $member->id }}" type="date" name="tanggal_akhir"
                                        value="{{ old('tanggal_akhir', $member->tanggal_akhir) }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            {{-- ALAMAT --}}
                            <div>
                                <x-ui.label for="alamat_{{ $member->id }}">Alamat</x-ui.label>
                                <textarea id="alamat_{{ $member->id }}" name="alamat" rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent">{{ old('alamat', $member->alamat) }}</textarea>
                            </div>

                            {{-- FOTO (INPUT FILE) --}}
                            <div class="space-y-2">
                                <x-ui.label for="foto_{{ $member->id }}">Ganti Foto (opsional)</x-ui.label>
                                <input id="foto_{{ $member->id }}" type="file" name="foto" accept="image/*"
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
                                            reader.onload = (e) => { fotoUrl = e.target.result; };
                                            reader.readAsDataURL(file);
                                        } else {
                                            fotoUrl = '{{ $foto }}';
                                        }
                                    ">
                                <p class="text-[11px] text-text-muted">
                                    Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
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
