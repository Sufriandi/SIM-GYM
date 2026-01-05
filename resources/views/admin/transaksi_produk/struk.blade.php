@php
    use Carbon\Carbon;

    /** @var \App\Models\TransaksiProduk $trx */

    $rupiah = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');

    // Identitas toko (silakan isi)
    $gymName = 'BETA GYM';
    $alamat = ''; // opsional
    $telp = ''; // opsional

    $noNota = (string) $trx->no_nota;
    $tanggal = $trx->tanggal_transaksi
        ? Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d M Y H:i')
        : Carbon::now()->translatedFormat('d M Y H:i');

    $buyerName = $trx->buyer?->user?->name ?? ($trx->buyer?->nama ?? 'Tamu');
    $kasirName = $trx->creator?->name ?? '-';
    $metode = strtoupper((string) $trx->metode_pembayaran);

    $items = $trx->items ?? collect();
    $subtotal = $items->sum(fn($it) => (int) $it->subtotal);
    $total = (int) ($trx->total ?? $subtotal);

    $keterangan = trim((string) ($trx->keterangan ?? ''));

    // Batas karakter nama produk untuk 58mm
    $maxName = 28;
@endphp

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $noNota }}</title>

    <style>
        /* ===== THERMAL STANDARD 58mm ===== */
        @page {
            size: 58mm auto;
            margin: 4mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 11px;
            line-height: 1.25;
            color: #111;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #555;
        }

        .hr {
            border-top: 1px dashed #999;
            margin: 7px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .col-left {
            flex: 1 1 auto;
            min-width: 0;
        }

        .col-right {
            flex: 0 0 auto;
            text-align: right;
        }

        .title {
            font-weight: 800;
            font-size: 13px;
        }

        .meta {
            font-size: 10px;
        }

        .item {
            margin: 6px 0;
        }

        .name {
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 10px;
        }

        .totals .row {
            margin: 2px 0;
        }

        .grand {
            font-weight: 900;
            font-size: 12px;
        }

        .actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        button,
        a.btn {
            border: 1px solid #111;
            background: #fff;
            padding: 6px 10px;
            font: inherit;
            cursor: pointer;
            text-decoration: none;
            color: #111;
        }

        @media print {
            .actions {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="center">
        <div class="title">{{ $gymName }}</div>
        @if ($alamat !== '')
            <div class="muted meta">{{ $alamat }}</div>
        @endif
        @if ($telp !== '')
            <div class="muted meta">{{ $telp }}</div>
        @endif
        <div class="muted meta">Struk Transaksi Produk</div>
    </div>

    <div class="hr"></div>

    <div class="meta">
        <div class="row">
            <div class="col-left">No Nota</div>
            <div class="col-right" style="font-weight:800;">{{ $noNota }}</div>
        </div>
        <div class="row">
            <div class="col-left">Tanggal</div>
            <div class="col-right">{{ $tanggal }}</div>
        </div>
        <div class="row">
            <div class="col-left">Pembeli</div>
            <div class="col-right">{{ $buyerName }}</div>
        </div>
        <div class="row">
            <div class="col-left">Kasir</div>
            <div class="col-right">{{ $kasirName }}</div>
        </div>
        <div class="row">
            <div class="col-left">Metode</div>
            <div class="col-right">{{ $metode }}</div>
        </div>
    </div>

    <div class="hr"></div>

    @foreach ($items as $it)
        @php
            $nama = $it->produk?->nama ?? 'Produk';
            $namaShort = mb_strimwidth($nama, 0, $maxName, '…', 'UTF-8');

            $qty = (int) $it->qty;
            $harga = (int) $it->harga_satuan;
            $lineTotal = (int) $it->subtotal;
        @endphp

        <div class="item">
            <div class="name" title="{{ $nama }}">{{ $namaShort }}</div>
            <div class="line">
                <div class="muted">{{ $qty }} x {{ $rupiah($harga) }}</div>
                <div class="right">{{ $rupiah($lineTotal) }}</div>
            </div>
        </div>
    @endforeach

    <div class="hr"></div>

    <div class="totals">
        <div class="row">
            <div class="col-left">Subtotal</div>
            <div class="col-right">{{ $rupiah($subtotal) }}</div>
        </div>

        <div class="row grand">
            <div class="col-left">TOTAL</div>
            <div class="col-right">{{ $rupiah($total) }}</div>
        </div>
    </div>

    @if ($keterangan !== '')
        <div class="hr"></div>
        <div class="muted meta">Catatan:</div>
        <div class="meta">{{ $keterangan }}</div>
    @endif

    <div class="hr"></div>

    <div class="center muted meta">
        Terima kasih.
        <div>Powered by Gymnesia</div>
    </div>

    <div class="actions">
        <button onclick="window.print()">Print</button>
        <a class="btn" href="{{ route('admin.transaksi_produk.history') }}">Kembali</a>
    </div>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>

</html>
