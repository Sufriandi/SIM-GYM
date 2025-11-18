{{-- resources/views/admin/coach/index.blade.php --}}

@php
    $pageTitle = $pageTitle ?? 'Manajemen Coach';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Kelola data coach yang terdaftar di BETA GYM."
>
    {{-- STATE UTAMA UNTUK MODAL CREATE --}}
    <div x-data="{ openCreate: false }">

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar coach aktif dan pengelolaan datanya."
        >
            <x-ui.button-primary @click="openCreate = true">
                + Tambah Coach
            </x-ui.button-primary>
        </x-ui.section-header>

        {{-- CARD TABEL COACH --}}
        <x-ui.card
            title="Daftar Coach"
            subtitle="Semua coach yang terdaftar dalam sistem."
            class="border-brand-borderSoft"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Foto
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Nama
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                No. HP
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Alamat
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Deskripsi
                            </th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($coaches as $coach)
                            {{-- STATE KHUSUS UNTUK MODAL EDIT PER-BARIS --}}
                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ openEdit: false }"
                            >
                                {{-- FOTO --}}
                                <td class="p-3 align-top">
                                    @if ($coach->foto)
                                        <img
                                            src="{{ Storage::url($coach->foto) }}"
                                            alt="Foto {{ $coach->nama }}"
                                            class="w-12 h-12 rounded-full object-cover border border-brand-borderSoft shadow-sm"
                                        >
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-full bg-brand-surface-50 border border-dashed border-brand-borderSoft flex items-center justify-center text-[10px] text-text-muted">
                                            No Foto
                                        </div>
                                    @endif
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-top">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ $coach->nama }}
                                    </div>
                                </td>

                                {{-- NO HP --}}
                                <td class="p-3 align-top">
                                    <div class="text-sm text-text-main">
                                        {{ $coach->no_hp }}
                                    </div>
                                </td>

                                {{-- ALAMAT --}}
                                <td class="p-3 align-top">
                                    <div class="text-xs text-text-muted max-w-xs">
                                        {{ \Illuminate\Support\Str::limit($coach->alamat, 80) }}
                                    </div>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-top">
                                    <div class="text-xs text-text-muted max-w-xs">
                                        {{ $coach->deskripsi ? \Illuminate\Support\Str::limit($coach->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-top">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- EDIT --}}
                                        <x-ui.button-secondary
                                            class="px-3 py-1.5 text-[11px]"
                                            @click="openEdit = true"
                                        >
                                            Edit
                                        </x-ui.button-secondary>

                                        {{-- HAPUS --}}
                                        <form
                                            id="delete-coach-{{ $coach->id }}"
                                            action="{{ route('admin.coaches.destroy', $coach) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button-secondary
                                                type="button"
                                                class="px-3 py-1.5 text-[11px] bg-danger-soft text-danger hover:bg-danger-soft/80"
                                                onclick="confirmDeleteCoach({{ $coach->id }}, '{{ $coach->nama }}')"
                                            >
                                                Hapus
                                            </x-ui.button-secondary>
                                        </form>
                                    </div>

                                    {{-- ======================= --}}
                                    {{-- MODAL EDIT DATA COACH   --}}
                                    {{-- ======================= --}}
                                    <div
                                        x-show="openEdit"
                                        x-cloak
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
                                    >
                                        <div
                                            @click.away="openEdit = false"
                                            class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
                                        >
                                            {{-- HEADER MODAL --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                                                <div>
                                                    {{-- Judul besar --}}
                                                    <h2 class="text-xl font-semibold text-text-main">
                                                        Edit Coach
                                                    </h2>
                                                    {{-- Nama coach kecil & pudar --}}
                                                    <p class="text-sm text-text-muted mt-0.5">
                                                        {{ $coach->nama }}
                                                    </p>
                                                </div>
                                                <button type="button"
                                                        class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                                                        @click="openEdit = false">
                                                    <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                                                </button>
                                            </div>

                                            {{-- ISI MODAL --}}
                                            <div class="px-6 pb-6 pt-4">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.coaches.update', $coach) }}"
                                                    enctype="multipart/form-data"
                                                    class="space-y-5"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        {{-- PANEL KIRI: FOTO + INFO SINGKAT --}}
                                                        <div class="md:col-span-1">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                                                @if ($coach->foto)
                                                                    <img
                                                                        src="{{ Storage::url($coach->foto) }}"
                                                                        alt="Foto {{ $coach->nama }}"
                                                                        class="w-24 h-24 rounded-full object-cover border-2 border-gold-500 shadow-md"
                                                                    >
                                                                @else
                                                                    <div
                                                                        class="w-24 h-24 rounded-full border-2 border-dashed border-brand-borderSoft flex items-center justify-center text-xs text-text-muted bg-brand-surface-50">
                                                                        No Foto
                                                                    </div>
                                                                @endif

                                                                <div class="text-center">
                                                                    <p class="text-sm font-semibold text-text-main">
                                                                        {{ $coach->nama }}
                                                                    </p>
                                                                    <p class="text-[11px] text-text-muted">
                                                                        {{ $coach->no_hp }}
                                                                    </p>
                                                                </div>

                                                                <p class="text-[11px] text-text-muted text-center">
                                                                    Perbarui data profil coach di form sebelah kanan.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- PANEL KANAN: FORM --}}
                                                        <div class="md:col-span-2">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    {{-- NAMA --}}
                                                                    <div>
                                                                        <x-ui.label for="nama_{{ $coach->id }}">Nama Coach</x-ui.label>
                                                                        <input
                                                                            type="text"
                                                                            id="nama_{{ $coach->id }}"
                                                                            name="nama"
                                                                            value="{{ old('nama', $coach->nama) }}"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                    </div>

                                                                    {{-- NO HP --}}
                                                                    <div>
                                                                        <x-ui.label for="no_hp_{{ $coach->id }}">Nomor HP</x-ui.label>
                                                                        <input
                                                                            type="text"
                                                                            id="no_hp_{{ $coach->id }}"
                                                                            name="no_hp"
                                                                            value="{{ old('no_hp', $coach->no_hp) }}"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                    </div>
                                                                </div>

                                                                {{-- ALAMAT --}}
                                                                <div>
                                                                    <x-ui.label for="alamat_{{ $coach->id }}">Alamat</x-ui.label>
                                                                    <textarea
                                                                        id="alamat_{{ $coach->id }}"
                                                                        name="alamat"
                                                                        rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                        required
                                                                    >{{ old('alamat', $coach->alamat) }}</textarea>
                                                                </div>

                                                                {{-- DESKRIPSI --}}
                                                                <div>
                                                                    <x-ui.label for="deskripsi_{{ $coach->id }}">Deskripsi / Keahlian</x-ui.label>
                                                                    <textarea
                                                                        id="deskripsi_{{ $coach->id }}"
                                                                        name="deskripsi"
                                                                        rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                    >{{ old('deskripsi', $coach->deskripsi) }}</textarea>
                                                                </div>

                                                                {{-- FOTO --}}
                                                                <div class="space-y-2">
                                                                    <x-ui.label for="foto_{{ $coach->id }}">Foto Profil (opsional)</x-ui.label>

                                                                    <input
                                                                        type="file"
                                                                        id="foto_{{ $coach->id }}"
                                                                        name="foto"
                                                                        accept="image/*"
                                                                        class="block w-full text-sm text-text-main
                                                                               file:mr-4 file:py-2 file:px-4
                                                                               file:rounded-full file:border-0
                                                                               file:text-sm file:font-semibold
                                                                               file:bg-gold-600 file:text-white
                                                                               hover:file:bg-gold-700"
                                                                    >
                                                                    <p class="text-[11px] text-text-muted">
                                                                        Maksimal 2MB. Jika diisi, foto lama akan diganti.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-3">
                                                        <x-ui.button-secondary type="button" @click="openEdit = false">
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-text-muted italic">
                                    Belum ada data coach yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $coaches->links() }}
            </div>
        </x-ui.card>

        {{-- ======================== --}}
        {{-- MODAL TAMBAH COACH      --}}
        {{-- ======================== --}}
        <div
            x-show="openCreate"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openCreate = false"
                class="relative w-full max-w-4xl rounded-3xl shadow-2xl border border-brand-borderSoft bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">
                            Tambah Coach
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5">
                            Data Coach Baru
                        </p>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openCreate = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                <div class="px-6 pb-6 pt-4">
                    <form
                        method="POST"
                        action="{{ route('admin.coaches.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- PANEL KIRI: PREVIEW AVATAR --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                    <div
                                        class="w-24 h-24 rounded-full border-2 border-dashed border-brand-borderSoft flex items-center justify-center text-xs text-text-muted bg-brand-surface-50">
                                        Foto Coach
                                    </div>
                                    <p class="text-[11px] text-text-muted text-center">
                                        Upload foto coach di kolom form kanan untuk menambahkan avatar.
                                    </p>
                                </div>
                            </div>

                            {{-- PANEL KANAN: FORM --}}
                            <div class="md:col-span-2">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- NAMA --}}
                                        <div>
                                            <x-ui.label for="nama_create">Nama Coach</x-ui.label>
                                            <input
                                                type="text"
                                                id="nama_create"
                                                name="nama"
                                                value="{{ old('nama') }}"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                        </div>

                                        {{-- NO HP --}}
                                        <div>
                                            <x-ui.label for="no_hp_create">Nomor HP</x-ui.label>
                                            <input
                                                type="text"
                                                id="no_hp_create"
                                                name="no_hp"
                                                value="{{ old('no_hp') }}"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                        </div>
                                    </div>

                                    {{-- ALAMAT --}}
                                    <div>
                                        <x-ui.label for="alamat_create">Alamat</x-ui.label>
                                        <textarea
                                            id="alamat_create"
                                            name="alamat"
                                            rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                            required
                                        >{{ old('alamat') }}</textarea>
                                    </div>

                                    {{-- DESKRIPSI --}}
                                    <div>
                                        <x-ui.label for="deskripsi_create">Deskripsi / Keahlian</x-ui.label>
                                        <textarea
                                            id="deskripsi_create"
                                            name="deskripsi"
                                            rows="3"
                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                        >{{ old('deskripsi') }}</textarea>
                                    </div>

                                    {{-- FOTO --}}
                                    <div>
                                        <x-ui.label for="foto_create">Foto Profil (opsional)</x-ui.label>
                                        <input
                                            type="file"
                                            id="foto_create"
                                            name="foto"
                                            accept="image/*"
                                            class="block w-full text-sm text-text-main
                                                   file:mr-4 file:py-2 file:px-4
                                                   file:rounded-full file:border-0
                                                   file:text-sm file:font-semibold
                                                   file:bg-gold-600 file:text-white
                                                   hover:file:bg-gold-700"
                                        >
                                        <p class="text-[11px] text-text-muted mt-1">
                                            Maksimal 2MB. Format yang didukung: JPG, PNG, dll.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <x-ui.button-secondary type="button" @click="openCreate = false">
                                Batal
                            </x-ui.button-secondary>
                            <x-ui.button-primary type="submit">
                                Simpan Coach
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SCRIPT KONFIRMASI HAPUS --}}
        <script>
            function confirmDeleteCoach(coachId, coachName) {
                if (typeof Swal === 'undefined') {
                    if (confirm(`Yakin ingin menghapus coach ${coachName}?`)) {
                        document.getElementById('delete-coach-' + coachId).submit();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Hapus Coach?',
                    text: `Anda yakin ingin menghapus data coach ${coachName}? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#C73527',
                    cancelButtonColor: '#6C5A46',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    background: '#21160F',
                    color: '#F8F2E7',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-coach-' + coachId).submit();
                    }
                });
            }
        </script>

        {{-- CUSTOM SCROLLBAR --}}
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #F5E6D6; /* brand.shell */
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #D4A757; /* gold-500 */
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #A67C39; /* gold-700 */
            }
        </style>
    </div>
</x-layouts.admin>
