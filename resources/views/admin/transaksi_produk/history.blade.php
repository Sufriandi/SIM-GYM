{{-- resources/views/admin/transaksi_produk/history.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Riwayat Transaksi Produk';
    $daftar_transaksi = $daftar_transaksi ?? collect();
    $members = $members ?? collect();

    // Aman untuk paginator / collection (tanpa ->items() pada Collection)
    $collection = method_exists($daftar_transaksi, 'getCollection')
        ? $daftar_transaksi->getCollection()
        : collect($daftar_transaksi);

    $trxData = $collection
        ->map(function ($t) {
            $tanggal = $t->tanggal_transaksi
                ? Carbon::parse($t->tanggal_transaksi)->translatedFormat('d M Y, H:i')
                : null;

            return [
                'id' => (int) $t->id,
                'no_nota' => (string) $t->no_nota,
                'tanggal_transaksi' => $tanggal,
                'metode_pembayaran' => strtoupper((string) $t->metode_pembayaran),
                'total' => (int) $t->total,
                'keterangan' => (string) ($t->keterangan ?? ''),
                'buyer' => $t->buyer?->user?->name ?? ($t->buyer?->nama ?? 'Tamu'),
                'kasir' => $t->creator?->name ?? '-',
                'items' => collect($t->items ?? [])
                    ->map(
                        fn($it) => [
                            'nama' => $it->produk?->nama ?? 'Produk',
                            'qty' => (int) $it->qty,
                            'harga_satuan' => (int) $it->harga_satuan,
                        ],
                    )
                    ->values(),
            ];
        })
        ->values();

    $metodeOptions = [
        '' => 'Semua Metode',
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Daftar transaksi produk yang sudah tercatat beserta detail itemnya.">

    {{-- FLASH MESSAGE --}}
    @if (session('success'))
        <div class="mb-4 bg-primary-soft border border-primary text-primary-dark px-4 py-3 rounded">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-danger-soft border border-danger text-danger px-4 py-3 rounded">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div x-data="trxHistory(@js($trxData))" class="space-y-6">
        <x-ui.section-header :title="$pageTitle" subtitle="Filter transaksi, lihat detail, atau batalkan transaksi." />
        <div class="mt-2 h-px w-full bg-brand-borderSoft/70"></div>

        <x-ui.card class="border-brand-borderSoft/80">
            <div class="flex flex-col md:flex-row md:items-end gap-3">
                <form method="GET" action="{{ route('admin.transaksi_produk.history') }}"
                    class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">

                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Cari</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                            placeholder="No nota / pembeli / produk..."
                            class="mt-1 w-full px-3 py-2 rounded-2xl bg-brand-shell border border-brand-borderSoft/70 text-sm">
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Metode</label>
                        <select name="metode_pembayaran"
                            class="mt-1 w-full px-3 py-2 rounded-2xl bg-brand-shell border border-brand-borderSoft/70 text-sm">
                            @foreach ($metodeOptions as $k => $v)
                                <option value="{{ $k }}" @selected(request('metode_pembayaran') === $k)>{{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-text-muted">Pembeli</label>
                        <select name="buyer_member_id"
                            class="mt-1 w-full px-3 py-2 rounded-2xl bg-brand-shell border border-brand-borderSoft/70 text-sm">
                            <option value="">Semua Pembeli</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->id }}" @selected((string) request('buyer_member_id') === (string) $m->id)>
                                    {{ $m->user->name ?? 'Member #' . $m->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3 flex items-center justify-end gap-2">
                        <a href="{{ route('admin.transaksi_produk.history') }}"
                            class="px-4 py-2 rounded-2xl border border-brand-borderSoft/70 text-sm hover:bg-brand-shell transition">
                            Reset
                        </a>
                        <button
                            class="px-4 py-2 rounded-2xl bg-brand-black text-white text-sm font-semibold hover:opacity-90 transition">
                            Filter
                        </button>
                    </div>
                </form>

                <div class="md:ml-auto">
                    <a href="{{ route('admin.transaksi_produk.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-gold-600 text-white text-sm font-semibold hover:opacity-90 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Transaksi Baru
                    </a>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="border-brand-borderSoft/80">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full border-collapse text-xs md:text-sm md:min-w-[950px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft/70 bg-brand-shell/70">
                            <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">No</th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">No Nota
                            </th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">Tanggal
                            </th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">Pembeli
                            </th>
                            <th class="px-3 py-2 text-right text-[11px] font-semibold uppercase tracking-wide">Total
                            </th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold uppercase tracking-wide">Metode
                            </th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wide">Kasir</th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold uppercase tracking-wide">Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($daftar_transaksi as $i => $t)
                            <tr class="border-b border-brand-borderSoft/60 hover:bg-brand-shell/40 transition-colors">
                                <td class="px-3 py-2">
                                    {{ (method_exists($daftar_transaksi, 'firstItem') ? $daftar_transaksi->firstItem() ?? 1 : 1) + $i }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-text-main">{{ $t->no_nota }}</td>
                                <td class="px-3 py-2 text-text-muted">
                                    {{ $t->tanggal_transaksi ? \Carbon\Carbon::parse($t->tanggal_transaksi)->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td class="px-3 py-2 text-text-main">
                                    {{ $t->buyer?->user?->name ?? ($t->buyer?->nama ?? 'Tamu') }}
                                </td>
                                <td class="px-3 py-2 text-right font-bold">
                                    Rp {{ number_format($t->total, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <x-ui.badge variant="neutral">{{ strtoupper($t->metode_pembayaran) }}</x-ui.badge>
                                </td>
                                <td class="px-3 py-2 text-text-main">
                                    {{ $t->creator?->name ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                            class="px-3 py-1.5 rounded-xl border border-brand-borderSoft/70 hover:bg-brand-shell transition text-xs font-semibold"
                                            @click="openDetail({{ $t->id }})">
                                            Detail
                                        </button>

                                        <form method="POST" action="{{ route('admin.transaksi_produk.destroy', $t) }}"
                                            onsubmit="return confirm('Batalkan transaksi {{ $t->no_nota }}? Stok akan dikembalikan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="px-3 py-1.5 rounded-xl bg-red-600 text-white hover:opacity-90 transition text-xs font-semibold">
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-10 text-center text-sm text-text-muted italic">
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($daftar_transaksi, 'links'))
                <div class="mt-4">
                    {{ $daftar_transaksi->links() }}
                </div>
            @endif
        </x-ui.card>

        @include('admin.transaksi_produk.modals.detail')
    </div>

    <script>
        function trxHistory(trxData) {
            return {
                openDetailModal: false,
                detail: null,
                trxData: Array.isArray(trxData) ? trxData : [],

                openDetail(id) {
                    this.detail = this.trxData.find(t => Number(t.id) === Number(id)) || null;
                    this.openDetailModal = !!this.detail;
                },

                close() {
                    this.openDetailModal = false;
                    this.detail = null;
                },

                formatRupiah(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(Number(n || 0));
                },

                itemSubtotal(it) {
                    return Number(it.qty || 0) * Number(it.harga_satuan || 0);
                }
            }
        }
    </script>
</x-layouts.admin>
