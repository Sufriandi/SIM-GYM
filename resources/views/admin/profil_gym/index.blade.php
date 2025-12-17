{{-- resources/views/admin/profil_gym/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $pageTitle ?? 'Profil Gym';

    $isUpdate = !empty($profil?->id);

    $infoAction = $isUpdate
        ? route('admin.profil_gym.update', $profil->id)
        : route('admin.profil_gym.store');

    $kontakAction = $infoAction;

    $openInfoOnLoad = ($errors->any() && old('_section') === 'info')
        ? 'true'
        : ($isUpdate ? 'false' : 'true');

    $openKontakOnLoad = ($errors->any() && old('_section') === 'kontak')
        ? 'true'
        : ($isUpdate ? 'false' : 'true');
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Konfigurasi profil BETA GYM yang digunakan di seluruh sistem (dashboard, member, footer, dan lain-lain).">

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="mb-4 bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.section-header :title="$pageTitle"
        subtitle="Atur nama gym, jam operasional, sosial media, dan branding dari satu tempat." />
    <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

    <div class="mt-6"
        x-data="{
            editInfo: {{ $openInfoOnLoad }},
            editKontak: {{ $openKontakOnLoad }},
        }">

        {{-- =========================
            CARD 1: INFORMASI UTAMA + BRANDING
        ========================== --}}
        <x-ui.card class="border-brand-borderSoft">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-text-main">Informasi Utama</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Nama gym, deskripsi, alamat, jam operasional, serta branding (logo/favicon/hero).
                    </p>
                </div>

                {{-- EDIT --}}
                <button
                    type="button"
                    title="Edit Informasi Utama"
                    class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                    @click="editInfo = true"
                >
                    <i data-lucide="square-pen" class="w-5 h-5"></i>
                    <span
                        class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                               text-[10px] font-medium text-yellow-600
                               opacity-0 group-hover:opacity-100
                               transition-opacity duration-150"
                    >
                        Edit
                    </span>
                </button>
            </div>

            <form x-ref="infoForm" action="{{ $infoAction }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-6">
                @csrf
                @if ($isUpdate)
                    @method('PUT')
                @endif
                <input type="hidden" name="_section" value="info">

                {{-- INFORMASI UTAMA --}}
                <div class="space-y-4">
                    <div>
                        <x-ui.label for="nama">Nama Gym</x-ui.label>
                        <input :disabled="!editInfo" type="text" id="nama" name="nama"
                            value="{{ old('nama', $profil->nama ?? '') }}" required
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                        @error('nama')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="deskripsi">Deskripsi Singkat</x-ui.label>
                        <textarea :disabled="!editInfo" id="deskripsi" name="deskripsi" rows="3"
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   disabled:opacity-60 disabled:cursor-not-allowed"
                            placeholder="Contoh: Gym dengan fasilitas lengkap, fokus pada strength &amp; conditioning.">{{ old('deskripsi', $profil->deskripsi ?? '') }}</textarea>
                        @error('deskripsi')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="lokasi">Alamat / Lokasi</x-ui.label>
                        <textarea :disabled="!editInfo" id="lokasi" name="lokasi" rows="3"
                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                   focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                   disabled:opacity-60 disabled:cursor-not-allowed"
                            placeholder="Contoh: Jl. Contoh No. 123, Kecamatan X, Kota Y.">{{ old('lokasi', $profil->lokasi ?? '') }}</textarea>
                        @error('lokasi')
                            <p class="text-xs text-danger mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-text-muted mt-1">
                            Alamat ini bisa dipakai di footer, halaman kontak, dan profil member.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.label for="jam_buka">Jam Buka</x-ui.label>
                            <input :disabled="!editInfo" type="time" id="jam_buka" name="jam_buka"
                                value="{{ old('jam_buka', $profil->jam_buka ?? '') }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       disabled:opacity-60 disabled:cursor-not-allowed">
                            @error('jam_buka')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-ui.label for="jam_tutup">Jam Tutup</x-ui.label>
                            <input :disabled="!editInfo" type="time" id="jam_tutup" name="jam_tutup"
                                value="{{ old('jam_tutup', $profil->jam_tutup ?? '') }}"
                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                                       disabled:opacity-60 disabled:cursor-not-allowed">
                            @error('jam_tutup')
                                <p class="text-xs text-danger mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BRANDING --}}
<div class="pt-2 border-t border-brand-borderSoft/70"></div>

<div class="space-y-5">
    <div>
        <h4 class="text-sm font-semibold text-text-main">Branding</h4>
        <p class="text-xs text-text-muted mt-0.5">
            Upload logo, favicon, dan hero image yang digunakan di layout utama.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Logo (kiri) --}}
        <div>
            <x-ui.label for="logo">Logo (gambar)</x-ui.label>
            <input :disabled="!editInfo" type="file" id="logo" name="logo" accept="image/*"
                class="block w-full text-sm text-text-main
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-full file:border-0
                       file:text-sm file:font-semibold
                       file:bg-gold-600 file:text-white
                       hover:file:bg-gold-700
                       disabled:opacity-60 disabled:cursor-not-allowed">
            @error('logo')
                <p class="text-xs text-danger mt-1">{{ $message }}</p>
            @enderror

            @if (!empty($profil?->logo))
                <div class="mt-3 flex items-center gap-3">
                    <img src="{{ Storage::url($profil->logo) }}" alt="Logo BETA GYM"
                        class="w-20 h-20 object-contain rounded-lg border border-brand-borderSoft bg-brand-shell"
                        onerror="this.onerror=null; this.src='https://placehold.co/120x120/3A2D2A/F5E6D6?text=Logo';">
                    <p class="text-[11px] text-text-muted break-all">{{ $profil->logo }}</p>
                </div>
            @endif

            <p class="text-[11px] text-text-muted mt-1">
                Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.
            </p>
        </div>

        {{-- Favicon --}}
<div>
    <x-ui.label for="favicon">Favicon (opsional)</x-ui.label>
    <input :disabled="!editInfo" type="file" id="favicon" name="favicon" accept="image/*"
        class="block w-full text-sm text-text-main
               file:mr-4 file:py-2 file:px-4
               file:rounded-full file:border-0
               file:text-sm file:font-semibold
               file:bg-gold-600 file:text-white
               hover:file:bg-gold-700
               disabled:opacity-60 disabled:cursor-not-allowed">
    @error('favicon')
        <p class="text-xs text-danger mt-1">{{ $message }}</p>
    @enderror

    @if (!empty($profil?->favicon))
        <div class="mt-3 flex items-center gap-3">
            <img src="{{ Storage::url($profil->favicon) }}" alt="Favicon BETA GYM"
                class="w-10 h-10 object-contain rounded-md border border-brand-borderSoft bg-brand-shell"
                onerror="this.onerror=null; this.src='https://placehold.co/40x40/3A2D2A/F5E6D6?text=F';">
            <p class="text-[11px] text-text-muted break-all">{{ $profil->favicon }}</p>
        </div>
    @endif

    {{-- TEKS KECIL (dikembalikan) --}}
    <p class="text-[11px] text-text-muted mt-1">
        Format: JPG, JPEG, PNG, WEBP. Maksimal 1MB.
    </p>
</div>

        {{-- Hero Image (bawah, full width) --}}
        <div class="md:col-span-2">
            <x-ui.label for="hero_image">Hero Image (opsional)</x-ui.label>
            <input :disabled="!editInfo" type="file" id="hero_image" name="hero_image" accept="image/*"
                class="block w-full text-sm text-text-main
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-full file:border-0
                       file:text-sm file:font-semibold
                       file:bg-gold-600 file:text-white
                       hover:file:bg-gold-700
                       disabled:opacity-60 disabled:cursor-not-allowed">
            @error('hero_image')
                <p class="text-xs text-danger mt-1">{{ $message }}</p>
            @enderror

            @if (!empty($profil?->hero_image))
                <div class="mt-3">
                    <img src="{{ Storage::url($profil->hero_image) }}" alt="Hero Image BETA GYM"
                        class="w-full max-w-xl h-32 object-cover rounded-xl border border-brand-borderSoft bg-brand-shell"
                        onerror="this.onerror=null; this.src='https://placehold.co/600x200/3A2D2A/F5E6D6?text=Hero';">
                    <p class="mt-1 text-[11px] text-text-muted break-all">{{ $profil->hero_image }}</p>
                </div>
            @endif

            <p class="text-[11px] text-text-muted mt-1">
                Disarankan rasio landscape (misal 16:9). Maksimal 4MB.
            </p>
        </div>
    </div>
</div>


                {{-- ACTIONS --}}
<div class="pt-4 flex items-center justify-end gap-2"
     x-show="editInfo" x-cloak x-transition.opacity>
    <x-ui.button-secondary type="button"
        @click="editInfo = false; $refs.infoForm.reset()">
        Batal
    </x-ui.button-secondary>

    <x-ui.button-primary type="submit">
        Simpan Perubahan
    </x-ui.button-primary>
</div>

            </form>
        </x-ui.card>

        {{-- =========================
    CARD 2: KONTAK & SOSMED
========================== --}}
<div class="mt-6"></div>

<x-ui.card class="border-brand-borderSoft">
    <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between gap-3">
        <div>
            <h3 class="text-xl font-bold text-text-main">Kontak & Sosial Media</h3>
            <p class="text-xs text-text-muted mt-0.5">
                Dipakai untuk tombol WhatsApp, email, dan link sosial media di berbagai halaman.
            </p>
        </div>

        {{-- EDIT --}}
        <button
            type="button"
            title="Edit Kontak & Sosial Media"
            class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
            @click="editKontak = true"
        >
            <i data-lucide="square-pen" class="w-5 h-5"></i>
            <span
                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                       text-[10px] font-medium text-yellow-600
                       opacity-0 group-hover:opacity-100
                       transition-opacity duration-150"
            >
                Edit
            </span>
        </button>
    </div>

    <form x-ref="kontakForm" action="{{ $kontakAction }}" method="POST" class="px-6 py-5 space-y-4">
        @csrf
        @if ($isUpdate)
            @method('PUT')
        @endif
        <input type="hidden" name="_section" value="kontak">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-ui.label for="whatsapp">WhatsApp (No. dengan kode negara)</x-ui.label>
                <input :disabled="!editKontak" type="text" id="whatsapp" name="whatsapp"
                    value="{{ old('whatsapp', $profil->whatsapp ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: 62812xxxxxxxx">
                @error('whatsapp')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-text-muted mt-1">
                    Isi hanya angka. Link resmi bisa dibentuk di frontend: <code>https://wa.me/nomor</code>.
                </p>
            </div>

            <div>
                <x-ui.label for="email_kontak">Email Kontak</x-ui.label>
                <input :disabled="!editKontak" type="email" id="email_kontak" name="email_kontak"
                    value="{{ old('email_kontak', $profil->email_kontak ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: admin@gym.com">
                @error('email_kontak')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-ui.label for="instagram">Instagram</x-ui.label>
                <input :disabled="!editKontak" type="text" id="instagram" name="instagram"
                    value="{{ old('instagram', $profil->instagram ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: https://instagram.com/gym atau @gym">
                @error('instagram')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-ui.label for="tiktok">TikTok</x-ui.label>
                <input :disabled="!editKontak" type="text" id="tiktok" name="tiktok"
                    value="{{ old('tiktok', $profil->tiktok ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: https://www.tiktok.com/@gym">
                @error('tiktok')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-ui.label for="youtube">YouTube</x-ui.label>
                <input :disabled="!editKontak" type="text" id="youtube" name="youtube"
                    value="{{ old('youtube', $profil->youtube ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: https://www.youtube.com/@gym">
                @error('youtube')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-ui.label for="facebook">Facebook</x-ui.label>
                <input :disabled="!editKontak" type="text" id="facebook" name="facebook"
                    value="{{ old('facebook', $profil->facebook ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                           focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                           disabled:opacity-60 disabled:cursor-not-allowed"
                    placeholder="Contoh: https://facebook.com/gym">
                @error('facebook')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <x-ui.label for="maps_url">URL Google Maps</x-ui.label>
            <input :disabled="!editKontak" type="text" id="maps_url" name="maps_url"
                value="{{ old('maps_url', $profil->maps_url ?? '') }}"
                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                       focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent
                       disabled:opacity-60 disabled:cursor-not-allowed"
                placeholder="Contoh: https://maps.app.goo.gl/xxxxxxx">
            @error('maps_url')
                <p class="text-xs text-danger mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-text-muted mt-1">
                Dipakai untuk tombol “Lihat di Maps” di aplikasi web / mobile.
            </p>
        </div>

        {{-- ACTIONS --}}
        <div class="pt-4 flex items-center justify-end gap-2"
             x-show="editKontak" x-cloak x-transition.opacity>
            <x-ui.button-secondary type="button"
                @click="editKontak = false; $refs.kontakForm.reset()">
                Batal
            </x-ui.button-secondary>

            <x-ui.button-primary type="submit">
                Simpan Perubahan
            </x-ui.button-primary>
        </div>
    </form>
</x-ui.card>


        <style>
            [x-cloak] { display: none !important; }
        </style>
    </div>
</x-layouts.admin>
