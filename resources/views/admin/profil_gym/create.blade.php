{{-- resources/views/admin/profil_gym/create.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Buat Profil Gym';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Buat konfigurasi awal profil BETA GYM agar bisa digunakan di seluruh sistem.">

    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <x-ui.section-header :title="$pageTitle" subtitle="Isi informasi dasar, kontak, sosial media, dan branding gym." />
    <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

    <form action="{{ route('admin.profil_gym.store') }}" method="POST" enctype="multipart/form-data"
        class="mt-6 space-y-6">
        @csrf

        @include('admin.profil_gym._form', ['profil' => null])

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.profil_gym.index') }}">
                <x-ui.button-secondary type="button">
                    Batal
                </x-ui.button-secondary>
            </a>
            <x-ui.button-primary type="submit">
                Simpan Profil Gym
            </x-ui.button-primary>
        </div>
    </form>
</x-layouts.admin>
