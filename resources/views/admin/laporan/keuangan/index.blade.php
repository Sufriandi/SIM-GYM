{{-- resources/views/laporan/keuangan/index.blade.php --}}

<x-layouts.admin title="Laporan Keuangan">
    <div class="space-y-6">
        @include('admin.laporan.keuangan.partials.tabs')

        {{-- Filter Range --}}
        <form method="GET" class="rounded-3xl border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Dari</label>
                        <input type="date" name="from" value="{{ optional($from)->toDateString() }}"
                            class="mt-1 w-full rounded-xl border-gray-200 focus:border-gray-400 focus:ring-0">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Sampai</label>
                        <input type="date" name="to" value="{{ optional($to)->toDateString() }}"
                            class="mt-1 w-full rounded-xl border-gray-200 focus:border-gray-400 focus:ring-0">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit"
                        class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Terapkan
                    </button>

                    {{-- Preset --}}
                    <a href="{{ route('admin.laporan.keuangan.index', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}"
                        class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        7 Hari
                    </a>
                    <a href="{{ route('admin.laporan.keuangan.index', ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()]) }}"
                        class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        30 Hari
                    </a>
                    <a href="{{ route('admin.laporan.keuangan.index', ['from' => now()->subDays(89)->toDateString(), 'to' => now()->toDateString()]) }}"
                        class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        90 Hari
                    </a>
                </div>
            </div>
        </form>

        {{-- KPI Cards (count-up animasi) --}}
        @php
            $rp = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
            $produkTotal = (int) $totalProduk;
            $memberTotal = (int) $totalMembership;
            $grand = (int) $grandTotal;
        @endphp

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-3xl border bg-white p-6 shadow-sm" x-data="kpiCard({ value: {{ $produkTotal }} })">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendapatan Produk</p>
                        <p class="mt-2 text-2xl font-semibold" x-text="rupiah(display)"></p>
                    </div>
                    <span class="rounded-2xl bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        Produk
                    </span>
                </div>
                <div class="mt-4 h-2 w-full rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-gray-900" :style="`width:${progress}%`"></div>
                </div>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm" x-data="kpiCard({ value: {{ $memberTotal }} })">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendapatan Membership</p>
                        <p class="mt-2 text-2xl font-semibold" x-text="rupiah(display)"></p>
                    </div>
                    <span class="rounded-2xl bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                        Membership
                    </span>
                </div>
                <div class="mt-4 h-2 w-full rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-gray-900" :style="`width:${progress}%`"></div>
                </div>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm" x-data="kpiCard({ value: {{ $grand }} })">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Gabungan</p>
                        <p class="mt-2 text-2xl font-semibold" x-text="rupiah(display)"></p>
                    </div>
                    <span class="rounded-2xl bg-gray-900 px-3 py-1 text-xs font-semibold text-white">
                        Total
                    </span>
                </div>
                <div class="mt-4 h-2 w-full rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-gray-900" :style="`width:${progress}%`"></div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-3xl border bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">Tren Pendapatan Harian</h2>
                        <p class="text-sm text-gray-500">Gabungan Produk + Membership dalam periode terpilih.</p>
                    </div>
                </div>

                <div class="mt-4">
                    <canvas id="chartDaily" height="120"></canvas>
                </div>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Komposisi Pendapatan</h2>
                <p class="text-sm text-gray-500">Produk vs Membership.</p>
                <div class="mt-4">
                    <canvas id="chartComposition" height="220"></canvas>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Produk</span>
                        <span class="font-semibold">{{ $rp($produkTotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Membership</span>
                        <span class="font-semibold">{{ $rp($memberTotal) }}</span>
                    </div>
                    <div class="border-t pt-2 flex items-center justify-between">
                        <span class="text-gray-900 font-semibold">Total</span>
                        <span class="text-gray-900 font-semibold">{{ $rp($grand) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metode Pembayaran Breakdown --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Metode Pembayaran (Produk)</h2>
                <p class="text-sm text-gray-500">Distribusi pendapatan berdasarkan metode pembayaran.</p>
                <div class="mt-4">
                    <canvas id="chartProdukMetode" height="200"></canvas>
                </div>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Metode Pembayaran (Membership)</h2>
                <p class="text-sm text-gray-500">Distribusi pendapatan berdasarkan metode pembayaran.</p>
                <div class="mt-4">
                    <canvas id="chartMemberMetode" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            // ---------- Helpers ----------
            function rupiah(n) {
                n = Number(n || 0);
                return 'Rp ' + n.toLocaleString('id-ID');
            }

            function kpiCard({
                value
            }) {
                return {
                    value: Number(value || 0),
                    display: 0,
                    progress: 0,
                    init() {
                        const duration = 900;
                        const start = performance.now();
                        const from = 0;
                        const to = this.value;

                        const step = (now) => {
                            const t = Math.min(1, (now - start) / duration);
                            // easing
                            const eased = 1 - Math.pow(1 - t, 3);
                            this.display = Math.floor(from + (to - from) * eased);

                            // progress bar: relatif terhadap total halaman (sekadar visual)
                            this.progress = Math.min(100, Math.round(eased * 100));
                            if (t < 1) requestAnimationFrame(step);
                        };
                        requestAnimationFrame(step);
                    },
                    rupiah
                }
            }

            // expose for Alpine
            window.kpiCard = kpiCard;

            // ---------- Data from backend ----------
            const daily = @json($daily);
            const labels = daily.map(d => d.tanggal);
            const dataProduk = daily.map(d => Number(d.produk || 0));
            const dataMember = daily.map(d => Number(d.membership || 0));
            const dataTotal = daily.map(d => Number(d.total || 0));

            const produkByMetode = @json($produkByMetode);
            const membershipByMetode = @json($membershipByMetode);

            const metodeLabels = ['cash', 'transfer', 'qris'];

            const produkMetodeData = metodeLabels.map(k => Number(produkByMetode[k] || 0));
            const memberMetodeData = metodeLabels.map(k => Number(membershipByMetode[k] || 0));

            // ---------- Charts ----------
            // Daily line chart (Total + optional breakdown)
            new Chart(document.getElementById('chartDaily'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                            label: 'Total',
                            data: dataTotal,
                            tension: 0.35
                        },
                        {
                            label: 'Produk',
                            data: dataProduk,
                            tension: 0.35
                        },
                        {
                            label: 'Membership',
                            data: dataMember,
                            tension: 0.35
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.dataset.label}: ${rupiah(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: (v) => rupiah(v)
                            }
                        }
                    }
                }
            });

            // Composition donut (produk vs membership)
            new Chart(document.getElementById('chartComposition'), {
                type: 'doughnut',
                data: {
                    labels: ['Produk', 'Membership'],
                    datasets: [{
                        data: [{{ (int) $produkTotal }}, {{ (int) $memberTotal }}],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${rupiah(ctx.raw)}`
                            }
                        }
                    }
                }
            });

            // Produk metode
            new Chart(document.getElementById('chartProdukMetode'), {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Transfer', 'QRIS'],
                    datasets: [{
                        data: produkMetodeData,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${rupiah(ctx.raw)}`
                            }
                        }
                    }
                }
            });

            // Membership metode
            new Chart(document.getElementById('chartMemberMetode'), {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Transfer', 'QRIS'],
                    datasets: [{
                        data: memberMetodeData,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${rupiah(ctx.raw)}`
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
</x-layouts.admin>
