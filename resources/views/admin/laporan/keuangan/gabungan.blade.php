{{-- resources/views/laporan/keuangan/gabungan.blade.php --}}

<x-layouts.admin title="Laporan Keuangan - Gabungan">
    <div class="space-y-6">
        @include('admin.laporan.keuangan.partials.tabs')

        @php $rp = fn($n) => 'Rp ' . number_format((int)$n, 0, ',', '.'); @endphp

        <form method="GET" class="rounded-3xl border bg-white p-5 shadow-sm space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
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
                <div>
                    <label class="text-sm font-medium text-gray-700">Sumber</label>
                    <select name="sumber"
                        class="mt-1 w-full rounded-xl border-gray-200 focus:border-gray-400 focus:ring-0">
                        <option value="">Semua</option>
                        <option value="produk" {{ $sumber === 'produk' ? 'selected' : '' }}>Produk</option>
                        <option value="membership" {{ $sumber === 'membership' ? 'selected' : '' }}>Membership</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Metode</label>
                    <select name="metode"
                        class="mt-1 w-full rounded-xl border-gray-200 focus:border-gray-400 focus:ring-0">
                        <option value="">Semua</option>
                        <option value="cash" {{ $metode === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="transfer" {{ $metode === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="qris" {{ $metode === 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Cari</label>
                    <input type="text" name="q" value="{{ $q }}"
                        placeholder="No nota / buyer / creator"
                        class="mt-1 w-full rounded-xl border-gray-200 focus:border-gray-400 focus:ring-0">
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Terapkan
                </button>
                <a href="{{ route('admin.laporan.keuangan.gabungan') }}"
                    class="rounded-xl border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-3xl border bg-white p-6 shadow-sm" x-data="kpiCard({ value: {{ (int) $total }} })">
                <p class="text-sm font-medium text-gray-500">Total Pendapatan Gabungan</p>
                <p class="mt-2 text-2xl font-semibold" x-text="rupiah(display)"></p>
                <p class="mt-2 text-sm text-gray-500">Jumlah baris (hasil filter): <span
                        class="font-semibold">{{ $totalRows }}</span></p>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-base font-semibold">Distribusi Sumber Pendapatan</h2>
                <p class="text-sm text-gray-500">Produk vs Membership.</p>
                <div class="mt-4">
                    <canvas id="chartSumber" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Distribusi Metode Pembayaran</h2>
                <p class="text-sm text-gray-500">Cash, Transfer, dan QRIS.</p>
                <div class="mt-4">
                    <canvas id="chartMetode" height="180"></canvas>
                </div>
            </div>

            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold">Ringkas Angka</h2>
                <p class="text-sm text-gray-500">Berguna untuk audit cepat periode terpilih.</p>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total</span>
                        <span class="font-semibold">{{ $rp($total) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Jumlah baris</span>
                        <span class="font-semibold">{{ (int) $totalRows }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b">
                <h2 class="text-base font-semibold">Detail Transaksi Gabungan</h2>
                <p class="text-sm text-gray-500">Satu tabel untuk audit kas: Produk + Membership (revenue only).</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-5 py-3 text-left font-semibold">Sumber</th>
                            <th class="px-5 py-3 text-left font-semibold">No Nota</th>
                            <th class="px-5 py-3 text-left font-semibold">Buyer</th>
                            <th class="px-5 py-3 text-left font-semibold">Creator</th>
                            <th class="px-5 py-3 text-left font-semibold">Metode</th>
                            <th class="px-5 py-3 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($rows as $trx)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-700">
                                    {{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-xl border px-2 py-1 text-xs font-semibold text-gray-700">
                                        {{ strtoupper($trx->sumber) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-semibold text-gray-900">
                                    {{ $trx->no_nota }}
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $trx->buyer_name }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $trx->creator_name }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-xl border px-2 py-1 text-xs font-semibold text-gray-700">
                                        {{ strtoupper($trx->metode_pembayaran) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900">
                                    {{ $rp($trx->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    Tidak ada data pada rentang/filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t">
                {{ $rows->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            function rupiah(n) {
                return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
            }

            function kpiCard({
                value
            }) {
                return {
                    value: Number(value || 0),
                    display: 0,
                    init() {
                        const duration = 850;
                        const start = performance.now();
                        const to = this.value;
                        const step = (now) => {
                            const t = Math.min(1, (now - start) / duration);
                            const eased = 1 - Math.pow(1 - t, 3);
                            this.display = Math.floor(to * eased);
                            if (t < 1) requestAnimationFrame(step);
                        };
                        requestAnimationFrame(step);
                    },
                    rupiah
                }
            }
            window.kpiCard = window.kpiCard || kpiCard;

            const bySumber = @json($bySumber ?? []);
            const sumberData = [
                Number(bySumber['produk'] || 0),
                Number(bySumber['membership'] || 0),
            ];

            new Chart(document.getElementById('chartSumber'), {
                type: 'bar',
                data: {
                    labels: ['Produk', 'Membership'],
                    datasets: [{
                        label: 'Total',
                        data: sumberData,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => rupiah(ctx.raw)
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

            const byMetode = @json($byMetode ?? []);
            const metodeData = ['cash', 'transfer', 'qris'].map(k => Number(byMetode[k] || 0));

            new Chart(document.getElementById('chartMetode'), {
                type: 'doughnut',
                data: {
                    labels: ['Cash', 'Transfer', 'QRIS'],
                    datasets: [{
                        data: metodeData,
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
