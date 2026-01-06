@php
    $active = fn(string $route) => request()->routeIs($route);
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Laporan Keuangan</h1>
        <p class="text-sm text-gray-500">Analisis pendapatan dari Membership dan Transaksi Produk.</p>
    </div>

    <div class="inline-flex flex-wrap gap-2 rounded-2xl border bg-white p-2 shadow-sm">
        <a href="{{ route('admin.laporan.keuangan.index') }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.index') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Ringkasan
        </a>
        <a href="{{ route('admin.laporan.keuangan.produk') }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.produk') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Produk
        </a>
        <a href="{{ route('admin.laporan.keuangan.membership') }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.membership') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Membership
        </a>
        <a href="{{ route('admin.laporan.keuangan.gabungan') }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition
                 {{ $active('admin.laporan.keuangan.gabungan') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Gabungan
        </a>
    </div>
</div>
