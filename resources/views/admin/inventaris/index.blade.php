{{-- resources/views/admin/inventaris/index.blade.php --}}

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $pageTitle = 'Inventaris Alat';

    // Ambil data langsung dari variabel $data yang dikirim Controller
    $items = $data;

    // Cek search dari request
    $search = request('search');

    // Meta untuk badge kondisi
    $kondisiMeta = [
        'Baik' => ['label' => 'Baik', 'variant' => 'success'],
        'Maintenance' => ['label' => 'Maintenance', 'variant' => 'warning'],
        'Rusak' => ['label' => 'Rusak', 'variant' => 'danger'],
    ];

    // Cek error untuk auto-open modal create
    $openCreateOnLoad = $errors->any() && old('_method') !== 'PUT' ? 'true' : 'false';
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle" page-subtitle="Kelola data alat dan inventaris gym.">

    <div x-data="{
        openCreate: {{ $openCreateOnLoad }},
        openDetailId: null,
        openEditId: null,
        search: @js($search ?? ''),
    }"
        @keydown.escape.window="
        openCreate = false;
        openDetailId = null;
        openEditId = null;
    ">

        {{-- HEADER --}}
        <x-ui.section-header :title="$pageTitle" subtitle="Daftar alat fitness dan status kondisinya." />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        {{-- BARIS SEARCH & TOMBOL --}}
        <div class="mt-6 mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- SEARCH FORM --}}
            <div class="relative w-full max-w-md">
                <form action="{{ route('admin.inventaris.index') }}" method="GET">
                    <div
                        class="flex items-center w-full rounded-full border border-brand-borderSoft bg-brand-card shadow-sm focus-within:ring-2 focus-within:ring-primary-dark/40 transition-all hover:border-brand-borderSoft/80 h-[42px]">
                        <div class="pl-4 text-text-muted">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </div>
                        <input type="text" name="search" x-model.debounce.300ms="search" value="{{ $search }}"
                            placeholder="Cari nama alat..."
                            class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/50 focus:ring-0 py-2 pl-3 pr-4 rounded-r-full focus:outline-none focus-visible:outline-none"
                            autocomplete="off">
                        <button type="submit" class="hidden">Cari</button>
                    </div>
                </form>
            </div>

            {{-- TOMBOL TAMBAH --}}
            <x-ui.button-primary type="button" class="shrink-0" @click="openCreate = true">
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Alat
            </x-ui.button-primary>
        </div>

        {{-- CARD TABEL --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="px-6 py-4 border-b border-brand-borderSoft flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-text-main">Daftar Inventaris</h3>
                    <p class="text-xs text-text-muted mt-0.5">Total item terdata.</p>
                </div>
                <div class="bg-brand-surface-50 border border-brand-borderSoft px-3 py-1 rounded-full">
                    <span class="text-xs font-semibold text-text-main">
                        {{ method_exists($items, 'total') ? $items->total() : $items->count() }} Item
                    </span>
                </div>
            </div>

            {{-- TABEL RESPONSIF --}}
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[900px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[5%]">
                                No</th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[12%]">
                                Foto</th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[25%]">
                                Nama Alat</th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                                Kondisi</th>
                            <th
                                class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[30%]">
                                Deskripsi</th>
                            <th
                                class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[13%]">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($items as $item)
                            @php
                                $fotoUrl = $item->foto
                                    ? Storage::url($item->foto)
                                    : 'https://placehold.co/100x100/3A2D2A/F5E6D6?text=No+Foto';

                                // Ambil badge, default ke 'Baik' jika data lama tidak cocok
                                $badgeData = $kondisiMeta[$item->kondisi] ?? $kondisiMeta['Baik'];
                            @endphp

                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 h-16">
                                {{-- NO --}}
                                <td class="p-3 align-middle text-center text-text-muted text-sm font-semibold">
                                    {{ $loop->iteration + (method_exists($items, 'currentPage') ? ($items->currentPage() - 1) * $items->perPage() : 0) }}
                                </td>

                                {{-- FOTO --}}
                                <td class="p-3 align-middle">
                                    <div
                                        class="w-12 h-12 rounded-lg border border-brand-borderSoft/80 bg-brand-surface-50 overflow-hidden shrink-0">
                                        <img src="{{ $fotoUrl }}" alt="{{ $item->nama }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                </td>

                                {{-- NAMA --}}
                                <td class="p-3 align-middle">
                                    <div class="text-sm font-semibold text-text-main line-clamp-1"
                                        title="{{ $item->nama }}">
                                        {{ $item->nama }}
                                    </div>
                                </td>

                                {{-- KONDISI --}}
                                <td class="p-3 align-middle text-center">
                                    <x-ui.badge :variant="$badgeData['variant']">
                                        {{ $badgeData['label'] }}
                                    </x-ui.badge>
                                </td>

                                {{-- DESKRIPSI --}}
                                <td class="p-3 align-middle">
                                    <div class="text-xs text-text-muted line-clamp-2" title="{{ $item->deskripsi }}">
                                        {{ $item->deskripsi ? Str::limit($item->deskripsi, 60) : '-' }}
                                    </div>
                                </td>

                                {{-- AKSI --}}
                                <td class="p-3 align-middle text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- DETAIL --}}
                                        <button type="button" @click="openDetailId = {{ $item->id }}"
                                            class="p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors"
                                            title="Detail">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>

                                        {{-- EDIT --}}
                                        <button type="button" @click="openEditId = {{ $item->id }}"
                                            class="p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors"
                                            title="Edit">
                                            <i data-lucide="square-pen" class="w-5 h-5"></i>
                                        </button>

                                        {{-- DELETE --}}
                                        <form id="delete-item-{{ $item->id }}"
                                            action="{{ route('admin.inventaris.destroy', $item) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors"
                                                title="Hapus"
                                                onclick="confirmDeleteItem({{ $item->id }}, '{{ $item->nama }}')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
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

            {{-- PAGINATION --}}
            @if (method_exists($items, 'links'))
                <div class="mt-6">
                    {{ $items->appends(['search' => $search])->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- INCLUDE MODALS (Path disesuaikan ke folder admin.inventaris.modals) --}}

        {{-- Create --}}
        @if (view()->exists('admin.inventaris.modals.create'))
            @include('admin.inventaris.modals.create')
        @endif

        {{-- Loop Edit & Detail --}}
        @foreach ($items as $item)
            {{-- PERBAIKAN: Gunakan key 'alat' agar sesuai dengan variabel di dalam modal --}}

            @if (view()->exists('admin.inventaris.modals.detail'))
                @include('admin.inventaris.modals.detail', ['alat' => $item])
            @endif

            @if (view()->exists('admin.inventaris.modals.edit'))
                @include('admin.inventaris.modals.edit', ['alat' => $item])
            @endif
        @endforeach

        {{-- STYLE SCROLLBAR --}}
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

    {{-- SCRIPT DELETE --}}
    <script>
        function confirmDeleteItem(id, nama) {
            if (typeof Swal === 'undefined') {
                if (confirm('Yakin ingin menghapus ' + nama + '?')) {
                    document.getElementById('delete-item-' + id).submit();
                }
                return;
            }
            Swal.fire({
                title: 'Hapus Item?',
                html: `Anda yakin ingin menghapus <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan.`,
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
                    document.getElementById('delete-item-' + id).submit();
                }
            });
        }
    </script>
</x-layouts.admin>
