@php use Carbon\Carbon; $rupiah = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.'); @endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Latihan Harian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .row { display: flex; gap: 10px; margin-bottom: 10px; }
        .box { border: 1px solid #ddd; padding: 10px; border-radius: 8px; flex: 1; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #eee; padding: 8px 6px; text-align: left; }
        th { background: #f6f6f6; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @include('admin.laporan.partials.pdf_header', [
        'reportTitle' => 'Laporan Keuangan - Latihan Harian',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])

    <div class="row">
        <div class="box">
            <div class="muted">Total Omzet Latihan Harian</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($total) }}</div>
        </div>
        <div class="box">
            <div class="muted">Jumlah Kunjungan</div>
            <div style="font-size:18px; font-weight:700;">{{ number_format($count) }}</div>
        </div>
    </div>

    <h2 style="font-size:13px; margin-top:16px;">Berdasarkan Kategori</h2>
    <table>
        <thead><tr><th>Kategori</th><th class="text-right">Jumlah</th><th class="text-right">Total</th></tr></thead>
        <tbody>
            @foreach($byKategori as $k)
                <tr><td>{{ ucfirst($k->kategori) }}</td><td class="text-right">{{ $k->jumlah }}</td><td class="text-right">{{ $rupiah($k->total) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="font-size:13px; margin-top:16px;">Detail Kunjungan</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Metode</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ Carbon::parse($r->tanggal)->translatedFormat('d M Y H:i') }}</td>
                    <td>{{ $r->nama }}</td>
                    <td>{{ ucfirst($r->kategori) }}</td>
                    <td>{{ strtoupper($r->metode_pembayaran) }}</td>
                    <td class="text-right">{{ $rupiah($r->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">Dicetak pada: {{ now()->translatedFormat('d M Y H:i') }}</div>
</body>
</html>
