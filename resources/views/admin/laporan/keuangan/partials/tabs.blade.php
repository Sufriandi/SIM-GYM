{{-- resources/views/admin/laporan/keuangan/partials/tabs.blade.php --}}
@php
    $query = array_filter(request()->only(['from', 'to']));

    $tabs = [
        [
            'label'  => 'Ringkasan',
            'route'  => 'admin.laporan.keuangan.index',
            'active' => request()->routeIs('admin.laporan.keuangan.index'),
        ],
        [
            'label'  => 'Produk',
            'route'  => 'admin.laporan.keuangan.produk',
            'active' => request()->routeIs('admin.laporan.keuangan.produk*'),
        ],
        [
            'label'  => 'Membership',
            'route'  => 'admin.laporan.keuangan.membership',
            'active' => request()->routeIs('admin.laporan.keuangan.membership*'),
        ],
        [
            'label'  => 'Harian',
            'route'  => 'admin.laporan.keuangan.harian',
            'active' => request()->routeIs('admin.laporan.keuangan.harian*'),
        ],
        [
            'label'  => 'Audit Data',
            'route'  => 'admin.laporan.keuangan.gabungan',
            'active' => request()->routeIs('admin.laporan.keuangan.gabungan*'),
        ],
    ];
@endphp

<div class="inline-flex bg-brand-card border border-brand-borderSoft rounded-xl p-1 shadow-sm shrink-0 overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
    <div class="flex items-center gap-1">
        @foreach ($tabs as $tab)
            @if ($tab['active'])
                <span class="px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs font-bold rounded-lg bg-gold-50 text-gold-700 border border-gold-300/70 dark:bg-gold-950/50 dark:text-gold-400 dark:border-gold-800/80 shadow-xs cursor-default whitespace-nowrap">
                    {{ $tab['label'] }}
                </span>
            @else
                <a href="{{ route($tab['route'], $query) }}"
                   class="px-3.5 py-1.5 sm:px-4 sm:py-2 text-xs font-medium rounded-lg text-text-muted hover:text-text-main hover:bg-brand-shell/50 transition-all whitespace-nowrap">
                    {{ $tab['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
