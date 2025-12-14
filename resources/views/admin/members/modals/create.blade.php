{{-- resources/views/admin/members/modals/create.blade.php --}}

<div x-show="openCreate" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4 py-6"
    @click.self="openCreate = false" @wheel.prevent @touchmove.prevent>
    <div class="w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft
               bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
        x-data="{
            imageUrl: null,
            paket: null,
            tanggalMulai: @js(old('tanggal_mulai')),
            tanggalAkhir: @js(old('tanggal_akhir')),
        
            paketList: [
                { id: 1, nama: 'Paket Harian · 1 Hari', durasi: 1 },
                { id: 2, nama: 'Paket Mingguan · 7 Hari', durasi: 7 },
                { id: 3, nama: 'Paket Bulanan · 30 Hari', durasi: 30 },
                { id: 4, nama: 'Paket Trio (3 Orang) · 30 Hari', durasi: 30 },
            ],
        
            pilihPaket(id) {
                this.paket = id;
                const item = this.paketList.find(p => p.id == id);
                if (!item) {
                    this.tanggalMulai = null;
                    this.tanggalAkhir = null;
                    return;
                }
        
                const today = new Date();
                const end = new Date();
                end.setDate(today.getDate() + item.durasi);
        
                this.tanggalMulai = today.toISOString().split('T')[0];
                this.tanggalAkhir = end.toISOString().split('T')[0];
            }
        }">
        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
            <div>
                <h2 class="text-xl font-semibold text-text-main">Tambah Member</h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Buat akun member baru dan (opsional) set periode membership.
                </p>
            </div>
            <button type="button" class="p-1.5 rounded-full hover:bg-brand-surface-50 transition"
                @click="openCreate = false">
                <i data-lucide="x" class="w-5 h-5 text-text-muted"></i>
            </button>
        </div>

        <div class="px-6 pb-6 pt-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data"
                class="space-y-5">
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- PREVIEW FOTO --}}
                    <div class="md:col-span-1">
                        <div
                            class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                            <div
                                class="w-24 h-24 rounded-full border-2 border-dashed border-brand-borderSoft overflow-hidden flex items-center justify-center bg-brand-surface-50">
                                <img x-show="imageUrl" :src="imageUrl" alt="Preview Foto Member"
                                    class="object-cover w-full h-full">
                                <span x-show="!imageUrl" class="text-xs text-text-muted text-center p-2">
                                    Preview Foto
                                </span>
                            </div>
                            <p class="text-[11px] text-text-muted text-center">
                                Foto profil akan tampil di sini.
                            </p>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <div class="md:col-span-2">
                        <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="nama_create">Nama Member</x-ui.label>
                                    <input id="nama_create" type="text" name="nama" value="{{ old('nama') }}"
                                        required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="username_create">Username</x-ui.label>
                                    <input id="username_create" type="text" name="username"
                                        value="{{ old('username') }}" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="email_create">Email (opsional)</x-ui.label>
                                    <input id="email_create" type="email" name="email" value="{{ old('email') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                </div>

                                <div>
                                    <x-ui.label for="no_hp_create">Nomor HP (opsional)</x-ui.label>
                                    <input id="no_hp_create" type="text" name="no_hp" value="{{ old('no_hp') }}"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="jenis_kelamin_create">Jenis Kelamin (opsional)</x-ui.label>
                                    <select id="jenis_kelamin_create" name="jenis_kelamin"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                        <option value="">- Pilih -</option>
                                        <option value="laki-laki" @selected(old('jenis_kelamin') === 'laki-laki')>Laki-laki</option>
                                        <option value="perempuan" @selected(old('jenis_kelamin') === 'perempuan')>Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <x-ui.label for="password_create">Password</x-ui.label>
                                    <input id="password_create" type="password" name="password" required
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <x-ui.label for="password_confirmation_create">Konfirmasi Password</x-ui.label>
                                <input id="password_confirmation_create" type="password" name="password_confirmation"
                                    required
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                            </div>

                            <div>
                                <x-ui.label for="alamat_create">Alamat (opsional)</x-ui.label>
                                <textarea id="alamat_create" name="alamat" rows="2"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">{{ old('alamat') }}</textarea>
                            </div>

                            {{-- PAKET (opsional, hanya untuk auto-fill tanggal di UI) --}}
                            <div class="space-y-1">
                                <x-ui.label for="paket_membership">Paket Membership (opsional)</x-ui.label>
                                <select id="paket_membership"
                                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                    x-model="paket" @change="pilihPaket($event.target.value)">
                                    <option value="">Tidak Memilih Paket</option>
                                    <template x-for="p in paketList" :key="p.id">
                                        <option :value="p.id" x-text="p.nama"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-[11px] text-text-muted">
                                    Jika memilih paket, tanggal mulai & akhir akan terisi otomatis.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="tanggal_mulai_create">Tanggal Mulai (opsional)</x-ui.label>
                                    <input id="tanggal_mulai_create" type="date" name="tanggal_mulai"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        :value="tanggalMulai">
                                </div>
                                <div>
                                    <x-ui.label for="tanggal_akhir_create">Tanggal Akhir (opsional)</x-ui.label>
                                    <input id="tanggal_akhir_create" type="date" name="tanggal_akhir"
                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        :value="tanggalAkhir">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <x-ui.label for="foto_create">Foto Profil (opsional)</x-ui.label>
                                <input id="foto_create" type="file" name="foto" accept="image/*"
                                    class="block w-full text-sm text-text-main
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-gold-600 file:text-white
                                           hover:file:bg-gold-700"
                                    @change="
                                        const f = $event.target.files[0];
                                        if (f) {
                                            const reader = new FileReader();
                                            reader.onload = e => imageUrl = e.target.result;
                                            reader.readAsDataURL(f);
                                        } else {
                                            imageUrl = null;
                                        }
                                    ">
                                <p class="text-[11px] text-text-muted mt-1">
                                    Maksimal 2MB. Format: JPG/PNG.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <x-ui.button-secondary type="button" @click="openCreate = false">Batal</x-ui.button-secondary>
                    <x-ui.button-primary type="submit">Simpan Member</x-ui.button-primary>
                </div>
            </form>
        </div>
    </div>
</div>
