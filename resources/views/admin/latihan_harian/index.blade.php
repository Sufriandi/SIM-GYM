{{-- resources/views/admin/latihan_harian/index.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $pageTitle = 'Latihan Harian';

    $data = $data ?? ($latihanHarian ?? collect());
    $defaultHarga = $defaultHarga ?? config('gym.harga_harian');

    $totalTransaksi = method_exists($data, 'total') ? $data->total() : $data->count();
    $startNumber = method_exists($data, 'currentPage') ? ($data->currentPage() - 1) * $data->perPage() : 0;
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Catat kunjungan latihan harian (umum dan pelajar) beserta tarifnya.">

    {{-- X-DATA UTAMA: Menambahkan openEditId: null --}}
    <div x-data="{
        openCreate: false,
        openDetail: false,
        detailItem: null,
        openEditId: null,
    
        // state filter (frontend)
        searchTerm: '',
        kategoriFilter: '',
        metodeFilter: '',
    }"
        x-on:open-latihan-detail.window="
            detailItem = $event.detail;
            openDetail = true;
        "
        @keydown.escape.window="
            openCreate = false;
            openDetail = false;
            openEditId = null;
        ">

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Catat siapa saja yang latihan dengan sistem bayar per hari." />

        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS PENCARIAN + TOMBOL TAMBAH --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- SEARCH BAR + FILTER --}}
            <div class="relative w-full max-w-md" x-data="{ showFilter: false }">
                <form action="{{ route('admin.latihan_harian.index') }}" method="GET">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/40 transition-all hover:border-brand-borderSoft/80 h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>

                        <input type="text" x-model.debounce.150ms="searchTerm" placeholder="Cari nama pelanggan..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-2 rounded-l-full focus:outline-none focus-visible:outline-none"
                            autocomplete="off" @keydown.enter.prevent>

                        <div class="h-6 w-px bg-brand-borderSoft mx-1"></div>

                        <button type="button" @click="showFilter = !showFilter"
                            class="flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-medium text-text-muted hover:text-text-main mr-2 rounded-full hover:bg-brand-surface-50 transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                    </div>

                    {{-- DROPDOWN FILTER --}}
                    <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                        class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-20">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter & Urutan</h4>
                                <a href="{{ route('admin.latihan_harian.index') }}"
                                    class="text-xs text-danger hover:underline">Reset</a>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-bold uppercase text-text-muted mb-1">Kategori</label>
                                    <div class="relative">
                                        <select x-model="kategoriFilter"
                                            class="custom-select w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                            <option value="">Semua kategori</option>
                                            <option value="umum">Umum</option>
                                            <option value="pelajar">Pelajar</option>
                                        </select>
                                        <i data-lucide="chevron-down"
                                            class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">Metode
                                        Pembayaran</label>
                                    <div class="relative">
                                        <select x-model="metodeFilter"
                                            class="custom-select w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                            <option value="">Semua metode</option>
                                            <option value="cash">Cash</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="qris">QRIS</option>
                                        </select>
                                        <i data-lucide="chevron-down"
                                            class="w-4 h-4 text-text-muted absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">Urutkan
                                    berdasarkan</label>
                                <select name="sort"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2">
                                    <option value="newest"
                                        {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Tanggal · Terbaru
                                        dulu</option>
                                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Tanggal
                                        · Terlama dulu</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md"
                                @click="showFilter = false">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TOMBOL TAMBAH --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Latihan
            </x-ui.button-primary>
        </div>


        {{-- CARD TABEL LATIHAN HARIAN --}}
        <x-ui.card class="border-brand-borderSoft">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Latihan Harian</h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semua transaksi latihan harian (umum & pelajar) yang tercatat di sistem.
                    </p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ $totalTransaksi }} Transaksi
                    </span>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[800px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[14%]">
                                Tanggal</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[30%]">
                                Nama</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Kategori</th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[13%]">
                                Harga</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Metode</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($data as $item)
                            <tr x-data="{
                                nama: @js($item->nama),
                                kategori: '{{ $item->kategori }}',
                                metode: '{{ $item->metode_pembayaran }}',
                            }"
                                x-show="
                                    (!searchTerm || nama.toLowerCase().includes(searchTerm.toLowerCase()))
&& (!kategoriFilter || kategoriFilter === kategori)
                                    && (!metodeFilter || metodeFilter === metode)
                                "
                                class="hover:bg-brand-surface-50 transition-colors duration-150">

                                {{-- NO --}}
                                <td class="p-3 text-center align-middle text-text-muted">
                                    {{ $loop->iteration + $startNumber }}
                                </td>

                                {{-- TANGGAL --}}
                                <td class="p-3 align-middle">
                                    <span class="text-sm text-text-main whitespace-nowrap">
                                        {{ $item->tanggal->translatedFormat('d M Y') }}
                                    </span>
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate max-w-[180px] md:max-w-[250px]"
                                        title="{{ $item->nama }}">
                                        {{ $item->nama }}
                                    </div>
                                </td>

                                {{-- KATEGORI --}}
                                <td class="p-3 align-middle text-center">
                                    @if ($item->kategori === 'umum')
                                        <x-ui.badge variant="neutral"
                                            class="px-2.5 py-0.5 text-[10px]">Umum</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="info"
                                            class="px-2.5 py-0.5 text-[10px]">Pelajar</x-ui.badge>
                                    @endif
                                </td>

                                {{-- HARGA --}}
                                <td class="p-3 align-middle text-left">
                                    <span class="text-sm font-semibold text-text-main whitespace-nowrap">
                                        Rp {{ number_format($item->harga, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- METODE --}}
                                <td class="p-3 align-middle text-center">
                                    @if ($item->metode_pembayaran === 'cash')
                                        <x-ui.badge variant="neutral"
                                            class="px-2 py-0.5 text-[10px]">Cash</x-ui.badge>
                                    @elseif ($item->metode_pembayaran === 'transfer')
                                        <x-ui.badge variant="info"
                                            class="px-2 py-0.5 text-[10px]">Transfer</x-ui.badge>
                                    @elseif ($item->metode_pembayaran === 'qris')
                                        <x-ui.badge variant="primary"
                                            class="px-2 py-0.5 text-[10px]">QRIS</x-ui.badge>
                                    @else
                                        <span class="text-xs text-text-muted">-</span>
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- DETAIL --}}
                                        <button type="button" title="Detail Transaksi"
                                            class="p-1.5 rounded-full text-info hover:bg-info-soft/60 transition-colors"
                                            @click="$dispatch('open-latihan-detail', {
                                                tanggal_label: '{{ $item->tanggal->translatedFormat('d M Y') }}',
                                                nama: @js($item->nama),
                                                kategori: '{{ ucfirst($item->kategori) }}',
                                                harga_label: 'Rp {{ number_format($item->harga, 0, ',', '.') }}',
                                                metode_label: '{{ ucfirst($item->metode_pembayaran) }}',
                                                keterangan: @js($item->keterangan ?: '-'),
                                            })">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        {{-- EDIT (MENGESET ID GLOBAL) --}}
                                        <button type="button" title="Edit Transaksi"
                                            class="p-1.5 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            @click="openEditId = {{ $item->id }}">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- HAPUS --}}
                                        <form id="delete-latihan-{{ $item->id }}"
                                            action="{{ route('admin.latihan_harian.destroy', $item) }}"
                                            method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" title="Hapus Transaksi"
                                                class="p-1.5 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                onclick="confirmDeleteLatihan({{ $item->id }}, '{{ $item->nama }}')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    Belum ada data latihan harian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if (method_exists($data, 'links'))
                <div class="mt-6">
                    {{ $data->onEachSide(1)->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- MODAL CREATE (global) --}}
        @include('admin.latihan_harian.modals.create', ['defaultHarga' => $defaultHarga])

        {{-- MODAL DETAIL (global) --}}
        @include('admin.latihan_harian.modals.detail')

        {{-- MODAL EDIT (DI-LOOPING DI LUAR TABEL) --}}
        @foreach ($data as $item)
            @include('admin.latihan_harian.modals.edit', [
                'item' => $item,
                'defaultHarga' => $defaultHarga,
            ])
        @endforeach

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

            .custom-select {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                padding-right: 2.5rem !important;
                cursor: pointer;
            }
        </style>
    </div>

    {{-- SCRIPT KONFIRMASI HAPUS + AUTO HARGA --}}
    <script>
        function formatRupiahPlain(value) {
            if (!value) return '';
            let number = value.toString().replace(/\D/g, '');
            if (number === '') return '';
            return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function syncRupiahInput(displayInput) {
            const targetId = displayInput.dataset.target;
            if (!targetId) return;
            const hidden = document.getElementById(targetId);
            if (!hidden) return;

            let numeric = displayInput.value.replace(/\D/g, '');
            hidden.value = numeric;
            displayInput.value = formatRupiahPlain(numeric);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const rupiahDisplays = document.querySelectorAll('[data-rupiah-display]');
            rupiahDisplays.forEach(function(input) {
                const targetId = input.dataset.target;
                if (!targetId) return;
                const hidden = document.getElementById(targetId);

                if (hidden && hidden.value) {
                    input.value = formatRupiahPlain(hidden.value);
                }

                input.addEventListener('input', function() {
                    syncRupiahInput(input);
                });
                input.addEventListener('blur', function() {
                    syncRupiahInput(input);
                });
            });
        });

        function setDefaultHargaLatihan(kategori, hargaUmum, hargaPelajar, hiddenId) {
            let value = '';
            if (kategori === 'umum') value = hargaUmum || '';
            else if (kategori === 'pelajar') value = hargaPelajar || '';

            const hidden = document.getElementById(hiddenId);
            if (hidden) hidden.value = value;

            const display = document.querySelector('[data-rupiah-display][data-target="' + hiddenId + '"]');
            if (display) display.value = formatRupiahPlain(value);
        }

        function confirmDeleteLatihan(id, name) {
            if (typeof Swal === 'undefined') {
                if (confirm(`Yakin ingin menghapus transaksi latihan harian atas nama ${name}?`)) {
                    document.getElementById('delete-latihan-' + id).submit();
                }
                return;
            }

            Swal.fire({
                title: 'Hapus Transaksi?',
                html: `Anda yakin ingin menghapus transaksi latihan harian atas nama <strong>${name}</strong>?`,
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
                    document.getElementById('delete-latihan-' + id).submit();
                }
            });
        }
    </script>
</x-layouts.admin>
