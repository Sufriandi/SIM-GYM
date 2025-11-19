@php
    $pageTitle = 'Inventaris Alat';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar alat yang tersedia di gym."
>
    <div x-data="{ openCreate: false }">

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar alat yang tersedia di gym."
        >
            {{-- tombol dipindah ke bawah garis --}}
        </x-ui.section-header>

        {{-- GARIS DI BAWAH JUDUL --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- TOMBOL TAMBAH DI BAWAH GARIS (RATA KANAN) --}}
        <div class="mt-6 mb-4 flex justify-end">
            <x-ui.button-primary @click="openCreate = true">
                + Tambah Alat
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL INVENTARIS --}}
        <x-ui.card
            title="Daftar Inventaris"
            subtitle="Semua alat yang terdaftar dalam sistem."
            class="border-brand-borderSoft"
        >
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[900px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                No
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Nama
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Foto
                            </th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Kondisi
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Deskripsi
                            </th>
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/70">
                        @forelse ($data as $alat)
                            {{-- setiap baris punya state modal edit sendiri --}}
                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ openEdit: false }"
                            >
                                {{-- NO --}}
                                <td class="p-3 align-top text-sm font-medium text-text-main">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-top">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ $alat->nama }}
                                    </div>
                                </td>

                                {{-- FOTO --}}
                                <td class="p-3 align-top">
                                    @if ($alat->foto)
                                        <img
                                            src="{{ asset('storage/' . $alat->foto) }}"
                                            alt="Foto {{ $alat->nama }}"
                                            class="w-14 h-14 rounded-lg object-cover border border-brand-borderSoft/80"
                                        >
                                    @else
                                        <div
                                            class="w-14 h-14 rounded-lg bg-brand-surface-50 border border-dashed border-brand-borderSoft flex items-center justify-center text-[10px] text-text-muted">
                                            No Foto
                                        </div>
                                    @endif
                                </td>

                                {{-- KONDISI --}}
                                <td class="p-3 text-center align-top">
                                    @if ($alat->kondisi === 'Baik')
                                        <x-ui.badge variant="success">Baik</x-ui.badge>
                                    @elseif ($alat->kondisi === 'Maintenance')
                                        <x-ui.badge variant="warning">Maintenance</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Rusak</x-ui.badge>
                                    @endif
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-top">
                                    <div class="text-[12px] text-text-muted max-w-xs">
                                        {{ $alat->deskripsi ? \Illuminate\Support\Str::limit($alat->deskripsi, 80) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-top">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- EDIT --}}
                                        <x-ui.button-secondary
                                            type="button"
                                            class="px-3 py-1.5 text-[11px]"
                                            @click="openEdit = true"
                                        >
                                            Edit
                                        </x-ui.button-secondary>

                                        {{-- HAPUS - SIMPLE FORM SUBMIT --}}
                                        <form
                                            action="{{ route('admin.inventaris.destroy', $alat) }}"
                                            method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Yakin ingin menghapus inventaris {{ $alat->nama }}?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <x-ui.button-secondary
                                                type="submit"
                                                class="px-3 py-1.5 text-[11px] bg-danger-soft text-danger hover:bg-danger-soft/80"
                                            >
                                                Hapus
                                            </x-ui.button-secondary>
                                        </form>
                                    </div>

                                    {{-- ======================= --}}
                                    {{-- MODAL EDIT INVENTARIS  --}}
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
                                            {{-- strip gradient kecil di atas --}}
                                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold-500 via-accent-500 to-gold-500"></div>

                                            {{-- HEADER MODAL --}}
                                            <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-brand-borderSoft/70">
                                                <div>
                                                    <p class="text-[11px] tracking-[0.25em] uppercase text-text-muted">
                                                        Edit Inventaris
                                                    </p>
                                                    <h2 class="text-lg font-semibold text-text-main">
                                                        {{ $alat->nama }}
                                                    </h2>
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
                                                    action="{{ route('admin.inventaris.update', $alat) }}"
                                                    enctype="multipart/form-data"
                                                    class="space-y-5"
                                                >
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        {{-- PANEL KIRI: FOTO + INFO SINGKAT --}}
                                                        <div class="md:col-span-1">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                                                @if ($alat->foto)
                                                                    <img
                                                                        src="{{ asset('storage/' . $alat->foto) }}"
                                                                        alt="Foto {{ $alat->nama }}"
                                                                        class="w-24 h-24 rounded-xl object-cover border-2 border-gold-500 shadow-md"
                                                                    >
                                                                @else
                                                                    <div
                                                                        class="w-24 h-24 rounded-xl border-2 border-dashed border-brand-borderSoft flex items-center justify-center text-xs text-text-muted bg-brand-surface-50">
                                                                        No Foto
                                                                    </div>
                                                                @endif

                                                                <div class="text-center">
                                                                    <p class="text-sm font-semibold text-text-main">
                                                                        {{ $alat->nama }}
                                                                    </p>
                                                                    <p class="text-[11px] text-text-muted">
                                                                        Kondisi: {{ $alat->kondisi }}
                                                                    </p>
                                                                </div>

                                                                <p class="text-[11px] text-text-muted text-center">
                                                                    Perbarui data alat di form sebelah kanan.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {{-- PANEL KANAN: FORM --}}
                                                        <div class="md:col-span-2">
                                                            <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                                                                {{-- NAMA + KONDISI --}}
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                    <div>
                                                                        <x-ui.label for="nama_{{ $alat->id }}">Nama Alat</x-ui.label>
                                                                        <input
                                                                            type="text"
                                                                            id="nama_{{ $alat->id }}"
                                                                            name="nama"
                                                                            value="{{ old('nama', $alat->nama) }}"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                    </div>

                                                                    <div>
                                                                        <x-ui.label for="kondisi_{{ $alat->id }}">Kondisi</x-ui.label>
                                                                        <select
                                                                            id="kondisi_{{ $alat->id }}"
                                                                            name="kondisi"
                                                                            class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                                   border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                            required
                                                                        >
                                                                            <option value="Baik" {{ old('kondisi', $alat->kondisi) === 'Baik' ? 'selected' : '' }}>Baik</option>
                                                                            <option value="Maintenance" {{ old('kondisi', $alat->kondisi) === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                                            <option value="Rusak" {{ old('kondisi', $alat->kondisi) === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                {{-- DESKRIPSI --}}
                                                                <div>
                                                                    <x-ui.label for="deskripsi_{{ $alat->id }}">Deskripsi</x-ui.label>
                                                                    <textarea
                                                                        id="deskripsi_{{ $alat->id }}"
                                                                        name="deskripsi"
                                                                        rows="3"
                                                                        class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                                               border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                                    >{{ old('deskripsi', $alat->deskripsi) }}</textarea>
                                                                </div>

                                                                {{-- FOTO --}}
                                                                <div class="space-y-2">
                                                                    <x-ui.label for="foto_{{ $alat->id }}">Foto (opsional)</x-ui.label>

                                                                    <input
                                                                        type="file"
                                                                        id="foto_{{ $alat->id }}"
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
                                                                        Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.
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
                                    Belum ada data inventaris.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- ======================== --}}
        {{-- MODAL TAMBAH INVENTARIS --}}
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
                {{-- strip gradient kecil --}}
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gold-500 via-accent-500 to-gold-500"></div>

                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-brand-borderSoft/70">
                    <div>
                        <p class="text-[11px] tracking-[0.25em] uppercase text-text-muted">
                            Tambah Inventaris
                        </p>
                        <h2 class="text-lg font-semibold text-text-main">
                            Data Alat Baru
                        </h2>
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
                        action="{{ route('admin.inventaris.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- PANEL KIRI: PREVIEW FOTO --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                    <div
                                        class="w-24 h-24 rounded-xl border-2 border-dashed border-brand-borderSoft flex items-center justify-center text-xs text-text-muted bg-brand-surface-50">
                                        Foto Alat
                                    </div>
                                    <p class="text-[11px] text-text-muted text-center">
                                        Upload foto alat di kolom form kanan untuk menambahkan gambar.
                                    </p>
                                </div>
                            </div>

                            {{-- PANEL KANAN: FORM --}}
                            <div class="md:col-span-2">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- NAMA --}}
                                        <div>
                                            <x-ui.label for="nama_create">Nama Alat</x-ui.label>
                                            <input
                                                type="text"
                                                id="nama_create"
                                                name="nama"
                                                value="{{ old('nama') }}"
                                                placeholder="Contoh: Dumbbell 10kg"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                        </div>

                                        {{-- KONDISI --}}
                                        <div>
                                            <x-ui.label for="kondisi_create">Kondisi</x-ui.label>
                                            <select
                                                id="kondisi_create"
                                                name="kondisi"
                                                class="w-full rounded-xl border bg-brand-shell text-sm text-text-main px-3 py-2
                                                       border-brand-borderSoft focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-transparent"
                                                required
                                            >
                                                <option value="Baik" {{ old('kondisi') === 'Baik' ? 'selected' : '' }}>Baik</option>
                                                <option value="Maintenance" {{ old('kondisi') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                <option value="Rusak" {{ old('kondisi') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- DESKRIPSI --}}
                                    <div>
                                        <x-ui.label for="deskripsi_create">Deskripsi</x-ui.label>
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
                                        <x-ui.label for="foto_create">Foto (opsional)</x-ui.label>
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
                                            Maksimal 2MB. Format yang didukung: JPG, JPEG, PNG.
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
                                Simpan Alat
                            </x-ui.button-primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- CUSTOM SCROLLBAR + X-CLOAK --}}
        <style>
            [x-cloak] { display: none !important; }

            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #F5E6D6;
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #D4A757;
                border-radius: 999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #A67C39;
            }
        </style>
    </div>
</x-layouts.admin>
