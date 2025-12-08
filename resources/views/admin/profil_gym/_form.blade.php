{{-- resources/views/admin/profil_gym/_form.blade.php --}}
@php
    /** @var \App\Models\ProfilGym|null $profil */
@endphp

<div class="space-y-6">
    {{-- INFORMASI UTAMA --}}
    <x-ui.card class="border-brand-borderSoft">
        <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-text-main">Informasi Utama</h3>
                <p class="text-xs text-text-muted mt-0.5">
                    Nama gym, deskripsi, dan alamat utama yang tampil di dashboard & aplikasi member.
                </p>
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            {{-- Nama --}}
            <div>
                <x-ui.label for="nama">Nama Gym</x-ui.label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $profil->nama ?? '') }}" required
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                              focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                @error('nama')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <x-ui.label for="deskripsi">Deskripsi Singkat</x-ui.label>
                <textarea id="deskripsi" name="deskripsi" rows="3"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                 focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                    placeholder="Contoh: Gym dengan fasilitas lengkap, fokus pada strength &amp; conditioning.">{{ old('deskripsi', $profil->deskripsi ?? '') }}</textarea>
                @error('deskripsi')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div>
                <x-ui.label for="lokasi">Alamat / Lokasi</x-ui.label>
                <textarea id="lokasi" name="lokasi" rows="3"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                 focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                    placeholder="Contoh: Jl. Contoh No. 123, Kecamatan X, Kota Y.">{{ old('lokasi', $profil->lokasi ?? '') }}</textarea>
                @error('lokasi')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-text-muted mt-1">
                    Alamat ini bisa dipakai di footer, halaman kontak, dan profil member.
                </p>
            </div>

            {{-- Jam Operasional --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-ui.label for="jam_buka">Jam Buka</x-ui.label>
                    <input type="time" id="jam_buka" name="jam_buka"
                        value="{{ old('jam_buka', $profil->jam_buka ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                    @error('jam_buka')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-ui.label for="jam_tutup">Jam Tutup</x-ui.label>
                    <input type="time" id="jam_tutup" name="jam_tutup"
                        value="{{ old('jam_tutup', $profil->jam_tutup ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent">
                    @error('jam_tutup')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- KONTAK & SOSMED --}}
    <x-ui.card class="border-brand-borderSoft">
        <div class="px-6 py-4 border-b border-brand-borderSoft">
            <h3 class="text-base font-semibold text-text-main">Kontak &amp; Sosial Media</h3>
            <p class="text-xs text-text-muted mt-0.5">
                Dipakai untuk tombol WhatsApp, email, dan link sosial media di berbagai halaman.
            </p>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- WhatsApp --}}
                <div>
                    <x-ui.label for="whatsapp">WhatsApp (No. dengan kode negara)</x-ui.label>
                    <input type="text" id="whatsapp" name="whatsapp"
                        value="{{ old('whatsapp', $profil->whatsapp ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="Contoh: 6281234567890">
                    @error('whatsapp')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-text-muted mt-1">
                        Isi hanya angka. Link resmi bisa dibentuk di frontend: <code>https://wa.me/nomor</code>.
                    </p>
                </div>

                {{-- Email --}}
                <div>
                    <x-ui.label for="email_kontak">Email Kontak</x-ui.label>
                    <input type="email" id="email_kontak" name="email_kontak"
                        value="{{ old('email_kontak', $profil->email_kontak ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="Contoh: info@betagym.com">
                    @error('email_kontak')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Instagram --}}
                <div>
                    <x-ui.label for="instagram">Instagram</x-ui.label>
                    <input type="text" id="instagram" name="instagram"
                        value="{{ old('instagram', $profil->instagram ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="https://instagram.com/betagym atau @betagym">
                    @error('instagram')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- TikTok --}}
                <div>
                    <x-ui.label for="tiktok">TikTok</x-ui.label>
                    <input type="text" id="tiktok" name="tiktok"
                        value="{{ old('tiktok', $profil->tiktok ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="https://www.tiktok.com/@betagym">
                    @error('tiktok')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- YouTube --}}
                <div>
                    <x-ui.label for="youtube">YouTube</x-ui.label>
                    <input type="text" id="youtube" name="youtube"
                        value="{{ old('youtube', $profil->youtube ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="https://www.youtube.com/@betagym">
                    @error('youtube')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Facebook --}}
                <div>
                    <x-ui.label for="facebook">Facebook</x-ui.label>
                    <input type="text" id="facebook" name="facebook"
                        value="{{ old('facebook', $profil->facebook ?? '') }}"
                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                                  focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                        placeholder="https://facebook.com/betagym">
                    @error('facebook')
                        <p class="text-xs text-danger mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Maps URL --}}
            <div>
                <x-ui.label for="maps_url">URL Google Maps</x-ui.label>
                <input type="text" id="maps_url" name="maps_url"
                    value="{{ old('maps_url', $profil->maps_url ?? '') }}"
                    class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2 border-brand-borderSoft
                              focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                    placeholder="https://maps.app.goo.gl/...">
                @error('maps_url')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-text-muted mt-1">
                    Dipakai untuk tombol “Lihat di Maps” di aplikasi web / mobile.
                </p>
            </div>
        </div>
    </x-ui.card>

    {{-- BRANDING (UPLOAD IMAGE) --}}
    <x-ui.card class="border-brand-borderSoft">
        <div class="px-6 py-4 border-b border-brand-borderSoft">
            <h3 class="text-base font-semibold text-text-main">Branding</h3>
            <p class="text-xs text-text-muted mt-0.5">
                Upload logo, favicon, dan hero image yang digunakan di layout utama.
            </p>
        </div>

        <div class="px-6 py-5 space-y-5">
            {{-- Logo --}}
            <div>
                <x-ui.label for="logo">Logo (gambar)</x-ui.label>
                <input type="file" id="logo" name="logo" accept="image/*"
                    class="block w-full text-sm text-text-main
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-gold-600 file:text-white
                              hover:file:bg-gold-700">
                @error('logo')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror

                @if (!empty($profil?->logo))
                    <div class="mt-3 flex items-center gap-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profil->logo) }}" alt="Logo BETA GYM"
                            class="w-20 h-20 object-contain rounded-lg border border-brand-borderSoft bg-brand-shell"
                            onerror="this.onerror=null; this.src='https://placehold.co/120x120/3A2D2A/F5E6D6?text=Logo';">
                        <p class="text-[11px] text-text-muted break-all">
                            {{ $profil->logo }}
                        </p>
                    </div>
                @endif

                <p class="text-[11px] text-text-muted mt-1">
                    Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.
                </p>
            </div>

            {{-- Favicon --}}
            <div>
                <x-ui.label for="favicon">Favicon (opsional)</x-ui.label>
                <input type="file" id="favicon" name="favicon" accept="image/*"
                    class="block w-full text-sm text-text-main
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-gold-600 file:text-white
                              hover:file:bg-gold-700">
                @error('favicon')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror

                @if (!empty($profil?->favicon))
                    <div class="mt-3 flex items-center gap-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profil->favicon) }}"
                            alt="Favicon BETA GYM"
                            class="w-10 h-10 object-contain rounded-md border border-brand-borderSoft bg-brand-shell"
                            onerror="this.onerror=null; this.src='https://placehold.co/40x40/3A2D2A/F5E6D6?text=F';">
                        <p class="text-[11px] text-text-muted break-all">
                            {{ $profil->favicon }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Hero Image --}}
            <div>
                <x-ui.label for="hero_image">Hero Image (opsional)</x-ui.label>
                <input type="file" id="hero_image" name="hero_image" accept="image/*"
                    class="block w-full text-sm text-text-main
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-gold-600 file:text-white
                              hover:file:bg-gold-700">
                @error('hero_image')
                    <p class="text-xs text-danger mt-1">{{ $message }}</p>
                @enderror

                @if (!empty($profil?->hero_image))
                    <div class="mt-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($profil->hero_image) }}"
                            alt="Hero Image BETA GYM"
                            class="w-full max-w-xl h-32 object-cover rounded-xl border border-brand-borderSoft bg-brand-shell"
                            onerror="this.onerror=null; this.src='https://placehold.co/600x200/3A2D2A/F5E6D6?text=Hero';">
                        <p class="mt-1 text-[11px] text-text-muted break-all">
                            {{ $profil->hero_image }}
                        </p>
                    </div>
                @endif

                <p class="text-[11px] text-text-muted mt-1">
                    Disarankan rasio landscape (misal 16:9). Maksimal 4MB.
                </p>
            </div>
        </div>
    </x-ui.card>
</div>
