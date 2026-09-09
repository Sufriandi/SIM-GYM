@php use Carbon\Carbon; @endphp
<!doctype html>
<html>
<head><meta charset="utf-8">
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; }
    th { background: #f0f0f0; font-weight: bold; text-align: left; }
    .text-right { text-align: right; mso-number-format: "#,##0"; }
    .title { font-size: 14px; font-weight: bold; }
</style>
</head>
<body>
    <div class="title">BETA GYM</div>
    <div class="title" style="font-size:12px;">Laporan Keuangan - Produk</div>
    <div>Periode: {{ Carbon::parse($fromDate)->translatedFormat('d M Y') }} - {{ Carbon::parse($toDate)->translatedFormat('d M Y') }}</div>
    <div><b>Diekspor pada:</b> {{ now()->format('d M Y H:i') }}</div>
    <br>
    <table>
        <tr><td>Total Omzet Produk</td><td class="text-right">{{ (int) $total }}</td></tr>
        <tr><td>Jumlah Transaksi</td><td class="text-right">{{ $count }}</td></tr>
    </table>
    <br>
    <table>
        <thead><tr><th>Produk Terlaris</th><th class="text-right">Qty Terjual</th></tr></thead>
        <tbody>
            @foreach($topProduk as $p)
                <tr><td>{{ $p->nama }}</td><td class="text-right">{{ $p->qty }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <table>
        <thead>
            <tr><th>No Nota</th><th>Tanggal</th><th>Pembeli</th><th>Metode</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->no_nota }}</td>
                    <td>{{ Carbon::parse($r->tanggal_transaksi)->format('d-m-Y H:i') }}</td>
                    <td>{{ $r->buyer->user->name ?? 'Non-member' }}</td>
                    <td>{{ strtoupper($r->metode_pembayaran) }}</td>
                    <td class="text-right">{{ (int) $r->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
