{{-- resources/views/profile/edit.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    /** @var \App\Models\User $user */
    $user = $user ?? auth()->user();
    $pageTitle = $pageTitle ?? 'Pengaturan Profil';

    // Tentukan route name yang dipakai (admin / umum)
    $isAdminRoute = request()->routeIs('admin.*');
    $routeUpdate = $isAdminRoute ? 'admin.profile.update' : 'profile.update';
    $routePassword = $isAdminRoute ? 'admin.profile.password' : 'profile.password';

    // Foto & inisial
    $fotoUrl = !empty($user?->foto) ? Storage::url($user->foto) : null;
    $initials = Str::of($user?->name ?: 'User')
        ->trim()
        ->explode(' ')
        ->map(fn($p) => Str::upper(Str::substr($p, 0, 1)))
        ->take(2)
        ->join('');

    // Tab awal
    $tabFromQuery = request('tab', 'profile');
    $openSecurityOnLoad =
        session()->has('password_success') ||
        ($errors->hasBag('password') && $errors->password->any()) ||
        $tabFromQuery === 'security';

    // Input base (jelas di dark, tidak “abu pudar”)
    $inputBase = "w-full rounded-xl border text-sm text-text-main px-4 py-3
                  bg-brand-cardSoft
                  border-brand-borderSoft
                  shadow-inner
                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                  transition-all duration-200";

    // NOTE: kalau kamu pakai cssVar di tailwind (yang kamu bilang “bisa”), bg-brand-cardSoft otomatis berubah saat .dark.
    $helpText = 'text-[11px] text-gray-500 dark:text-text-muted mt-1';

    // Mini card kiri yang rapi
    $miniCard = "rounded-2xl border border-brand-borderSoft bg-brand-surface-50 shadow-sm
                 ring-1 ring-brand-borderSoft/40";
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Kelola data profil dan keamanan akun Anda.">
    <div x-data="{
        tab: '{{ $openSecurityOnLoad ? 'security' : 'profile' }}',
        photoPreview: null,
        photoName: '',
    
        pickTab(name) {
            this.tab = name;
            const url = new URL(window.location);
            name === 'security' ? url.searchParams.set('tab', name) : url.searchParams.delete('tab');
            window.history.pushState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    
        onPickPhoto(e) {
            const f = e.target.files?.[0];
            if (!f) { this.photoPreview = null;
                this.photoName = ''; return; }
            this.photoName = f.name;
            const r = new FileReader();
            r.onload = (ev) => this.photoPreview = ev.target.result;
            r.readAsDataURL(f);
        },
    }" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- =======================
                 PANEL KIRI: RINGKASAN
               ======================= --}}
            <x-ui.card class="lg:col-span-4 h-fit !p-0 overflow-hidden">
                {{-- IMPORTANT: jangan pakai bg-white/.. agar tidak “dibalut putih” di dark --}}
                <div class="relative p-6 bg-brand-card">
                    {{-- Light overlay (halus) --}}
                    <div aria-hidden="true"
                        class="pointer-events-none absolute inset-0
                                bg-[linear-gradient(145deg,rgba(255,255,255,0.70),rgba(245,230,214,0.35))]
                                dark:opacity-0">
                    </div>

                    {{-- Dark overlay (radial gold tipis biar tidak flat & tidak gelap total) --}}
                    <div aria-hidden="true"
                        class="pointer-events-none absolute inset-0 opacity-0 dark:opacity-100
                                bg-[radial-gradient(circle_at_top,rgba(212,167,87,0.16),transparent_60%)]">
                    </div>

                    <div class="relative">
                        {{-- Avatar --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-4 group cursor-pointer" @click="$refs.fileInput.click()"
                                title="Klik untuk mengganti foto">
                                <div
                                    class="w-28 h-28 rounded-full overflow-hidden
                                            border-4 border-brand-card
                                            ring-4 ring-primary-dark/35
                                            bg-brand-surface-50
                                            flex items-center justify-center
                                            shadow-lg transition-all group-hover:ring-primary-dark/60">
                                    <img x-show="photoPreview" :src="photoPreview" class="w-full h-full object-cover"
                                        alt="Preview Foto">
                                    @if ($fotoUrl)
                                        <img x-show="!photoPreview" src="{{ $fotoUrl }}"
                                            class="w-full h-full object-cover" alt="Foto Profil">
                                    @else
                                        <span x-show="!photoPreview"
                                            class="text-3xl font-extrabold text-primary-dark">{{ $initials }}</span>
                                    @endif
                                </div>

                                {{-- Overlay hover --}}
                                <div
                                    class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center
                                            opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>

                                <span
                                    class="absolute -bottom-1 -right-1 rounded-full px-2 py-0.5 text-[10px] font-bold shadow-md
                                    {{ $user?->email_verified_at ? 'bg-success text-white' : 'bg-warning text-gray-900' }}">
                                    {{ $user?->email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>

                            <div class="min-w-0">
                                <div class="text-xl font-extrabold text-text-main truncate">{{ $user?->name }}</div>
                                <div class="text-sm text-text-muted truncate mt-0.5">{{ $user?->email }}</div>
                            </div>

                            <div class="mt-3 flex flex-wrap justify-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full bg-primary-dark px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                                    </svg>
                                    {{ $user?->role ? Str::title($user->role) : 'User' }}
                                </span>

                                @if (!empty($user?->username))
                                    <span
                                        class="inline-flex items-center rounded-full bg-brand-surface-100 px-3 py-1 text-xs text-text-muted">
                                        <span class="font-mono">{{ '@' . $user->username }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="my-6 border-t border-brand-borderSoft"></div>

                        {{--
                            INFO RINGKAS (versi umum/normal)
                            Sebelumnya ini 3 "mini card" terpisah dengan ikon di grid 2 kolom,
                            keliatan ramai/berantakan buat data yang isinya cuma teks pendek.
                            Diganti jadi satu list sederhana: label kecil di atas, nilai di
                            bawah, dipisah garis tipis - pola umum yang biasa dipakai di
                            halaman profil pada umumnya.
                        --}}
                        <dl class="divide-y divide-brand-borderSoft">
                            <div class="py-3 first:pt-0">
                                <dt class="text-xs font-semibold text-text-muted uppercase tracking-wide">No. HP</dt>
                                <dd class="mt-1 text-sm font-semibold text-text-main">
                                    {{ $user?->no_hp ?: '-' }}
                                </dd>
                            </div>

                            <div class="py-3">
                                <dt class="text-xs font-semibold text-text-muted uppercase tracking-wide">Jenis Kelamin
                                </dt>
                                <dd class="mt-1 text-sm font-semibold text-text-main">
                                    {{ $user?->jenis_kelamin ? Str::title(str_replace('-', ' ', $user->jenis_kelamin)) : '-' }}
                                </dd>
                            </div>

                            <div class="py-3 last:pb-0">
                                <dt class="text-xs font-semibold text-text-muted uppercase tracking-wide">Alamat</dt>
                                <dd class="mt-1 text-sm text-text-main leading-relaxed">
                                    {{ $user?->alamat ?: '-' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 pt-4 border-t border-brand-borderSoft flex justify-end">
                            <button type="button" @click="pickTab('security')"
                                class="text-xs font-semibold text-primary-dark hover:underline">
                                Ganti Password?
                            </button>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- =======================
                 PANEL KANAN: FORM
               ======================= --}}
            <x-ui.card class="lg:col-span-8 !p-0 overflow-hidden dark:!bg-brand-cardSoft">
                <div class="relative">

                    {{-- Tambahan supaya dark tidak “gelap total”: highlight + soft vignette --}}
                    <div aria-hidden="true"
                        class="pointer-events-none absolute inset-0 opacity-0 dark:opacity-100
                                bg-[radial-gradient(circle_at_30%_0%,rgba(212,167,87,0.18),transparent_60%)]">
                    </div>
                    <div aria-hidden="true"
                        class="pointer-events-none absolute inset-0 opacity-0 dark:opacity-100
                                bg-[linear-gradient(180deg,rgba(255,255,255,0.02),transparent_35%)]">
                    </div>

                    {{-- Header --}}
                    <div class="relative px-6 pt-6 pb-4 border-b border-brand-borderSoft bg-brand-card">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-2xl font-bold text-text-main">Pengaturan Akun</h3>
                                <p class="text-sm text-text-muted mt-1">Perbarui data profil atau ubah kata sandi.</p>
                            </div>

                            {{-- Switch tab --}}
                            <div
                                class="flex items-center gap-1 p-1 rounded-full
                                        bg-brand-surface-50 border border-brand-borderSoft">
                                <button type="button" @click="pickTab('profile')"
                                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 flex items-center gap-2"
                                    :class="tab === 'profile'
                                        ?
                                        'bg-primary-dark text-white shadow-md' :
                                        'bg-transparent text-text-muted hover:text-primary-dark'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    Profil
                                </button>

                                <button type="button" @click="pickTab('security')"
                                    class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 flex items-center gap-2"
                                    :class="tab === 'security'
                                        ?
                                        'bg-primary-dark text-white shadow-md' :
                                        'bg-transparent text-text-muted hover:text-primary-dark'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v3h8z">
                                        </path>
                                    </svg>
                                    Keamanan
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: PROFIL --}}
                    <div x-show="tab==='profile'" x-cloak class="relative p-6">
                        <div class="mb-5 border-l-4 border-gold-600 pl-3">
                            <h4 class="text-lg font-bold text-text-main">Data Diri</h4>
                            <p class="text-xs text-text-muted mt-0.5">Pastikan data yang Anda masukkan valid.</p>
                        </div>

                        <form method="POST" action="{{ route($routeUpdate) }}" enctype="multipart/form-data"
                            class="space-y-6">
                            @csrf
                            @method('PUT')

                            <input x-ref="fileInput" type="file" name="foto" accept="image/*" class="hidden"
                                @change="onPickPhoto($event)">

                            @error('foto', 'profile')
                                <div class="rounded-md bg-danger-soft/20 p-3 text-sm text-danger border border-danger/30">
                                    <strong>Error Foto:</strong> {{ $message }}
                                </div>
                            @enderror

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-5">
                                    <div>
                                        <x-ui.label for="name">Nama Lengkap</x-ui.label>
                                        <input id="name" name="name" type="text"
                                            value="{{ old('name', $user?->name) }}"
                                            class="{{ $inputBase }} @error('name', 'profile') border-danger ring-danger-soft @enderror"
                                            required>
                                        @error('name', 'profile')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label for="email">Email</x-ui.label>
                                        <input id="email" name="email" type="email"
                                            value="{{ old('email', $user?->email) }}"
                                            class="{{ $inputBase }} @error('email', 'profile') border-danger ring-danger-soft @enderror"
                                            required>
                                        @error('email', 'profile')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-ui.label for="no_hp">No. HP</x-ui.label>
                                        <input id="no_hp" name="no_hp" type="text"
                                            value="{{ old('no_hp', $user?->no_hp) }}" placeholder="cth: 0812..."
                                            class="{{ $inputBase }} @error('no_hp', 'profile') border-danger ring-danger-soft @enderror">
                                        @error('no_hp', 'profile')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <x-ui.label for="username">Username</x-ui.label>
                                        <input id="username" name="username" type="text"
                                            value="{{ old('username', $user?->username) }}"
                                            class="{{ $inputBase }} @error('username', 'profile') border-danger ring-danger-soft @enderror"
                                            readonly>
                                        @error('username', 'profile')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                        <p class="{{ $helpText }}">Digunakan untuk login alternatif.</p>
                                    </div>

                                    <div>
                                        <x-ui.label for="jenis_kelamin">Jenis Kelamin</x-ui.label>
                                        <select id="jenis_kelamin" name="jenis_kelamin"
                                            class="{{ $inputBase }} @error('jenis_kelamin', 'profile') border-danger ring-danger-soft @enderror">
                                            <option value="">- Pilih -</option>
                                            <option value="laki-laki"
                                                {{ old('jenis_kelamin', $user?->jenis_kelamin) === 'laki-laki' ? 'selected' : '' }}>
                                                Laki-laki</option>
                                            <option value="perempuan"
                                                {{ old('jenis_kelamin', $user?->jenis_kelamin) === 'perempuan' ? 'selected' : '' }}>
                                                Perempuan</option>
                                        </select>
                                        @error('jenis_kelamin', 'profile')
                                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <x-ui.label for="alamat">Alamat Lengkap</x-ui.label>
                                <textarea id="alamat" name="alamat" rows="3"
                                    class="{{ $inputBase }} @error('alamat', 'profile') border-danger ring-danger-soft @enderror"
                                    placeholder="Alamat lengkap...">{{ old('alamat', $user?->alamat) }}</textarea>
                                @error('alamat', 'profile')
                                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-brand-borderSoft">
                                <x-ui.button-primary type="submit" class="!shadow-lg !shadow-primary-dark/30">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Perubahan
                                </x-ui.button-primary>
                            </div>
                        </form>
                    </div>

                    {{-- TAB: SECURITY --}}
                    <div x-show="tab==='security'" x-cloak class="relative p-6">
                        <div class="mb-5 border-l-4 border-gold-600 pl-3">
                            <h4 class="text-lg font-bold text-text-main">Ubah Kata Sandi</h4>
                            <p class="text-xs text-text-muted mt-0.5">Disarankan kombinasi huruf & angka minimal 8
                                karakter.</p>
                        </div>

                        <x-ui.card class="!p-0 overflow-hidden">
                            <div class="p-6">
                                <form method="POST" action="{{ route($routePassword) }}" class="space-y-5">
                                    @csrf
                                    @method('PUT')

                                    <div class="flex flex-col gap-5">
                                        <div>
                                            <x-ui.label for="current_password">Password Saat Ini</x-ui.label>
                                            <input id="current_password" name="current_password" type="password"
                                                autocomplete="current-password"
                                                class="{{ $inputBase }} @error('current_password', 'password') border-danger ring-danger-soft @enderror"
                                                required>
                                            @error('current_password', 'password')
                                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-ui.label for="password">Password Baru</x-ui.label>
                                            <input id="password" name="password" type="password"
                                                autocomplete="new-password"
                                                class="{{ $inputBase }} @error('password', 'password') border-danger ring-danger-soft @enderror"
                                                required>
                                            @error('password', 'password')
                                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <x-ui.label for="password_confirmation">Konfirmasi Password</x-ui.label>
                                            <input id="password_confirmation" name="password_confirmation"
                                                type="password" autocomplete="new-password"
                                                class="{{ $inputBase }}" required>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex items-center justify-end border-t border-brand-borderSoft">
                                        <x-ui.button-primary type="submit"
                                            class="!bg-gray-800 hover:!bg-gray-900 !shadow-lg !shadow-gray-800/30">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v3h8z">
                                                </path>
                                            </svg>
                                            Update Password
                                        </x-ui.button-primary>
                                    </div>
                                </form>
                            </div>
                        </x-ui.card>
                    </div>

                    <style>
                        [x-cloak] {
                            display: none !important;
                        }
                    </style>
                </div>
            </x-ui.card>

        </div>
    </div>
</x-layouts.admin>
