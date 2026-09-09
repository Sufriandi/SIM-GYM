@php use Carbon\Carbon; $rupiah = fn($n) => 'Rp ' . number_format((int) $n, 0, ',', '.'); @endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Membership</title>
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
        'reportTitle' => 'Laporan Keuangan - Membership',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])

    <div class="row">
        <div class="box">
            <div class="muted">Total Omzet Membership</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($total) }}</div>
        </div>
        <div class="box">
            <div class="muted">Jumlah Transaksi</div>
            <div style="font-size:18px; font-weight:700;">{{ number_format($count) }}</div>
        </div>
    </div>

    <h2 style="font-size:13px; margin-top:16px;">Paket Terlaris (Top 10 Omzet)</h2>
    <table>
        <thead><tr><th>Paket</th><th class="text-right">Jml Transaksi</th><th class="text-right">Omzet</th></tr></thead>
        <tbody>
            @foreach($topPaket as $p)
                <tr><td>{{ $p->paket }}</td><td class="text-right">{{ $p->trx }}</td><td class="text-right">{{ $rupiah($p->total) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="font-size:13px; margin-top:16px;">Detail Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>No Nota</th>
                <th>Tanggal</th>
                <th>Member</th>
                <th>Paket</th>
                <th>Metode</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td>{{ $r->no_nota }}</td>
                    <td>{{ Carbon::parse($r->tanggal_transaksi)->translatedFormat('d M Y H:i') }}</td>
                    <td>{{ $r->buyer->user->name ?? '-' }}</td>
                    <td>{{ $r->paket->nama ?? '-' }}</td>
                    <td>{{ strtoupper($r->metode_pembayaran ?? '-') }}</td>
                    <td class="text-right">{{ $rupiah($r->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">Dicetak pada: {{ now()->translatedFormat('d M Y H:i') }}</div>
</body>
</html>
