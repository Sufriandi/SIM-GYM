{{-- resources/views/admin/laporan/keuangan/produk.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = 'Laporan Produk';

    // =========================================================================
    // LOGIC FROM CONTROLLER (UNCHANGED)
    // =========================================================================
    $from = $from ?? now()->subDays(29)->startOfDay();
    $to = $to ?? now()->endOfDay();

    $fromDate = $from instanceof \Carbon\CarbonInterface ? $from->format('Y-m-d') : (string) $from;
    $toDate = $to instanceof \Carbon\CarbonInterface ? $to->format('Y-m-d') : (string) $to;

    $q = $q ?? '';
    $metode = $metode ?? '';

    $rows = $rows ?? null;

    $total = (int) ($total ?? 0);
    $count = (int) ($count ?? (method_exists($rows, 'total') ? $rows->total() : 0));
    $avg = (int) ($avg ?? ($count > 0 ? round($total / $count) : 0));

    $byMetode = $byMetode ?? [];
    $daily = $daily ?? [];
    $topProduk = $topProduk ?? collect();

    $rupiah = function ($n) {
        $n = (int) $n;
        return 'Rp ' . number_format($n, 0, ',', '.');
    };

    $fmtDateTime = function ($dt) {
        try {
            return Carbon::parse($dt)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return (string) $dt;
        }
    };

    $metodeLabel = function ($m) {
        $m = (string) $m;
        if ($m === '' || $m === 'unknown') {
            return 'Tidak diketahui';
        }
        return strtoupper($m);
    };

    // Helper query string
    $qs = http_build_query(request()->query());
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Analisis omzet penjualan produk, tren harian, metode pembayaran, dan produk terlaris.">

    @once
        <style>
            .stat-number {
                font-variant-numeric: tabular-nums;
                letter-spacing: -0.02em;
            }

            .custom-scrollbar::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.02);
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, 0.12);
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(0, 0, 0, 0.22);
            }
        </style>
    @endonce

    <div class="space-y-6 font-sans text-text-main">

        {{-- 1. NAVIGATION & FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between">

            {{-- Navigation Tabs (Consistent with Index) --}}
            <div
                class="inline-flex bg-white border border-brand-borderSoft rounded-lg p-1 shadow-sm overflow-x-auto custom-scrollbar">
                <div class="flex items-center gap-1">
                    <a href="{{ url()->route('admin.laporan.keuangan.index') }}?{{ $qs }}"
                        class="px-4 py-2 text-xs font-medium rounded-md text-text-muted hover:text-text-main hover:bg-gray-50 transition-all whitespace-nowrap">
                        Ringkasan
                    </a>

                    {{-- Active State --}}
                    <span
                        class="px-4 py-2 text-xs font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-default whitespace-nowrap">
                        Produk
                    </span>

                    <a href="{{ url()->route('admin.laporan.keuangan.membership') }}?{{ $qs }}"
                        class="px-4 py-2 text-xs font-medium rounded-md text-text-muted hover:text-text-main hover:bg-gray-50 transition-all whitespace-nowrap">
                        Membership
                    </a>
                    <a href="{{ url()->route('admin.laporan.keuangan.harian') }}?{{ $qs }}"
                        class="px-4 py-2 text-xs font-medium rounded-md text-text-muted hover:text-text-main hover:bg-gray-50 transition-all whitespace-nowrap">
                        Harian
                    </a>
                    <a href="{{ url()->route('admin.laporan.keuangan.gabungan') }}?{{ $qs }}"
                        class="px-4 py-2 text-xs font-medium rounded-md text-text-muted hover:text-text-main hover:bg-gray-50 transition-all whitespace-nowrap">
                        Audit Data
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <div
                class="flex-shrink-0 bg-white border border-brand-borderSoft rounded-lg p-3 shadow-sm w-full lg:w-auto">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Date Range --}}
                        <div class="flex items-center gap-2 bg-gray-50 rounded-md p-1 px-2 border border-gray-100">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <input type="date" name="from" value="{{ $fromDate }}"
                                class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-24 cursor-pointer">
                            <span class="text-text-muted text-xs">➜</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-24 cursor-pointer">
                        </div>

                        {{-- Metode Dropdown --}}
                        <div class="relative">
                            <select name="metode"
                                class="appearance-none border-none bg-gray-50 text-xs font-medium text-text-main rounded-md py-1.5 pl-3 pr-8 focus:ring-0 cursor-pointer border border-gray-100">
                                <option value="">Semua Metode</option>
                                <option value="cash" @selected($metode === 'cash')>Cash</option>
                                <option value="transfer" @selected($metode === 'transfer')>Transfer</option>
                                <option value="qris" @selected($metode === 'qris')>QRIS</option>
                            </select>
                            <i data-lucide="chevron-down"
                                class="w-3 h-3 text-text-muted absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Search --}}
                        <div class="relative flex-grow">
                            <i data-lucide="search"
                                class="w-3.5 h-3.5 text-text-muted absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Cari nota / pembeli..."
                                class="w-full border-none bg-gray-50 text-xs text-text-main rounded-md py-1.5 pl-9 pr-3 focus:ring-1 focus:ring-emerald-500 placeholder:text-text-muted/70">
                        </div>

                        {{-- Actions --}}
                        <button type="submit"
                            class="px-4 py-1.5 bg-black text-white text-xs font-bold rounded hover:bg-gray-800 transition shadow-sm">
                            Terapkan
                        </button>
                        <a href="{{ url()->current() }}"
                            class="px-3 py-1.5 border border-gray-200 text-text-muted text-xs font-medium rounded hover:bg-gray-50 transition">
                            Reset
                        </a>
                    </div>
                </form>

                {{-- Export Dropdown --}}
                <div x-data="{ open: false }" class="relative flex-shrink-0">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold
                               bg-white border border-brand-borderSoft hover:bg-gray-50 transition shadow-sm">
                        <i data-lucide="download" class="w-4 h-4 text-text-main"></i>
                        <span>Ekspor</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-text-muted"></i>
                    </button>
                    <div x-show="open" x-cloak @click.away="open=false"
                        class="absolute right-0 mt-2 w-64 rounded-2xl border border-brand-borderSoft bg-white shadow-2xl overflow-hidden z-30">
                        <a class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50"
                            href="{{ route('admin.laporan.keuangan.produk.excel') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <div class="min-w-0">
                                <div class="font-semibold text-text-main">Ekspor Excel</div>
                                <div class="text-xs text-text-muted">Rekap laporan produk.</div>
                            </div>
                        </a>
                        <a class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-gray-50"
                            href="{{ route('admin.laporan.keuangan.produk.pdf') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <div class="min-w-0">
                                <div class="font-semibold text-text-main">Ekspor PDF</div>
                                <div class="text-xs text-text-muted">Siap cetak, khusus laporan produk.</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. KPI CARDS --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            {{-- Omzet Produk (Dark Card - Emerald Theme) --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#064e3b] p-5 shadow-lg">
                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-200/80">Omzet Produk
                            Retail</p>
                        <h3 class="mt-2 text-3xl font-bold text-white stat-number">{{ $rupiah($total) }}</h3>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="h-1.5 w-full rounded-full bg-emerald-900/50">
                            <div class="h-1.5 rounded-full bg-emerald-400 w-full"></div>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-emerald-200/70">Total pendapatan dari penjualan barang</p>
                </div>
                {{-- Decorative Blob --}}
                <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-emerald-400/10 blur-3xl"></div>
            </div>

            {{-- Jumlah Transaksi --}}
            <x-ui.card class="p-5 border-brand-borderSoft hover:border-emerald-200 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Volume Transaksi</p>
                        <p class="mt-1 text-2xl font-bold text-text-main stat-number">
                            {{ number_format($count, 0, ',', '.') }}</p>
                    </div>
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-600">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="mt-4 text-[11px] text-text-muted">Jumlah struk penjualan yang diterbitkan.</p>
            </x-ui.card>

            {{-- AOV --}}
            <x-ui.card class="p-5 border-brand-borderSoft hover:border-emerald-200 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-text-muted">Rata-rata Transaksi
                            (AOV)</p>
                        <p class="mt-1 text-2xl font-bold text-text-main stat-number">{{ $rupiah($avg) }}</p>
                    </div>
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-600">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="mt-4 text-[11px] text-text-muted">Nilai rata-rata per pembelanjaan.</p>
            </x-ui.card>
        </div>

        {{-- 3. CHARTS AREA --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- Main Trend Chart --}}
            <div class="lg:col-span-8">
                <x-ui.card class="p-6 border-brand-borderSoft h-full">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-text-main">Tren Penjualan Produk</h3>
                            <p class="text-xs text-text-muted mt-0.5">Grafik omzet harian dalam periode terpilih.</p>
                        </div>
                    </div>

                    <div class="relative h-[320px] w-full">
                        <canvas id="produkDailyChart"></canvas>
                    </div>

                    <div id="produkDailyFallback" class="mt-6 hidden">
                        <div class="p-4 text-center text-sm text-text-muted bg-gray-50 rounded-lg">
                            Grafik tidak dapat dimuat. Silakan periksa koneksi internet untuk memuat library Chart.js.
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Side Stats: Metode & Top Produk --}}
            <div class="lg:col-span-4 flex flex-col gap-6">

                {{-- Metode Pembayaran --}}
                <x-ui.card class="p-6 border-brand-borderSoft">
                    <h3 class="text-sm font-bold text-text-main mb-4">Metode Pembayaran</h3>
                    <div class="space-y-3">
                        @php $sumMetode = array_sum(array_map('intval', $byMetode)); @endphp
                        @forelse($byMetode as $m => $v)
                            @php
                                $v = (int) $v;
                                $pct = $sumMetode > 0 ? round(($v / max($sumMetode, 1)) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 group-hover:border-emerald-200 transition-colors">
                                        @if ($m == 'cash')
                                            <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                                        @elseif($m == 'transfer')
                                            <i data-lucide="arrow-left-right" class="w-4 h-4 text-blue-600"></i>
                                        @else
                                            <i data-lucide="qr-code" class="w-4 h-4 text-purple-600"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span
                                            class="block text-xs font-semibold text-text-muted uppercase tracking-wide">{{ $metodeLabel($m) }}</span>
                                        <div class="h-1 w-16 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="block text-sm font-bold text-text-main stat-number">{{ $rupiah($v) }}</span>
                                    <span class="block text-[10px] text-text-muted">{{ $pct }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-text-muted italic text-center py-4">Tidak ada data pembayaran.
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                {{-- Top Produk --}}
                <x-ui.card class="p-6 border-brand-borderSoft flex-1" x-data="{ tab: 'suplemen' }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-text-main">Produk Terlaris per Kategori</h3>
                        <span
                            class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 font-medium">
                            Top 5 Qty
                        </span>
                    </div>

                    {{-- Tabs --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach (['suplemen' => 'Suplemen', 'minuman' => 'Minuman', 'lainnya' => 'Lainnya'] as $k => $label)
                            <button type="button" @click="tab='{{ $k }}'"
                                :class="tab === '{{ $k }}' ?
                                    'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                    'bg-gray-50 text-text-muted border-gray-200 hover:bg-gray-100'"
                                class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide rounded-md border transition">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Content per tab --}}
                    @php
                        $topProdukByKategori = $topProdukByKategori ?? [];
                    @endphp

                    <div class="space-y-4">
                        @foreach (['suplemen', 'minuman', 'lainnya'] as $kat)
                            <div x-show="tab==='{{ $kat }}'" x-cloak class="space-y-4">
                                @php
                                    $list = $topProdukByKategori[$kat] ?? collect();
                                @endphp

                                @forelse($list as $index => $tp)
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex-shrink-0 w-6 h-6 rounded bg-gray-100 text-gray-500 text-[10px] font-bold flex items-center justify-center">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-text-main truncate"
                                                title="{{ $tp->nama }}">
                                                {{ $tp->nama }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-bold text-emerald-600 stat-number">
                                                {{ number_format((int) $tp->qty, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] text-text-muted ml-0.5">pcs</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-text-muted italic text-center py-4">
                                        Belum ada data penjualan pada kategori ini.
                                    </div>
                                @endforelse
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-4 text-[10px] text-text-muted">
                        Catatan: per kategori mengurangi bias “fast moving item” pada peringkat global.
                    </p>
                </x-ui.card>

            </div>
        </div>

        {{-- 4. TABLE --}}
        <x-ui.card class="border-brand-borderSoft overflow-hidden">
            <div
                class="px-6 py-4 border-b border-brand-borderSoft flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-text-main">Riwayat Transaksi Produk</h3>
                    <p class="text-xs text-text-muted mt-0.5">Menampilkan data sesuai filter yang aktif.</p>
                </div>
                <div
                    class="text-[10px] font-medium bg-gray-100 text-text-muted px-3 py-1 rounded-full border border-gray-200">
                    Total: {{ $rows ? $rows->total() : 0 }} Transaksi
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <thead
                        class="bg-gray-50 text-text-muted font-semibold uppercase tracking-wider border-b border-brand-borderSoft">
                        <tr>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">No Nota</th>
                            <th class="px-6 py-3">Pembeli</th>
                            <th class="px-6 py-3">Kasir</th>
                            <th class="px-6 py-3 text-center">Metode</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($rows as $r)
                            @php
                                $buyerName = $r->buyer?->user?->name ?? 'Guest';
                                $cashier = $r->creator?->name ?? '-';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3 text-text-main whitespace-nowrap">
                                    {{ $fmtDateTime($r->tanggal_transaksi) }}</td>
                                <td class="px-6 py-3 font-mono text-text-muted">{{ $r->no_nota }}</td>
                                <td class="px-6 py-3 font-medium text-text-main">{{ $buyerName }}</td>
                                <td class="px-6 py-3 text-text-muted">{{ $cashier }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase border 
                                        {{ $r->metode_pembayaran == 'cash'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                            : ($r->metode_pembayaran == 'transfer'
                                                ? 'bg-blue-50 text-blue-700 border-blue-100'
                                                : 'bg-purple-50 text-purple-700 border-purple-100') }}">
                                        {{ $r->metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-text-main stat-number">
                                    {{ $rupiah($r->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-text-muted italic bg-gray-50/30">
                                    Tidak ada data transaksi yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rows && $rows->hasPages())
                <div class="px-6 py-4 border-t border-brand-borderSoft bg-gray-50/30">
                    {{ $rows->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- SCRIPT --}}
        <script>
            (function() {
                const daily = @json($daily);

                function loadScript(src) {
                    return new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = src;
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                }

                async function ensureChart() {
                    if (typeof window.Chart !== 'undefined') return true;
                    try {
                        await loadScript('https://cdn.jsdelivr.net/npm/chart.js');
                        return typeof window.Chart !== 'undefined';
                    } catch (e) {
                        return false;
                    }
                }

                function rupiahTick(v) {
                    if (v >= 1000000) return (v / 1000000).toFixed(1) + 'jt';
                    if (v >= 1000) return (v / 1000).toFixed(0) + 'rb';
                    return v;
                }

                async function init() {
                    const ok = await ensureChart();
                    if (!ok) {
                        const fb = document.getElementById('produkDailyFallback');
                        if (fb) fb.classList.remove('hidden');
                        return;
                    }

                    Chart.defaults.font.family = "'Inter', sans-serif";
                    Chart.defaults.color = '#94a3b8';

                    const labels = daily.map(r => r.tanggal);
                    const values = daily.map(r => Number(r.total || 0));

                    const el = document.getElementById('produkDailyChart');
                    if (!el) return;

                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Omzet Produk',
                                data: values,
                                borderColor: '#10b981', // Emerald 500
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: '#10b981',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#1e1e1e',
                                    titleColor: '#10b981',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(ctx) {
                                            return `Rp ${Number(ctx.parsed.y).toLocaleString('id-ID')}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 7
                                    }
                                },
                                y: {
                                    border: {
                                        display: false
                                    },
                                    grid: {
                                        color: '#f1f5f9',
                                        borderDash: [4, 4]
                                    },
                                    ticks: {
                                        callback: (v) => rupiahTick(v),
                                        font: {
                                            size: 10
                                        },
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                }

                init();
            })();
        </script>

    </div>
</x-layouts.admin>
