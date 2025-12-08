{{-- resources/views/admin/paket_memberships/index.blade.php --}}
@php
    use Illuminate\Support\Str;

    $pageTitle = $pageTitle ?? 'Paket Membership';

    // parameter pencarian sederhana
    $search = request('q', '');
    $filterTipe = request('tipe', '');

    // modal create auto terbuka kalau ada error dan bukan PUT
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';

    // data untuk pagination
    $currentPage = $paketMemberships->currentPage() ?? 1;
    $perPage = $paketMemberships->perPage() ?? 15;

    /**
     * Meta tipe paket:
     * - key HARUS sama dengan enum di database: single, double, triple
     * - variant badge dibikin kontras:
     *   single  -> primary (indigo)
     *   double  -> success (hijau)
     *   triple  -> warning (amber/kuning)
     */
    $tipeMeta = [
        'single' => [
            'label' => 'single',
            'icon' => 'user',
            'variant' => 'primary',
        ],
        'double' => [
            'label' => 'double',
            'icon' => 'users',
            'variant' => 'success',
        ],
        'triple' => [
            'label' => 'triple',
            'icon' => 'users-2',
            'variant' => 'warning',
        ],
    ];

    // opsi dropdown filter
    $tipeOptions = collect($tipeMeta)->mapWithKeys(fn($meta, $key) => [$key => ucfirst($meta['label'])])->all();
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
        searchQuery: '{{ $search }}',
    }">
        {{-- HEADER HALAMAN --}}
        <x-ui.section-header :title="$pageTitle"
            subtitle="Atur nama paket, tipe paket (single/double/triple), durasi dalam hari, dan harga." />
        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- ROW: SEARCH + TOMBOL TAMBAH --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH & FILTER TIPE --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full md:max-w-xl">
                <form action="{{ route('admin.paket_memberships.index') }}" method="GET"
                    class="flex-1 flex items-center rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">
                    <div class="pl-4 text-text-muted">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>

                    <input type="text" name="q" x-model.debounce.300ms="searchQuery"
                        value="{{ $search }}" placeholder="Cari nama paket membership..."
                        class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/60 focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                        autocomplete="off">

                    {{-- separator --}}
                    <div class="h-6 w-px bg-brand-borderSoft mx-2"></div>

                    {{-- FILTER TIPE --}}
                    <select name="tipe"
                        class="mr-3 text-xs bg-transparent border-none text-text-main focus:ring-0 focus:outline-none">
                        <option value="">Semua Tipe</option>
                        @foreach ($tipeOptions as $value => $label)
                            <option value="{{ $value }}" {{ $filterTipe === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    {{-- submit hidden (enter) --}}
                    <button type="submit" class="hidden">Cari</button>
                </form>
            </div>

            {{-- TOMBOL TAMBAH PAKET --}}
            <div class="flex items-center justify-end">
                <x-ui.button-primary type="button" @click="openCreate = true">
                    <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Paket Membership
                </x-ui.button-primary>
            </div>
        </div>

        {{-- CARD TABEL PAKET MEMBERSHIP --}}
        <x-ui.card title="Daftar Paket Membership" subtitle="Semua paket membership yang tersedia di BETA GYM."
            class="border-brand-borderSoft">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No.
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[25%]">
                                Nama Paket
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Tipe Paket
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Durasi
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Harga
                            </th>
                            <th
                                class="p-3 text-left text-[10px] font-bold uppercase tracking-wide text-text-muted w-[20%]">
                                Deskripsi
                            </th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @php $no = ($currentPage - 1) * $perPage + 1; @endphp

                        @forelse ($paketMemberships as $paket)
                            @php
                                $openEditOnLoad =
                                    $errors->any() && old('_method') === 'PUT' && old('paket_id') == $paket->id
                                        ? 'true'
                                        : 'false';

                                $meta = $tipeMeta[$paket->tipe] ?? $tipeMeta['single'];
                                $tipeLabel = ucfirst($meta['label']);
                                $tipeIcon = $meta['icon'];
                                $badgeVar = $meta['variant'];

                                $durasiText = $paket->durasi . ' hari';
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150"
                                x-data="{ openEdit: {{ $openEditOnLoad }} }">
                                <td class="p-3 text-center align-middle text-text-muted">
                                    {{ $no++ }}
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1">
                                        {{ $paket->nama }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    {{-- TIPE PAKET MENGGUNAKAN BADGE --}}
                                    <x-ui.badge :variant="$badgeVar" class="gap-1.5 px-3 py-1">
                                        <i data-lucide="{{ $tipeIcon }}" class="w-3.5 h-3.5"></i>
                                        <span class="font-semibold">{{ $tipeLabel }}</span>
                                    </x-ui.badge>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm text-text-main">
                                        {{ $durasiText }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main">
                                        {{ 'Rp ' . number_format($paket->harga, 0, ',', '.') }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2">
                                        {{ $paket->deskripsi ? Str::limit($paket->deskripsi, 80) : '—' }}
                                    </div>
                                </td>

                                <td class="p-3 align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- EDIT BUTTON --}}
                                        <button type="button" @click.stop="openEdit = true"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            title="Edit Paket">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- DELETE BUTTON --}}
                                        <form id="delete-paket-{{ $paket->id }}"
                                            action="{{ route('admin.paket_memberships.destroy', $paket) }}"
                                            method="POST" class="inline-block" @click.stop>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                title="Hapus Paket"
                                                onclick="if(confirm('Yakin ingin menghapus paket {{ $paket->nama }}?')) document.getElementById('delete-paket-{{ $paket->id }}').submit();">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>

                                    {{-- MODAL EDIT (DI-include) --}}
                                    @include('admin.paket_memberships.modals.edit', [
                                        'paket' => $paket,
                                        'tipeOptions' => $tipeOptions,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-text-muted italic">
                                    @if ($search || $filterTipe)
                                        Tidak ada paket membership yang cocok dengan pencarian / filter.
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
                    ])->links() }}
            </div>
        </x-ui.card>

        {{-- MODAL CREATE (TERPISAH) --}}
        @include('admin.paket_memberships.modals.create', [
            'tipeOptions' => $tipeOptions,
            'openCreateOnLoad' => $openCreateOnLoad,
        ])

        {{-- STYLE KECIL UNTUK x-cloak --}}
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    </div>
</x-layouts.admin>
