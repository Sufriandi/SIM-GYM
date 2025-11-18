<x-layouts.admin
    :title="$pageTitle . ' – BETA GYM'"
    :page-title="$pageTitle"
    page-subtitle="Kelola data dasar produk yang dijual."
>
    {{-- HEADER UTAMA HALAMAN --}}
    <x-ui.section-header
        :title="$pageTitle"
        subtitle="Kelola data dasar produk yang dijual."
    >
        {{-- Tombol Tambah Produk Baru --}}
        <a href="{{ route('admin.produk.create') }}">
            <x-ui.button-primary>
                <i data-lucide="plus" class="w-5 h-5 mr-1"></i> Tambah Produk Baru
            </x-ui.button-primary>
        </a>
    </x-ui.section-header>

    {{-- FLASH MESSAGES (SWEETALERT2) --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#C73527',
                background: '#21160F',
                color: '#F8F2E7',
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#C73527',
                background: '#21160F',
                color: '#F8F2E7',
            });
        </script>
    @endif

    {{-- CARD UTAMA: TABEL PRODUK --}}
    <x-ui.card
        title="Daftar Produk"
        subtitle="Semua produk yang tersedia untuk penjualan dan manajemen stok."
        class="border-brand-borderSoft"
    >
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full border-collapse min-w-[1000px] text-sm">
                <thead>
                    <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                        <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[30%]">
                            Nama Produk
                        </th>
                        <th class="p-3 text-left text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                            Kategori
                        </th>
                        <th class="p-3 text-right text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[15%]">
                            Harga Jual
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                            Stok
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[10%]">
                            Status
                        </th>
                        <th class="p-3 text-center text-[11px] font-semibold uppercase tracking-wide text-text-muted w-[20%]">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-brand-borderSoft/80">
                    @forelse ($produks as $produk)
                        <tr class="hover:bg-brand-surface-50 transition-colors duration-150">
                            {{-- Nama Produk --}}
                            <td class="p-3 text-left align-top">
                                <div class="text-sm font-semibold text-text-main">
                                    {{ $produk->nama }}
                                </div>
                                <div class="text-[11px] text-text-muted italic max-w-xs truncate">
                                    {{ $produk->deskripsi ?? 'Tidak ada deskripsi.' }}
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="p-3 text-left align-top">
                                <span class="text-sm text-text-muted">
                                    {{ $produk->kategori }}
                                </span>
                            </td>

                            {{-- Harga Jual --}}
                            <td class="p-3 text-right align-top">
                                <span class="text-sm font-semibold text-gold-500">
                                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                </span>
                            </td>
                            
                            {{-- Stok --}}
                            <td class="p-3 text-center align-top">
                                <span class="text-sm font-bold text-text-main">
                                    {{ $produk->stok }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="p-3 text-center align-top">
                                @if ($produk->stok > 10)
                                    <x-ui.badge variant="success">Stok Aman</x-ui.badge>
                                @elseif ($produk->stok > 0)
                                    <x-ui.badge variant="warning">Stok Rendah</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">Stok Habis</x-ui.badge>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="p-3 text-center align-top">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Detail --}}
                                    <a href="{{ route('admin.produk.show', $produk) }}">
                                        <x-ui.button-secondary class="px-3 py-1.5 text-[11px]">
                                            Detail
                                        </x-ui.button-secondary>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.produk.edit', $produk) }}">
                                        <x-ui.button-secondary class="px-3 py-1.5 text-[11px] bg-blue-soft text-blue-700 hover:bg-blue-soft/80">
                                            Edit
                                        </x-ui.button-secondary>
                                    </a>

                                    {{-- Hapus --}}
                                    <form
                                        id="delete-form-{{ $produk->id }}"
                                        method="POST"
                                        action="{{ route('admin.produk.destroy', $produk) }}"
                                        class="inline-block"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button-secondary
                                            type="button"
                                            class="px-3 py-1.5 text-[11px] bg-danger-soft text-danger hover:bg-danger-soft/80"
                                            onclick="confirmDelete({{ $produk->id }}, '{{ $produk->nama }}')"
                                        >
                                            Hapus
                                        </x-ui.button-secondary>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-text-muted italic">
                                Belum ada data produk yang tersimpan.
                                <a href="{{ route('admin.produk.create') }}" class="text-gold-500 hover:underline">Tambahkan produk baru sekarang.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $produks->links() }}
        </div>
    </x-ui.card>

    {{-- SCRIPT KONFIRMASI SWEETALERT2 UNTUK TOMBOL HAPUS --}}
    <script>
        function confirmDelete(produkId, produkName) {
            Swal.fire({
                title: 'Hapus Produk?',
                text: `Anda yakin ingin menghapus produk ${produkName}? Aksi ini tidak dapat dibatalkan.`,
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
                    document.getElementById('delete-form-' + produkId).submit();
                }
            });
        }
    </script>

</x-layouts.admin>
