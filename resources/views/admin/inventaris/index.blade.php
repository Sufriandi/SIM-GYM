@php
    $pageTitle = 'Inventaris Alat';
@endphp

<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Daftar alat yang tersedia di gym."
>
    <div
        x-data="{
            openCreate: false,
            openDetail: false,
            detailItem: null,
            previewCreateUrl: null,

            // state pencarian & filter (client-side)
            search: '{{ request('search') }}',
            filterKondisi: '{{ request('kondisi') }}',

            setCreatePreview(event) {
                const file = event.target.files[0];
                this.previewCreateUrl = file ? URL.createObjectURL(file) : null;
            },
        }"
        x-on:open-detail.window="
            detailItem = $event.detail;
            openDetail = true;
        "
    >

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header
            :title="$pageTitle"
            subtitle="Daftar alat yang tersedia di gym."
        >
            {{-- tombol dipindah ke bawah garis --}}
        </x-ui.section-header>

        {{-- GARIS DI BAWAH JUDUL --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS PENCARIAN + FILTER + TOMBOL TAMBAH --}}
        <div class="mt-6 mb-4 flex flex-wrap items-center justify-between gap-3">
            {{-- BLOK KIRI: PENCARIAN + FILTER --}}
            <div class="flex-1 min-w-[240px] md:max-w-xl space-y-2">
                <div class="flex flex-col sm:flex-row gap-2">
                    {{-- FORM PENCARIAN (ENTER tetap kirim ke server) --}}
                    <form
                        method="GET"
                        action="{{ route('admin.inventaris.index') }}"
                        class="flex-1"
                    >
                        <div
                            class="flex items-center gap-3 rounded-full bg-gradient-to-r from-brand-shell via-brand-card to-brand-shell
                                   border border-brand-borderSoft/80 px-4 h-10 shadow-inner
                                   focus-within:ring-2 focus-within:ring-primary-dark/70 transition"
                        >
                            {{-- ICON SEARCH --}}
                            <div
                                class="flex items-center justify-center w-7 h-7 rounded-full bg-brand-surface-50/80 border border-brand-borderSoft/60">
                                <i data-lucide="search" class="w-3.5 h-3.5 text-text-muted"></i>
                            </div>

                            {{-- INPUT --}}
                            <input
                                type="text"
                                name="search"
                                x-model="search"
                                value="{{ request('search') }}"
                                placeholder="Cari nama alat..."
                                class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-sm text-text-main
                                       placeholder:text-text-muted/70"
                            >
                        </div>

                        {{-- supaya saat tekan ENTER, kondisi aktif juga ikut terkirim ke server --}}
                        <input type="hidden" name="kondisi" :value="filterKondisi">
                    </form>

                   {{-- FILTER KONDISI: DROPDOWN --}}
<div class="relative" x-data="{ openFilter: false }">
    <button
        type="button"
        class="inline-flex items-center gap-2 rounded-full
               bg-gradient-to-r from-brand-shell to-brand-card
               border border-brand-borderSoft/80 px-4 h-10
               text-sm text-text-main shadow-inner
               hover:shadow-[0_8px_22px_rgba(0,0,0,0.18)]
               hover:-translate-y-[1px]
               transition-all duration-200 ease-out"
        @click="openFilter = !openFilter"
    >
        <span class="text-[10px] font-semibold tracking-wide text-text-muted uppercase">
            KONDISI
        </span>

        <span class="font-semibold"
              x-text="filterKondisi || 'Semua'"></span>

        <i data-lucide="chevron-down"
           class="w-4 h-4 text-text-muted transition-transform duration-200 ease-out"
           :class="openFilter ? 'rotate-180' : ''"></i>
    </button>

    {{-- MENU DROPDOWN --}}
    <div
        x-show="openFilter"
        x-cloak
        @click.away="openFilter = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute mt-2 w-44 rounded-3xl overflow-hidden z-20
               bg-brand-shell/95 backdrop-blur
               border border-brand-borderSoft/80
               shadow-[0_18px_45px_rgba(0,0,0,0.18)]"
    >
        {{-- SEMUA --}}
        <button
            type="button"
            class="w-full px-4 py-2 text-left flex items-center justify-between text-sm
                   transition-colors duration-200 ease-out
                   hover:bg-primary-dark/90 hover:text-white"
            :class="filterKondisi === '' 
                    ? 'bg-primary-dark/90 text-white' 
                    : 'text-text-main'"
            @click="filterKondisi = ''; openFilter = false"
        >
            <span>Semua</span>
            <span x-show="filterKondisi === ''" class="text-[10px] opacity-90">Aktif</span>
        </button>

        {{-- BAIK – hijau terang lembut --}}
        <button
            type="button"
            class="w-full px-4 py-2 text-left text-sm
                   transition-colors duration-200 ease-out
                   hover:bg-emerald-50 hover:text-emerald-700"
            :class="filterKondisi === 'Baik'
                    ? 'bg-emerald-50 text-emerald-700 font-semibold ring-1 ring-emerald-200'
                    : 'text-text-main'"
            @click="filterKondisi = 'Baik'; openFilter = false"
        >
            Baik
        </button>

        {{-- MAINTENANCE – kuning terang lembut --}}
        <button
            type="button"
            class="w-full px-4 py-2 text-left text-sm
                   transition-colors duration-200 ease-out
                   hover:bg-amber-50 hover:text-amber-700"
            :class="filterKondisi === 'Maintenance'
                    ? 'bg-amber-50 text-amber-700 font-semibold ring-1 ring-amber-200'
                    : 'text-text-main'"
            @click="filterKondisi = 'Maintenance'; openFilter = false"
        >
            Maintenance
        </button>

        {{-- RUSAK – merah terang lembut --}}
        <button
            type="button"
            class="w-full px-4 py-2 text-left text-sm
                   transition-colors duration-200 ease-out
                   hover:bg-rose-50 hover:text-rose-700"
            :class="filterKondisi === 'Rusak'
                    ? 'bg-rose-50 text-rose-700 font-semibold ring-1 ring-rose-200'
                    : 'text-text-main'"
            @click="filterKondisi = 'Rusak'; openFilter = false"
        >
            Rusak
        </button>
    </div>
</div>


                </div>
            </div>

            {{-- TOMBOL TAMBAH ALAT --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Alat
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
                            <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                No
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Foto
                            </th>
                            <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted">
                                Nama
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
                            <tr
                                class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-show="
                                    // filter nama (client-side, berdasarkan nama saja)
                                    (!search || @js(strtolower($alat->nama)).includes(search.toLowerCase()))
                                    &&
                                    // filter kondisi (client-side)
                                    (!filterKondisi || @js($alat->kondisi) === filterKondisi)
                                "
                            >
                                {{-- NO URUT --}}
                                <td class="p-3 text-center align-middle text-sm font-semibold text-text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- FOTO --}}
                                <td class="p-3 align-middle">
                                    <div class="w-14 h-14 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                        @if ($alat->foto)
                                            <img
                                                src="{{ asset('storage/' . $alat->foto) }}"
                                                alt="Foto {{ $alat->nama }}"
                                                class="w-full h-full object-cover"
                                            >
                                        @else
                                            <span class="text-[10px] text-text-muted">No Foto</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main max-w-[220px] whitespace-nowrap overflow-hidden text-ellipsis">
                                        {{ $alat->nama }}
                                    </div>
                                </td>

                                {{-- KONDISI --}}
                                <td class="p-3 text-center align-middle">
                                    @if ($alat->kondisi === 'Baik')
                                        <x-ui.badge variant="success">Baik</x-ui.badge>
                                    @elseif ($alat->kondisi === 'Maintenance')
                                        <x-ui.badge variant="warning">Maintenance</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger">Rusak</x-ui.badge>
                                    @endif
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle">
                                    <div class="text-[12px] text-text-muted max-w-[260px] whitespace-nowrap overflow-hidden text-ellipsis">
                                        {{ $alat->deskripsi ?: '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle">
                                    <div
                                        class="flex items-center justify-center gap-1.5"
                                        x-data="{
                                            openEdit: false,
                                            previewEditUrl: @js($alat->foto ? asset('storage/' . $alat->foto) : null),

                                            setEditPreview(event) {
                                                const file = event.target.files[0];
                                                if (file) {
                                                    this.previewEditUrl = URL.createObjectURL(file);
                                                }
                                            },
                                        }"
                                    >
                                        {{-- DETAIL ICON --}}
                                        <button
                                            type="button"
                                            title="Detail Inventaris"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150"
                                            @click="$dispatch('open-detail', {
                                                nama: @js($alat->nama),
                                                kondisi: @js($alat->kondisi),
                                                deskripsi: @js($alat->deskripsi ?: '-'),
                                                foto: @js($alat->foto ? asset('storage/' . $alat->foto) : null),
                                            })"
                                        >
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        {{-- EDIT ICON --}}
                                        <button
                                            type="button"
                                            title="Edit Inventaris"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                                            @click="openEdit = true"
                                        >
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- HAPUS ICON --}}
                                        <form
                                            id="delete-inventaris-{{ $alat->id }}"
                                            action="{{ route('admin.inventaris.destroy', $alat) }}"
                                            method="POST"
                                            class="inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                title="Hapus Inventaris"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                onclick="confirmDeleteInventaris({{ $alat->id }}, '{{ $alat->nama }}')"
                                            >
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>

                                        {{-- MODAL EDIT INVENTARIS --}}
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
                                                        <h2 class="text-xl font-semibold text-text-main">
                                                            Edit Inventaris Alat
                                                        </h2>
                                                        <p class="text-sm text-text-muted mt-0.5">
                                                            Perbarui informasi alat dan kondisinya.
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
                                                        action="{{ route('admin.inventaris.update', $alat) }}"
                                                        enctype="multipart/form-data"
                                                        class="space-y-5"
                                                    >
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                            {{-- PANEL KIRI: PREVIEW FOTO --}}
                                                            <div class="md:col-span-1">
                                                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                                                    <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                                                        <template x-if="previewEditUrl">
                                                                            <img
                                                                                :src="previewEditUrl"
                                                                                alt="Foto alat"
                                                                                class="w-full h-full object-cover"
                                                                            >
                                                                        </template>
                                                                        <template x-if="!previewEditUrl">
                                                                            <span class="text-[11px] text-text-muted">
                                                                                Belum ada foto
                                                                            </span>
                                                                        </template>
                                                                    </div>
                                                                    <p class="text-[11px] text-text-muted text-center">
                                                                        Pilih foto baru untuk mengganti gambar alat.
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
                                                                                <option value="" disabled>
                                                                                    -- Pilih Kondisi --
                                                                                </option>
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
                                                                            @change="setEditPreview($event)"
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

        {{-- MODAL DETAIL INVENTARIS --}}
        <div
            x-show="openDetail"
            x-cloak
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/40 backdrop-blur-sm"
        >
            <div
                @click.away="openDetail = false"
                class="relative w-full max-w-3xl max-h-[88vh]
                       flex flex-col rounded-3xl shadow-2xl border border-brand-borderSoft
                       bg-gradient-to-br from-brand-shell via-brand-card to-brand-shell"
            >
                {{-- HEADER --}}
                <div class="flex items-center justify-between px-6 pt-4 pb-3 border-b border-brand-borderSoft/70">
                    <div>
                        <p class="text-[11px] tracking-[0.20em] font-semibold uppercase text-text-muted">
                            Detail Inventaris
                        </p>
                        <h2 class="text-xl font-semibold text-text-main mt-1">
                            Informasi Alat & Kondisi
                        </h2>
                    </div>
                    <button type="button"
                            class="rounded-full p-1.5 hover:bg-brand-surface-50 transition"
                            @click="openDetail = false">
                        <i data-lucide="x" class="w-4 h-4 text-text-muted"></i>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="flex-1 px-6 pb-5 pt-4 overflow-y-auto custom-scrollbar space-y-6">

                    {{-- FOTO --}}
                    <div class="w-full flex flex-col items-center gap-3">
                        <div class="relative w-28 h-28 md:w-36 md:h-36 rounded-2xl overflow-hidden shadow-[0_12px_32px_rgba(0,0,0,0.25)] bg-brand-surface-50">
                            <template x-if="detailItem && detailItem.foto">
                                <img :src="detailItem.foto" class="w-full h-full object-cover">
                            </template>

                            <template x-if="!detailItem || !detailItem.foto">
                                <div class="w-full h-full flex items-center justify-center text-[11px] text-text-muted">
                                    Tidak ada foto
                                </div>
                            </template>
                        </div>

                        <p class="text-[11px] text-text-muted">
                            Foto terkini dari alat yang terdaftar.
                        </p>
                    </div>

                    {{-- BLOK INFORMASI (Nama + Kondisi + Deskripsi) --}}
                    <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- NAMA ALAT --}}
                            <div>
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1">
                                    Nama Alat
                                </p>
                                <div class="rounded-xl bg-brand-shell border border-brand-borderSoft px-4 py-3 flex items-center">
                                    <p class="text-sm font-semibold text-text-main break-words">
                                        <span x-text="detailItem ? detailItem.nama : ''"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- KONDISI --}}
                            <div>
                                <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1">
                                    Kondisi
                                </p>
                                <div class="rounded-xl bg-brand-shell border border-brand-borderSoft px-4 py-3 flex items-center">
                                    <p class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                       :class="{
                                           'bg-success-soft text-success-dark': detailItem && detailItem.kondisi === 'Baik',
                                           'bg-warning-soft text-warning-dark': detailItem && detailItem.kondisi === 'Maintenance',
                                           'bg-danger-soft text-danger-dark': detailItem && detailItem.kondisi === 'Rusak'
                                       }">
                                        <span x-text="detailItem ? detailItem.kondisi : ''"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- DESKRIPSI ALAT --}}
                        <div>
                            <p class="text-[11px] font-semibold tracking-wide text-text-muted uppercase mb-1">
                                Deskripsi Alat
                            </p>
                            <div class="rounded-xl bg-brand-shell border border-brand-borderSoft px-4 py-3">
                                <p class="text-sm text-text-main leading-relaxed break-words">
                                    <span x-text="detailItem && detailItem.deskripsi ? detailItem.deskripsi : 'Belum ada deskripsi untuk alat ini.'"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end px-6 py-3 bg-brand-shell/70 rounded-b-3xl">
                    <x-ui.button-secondary type="button" @click="openDetail = false">
                        Tutup
                    </x-ui.button-secondary>
                </div>
            </div>
        </div>

        {{-- MODAL TAMBAH INVENTARIS --}}
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
                {{-- HEADER --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b-2 border-brand-borderSoft/80">
                    <div>
                        <h2 class="text-xl font-semibold text-text-main">
                            Tambah Inventaris Alat
                        </h2>
                        <p class="text-sm text-text-muted mt-0.5">
                            Masukkan data alat baru beserta kondisinya.
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
                        action="{{ route('admin.inventaris.store') }}"
                        enctype="multipart/form-data"
                        class="space-y-5"
                    >
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- PANEL KIRI: PREVIEW FOTO --}}
                            <div class="md:col-span-1">
                                <div class="rounded-2xl border border-brand-borderSoft/70 bg-brand-card p-4 flex flex-col items-center gap-3">
                                    <div class="w-28 h-28 rounded-xl border-2 border-dashed border-brand-borderSoft bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                        <template x-if="previewCreateUrl">
                                            <img
                                                :src="previewCreateUrl"
                                                alt="Preview foto alat"
                                                class="w-full h-full object-cover"
                                            >
                                        </template>
                                        <template x-if="!previewCreateUrl">
                                            <span class="text-[11px] text-text-muted">
                                                Foto Alat
                                            </span>
                                        </template>
                                    </div>
                                    <p class="text-[11px] text-text-muted text-center">
                                        Pilih gambar untuk menambahkan foto inventaris.
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
                                                <option value="" disabled {{ old('kondisi') ? '' : 'selected' }}>
                                                    -- Pilih Kondisi --
                                                </option>
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
                                            @change="setCreatePreview($event)"
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

    {{-- SCRIPT KONFIRMASI HAPUS --}}
    <script>
        function confirmDeleteInventaris(id, name) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus inventaris ${name}?`)) {
                    document.getElementById('delete-inventaris-' + id).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Inventaris?',
                html: `Anda yakin ingin menghapus alat <strong>${name}</strong> dari daftar?`,
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
                    document.getElementById('delete-inventaris-' + id).submit();
                }
            });
        }
    </script>
</x-layouts.admin>
