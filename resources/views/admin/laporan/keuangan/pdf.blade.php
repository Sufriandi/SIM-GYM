{{-- resources/views/admin/laporan/keuangan/pdf.blade.php --}}
@php
    use Carbon\Carbon;

    $fromDate = $from instanceof \Carbon\CarbonInterface ? $from->format('Y-m-d') : (string) $from;
    $toDate = $to instanceof \Carbon\CarbonInterface ? $to->format('Y-m-d') : (string) $to;

    $rupiah = function ($n) {
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };

    $daily = $daily ?? [];
    $grandMetode = $grandMetode ?? ['cash' => 0, 'transfer' => 0, 'qris' => 0];
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            flex: 1;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border-bottom: 1px solid #eee;
            padding: 8px 6px;
            text-align: left;
        }

        th {
            background: #f6f6f6;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    @include('admin.laporan.partials.pdf_header', [
        'reportTitle' => 'Laporan Keuangan - Ringkasan',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])

    <div class="row" style="margin-top:10px;">
        <div class="box">
            <div class="muted">Total Pendapatan Bersih</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($grandTotal) }}</div>
        </div>
        <div class="box">
            <div class="muted">Membership</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($totalMembership) }}</div>
        </div>
        <div class="box">
            <div class="muted">Produk Retail</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($totalProduk) }}</div>
        </div>
        <div class="box">
            <div class="muted">Latihan Harian</div>
            <div style="font-size:18px; font-weight:700;">{{ $rupiah($totalHarian) }}</div>
        </div>
    </div>

    <h2 style="font-size:13px; margin-top:16px;">Metode Pembayaran</h2>
    <table>
        <thead>
            <tr>
                <th>Metode</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cash</td>
                <td class="text-right">{{ $rupiah($grandMetode['cash'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Transfer</td>
                <td class="text-right">{{ $rupiah($grandMetode['transfer'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>QRIS</td>
                <td class="text-right">{{ $rupiah($grandMetode['qris'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:13px; margin-top:16px;">Tren Harian</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Produk</th>
                <th class="text-right">Membership</th>
                <th class="text-right">Harian</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daily as $d)
                @php
                    $tglLabel = Carbon::parse($d['tanggal'])->translatedFormat('d M Y');
                    $rowTotal = ($d['produk'] ?? 0) + ($d['membership'] ?? 0) + ($d['harian'] ?? 0);
                @endphp
                <tr>
                    <td>{{ $tglLabel }}</td>
                    <td class="text-right">{{ $rupiah($d['produk'] ?? 0) }}</td>
                    <td class="text-right">{{ $rupiah($d['membership'] ?? 0) }}</td>
                    <td class="text-right">{{ $rupiah($d['harian'] ?? 0) }}</td>
                    <td class="text-right">{{ $rupiah($rowTotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">
        Dicetak pada: {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>

</html>
