{{-- resources/views/admin/laporan/keuangan/index.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Laporan Keuangan';

    // =========================================================================
    // LOGIC FROM CONTROLLER (UNCHANGED)
    // =========================================================================
    $from = $from ?? now()->subDays(29)->startOfDay();
    $to = $to ?? now()->endOfDay();

    $fromDate = $from instanceof \Carbon\CarbonInterface ? $from->format('Y-m-d') : (string) $from;
    $toDate = $to instanceof \Carbon\CarbonInterface ? $to->format('Y-m-d') : (string) $to;

    $totalProduk = (int) ($totalProduk ?? 0);
    $totalMembership = (int) ($totalMembership ?? 0);
    $totalHarian = (int) ($totalHarian ?? 0);
    $grandTotal = (int) ($grandTotal ?? 0);

    // Tren dari controller
    $daily = $daily ?? [];

    // Metode dari controller
    $grandMetode = $grandMetode ?? ['cash' => 0, 'transfer' => 0, 'qris' => 0];

    $rupiah = function ($n) {
        $n = (int) $n;
        return 'Rp ' . number_format($n, 0, ',', '.');
    };

    $fmtDate = function ($ymd) {
        try {
            return Carbon::parse($ymd)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return (string) $ymd;
        }
    };

    // Komposisi sumber pendapatan
    $komposisiSumber = [
        ['label' => 'Latihan Harian', 'total' => $totalHarian, 'color' => '#f59e0b'], // Amber
        ['label' => 'Membership', 'total' => $totalMembership, 'color' => '#3b82f6'], // Blue
        ['label' => 'Produk', 'total' => $totalProduk, 'color' => '#10b981'], // Emerald
    ];

    // Untuk tabel metode pembayaran
    $metodeRows = [
        ['metode' => 'cash', 'total' => (int) ($grandMetode['cash'] ?? 0)],
        ['metode' => 'transfer', 'total' => (int) ($grandMetode['transfer'] ?? 0)],
        ['metode' => 'qris', 'total' => (int) ($grandMetode['qris'] ?? 0)],
    ];
    usort($metodeRows, fn($a, $b) => $b['total'] <=> $a['total']);

    // Helper query string
    $qs = http_build_query(request()->query());
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Ringkasan pendapatan dari Latihan Harian, Membership, dan Penjualan Produk.">

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

    <div class="space-y-6">

        {{-- 1. NAVIGATION & FILTER --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

            {{-- Navigation Tabs --}}
            @include('admin.laporan.keuangan.partials.tabs')

            {{-- Filter & Actions (Right Side) --}}
            <div class="flex flex-wrap items-center gap-2 justify-start lg:justify-end">
                {{-- Date Filter Form --}}
                <form method="GET" action="{{ route('admin.laporan.keuangan.index') }}" class="flex items-center gap-2">
                    <div class="flex items-center gap-2 bg-white dark:bg-brand-card border border-brand-borderSoft rounded-xl px-3 py-1.5 shadow-xs">
                        <i data-lucide="calendar" class="w-4 h-4 text-gold-500 shrink-0"></i>
                        <input type="date" name="from" value="{{ $fromDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">
                        <span class="text-text-muted text-xs">➜</span>
                        <input type="date" name="to" value="{{ $toDate }}"
                            class="border-none text-xs font-medium text-text-main focus:ring-0 p-0 bg-transparent w-28 cursor-pointer">

                        <button type="submit"
                            class="ml-1 px-3 py-1 bg-black dark:bg-gold-500 text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gold-400 text-xs font-bold rounded-lg transition shadow-xs">
                            Filter
                        </button>
                    </div>
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
                            href="{{ route('admin.laporan.keuangan.excel') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                            <div class="min-w-0">
                                <div class="font-bold text-text-main">Ekspor Excel</div>
                                <div class="text-[11px] text-text-muted">Rekap ringkasan keuangan.</div>
                            </div>
                        </a>

                        <a class="flex items-center gap-2.5 px-4 py-2.5 text-xs hover:bg-brand-shell/40 transition"
                            href="{{ route('admin.laporan.keuangan.pdf') . ($qs ? '?' . $qs : '') }}">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-600"></i>
                            <div class="min-w-0">
                                <div class="font-bold text-text-main">Ekspor PDF</div>
                                <div class="text-[11px] text-text-muted">Siap cetak, rapi untuk laporan.</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. KPI CARDS --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Pendapatan (Dark Card Style) --}}
            <div class="relative overflow-hidden rounded-2xl bg-[#1A1A1A] p-5 shadow-xs border border-gray-800 flex flex-col justify-between min-h-[135px]">
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Pendapatan Bersih</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gold-500/20 text-gold-400">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-white stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($grandTotal) }}
                    </h3>
                </div>
                <div class="relative z-10 mt-3">
                    <div class="h-1.5 w-full rounded-full bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gold-500 w-full"></div>
                    </div>
                    <p class="mt-1.5 text-[10px] text-gray-400 truncate">Gabungan Produk, Membership & Harian</p>
                </div>
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-gold-500/10 blur-2xl pointer-events-none"></div>
            </div>

            {{-- Membership --}}
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px] hover:border-blue-300 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Membership</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($totalMembership) }}
                    </h3>
                </div>
                <div class="mt-3">
                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $grandTotal > 0 ? ($totalMembership / $grandTotal) * 100 : 0 }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-[10px] text-text-muted">
                        <span>Porsi Pendapatan</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $grandTotal > 0 ? round(($totalMembership / $grandTotal) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

            {{-- Produk --}}
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px] hover:border-emerald-300 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Produk Retail</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($totalProduk) }}
                    </h3>
                </div>
                <div class="mt-3">
                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $grandTotal > 0 ? ($totalProduk / $grandTotal) * 100 : 0 }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-[10px] text-text-muted">
                        <span>Porsi Pendapatan</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $grandTotal > 0 ? round(($totalProduk / $grandTotal) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>

            {{-- Harian --}}
            <div class="bg-white dark:bg-brand-card border border-brand-borderSoft rounded-2xl p-5 shadow-xs flex flex-col justify-between min-h-[135px] hover:border-amber-300 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-text-muted">Visit Harian</p>
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                            <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                    <h3 class="mt-2 text-xl xl:text-2xl font-extrabold text-text-main stat-number whitespace-nowrap tracking-tight">
                        {{ $rupiah($totalHarian) }}
                    </h3>
                </div>
                <div class="mt-3">
                    <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-amber-500" style="width: {{ $grandTotal > 0 ? ($totalHarian / $grandTotal) * 100 : 0 }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-[10px] text-text-muted">
                        <span>Porsi Pendapatan</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $grandTotal > 0 ? round(($totalHarian / $grandTotal) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. CHARTS AREA --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Main Trend Chart --}}
            <div class="lg:col-span-2">
                <x-ui.card class="p-6 border-brand-borderSoft h-full">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-text-main">Tren Pendapatan & Komposisi</h3>
                            <p class="text-xs text-text-muted mt-0.5">Grafik akumulasi harian berdasarkan sumber.</p>
                        </div>
                        {{-- Legend Custom --}}
                        <div class="flex gap-3 text-[10px] font-medium uppercase tracking-wide">
                            <div class="flex items-center gap-1.5"><span
                                    class="h-2 w-2 rounded-full bg-blue-500"></span> Member</div>
                            <div class="flex items-center gap-1.5"><span
                                    class="h-2 w-2 rounded-full bg-emerald-500"></span> Produk</div>
                            <div class="flex items-center gap-1.5"><span
                                    class="h-2 w-2 rounded-full bg-amber-500"></span> Harian</div>
                        </div>
                    </div>

                    <div class="relative h-[320px] w-full">
                        <canvas id="trendChart"></canvas>
                    </div>

                    {{-- Fallback Table --}}
                    <div id="trendFallback" class="mt-6 hidden">
                        <div class="overflow-x-auto custom-scrollbar rounded-lg border border-gray-100">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-50 text-text-muted font-bold">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Tanggal</th>
                                        <th class="px-4 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($daily as $t)
                                        <tr>
                                            <td class="px-4 py-2 text-text-main">{{ $fmtDate($t['tanggal'] ?? '') }}
                                            </td>
                                            <td class="px-4 py-2 text-right font-mono">{{ $rupiah($t['total'] ?? 0) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Right Column: Donut & Payment Methods --}}
            <div class="flex flex-col gap-6">
                {{-- Payment Methods --}}
                <x-ui.card class="p-6 border-brand-borderSoft flex-1">
                    <h3 class="text-sm font-bold text-text-main mb-4">Metode Pembayaran</h3>

                    <div class="space-y-4">
                        @foreach ($metodeRows as $r)
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-100 group-hover:border-gold-200 transition-colors">
                                        @if ($r['metode'] == 'cash')
                                            <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                                        @elseif($r['metode'] == 'transfer')
                                            <i data-lucide="arrow-left-right" class="w-4 h-4 text-blue-600"></i>
                                        @else
                                            <i data-lucide="qr-code" class="w-4 h-4 text-purple-600"></i>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs font-semibold text-text-muted uppercase tracking-wide">{{ $r['metode'] }}</span>
                                </div>
                                <span
                                    class="text-sm font-bold text-text-main stat-number">{{ $rupiah($r['total']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 pt-6 border-t border-dashed border-gray-100">
                        <h4 class="text-[10px] font-bold text-text-muted uppercase mb-3">Proporsi Sumber</h4>
                        <div class="relative h-[160px] w-full">
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- SCRIPT AREA --}}
        <script>
            (function() {
                const daily = @json($daily);
                const komposisi = @json($komposisiSumber);

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
                        const fb = document.getElementById('trendFallback');
                        if (fb) fb.classList.remove('hidden');
                        return;
                    }

                    Chart.defaults.font.family = "'Inter', sans-serif";
                    Chart.defaults.color = '#94a3b8'; // text-slate-400

                    // Trend chart (stacked bar)
                    const labels = daily.map(r => r.tanggal);
                    const dsHarian = daily.map(r => Number(r.harian || 0));
                    const dsMember = daily.map(r => Number(r.membership || 0));
                    const dsProduk = daily.map(r => Number(r.produk || 0));

                    const trendEl = document.getElementById('trendChart');
                    if (trendEl) {
                        new Chart(trendEl, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                        label: 'Harian',
                                        data: dsHarian,
                                        backgroundColor: '#f59e0b', // Amber 500
                                        borderRadius: 2,
                                        stack: 'income'
                                    },
                                    {
                                        label: 'Membership',
                                        data: dsMember,
                                        backgroundColor: '#3b82f6', // Blue 500
                                        borderRadius: 2,
                                        stack: 'income'
                                    },
                                    {
                                        label: 'Produk',
                                        data: dsProduk,
                                        backgroundColor: '#10b981', // Emerald 500
                                        borderRadius: 2,
                                        stack: 'income'
                                    },
                                ]
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
                                        titleColor: '#fbbf24', // Gold
                                        padding: 12,
                                        cornerRadius: 8,
                                        callbacks: {
                                            label: function(ctx) {
                                                return ` ${ctx.dataset.label}: Rp ${Number(ctx.parsed.y).toLocaleString('id-ID')}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        stacked: true,
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 10
                                            },
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    },
                                    y: {
                                        stacked: true,
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
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Komposisi sumber (donut)
                    const srcLabels = komposisi.map(x => x.label);
                    const srcValues = komposisi.map(x => Number(x.total || 0));
                    const srcColors = ['#f59e0b', '#3b82f6', '#10b981'];

                    const sourceEl = document.getElementById('sourceChart');
                    if (sourceEl) {
                        new Chart(sourceEl, {
                            type: 'doughnut',
                            data: {
                                labels: srcLabels,
                                datasets: [{
                                    data: srcValues,
                                    backgroundColor: srcColors,
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '70%',
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'right',
                                        labels: {
                                            boxWidth: 10,
                                            font: {
                                                size: 10
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#1e1e1e',
                                        callbacks: {
                                            label: function(ctx) {
                                                return ` ${ctx.label}: Rp ${Number(ctx.parsed).toLocaleString('id-ID')}`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }

                init();
            })();
        </script>
    </div>
</x-layouts.admin>
