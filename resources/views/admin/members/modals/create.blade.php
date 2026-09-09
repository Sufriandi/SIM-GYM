{{-- resources/views/admin/members/modals/create.blade.php --}}

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm">

    <div @click.away="openCreate = false" @keydown.escape.window="openCreate = false"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-5xl my-auto rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{
            imageUrl: null,
            tanggalDaftar: @js(old('tanggal_daftar')) || new Date().toISOString().split('T')[0],
        }">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Buat akun user baru dan otomatis buat record member (tanggal daftar).
                </p>
            </div>

            <button type="button" class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
            </button>
        </div>

        {{-- BODY (scrollable, aman di layar kecil) --}}
        <div class="px-6 pb-6 pt-4 max-h-[80vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                {{-- ERROR VALIDASI UNTUK CREATE --}}
                @php $openCreateFormErrors = $errors->any() && old('_method') !== 'PUT'; @endphp
                @if ($openCreateFormErrors)
                    <div class="bg-danger-soft text-danger p-3 rounded-xl border border-danger/50">
                        <p class="text-sm font-semibold">Ada kesalahan input:</p>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    {{-- KIRI: FOTO (KEMBALI KE DESAIN AWAL w-28 h-28) --}}
                    <div class="lg:col-span-4">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-text-main">Foto Profil</h3>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-col items-center gap-3">
                                {{-- KEMBALI KE UKURAN ASLI --}}
                                <div
                                    class="w-28 h-28 rounded-2xl border-2 border-dashed border-brand-borderSoft overflow-hidden
                                           flex items-center justify-center bg-brand-surface-50">
                                    <img x-show="imageUrl" :src="imageUrl" alt="Preview Foto"
                                        class="object-cover w-full h-full">
                                    <span x-show="!imageUrl" class="text-xs text-text-muted text-center px-2">
                                        Preview Foto
                                    </span>
                                </div>
                                {{-- INPUT FILE SUDAH DIPINDAHKAN DARI SINI --}}
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: FORM --}}
                    <div class="lg:col-span-8">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                            {{-- tanggal daftar (members) --}}
                            <div>
                                <x-ui.label for="tanggal_daftar_create">Tanggal Daftar</x-ui.label>
                                <input id="tanggal_daftar_create" type="date" name="tanggal_daftar"
                                    x-model="tanggalDaftar"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent">
                                <p class="text-[11px] text-text-muted mt-1">
                                    Disimpan ke tabel <span class="font-semibold">members.tanggal_daftar</span>.
                                </p>
                                @error('tanggal_daftar')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_create">Nama<span class="text-danger">*</span></x-ui.label>
                                    <input id="nama_create" type="text" name="nama" value="{{ old('nama') }}"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('nama') border-danger ring-danger-soft @enderror">
                                    @error('nama')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-ui.label for="username_create">Username<span
                                            class="text-danger">*</span></x-ui.label>
                                    <input id="username_create" type="text" name="username"
                                        value="{{ old('username') }}" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('username') border-danger ring-danger-soft @enderror">
                                    @error('username')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="email_create">Email (opsional)</x-ui.label>
                                    <input id="email_create" type="email" name="email" value="{{ old('email') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('email') border-danger ring-danger-soft @enderror">
                                    @error('email')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-ui.label for="no_hp_create">Nomor HP (opsional)</x-ui.label>
                                    <input id="no_hp_create" type="text" name="no_hp" value="{{ old('no_hp') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('no_hp') border-danger ring-danger-soft @enderror">
                                    @error('no_hp')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="jenis_kelamin_create">Jenis Kelamin (opsional)</x-ui.label>
                                    <select id="jenis_kelamin_create" name="jenis_kelamin"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('jenis_kelamin') border-danger ring-danger-soft @enderror">
                                        <option value="">- Pilih -</option>
                                        <option value="laki-laki" @selected(old('jenis_kelamin') === 'laki-laki')>Laki-laki</option>
                                        <option value="perempuan" @selected(old('jenis_kelamin') === 'perempuan')>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-ui.label for="password_create">Password<span
                                            class="text-danger">*</span></x-ui.label>
                                    <input id="password_create" type="password" name="password" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                               border-brand-borderSoft focus:outline-none focus:ring-2
                                               focus:ring-primary-dark focus:border-transparent @error('password') border-danger ring-danger-soft @enderror">
                                    @error('password')
                                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="password_confirmation_create">Konfirmasi Password<span
                                        class="text-danger">*</span></x-ui.label>
                                <input id="password_confirmation_create" type="password" name="password_confirmation"
                                    required
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent">
                            </div>

                            <div>
                                <x-ui.label for="alamat_create">Alamat (opsional)</x-ui.label>
                                <textarea id="alamat_create" name="alamat" rows="3"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-2
                                           focus:ring-primary-dark focus:border-transparent @error('alamat') border-danger ring-danger-soft @enderror">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- INPUT FILE BERADA DISINI SEKARANG --}}
                            <div class="pt-2 border-t border-brand-borderSoft/50">
                                <x-ui.label for="foto_create">Upload Foto (opsional)</x-ui.label>
                                <input id="foto_create" type="file" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700
                                           cursor-pointer"
                                    @change="
                                        const f = $event.target.files[0];
                                        if (f) {
                                            const r = new FileReader();
                                            r.onload = e => imageUrl = e.target.result;
                                            r.readAsDataURL(f);
                                        } else {
                                            imageUrl = null;
                                        }
                                    ">
                                <p class="text-[11px] text-text-muted mt-1">
                                    Maksimal 5MB. Format: JPG, JPEG, PNG, WebP (Otomatis dikompres & dikonversi ke WebP HD).
                                </p>
                                @error('foto')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-brand-borderSoft/70 mt-2">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Member</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
