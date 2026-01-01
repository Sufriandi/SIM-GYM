{{-- resources/views/admin/members/modals/edit.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;

    $user = $member->user;

    $foto = !empty($user?->foto)
        ? Storage::url($user->foto)
        : 'https://placehold.co/150x150/3A2D2A/F5E6D6?text=No+Foto';

    // trigger buka modal jika error PUT untuk member ini
    $openEditOnLoad =
        $errors->any() && old('_method') === 'PUT' && (int) old('member_id') === (int) $member->id ? 'true' : 'false';
@endphp

<style>
    /* CSS untuk menyembunyikan scrollbar tapi tetap bisa di-scroll */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div x-show="openEditId === {{ $member->id }} || {{ $openEditOnLoad }}" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
    @keydown.escape.window="openEditId = null">

    <div @click.away="openEditId = null"
        class="relative w-full max-w-5xl max-h-[90vh] overflow-y-auto hide-scrollbar rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{ fotoUrl: '{{ $foto }}' }">

        {{-- HEADER --}}
        <div
            class="sticky top-0 z-20 bg-brand-shell/95 backdrop-blur-md flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Edit Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Update profil di <b>users</b> dan tanggal daftar di <b>members</b>.
                </p>
            </div>

            <button type="button" class="p-1.5 rounded-full hover:bg-brand-surface-50 transition"
                @click="openEditId = null">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 pb-6 pt-4">
            <form method="POST" action="{{ route('admin.members.update', $member) }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="member_id" value="{{ $member->id }}">

                @if ($errors->any() && old('_method') === 'PUT' && (int) old('member_id') === (int) $member->id)
                    <div class="bg-danger-soft text-danger p-3 rounded-2xl border border-danger/50">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                    {{-- KIRI: FOTO PREVIEW --}}
                    {{-- PERBAIKAN: h-full dihapus, diganti h-fit sticky top-24 agar diam di atas --}}
                    <div class="lg:col-span-4 sticky top-24 z-0">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 h-fit">
                            <h3 class="text-sm font-semibold text-text-main">Foto Profil</h3>
                            <p class="text-[11px] text-text-muted mt-0.5">Preview foto saat ini.</p>

                            <div class="mt-3 flex flex-col items-center gap-3">
                                <div
                                    class="w-28 h-28 rounded-2xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 overflow-hidden flex items-center justify-center">
                                    <template x-if="fotoUrl && !fotoUrl.includes('No+Foto')">
                                        <img :src="fotoUrl" alt="Foto" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!fotoUrl || fotoUrl.includes('No+Foto')">
                                        <span class="text-[11px] text-text-muted text-center px-2">Belum ada foto</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: FORM --}}
                    <div class="lg:col-span-8">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            <div>
                                <x-ui.label for="tanggal_daftar_{{ $member->id }}">Tanggal Daftar</x-ui.label>
                                <input id="tanggal_daftar_{{ $member->id }}" type="date" name="tanggal_daftar"
                                    value="{{ old('tanggal_daftar', $member->tanggal_daftar) }}"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_{{ $member->id }}">Nama</x-ui.label>
                                    <input id="nama_{{ $member->id }}" type="text" name="nama"
                                        value="{{ old('nama', $user?->name ?? '') }}" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="username_{{ $member->id }}">Username</x-ui.label>
                                    <input id="username_{{ $member->id }}" type="text" name="username"
                                        value="{{ old('username', $user?->username ?? '') }}" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="email_{{ $member->id }}">Email (opsional)</x-ui.label>
                                    <input id="email_{{ $member->id }}" type="email" name="email"
                                        value="{{ old('email', $user?->email ?? '') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="no_hp_{{ $member->id }}">Nomor HP (opsional)</x-ui.label>
                                    <input id="no_hp_{{ $member->id }}" type="text" name="no_hp"
                                        value="{{ old('no_hp', $user?->no_hp ?? '') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="jenis_kelamin_{{ $member->id }}">Jenis Kelamin
                                        (opsional)</x-ui.label>
                                    <select id="jenis_kelamin_{{ $member->id }}" name="jenis_kelamin"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                        <option value="">- Pilih -</option>
                                        <option value="laki-laki" @selected(old('jenis_kelamin', $user?->jenis_kelamin ?? '') === 'laki-laki')>Laki-laki</option>
                                        <option value="perempuan" @selected(old('jenis_kelamin', $user?->jenis_kelamin ?? '') === 'perempuan')>Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <x-ui.label for="alamat_{{ $member->id }}">Alamat (opsional)</x-ui.label>
                                    <input id="alamat_{{ $member->id }}" type="text" name="alamat"
                                        value="{{ old('alamat', $user?->alamat ?? '') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            {{-- password opsional saat edit --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="password_{{ $member->id }}">Password Baru
                                        (opsional)</x-ui.label>
                                    <input id="password_{{ $member->id }}" type="password" name="password"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                    <p class="text-[11px] text-text-muted mt-1">Kosongkan jika tidak ingin mengubah
                                        password.</p>
                                </div>

                                <div>
                                    <x-ui.label for="password_confirmation_{{ $member->id }}">Konfirmasi Password
                                        (opsional)</x-ui.label>
                                    <input id="password_confirmation_{{ $member->id }}" type="password"
                                        name="password_confirmation"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            {{-- INPUT FILE ADA DI SINI --}}
                            <div class="pt-2 border-t border-brand-borderSoft/50">
                                <x-ui.label for="foto_{{ $member->id }}">Ganti Foto (opsional)</x-ui.label>
                                <input id="foto_{{ $member->id }}" type="file" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700
                                           cursor-pointer"
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
                                <p class="text-[11px] text-text-muted mt-1">Kosongkan jika tidak ingin mengubah. Maks
                                    2MB. Format: JPG/PNG.</p>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openEditId = null">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Perubahan</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
