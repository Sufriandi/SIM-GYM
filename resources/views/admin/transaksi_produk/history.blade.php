{{-- resources/views/admin/transaksi_produk/history.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Riwayat Transaksi Produk';
    $daftar_transaksi = $daftar_transaksi ?? collect();
    $members = $members ?? collect();

    // Variable Filter dari Controller
    $search = $search ?? '';
    $filterMetode = $filterMetode ?? '';
    $filterBuyer = $filterBuyer ?? '';
    $hasActiveFilter = $filterMetode || $filterBuyer;

    // Aman untuk paginator / collection
    $collection = method_exists($daftar_transaksi, 'getCollection')
        ? $daftar_transaksi->getCollection()
        : collect($daftar_transaksi);

    // Persiapan Data JSON untuk Modal Detail (AlpineJS)
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
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
    ];
@endphp

<x-layouts.admin :title="$pageTitle . ' – BETA GYM'" :page-title="$pageTitle"
    page-subtitle="Daftar transaksi produk yang sudah tercatat beserta detail itemnya.">

    {{-- WRAPPER UTAMA ALPINE JS --}}
    <div x-data="trxHistory(@js($trxData), @js($search))">

        <x-ui.section-header :title="$pageTitle" subtitle="Filter transaksi, lihat detail, cetak struk, atau batalkan." />
        <hr class="border-t border-brand-borderSoft mb-6">

        {{-- BARIS SEARCH + FILTER + TOMBOL ADD --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">

            {{-- SEARCH + FILTER POPUP --}}
            <div class="relative w-full md:max-w-xl">
                <form action="{{ route('admin.transaksi_produk.history') }}" method="GET"
                    class="flex-1 flex items-center rounded-full border border-brand-borderSoft bg-brand-card shadow-sm
                           focus-within:ring-2 focus-within:ring-primary-dark/50 transition-all hover:border-brand-borderSoft/80">

                    {{-- Icon Search --}}
                    <div class="pl-4 text-text-muted">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>

                    {{-- Input Search --}}
                    <input type="text" name="q" x-model.debounce.300ms="searchQuery"
                        placeholder="No nota / pembeli / produk..."
                        class="w-full bg-transparent border-none text-sm text-text-main placeholder:text-text-muted/60
                               focus:ring-0 py-3 pl-3 pr-2 rounded-l-full"
                        autocomplete="off">

                    {{-- Divider --}}
                    <div class="h-6 w-px bg-brand-borderSoft mx-2"></div>

                    {{-- Tombol Toggle Filter --}}
                    <button type="button" @click="showFilter = !showFilter"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors mr-2
                               rounded-full hover:bg-brand-surface-50"
                        :class="(showFilter || @js($hasActiveFilter)) ? 'text-gold-600 bg-brand-surface-50' :
                        'text-text-muted hover:text-text-main'">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>

                    {{-- Hidden Submit (untuk enter key) --}}
                    <button type="submit" class="hidden">Cari</button>
                </form>

                {{-- POPUP FILTER DROPDOWN --}}
                <div x-show="showFilter" x-cloak @click.outside="showFilter = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute top-full left-0 right-0 mt-3 bg-brand-card border border-brand-borderSoft rounded-2xl shadow-xl p-5 z-40">

                    <form action="{{ route('admin.transaksi_produk.history') }}" method="GET">
                        <input type="hidden" name="q" :value="searchQuery">

                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-2 border-b border-brand-borderSoft/50">
                                <h4 class="text-sm font-semibold text-text-main">Filter Data</h4>
                                <a href="{{ route('admin.transaksi_produk.history') }}"
                                    class="text-xs text-danger hover:underline">Reset</a>
                            </div>

                            {{-- Filter Metode --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                    Metode Pembayaran
                                </label>
                                <select name="metode_pembayaran"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                    <option value="">Semua Metode</option>
                                    @foreach ($metodeOptions as $k => $v)
                                        <option value="{{ $k }}" @selected($filterMetode === $k)>
                                            {{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Pembeli --}}
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-text-muted mb-1">
                                    Pembeli (Member)
                                </label>
                                <select name="buyer_member_id"
                                    class="w-full rounded-lg border bg-brand-shell text-xs text-text-main px-3 py-2
                                           border-brand-borderSoft focus:outline-none focus:ring-1 focus:ring-primary-dark">
                                    <option value="">Semua Pembeli</option>
                                    @foreach ($members as $m)
                                        <option value="{{ $m->id }}" @selected((string) $filterBuyer === (string) $m->id)>
                                            {{ $m->user->name ?? 'Member #' . $m->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary-dark hover:bg-primary-dark/90 text-white text-sm font-medium py-2 rounded-lg transition shadow-md">
                                Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TOMBOL TRANSAKSI BARU --}}
            <div class="flex items-center justify-end">
                <a href="{{ route('admin.transaksi_produk.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-dark text-white text-sm font-semibold
               hover:bg-primary-dark/90 transition shadow-md shrink-0">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Transaksi Baru
                </a>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <x-ui.card class="border-brand-borderSoft overflow-visible max-h-none">
            <div class="w-full overflow-x-auto overflow-y-hidden custom-scrollbar">
                <table class="w-full border-collapse text-sm min-w-[860px]">
                    <thead>
                        <tr class="border-b border-brand-borderSoft bg-brand-surface-50">
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[5%]">
                                No</th>
                            <th
                                class="p-3 text-left   text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                No Nota</th>
                            <th
                                class="p-3 text-left   text-[10px] font-bold uppercase tracking-wide text-text-muted w-[12%]">
                                Tanggal</th>
                            <th
                                class="p-3 text-left   text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Pembeli</th>
                            <th
                                class="p-3 text-right  text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Total</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[10%]">
                                Metode</th>
                            <th
                                class="p-3 text-left   text-[10px] font-bold uppercase tracking-wide text-text-muted w-[13%]">
                                Kasir</th>
                            <th
                                class="p-3 text-center text-[10px] font-bold uppercase tracking-wide text-text-muted w-[15%]">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-brand-borderSoft/80">
                        @forelse ($daftar_transaksi as $i => $t)
                            <tr class="hover:bg-brand-surface-50 transition-colors duration-150 group">
                                <td class="p-3 text-center text-text-muted align-middle">
                                    {{ (method_exists($daftar_transaksi, 'firstItem') ? $daftar_transaksi->firstItem() ?? 1 : 1) + $i }}
                                </td>

                                <td class="p-3 font-semibold text-text-main align-middle">
                                    {{ $t->no_nota }}
                                </td>

                                <td class="p-3 text-text-muted text-xs align-middle">
                                    {{ $t->tanggal_transaksi ? \Carbon\Carbon::parse($t->tanggal_transaksi)->translatedFormat('d M Y, H:i') : '-' }}
                                </td>

                                <td class="p-3 text-text-main align-middle truncate max-w-[150px]">
                                    {{ $t->buyer?->user?->name ?? ($t->buyer?->nama ?? 'Tamu') }}
                                </td>

                                <td class="p-3 text-right font-bold text-text-main align-middle whitespace-nowrap">
                                    Rp {{ number_format($t->total, 0, ',', '.') }}
                                </td>

                                <td class="p-3 text-center align-middle">
                                    <x-ui.badge variant="neutral">{{ strtoupper($t->metode_pembayaran) }}</x-ui.badge>
                                </td>

                                <td class="p-3 text-text-muted text-xs align-middle truncate max-w-[120px]">
                                    {{ $t->creator?->name ?? '-' }}
                                </td>

                                <td class="p-3 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- DETAIL --}}
                                        <button type="button" @click="openDetail({{ $t->id }})"
                                            class="relative group/btn p-2 rounded-full text-info hover:bg-info-soft/60 transition-colors duration-150">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                       text-[10px] font-medium text-info
                                                       opacity-0 group-hover/btn:opacity-100
                                                       transition-opacity duration-150 whitespace-nowrap">
                                                Detail
                                            </span>
                                        </button>

                                        {{-- CETAK --}}
                                        <a href="{{ route('admin.transaksi_produk.cetak', $t) }}" target="_blank"
                                            class="relative group/btn p-2 rounded-full text-yellow-600 hover:bg-yellow-100/60 transition-colors duration-150"
                                            title="Cetak Struk">
                                            <i data-lucide="printer" class="w-5 h-5"></i>
                                            <span
                                                class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                text-[10px] font-medium text-yellow-600
                                                opacity-0 group-hover/btn:opacity-100
                                                transition-opacity duration-150 whitespace-nowrap">
                                                Cetak
                                            </span>
                                        </a>

                                        {{-- BATALKAN --}}
                                        <form id="cancel-trx-{{ $t->id }}" method="POST"
                                            action="{{ route('admin.transaksi_produk.destroy', $t) }}"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                class="js-trx-cancel relative group/btn p-2 rounded-full text-danger hover:bg-danger-soft/60 transition-colors duration-150"
                                                data-form="cancel-trx-{{ $t->id }}"
                                                data-nota="{{ e($t->no_nota) }}">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                <span
                                                    class="pointer-events-none absolute -bottom-5 left-1/2 -translate-x-1/2
                                                           text-[10px] font-medium text-danger
                                                           opacity-0 group-hover/btn:opacity-100
                                                           transition-opacity duration-150 whitespace-nowrap">
                                                    Batal
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-text-muted italic">
                                    @if ($search || $hasActiveFilter)
                                        Tidak ada transaksi yang ditemukan dengan filter tersebut.
                                    @else
                                        Belum ada riwayat transaksi produk.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if (method_exists($daftar_transaksi, 'links'))
                <div class="px-4 py-4 border-t border-brand-borderSoft">
                    {{ $daftar_transaksi->links() }}
                </div>
            @endif
        </x-ui.card>

        {{-- INCLUDE MODAL DETAIL --}}
        @include('admin.transaksi_produk.modals.detail')
    </div>

    {{-- SCRIPT ALPINE --}}
    <script>
        function trxHistory(trxData, initialSearch) {
            return {
                showFilter: false,
                searchQuery: initialSearch || '',
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

    {{-- STYLE --}}
    <style>
        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F5E6D6;
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D4A757;
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #A67C39;
        }
    </style>

    {{-- SWEETALERT + HANDLERS --}}
    <script>
        (function() {
            function swalReady(cb, attempt) {
                attempt = attempt || 0;
                if (typeof Swal === 'undefined' && attempt < 60) {
                    return setTimeout(function() {
                        swalReady(cb, attempt + 1);
                    }, 50);
                }
                cb();
            }

            function trxSwalInfo(message) {
                var msg = String(message || '');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Info',
                        text: msg,
                        icon: 'info',
                        confirmButtonText: 'OK',
                        background: '#21160F',
                        color: '#F8F2E7',
                        confirmButtonColor: '#D4A757',
                    });
                    return;
                }
                alert(msg);
            }

            function trxSwalConfirmCancel(formId, noNota) {
                var text = 'Batalkan transaksi ' + String(noNota || '') +
                    '? Stok akan dikembalikan dan tidak bisa di-undo.';
                var form = document.getElementById(formId);

                if (!form) return;

                if (typeof Swal === 'undefined') {
                    if (confirm(text)) form.submit();
                    return;
                }

                Swal.fire({
                    title: 'Batalkan Transaksi?',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Batal',
                    background: '#21160F',
                    color: '#F8F2E7',
                    confirmButtonColor: '#C73527',
                    cancelButtonColor: '#6C5A46',
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            }

            // Delegation: aman dari masalah quote/parse error
            document.addEventListener('click', function(e) {

                var cancelEl = e.target.closest('.js-trx-cancel');
                if (cancelEl) {
                    e.preventDefault();
                    trxSwalConfirmCancel(
                        cancelEl.getAttribute('data-form'),
                        cancelEl.getAttribute('data-nota')
                    );
                }
            });

            // Session alert (success/error)
            document.addEventListener('DOMContentLoaded', function() {
                swalReady(function() {
                    @if (session('success'))
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Berhasil',
                                text: @js(session('success')),
                                icon: 'success',
                                confirmButtonText: 'OK',
                                background: '#21160F',
                                color: '#F8F2E7',
                                confirmButtonColor: '#D4A757',
                            });
                        } else {
                            alert(@js(session('success')));
                        }
                    @endif

                    @if (session('error'))
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Gagal',
                                text: @js(session('error')),
                                icon: 'error',
                                confirmButtonText: 'OK',
                                background: '#21160F',
                                color: '#F8F2E7',
                                confirmButtonColor: '#C73527',
                            });
                        } else {
                            alert(@js(session('error')));
                        }
                    @endif
                });
            });
        })();
    </script>
</x-layouts.admin>
