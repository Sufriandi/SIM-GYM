{{-- resources/views/admin/profil_gym/edit.blade.php --}}

@php
    /** @var \App\Models\ProfilGym $profilGym */
    $pageTitle = $pageTitle ?? 'Edit Profil Gym';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Perbarui konfigurasi profil BETA GYM yang digunakan di dashboard dan aplikasi member.">

    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.section-header :title="$pageTitle"
        subtitle="Sesuaikan nama gym, jam operasional, kontak, sosial media, dan branding." />
    <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

    <form action="{{ route('admin.profil_gym.update', $profilGym) }}" method="POST" enctype="multipart/form-data"
        class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        @include('admin.profil_gym._form', ['profil' => $profilGym])

        <div class="flex items-center justify-between gap-2">
            <div class="text-[11px] text-text-muted">
                Terakhir diperbarui:
                {{ $profilGym->updated_at ? $profilGym->updated_at->format('d M Y H:i') : 'Belum pernah diperbarui' }}
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.profil_gym.index') }}">
                    <x-ui.button-secondary type="button">
                        Kembali
                    </x-ui.button-secondary>
                </a>
                <x-ui.button-primary type="submit">
                    Simpan Perubahan
                </x-ui.button-primary>
            </div>
        </div>
    </form>
</x-layouts.admin>
