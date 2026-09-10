{{-- resources/views/admin/laporan/keuangan/membership.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = 'Laporan Membership';

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
    $topPaket = $topPaket ?? collect();

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

    $qs = http_build_query(request()->query());
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Analisis pendapatan membership, tren harian, metode pembayaran, dan paket terlaris.">

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
                background: rgba(0, 0, 0, .02);
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, .12);
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(0, 0, 0, .22);
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
    @endonce

    <div class="space-y-6 font-sans text-text-main">

        {{-- 1. NAVIGATION & FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

            {{-- Navigation Tabs --}}
            @include('admin.laporan.keuangan.partials.tabs')

            {{-- Filter & Actions (Right Side) --}}
            <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                <form method="GET" action="{{ route('admin.laporan.keuangan.membership') }}" class="flex flex-wrap items-center gap-2">
                    {{-- Date Range --}}
                    <div class="flex items-center gap-2 bg-white dark:bg-brand-card border border-brand-borderSoft rounded-xl px-3 py-1.5 shadow-xs">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500 shrink-0"></i>
                        <input type="date" name="from" value="{{ $fromDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">
                        <span class="text-text-muted text-xs">➜</span>
                        <input type="date" name="to" value="{{ $toDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">
                    </div>

                    {{-- Metode --}}
                    <div class="relative">
                        <select name="metode"
                            class="appearance-none bg-white dark:bg-brand-card border border-brand-borderSoft text-xs font-medium text-text-main rounded-xl pl-3 pr-8 py-2 focus:ring-1 focus:ring-gold-500 shadow-xs cursor-pointer">
                            <option value="">Semua Metode</option>
                            <option value="cash" @selected($metode === 'cash')>Cash</option>
                            <option value="transfer" @selected($metode === 'transfer')>Transfer</option>
                            <option value="qris" @selected($metode === 'qris')>QRIS</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-text-muted absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>

                    {{-- Search --}}
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-text-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nota / member / paket..."
                            class="bg-white dark:bg-brand-card border border-brand-borderSoft text-xs text-text-main rounded-xl pl-8 pr-3 py-2 focus:ring-1 focus:ring-gold-500 placeholder:text-text-muted/60 shadow-xs w-44">
                    </div>

                    {{-- Buttons --}}
                    <button type="submit"
                        class="px-3.5 py-2 bg-black dark:bg-gold-500 text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gold-400 text-xs font-bold rounded-xl transition shadow-xs">
                        Terapkan
                    </button>
                    @if (request()->anyFilled(['from', 'to', 'metode', 'q']))
                        <a href="{{ route('admin.laporan.keuangan.membership') }}"
                            class="px-3 py-2 border border-brand-borderSoft bg-white dark:bg-brand-card text-text-muted hover:text-text-main text-xs font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-brand-shell/50 transition shadow-xs">
                            Reset
                        </a>
                    @endif
                </form>

                {{-- Export Dropdown --}}
                <div x-data="{ open: false }" class="relative flex-shrink-0">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold
                               bg-white dark:bg-brand-card border border-brand-borderSoft hover:bg-gray-50 dark:hover:bg-brand-shell/50 transition shadow-xs text-text-main">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-text-main"></i>
                        <span>Ekspor</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-text-muted"></i>
                    </button>

                    <div x-show="open" x-cloak @click.away="open=false"
                        class="absolute right-0 mt-2 w-56 rounded-xl border border-brand-borderSoft bg-white dark:bg-brand-card shadow-xl overflow-hidden z-30 py-1">
                        <a class="flex items-center gap-2.5 px-4 py-2.5 text-xs hover:bg-brand-shell/40 transition"
                            href="{{ route('admin.laporan.keuangan.membership.excel') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                            <div class="min-w-0">
                                <div class="font-bold text-text-main">Ekspor Excel</div>
                                <div class="text-[11px] text-text-muted">Rekap laporan membership.</div>
                            </div>
                        </a>

                        <a class="flex items-center gap-2.5 px-4 py-2.5 text-xs hover:bg-brand-shell/40 transition"
                            href="{{ route('admin.laporan.keuangan.membership.pdf') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-600"></i>
                            <div class="min-w-0">
                                <div class="font-bold text-text-main">Ekspor PDF</div>
                                <div class="text-[11px] text-text-muted">Siap cetak, khusus laporan membership.</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2) KPI CARDS --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Total Membership (Hero Dark Card - Emerald/Dark Theme) --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#064e3b] p-5 shadow-xs border border-emerald-900/60 flex flex-col justify-between min-h-[135px]">
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-200/80">Omzet Membership</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-400/20 text-emerald-300">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-white stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($total) }}
                    </h3>
                </div>
                <div class="relative z-10 mt-3">
                    <div class="h-1.5 w-full rounded-full bg-emerald-900/60 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-400 w-full"></div>
                    </div>
                    <p class="mt-1.5 text-[10px] text-emerald-200/70 truncate">Revenue dari transaksi membership (pembayaran)</p>
                </div>
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-emerald-400/10 blur-2xl pointer-events-none"></div>
            </div>

            {{-- Volume --}}
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px] hover:border-emerald-300 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Volume Transaksi</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                            <i data-lucide="id-card" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ number_format($count, 0, ',', '.') }}
                    </h3>
                </div>
                <p class="mt-3 text-[10px] text-text-muted">Jumlah transaksi membership dalam periode.</p>
            </div>

            {{-- AOV --}}
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px] hover:border-emerald-300 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Rata-rata Transaksi (AOV)</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($avg) }}
                    </h3>
                </div>
                <p class="mt-3 text-[10px] text-text-muted">Rata-rata nilai per transaksi membership.</p>
            </div>
        </div>

        {{-- 3) CHART + SIDE --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- Trend --}}
            <div class="lg:col-span-8">
                <x-ui.card class="p-6 border-brand-borderSoft h-full">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-text-main">Tren Pendapatan Membership</h3>
                            <p class="text-xs text-text-muted mt-0.5">Grafik omzet harian membership.</p>
                        </div>
                    </div>

                    <div class="relative h-[320px] w-full">
                        <canvas id="memberDailyChart"></canvas>
                    </div>

                    <div id="memberDailyFallback" class="mt-6 hidden">
                        <div class="p-4 text-center text-sm text-text-muted bg-gray-50 rounded-lg">
                            Grafik tidak dapat dimuat. Periksa pemuatan Chart.js.
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-4 flex flex-col gap-6">

                {{-- Metode --}}
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

                {{-- Top Paket --}}
                <x-ui.card class="p-6 border-brand-borderSoft flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-text-main">Paket Terlaris</h3>
                        <span
                            class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 font-medium">
                            Top 5 Omzet
                        </span>
                    </div>

                    <div class="space-y-4">
                        @forelse($topPaket as $index => $p)
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-6 h-6 rounded bg-gray-100 text-gray-500 text-[10px] font-bold flex items-center justify-center">
                                    #{{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-text-main truncate"
                                        title="{{ $p->paket }}">
                                        {{ $p->paket }}
                                    </p>
                                    <p class="text-[10px] text-text-muted mt-0.5">
                                        {{ number_format((int) $p->trx, 0, ',', '.') }} transaksi
                                    </p>
                                </div>

                                <div class="text-right">
                                    <span
                                        class="text-sm font-bold text-emerald-600 stat-number">{{ $rupiah((int) $p->total) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-text-muted italic text-center py-4">Belum ada data paket.</div>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- 4) TABLE --}}
        <x-ui.card class="border-brand-borderSoft overflow-hidden">
            <div
                class="px-6 py-4 border-b border-brand-borderSoft flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-text-main">Riwayat Transaksi Membership</h3>
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
                            <th class="px-6 py-3">Member</th>
                            <th class="px-6 py-3">Paket</th>
                            <th class="px-6 py-3">Kasir</th>
                            <th class="px-6 py-3 text-center">Metode</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                        @forelse ($rows as $r)
                            @php
                                $buyerName = $r->buyer?->user?->name ?? '-';
                                $cashier = $r->creator?->name ?? '-';
                                $paketName = $r->paket?->nama ?? '-';
                            @endphp

                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3 text-text-main whitespace-nowrap">
                                    {{ $fmtDateTime($r->tanggal_transaksi) }}</td>
                                <td class="px-6 py-3 font-mono text-text-muted">{{ $r->no_nota }}</td>
                                <td class="px-6 py-3 font-medium text-text-main">{{ $buyerName }}</td>
                                <td class="px-6 py-3 text-text-muted">{{ $paketName }}</td>
                                <td class="px-6 py-3 text-text-muted">{{ $cashier }}</td>

                                <td class="px-6 py-3 text-center">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase border
                                        {{ $r->metode_pembayaran == 'cash'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                            : ($r->metode_pembayaran == 'transfer'
                                                ? 'bg-blue-50 text-blue-700 border-blue-100'
                                                : 'bg-purple-50 text-purple-700 border-purple-100') }}">
                                        {{ $r->metode_pembayaran ?: 'unknown' }}
                                    </span>
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-text-main stat-number">
                                    {{ $rupiah((int) $r->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-text-muted italic bg-gray-50/30">
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

        {{-- SCRIPT CHART --}}
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
                        const fb = document.getElementById('memberDailyFallback');
                        if (fb) fb.classList.remove('hidden');
                        return;
                    }

                    Chart.defaults.font.family = "'Inter', sans-serif";
                    Chart.defaults.color = '#94a3b8';

                    const labels = daily.map(r => r.tanggal);
                    const values = daily.map(r => Number(r.total || 0));

                    const el = document.getElementById('memberDailyChart');
                    if (!el) return;

                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Omzet Membership',
                                data: values,
                                borderColor: '#10b981',
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
