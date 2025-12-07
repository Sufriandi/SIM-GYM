{{-- resources/views/admin/profil_gym/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;

    $pageTitle = $pageTitle ?? 'Profil Gym';
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

    <div class="mt-6">
        <x-ui.card class="border-brand-borderSoft">
            @if ($profil)
                {{-- Header card ketika data ada --}}
                <div
                    class="px-6 py-4 border-b border-brand-borderSoft flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-text-main">
                            {{ $profil->nama }}
                        </h3>
                        <p class="text-xs text-text-muted mt-0.5">
                            Profil ini aktif dan digunakan di seluruh tampilan aplikasi.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Tombol edit --}}
                        <a href="{{ route('admin.profil_gym.edit', $profil->id) }}">
                            <x-ui.button-primary type="button">
                                <i data-lucide="square-pen" class="w-5 h-5 mr-1"></i>
                                Edit Profil Gym
                            </x-ui.button-primary>
                        </a>
                    </div>
                </div>

                {{-- Body card --}}
                <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Info utama --}}
                    <div class="lg:col-span-2 space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-text-main mb-1">Deskripsi</h4>
                            <p class="text-sm text-text-muted">
                                {{ $profil->deskripsi ?: 'Belum ada deskripsi.' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-semibold text-text-main mb-1">Jam Operasional</h4>
                                <p class="text-sm text-text-muted">
                                    @if ($profil->jam_buka || $profil->jam_tutup)
                                        {{ $profil->jam_buka ? \Carbon\Carbon::parse($profil->jam_buka)->format('H:i') : '??:??' }}
                                        –
                                        {{ $profil->jam_tutup ? \Carbon\Carbon::parse($profil->jam_tutup)->format('H:i') : '??:??' }}
                                    @else
                                        Belum diatur.
                                    @endif
                                </p>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-text-main mb-1">Kontak</h4>
                                <p class="text-sm text-text-muted">
                                    WhatsApp:
                                    @if ($profil->whatsapp)
                                        <span class="font-semibold text-text-main">{{ $profil->whatsapp }}</span>
                                    @else
                                        <span class="italic">Belum diatur</span>
                                    @endif
                                    <br>
                                    Email:
                                    @if ($profil->email_kontak)
                                        <span class="font-semibold text-text-main">{{ $profil->email_kontak }}</span>
                                    @else
                                        <span class="italic">Belum diatur</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-text-main mb-1">Alamat / Lokasi</h4>
                            <p class="text-sm text-text-muted whitespace-pre-line">
                                {{ $profil->lokasi ?: 'Belum diatur.' }}
                            </p>
                            @if ($profil->maps_url)
                                <div class="mt-2">
                                    <a href="{{ $profil->maps_url }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center text-xs text-primary-dark hover:underline">
                                        <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i>
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Panel kanan: logo & sosmed --}}
                    <div class="space-y-4">
                        <div class="border border-brand-borderSoft rounded-2xl p-4 bg-brand-shell">
                            <h4 class="text-sm font-semibold text-text-main mb-3">Preview Branding</h4>

                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-16 h-16 rounded-xl border border-brand-borderSoft bg-brand-card flex items-center justify-center overflow-hidden">
                                    @if ($profil->logo)
                                        <img src="{{ Storage::url($profil->logo) }}" alt="Logo"
                                            class="w-full h-full object-contain"
                                            onerror="this.onerror=null; this.src='https://placehold.co/120x120/3A2D2A/F5E6D6?text=Logo';">
                                    @else
                                        <span class="text-[11px] text-text-muted text-center px-2">
                                            Belum ada logo
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-text-muted mb-1">Logo utama</p>
                                    <p class="text-[11px] text-text-muted break-all">
                                        {{ $profil->logo ?: '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-[11px] text-text-muted space-y-1">
                                <div>
                                    <span class="font-semibold text-text-main">Favicon:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->favicon ?: 'Belum diatur' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-text-main">Hero Image:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->hero_image ?: 'Belum diatur' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border border-brand-borderSoft rounded-2xl p-4 bg-brand-shell">
                            <h4 class="text-sm font-semibold text-text-main mb-3">Sosial Media</h4>
                            <ul class="space-y-1 text-xs text-text-muted">
                                <li>
                                    <span class="font-semibold text-text-main">Instagram:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->instagram ?: 'Belum diatur' }}</span>
                                </li>
                                <li>
                                    <span class="font-semibold text-text-main">TikTok:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->tiktok ?: 'Belum diatur' }}</span>
                                </li>
                                <li>
                                    <span class="font-semibold text-text-main">YouTube:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->youtube ?: 'Belum diatur' }}</span>
                                </li>
                                <li>
                                    <span class="font-semibold text-text-main">Facebook:</span>
                                    <br>
                                    <span class="break-all">{{ $profil->facebook ?: 'Belum diatur' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                {{-- Kalau belum ada data profil --}}
                <div class="px-6 py-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-text-main">Belum ada Profil Gym</h3>
                        <p class="text-sm text-text-muted mt-1 max-w-xl">
                            Konfigurasi profil gym belum dibuat. Silakan buat terlebih dahulu agar logo, kontak, dan
                            informasi
                            gym bisa digunakan di dashboard dan aplikasi member.
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.profil_gym.create') }}">
                            <x-ui.button-primary type="button">
                                <i data-lucide="plus" class="w-5 h-5 mr-1"></i>
                                Buat Profil Gym
                            </x-ui.button-primary>
                        </a>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-layouts.admin>
