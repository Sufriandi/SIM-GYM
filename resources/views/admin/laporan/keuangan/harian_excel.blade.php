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
    <div class="title" style="font-size:12px;">Laporan Keuangan - Latihan Harian</div>
    <div>Periode: {{ Carbon::parse($fromDate)->translatedFormat('d M Y') }} - {{ Carbon::parse($toDate)->translatedFormat('d M Y') }}</div>
    <div><b>Diekspor pada:</b> {{ now()->format('d M Y H:i') }}</div>
    <br>
    <table>
        <tr><td>Total Omzet Latihan Harian</td><td class="text-right">{{ (int) $total }}</td></tr>
        <tr><td>Jumlah Kunjungan</td><td class="text-right">{{ $count }}</td></tr>
    </table>
    <br>
    <table>
        <thead><tr><th>Kategori</th><th class="text-right">Jumlah</th><th class="text-right">Total</th></tr></thead>
        <tbody>
            @foreach($byKategori as $k)
                <tr><td>{{ ucfirst($k->kategori) }}</td><td class="text-right">{{ $k->jumlah }}</td><td class="text-right">{{ (int) $k->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Nama</th><th>Kategori</th><th>Metode</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ Carbon::parse($r->tanggal)->format('d-m-Y H:i') }}</td>
                    <td>{{ $r->nama }}</td>
                    <td>{{ ucfirst($r->kategori) }}</td>
                    <td>{{ strtoupper($r->metode_pembayaran) }}</td>
                    <td class="text-right">{{ (int) $r->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
