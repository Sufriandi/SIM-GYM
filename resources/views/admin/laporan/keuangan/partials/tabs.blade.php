@php
    $active = fn(string $route) => request()->routeIs($route);
    $qs = request()->only(['from', 'to']);
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Laporan Keuangan</h1>
        <p class="text-sm text-gray-500">Analisis pendapatan dari Produk, Membership, dan Latihan Harian.</p>
    </div>

    <div class="inline-flex flex-wrap gap-2 rounded-2xl border bg-white p-2 shadow-sm">
        <a href="{{ route('admin.laporan.keuangan.index', $qs) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.index') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Ringkasan
        </a>

        <a href="{{ route('admin.laporan.keuangan.produk', $qs) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.produk') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Produk
        </a>

        <a href="{{ route('admin.laporan.keuangan.membership', $qs) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.membership') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Membership
        </a>

        {{-- TAB BARU --}}
        <a href="{{ route('admin.laporan.keuangan.harian', $qs) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.harian') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Harian
        </a>

        <a href="{{ route('admin.laporan.keuangan.gabungan', $qs) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.gabungan') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Gabungan
        </a>
    </div>
</div>
