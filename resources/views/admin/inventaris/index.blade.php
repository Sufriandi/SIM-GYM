@php
    $pageTitle = 'Inventaris Alat';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Daftar alat yang tersedia di gym.">
    <div x-data="{
        openCreate: false,
        openDetail: false,
        detailItem: null,
        previewCreateUrl: null,
    
        // state pencarian & filter (sinkron dengan server)
        search: '{{ request('search') }}',
    
        // filterKondisi = nilai yang SUDAH diterapkan (dari server)
        filterKondisi: '{{ request('kondisi') }}',
    
        // filterKondisiDraft = nilai dropdown yang sedang dipilih user (belum tentu diterapkan)
        filterKondisiDraft: '{{ request('kondisi') }}',
    
        setCreatePreview(event) {
            const file = event.target.files[0];
            this.previewCreateUrl = file ? URL.createObjectURL(file) : null;
        },
    }"
        x-on:open-detail.window="
            detailItem = $event.detail;
            openDetail = true;
        ">
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Daftar alat yang tersedia di gym." />

        {{-- GARIS DI BAWAH JUDUL --}}
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS PENCARIAN + TOMBOL TAMBAH --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- SEARCH + FILTER ala izin_latihan (tapi pakai logika inventaris) --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.inventaris.index') }}" method="GET">
                    {{-- Container Input Gabungan --}}
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card
                               shadow-sm transition-all hover:border-brand-borderSoft/80
                               focus-within:ring-0 focus-within:border-brand-borderSoft h-[42px]">
                        {{-- Icon Search --}}
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        {{-- Input Text --}}
                        <input type="text" name="search" x-model="search" value="{{ request('search') }}"
                            placeholder="Cari nama alat..."
                            class="w-full bg-transparent border-none text-sm text-text-main
                                   placeholder:text-text-muted/50 py-2 pl-3 pr-2 rounded-l-full
                                   focus:ring-0 focus:outline-none focus-visible:outline-none"
                            autocomplete="off">

                        {{-- Divider Vertical --}}
                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                        {{-- Tombol Filter Toggle --}}
                        <button type="button" @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-5 py-2 text-sm font-medium
                                   text-text-muted hover:text-text-main transition-colors mr-1
                                   rounded-full hover:bg-brand-surface-50"
                            :class="showFilter ? 'text-gold-600 bg-brand-surface-50' : ''">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>

                        {{-- Hidden submit buat tombol Enter --}}
                        <button type="submit" class="hidden"></button>
                    </div>

                    {{-- DROPDOWN FILTER KONDISI --}}
                    <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card
                               border border-brand-borderSoft rounded-2xl shadow-xl p-5">
                        <div class="space-y-4">
                            {{-- HEADER POPUP --}}
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">
                                    Filter &amp; Urutan
                                </h4>
                                <a href="{{ route('admin.inventaris.index') }}"
                                    class="text-xs text-danger hover:underline">
                                    Reset
                                </a>
                            </div>

                            {{-- BLOK PILIHAN KONDISI --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-bold uppercase text-text-muted">
                                    Urutkan berdasarkan
                                </p>

                                <div class="relative">
                                    {{-- NOTE: pakai filterKondisiDraft, bukan filterKondisi --}}
                                    <select x-model="filterKondisiDraft"
                                        class="w-full rounded-lg border border-brand-borderSoft bg-brand-shell
                                               text-xs text-text-main px-3 py-2 pr-8
                                               focus:outline-none focus:ring-2 focus:ring-primary-dark focus:border-primary-dark
                                               appearance-none">
                                        <option value="">Semua kondisi</option>
                                        <option value="Baik">Kondisi · Baik</option>
                                        <option value="Maintenance">Kondisi · Maintenance</option>
                                        <option value="Rusak">Kondisi · Rusak</option>
                                    </select>

                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-text-muted">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </span>
                                </div>

                                <p class="text-[11px] text-text-muted leading-snug">
                                    Pilih kondisi alat yang ingin ditampilkan di daftar inventaris.
                                </p>
                            </div>

                            {{-- nilai yang akan dikirim ke server --}}
                            <input type="hidden" name="kondisi" :value="filterKondisiDraft">
                            <input type="hidden" name="search" :value="search">

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white
                                       text-sm font-semibold py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TOMBOL TAMBAH ALAT --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Alat
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL INVENTARIS --}}
        <x-ui.card class="border-brand-borderSoft">
            {{-- HEADER CARD --}}
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Inventaris</h3>
                    <p class="text-xs text-text-muted mt-0.5">Semua alat yang terdaftar dalam sistem.</p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">{{ $data->count() }} Alat</span>
                </div>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                {{-- table-fixed + width per kolom supaya tidak melebar saat teks panjang --}}
                <table class="table-fixed w-full border-collapse text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[6%]">
                                No
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                                Foto
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[24%]">
                                Nama
                            </th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                                Kondisi
                            </th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[30%]">
                                Deskripsi
                            </th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($data as $alat)
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 h-20"
                                x-show="
                                    // SEARCH: prefix match berdasarkan awal nama (case-insensitive)
                                    (!search ||
                                        @js(strtolower($alat->nama)).startsWith(search.trim().toLowerCase()))
&&
                                    // FILTER KONDISI: hanya pakai nilai yang sudah diterapkan (filterKondisi)
                                    (
                                        !filterKondisi ||
                                        @js($alat->kondisi) === filterKondisi
                                    )
                                ">
                                {{-- NO URUT --}}
                                <td class="p-3 text-center align-middle text-sm font-semibold text-text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- FOTO --}}
                                <td class="p-3 align-middle">
                                    <div
                                        class="w-14 h-14 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 flex items-center justify-center overflow-hidden">
                                        @if ($alat->foto)
                                            <img src="{{ asset('storage/' . $alat->foto) }}"
                                                alt="Foto {{ $alat->nama }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[10px] text-text-muted">No Foto</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle">
                                    <div
                                        class="text-sm font-semibold text-text-main max-w-[260px] overflow-hidden text-ellipsis whitespace-nowrap">
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
                                    <div
                                        class="text-[12px] text-text-muted max-w-[320px] overflow-hidden text-ellipsis whitespace-nowrap">
                                        {{ $alat->deskripsi ?: '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4 align-middle w-[15%] min-w-[140px]">
                                    <div class="flex items-center justify-center gap-3 h-full"
                                        x-data="{
                                            openEdit: false,
                                            previewEditUrl: @js($alat->foto ? asset('storage/' . $alat->foto) : null),
                                        
                                            setEditPreview(event) {
                                                const file = event.target.files[0];
                                                if (file) {
                                                    this.previewEditUrl = URL.createObjectURL(file);
                                                }
                                            },
                                        }">
                                        {{-- DETAIL --}}
                                        <button type="button" title="Detail Inventaris"
                                            class="relative group p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150"
                                            @click="$dispatch('open-detail', {
                                                nama: @js($alat->nama),
                                                kondisi: @js($alat->kondisi),
                                                deskripsi: @js($alat->deskripsi ?: '-'),
                                                foto: @js($alat->foto ? asset('storage/' . $alat->foto) : null),
                                            })">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-info
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150">
                                                Detail
                                            </span>
                                        </button>

                                        {{-- EDIT --}}
                                        <button type="button" title="Edit Inventaris"
                                            class="relative group p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                                            @click="openEdit = true">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-yellow-600
                                                       opacity-0 group-hover:opacity-100
                                                       transition-opacity duration-150">
                                                Edit
                                            </span>
                                        </button>

                                        {{-- HAPUS --}}
                                        <form id="delete-inventaris-{{ $alat->id }}"
                                            action="{{ route('admin.inventaris.destroy', $alat) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus Inventaris"
                                                class="relative group p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                onclick="confirmDeleteInventaris({{ $alat->id }}, '{{ $alat->nama }}')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover:opacity-100
                                                           transition-opacity duration-150">
                                                    Hapus
                                                </span>
                                            </button>
                                        </form>

                                        {{-- MODAL EDIT --}}
                                        @include('admin.inventaris.modals.edit', ['alat' => $alat])
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

        {{-- MODAL DETAIL & CREATE (global) --}}
        @include('admin.inventaris.modals.detail')
        @include('admin.inventaris.modals.create')

        {{-- CUSTOM SCROLLBAR + X-CLOAK --}}
        <style>
            [x-cloak] {
                display: none !important;
            }

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
