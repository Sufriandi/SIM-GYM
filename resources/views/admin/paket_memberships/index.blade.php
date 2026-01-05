{{-- resources/views/admin/paket_memberships/index.blade.php --}}
@php
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Paket Membership';

    // Parameter dari Controller
    $search = request('q', '');
    $filterTipe = request('tipe', '');
    $filterVisibility = request('visibility', '');
    $sort = request('sort', 'newest');

    // Cek apakah ada filter aktif untuk styling tombol filter
    $hasActiveFilter = $filterTipe || $filterVisibility !== '' || $sort !== 'newest';

    // Modal Create auto-open jika error validation
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';

    // Data Pagination
    $currentPage = $paketMemberships->currentPage() ?? 1;
    $perPage = $paketMemberships->perPage() ?? 15;

    // Opsi Tipe
    $tipeMeta = [
        'single' => ['label' => 'Single', 'icon' => 'user', 'variant' => 'primary'],
        'double' => ['label' => 'Double', 'icon' => 'users', 'variant' => 'success'],
        'triple' => ['label' => 'Triple', 'icon' => 'users-2', 'variant' => 'warning'],
    ];
    $tipeOptions = collect($tipeMeta)->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])->all();

    // Opsi Sorting
    $sortOptions = [
        'newest' => 'Terbaru (Default)',
        'price_asc' => 'Harga Termurah',
        'price_desc' => 'Harga Termahal',
        'duration_asc' => 'Durasi Terpendek',
        'duration_desc' => 'Durasi Terpanjang',
    ];

    $visibilityOptions = [
        '' => 'Semua',
        'public' => 'Public (tampil di member)',
        'internal' => 'Internal (admin only)',
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Kelola paket membership BETA GYM (harian, bulanan, couple/family, dll).">

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-danger-soft border border-danger text-danger px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openEditId: null,
        searchQuery: @js($search ?? ''),
    
        // State untuk Filter Popup
        showFilter: false,
        tipeDraft: @js($filterTipe),
        visibilityDraft: @js($filterVisibility),
        sortDraft: @js($sort),
    }"
        @keydown.escape.window="
        openCreate = false;
        openEditId = null;
        showFilter = false;
    ">

        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle"
            subtitle="Atur nama paket, tipe paket (single/double/triple), durasi dalam hari, dan harga." />
        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- ROW: SEARCH + FILTER + TOMBOL TAMBAH --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH BAR & FILTER POPUP --}}
            <div class="relative w-full md:max-w-xl">
                <form action="{{ route('admin.paket_memberships.index') }}" method="GET"
                    class="flex-1 flex items-center rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">

                    {{-- Hidden inputs agar value filter tetap terbawa saat search diketik --}}
                    <input type="hidden" name="tipe" :value="tipeDraft">
                    <input type="hidden" name="visibility" :value="visibilityDraft">
                    <input type="hidden" name="sort" :value="sortDraft">

                    <div class="pl-4 text-text-muted">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>

                    <input type="text" name="q" x-model.debounce.300ms="searchQuery"
                        value="{{ $search }}" placeholder="Cari nama paket membership..."
                        class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/60 focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                        autocomplete="off">

                    <div class="h-6 w-px bg-brand-borderSoft mx-2"></div>

                    {{-- TOMBOL PEMICU FILTER --}}
                    <button type="button" @click="showFilter = !showFilter"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors mr-2 rounded-full hover:bg-brand-surface-50"
                        :class="(showFilter || {{ $hasActiveFilter ? 'true' : 'false' }}) ?
                        'text-gold-600 bg-brand-surface-50' : 'text-text-muted hover:text-text-main'">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>

                    <button type="submit" class="hidden">Cari</button>
                </form>

                {{-- POPUP FILTER (Dropdown) --}}
                <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-40">

                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                            <h4 class="text-sm font-semibold text-text-main">Filter &amp; Urutan</h4>
                            <a href="{{ route('admin.paket_memberships.index') }}"
                                class="text-xs text-danger hover:underline">
                                Reset
                            </a>
                        </div>

                        {{-- Filter Tipe --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">Tipe Paket</label>
                            <select x-model="tipeDraft"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                <option value="">Semua Tipe</option>
                                @foreach ($tipeOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Visibilitas --}}
                        <div>
                            <label
                                class="block text-[10px] font-bold uppercase text-text-muted mb-1">Visibilitas</label>
                            <select x-model="visibilityDraft"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                @foreach ($visibilityOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Sort --}}
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">Urutan</label>
                            <select x-model="sortDraft"
                                class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2 border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                @foreach ($sortOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tombol Terapkan --}}
                        <button type="button" @click="$el.closest('.relative').querySelector('form').submit()"
                            class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            {{-- TOMBOL TAMBAH PAKET --}}
            <div class="flex items-center justify-end">
                <x-ui.button-primary type="button" @click="openCreate = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Paket
                </x-ui.button-primary>
            </div>
        </div>

        {{-- CARD TABEL --}}
        <x-ui.card title="Daftar Paket Membership" subtitle="Semua paket membership yang tersedia di BETA GYM."
            class="border-brand-borderSoft">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse min-w-[860px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No.
                            </th>
                            {{-- UBAH: Lebar dikurangi dari 22% menjadi 18% --}}
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[18%]">
                                Nama Paket
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Tipe Paket
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Durasi
                            </th>
                            {{-- UBAH: Lebar ditambah dari 12% menjadi 16% --}}
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[16%]">
                                Harga
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Visibilitas
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[17%]">
                                Deskripsi
                            </th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no = ($currentPage - 1) * $perPage + 1; @endphp

                        @forelse ($paketMemberships as $paket)
                            @php
                                $meta = $tipeMeta[$paket->tipe] ?? $tipeMeta['single'];
                                $tipeLabel = $meta['label'];
                                $tipeIcon = $meta['icon'];
                                $badgeVar = $meta['variant'];
                                $durasiText = $paket->durasi . ' hari';
                                // Format harga disiapkan di sini
                                $hargaFormatted = 'Rp ' . number_format($paket->harga, 0, ',', '.');
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                                <td class="p-3 text-center align-middle text-text-muted">{{ $no++ }}</td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1">
                                        {{ $paket->nama }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <x-ui.badge :variant="$badgeVar" class="gap-1.5 px-3 py-1">
                                        <i data-lucide="{{ $tipeIcon }}" class="w-3.5 h-3.5"></i>
                                        <span class="font-semibold">{{ $tipeLabel }}</span>
                                    </x-ui.badge>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main">{{ $durasiText }}</div>
                                </td>

                                {{-- UBAH: Tambahkan class 'truncate' dan title agar tooltip muncul saat di-hover --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main truncate"
                                        title="{{ $hargaFormatted }}">
                                        {{ $hargaFormatted }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    @if ($paket->is_public)
                                        <x-ui.badge variant="success" class="px-3 py-1">Public</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="info" class="px-3 py-1">Internal</x-ui.badge>
                                    @endif
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2">
                                        {{ $paket->deskripsi ? Str::limit($paket->deskripsi, 80) : '—' }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- EDIT --}}
                                        <button type="button" @click.stop="openEditId = {{ $paket->id }}"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            title="Edit Paket">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- DELETE --}}
                                        <form id="delete-paket-{{ $paket->id }}"
                                            action="{{ route('admin.paket_memberships.destroy', $paket) }}"
                                            method="POST" class="inline-block" @click.stop>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                title="Hapus Paket"
                                                onclick="confirmDeletePaket({{ $paket->id }}, @js($paket->nama))">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- ... (bagian empty tetap sama) ... --}}
                            <tr>
                                <td colspan="8" class="p-6 text-center text-text-muted italic">
                                    @if ($search || $filterTipe || $filterVisibility)
                                        Tidak ada paket membership yang cocok dengan filter pencarian.
                                    @else
                                        Belum ada paket membership yang tersimpan.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-6">
                {{ $paketMemberships->appends([
                        'q' => $search,
                        'tipe' => $filterTipe,
                        'visibility' => $filterVisibility,
                        'sort' => $sort,
                    ])->links() }}
            </div>
        </x-ui.card>

        {{-- MODALS EDIT --}}
        @foreach ($paketMemberships as $paket)
            @include('admin.paket_memberships.modals.edit', [
                'paket' => $paket,
                'tipeOptions' => $tipeOptions,
            ])
        @endforeach

        {{-- MODAL CREATE --}}
        @include('admin.paket_memberships.modals.create', [
            'tipeOptions' => $tipeOptions,
            'openCreateOnLoad' => $openCreateOnLoad,
        ])

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

    <script>
        function confirmDeletePaket(paketId, paketNama) {
            if (typeof Swal === 'undefined') {
                if (confirm('Yakin ingin menghapus paket ' + paketNama + '?')) {
                    document.getElementById('delete-paket-' + paketId).submit();
                }
                return;
            }
            Swal.fire({
                title: 'Hapus Paket?',
                text: 'Anda yakin ingin menghapus paket ' + paketNama + '? Tindakan ini tidak dapat dibatalkan.',
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
                    document.getElementById('delete-paket-' + paketId).submit();
                }
            });
        }
    </script>
</x-layouts.admin>
