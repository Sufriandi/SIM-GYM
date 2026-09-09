{{-- resources/views/admin/laporan/keuangan/excel.blade.php --}}
@php
    use Carbon\Carbon;

    $fromDate = $from instanceof \Carbon\CarbonInterface ? $from->format('Y-m-d') : (string) $from;
    $toDate = $to instanceof \Carbon\CarbonInterface ? $to->format('Y-m-d') : (string) $to;
    $exportedAt = Carbon::now()->format('d M Y H:i');

    $daily = $daily ?? [];
    $grandMetode = $grandMetode ?? ['cash' => 0, 'transfer' => 0, 'qris' => 0];
@endphp
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
            mso-number-format: "#,##0";
        }

        .title {
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="title">BETA GYM</div>
    <div class="title" style="font-size:12px;">Laporan Keuangan - Ringkasan</div>
    <div>Periode: {{ Carbon::parse($fromDate)->translatedFormat('d M Y') }} -
        {{ Carbon::parse($toDate)->translatedFormat('d M Y') }}</div>
    <div><b>Diekspor pada:</b> {{ $exportedAt }}</div>
    <br>

    <table>
        <thead>
            <tr>
                <th>Ringkasan</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Pendapatan Bersih</td>
                <td class="text-right">{{ (int) $grandTotal }}</td>
            </tr>
            <tr>
                <td>Membership</td>
                <td class="text-right">{{ (int) $totalMembership }}</td>
            </tr>
            <tr>
                <td>Produk Retail</td>
                <td class="text-right">{{ (int) $totalProduk }}</td>
            </tr>
            <tr>
                <td>Latihan Harian</td>
                <td class="text-right">{{ (int) $totalHarian }}</td>
            </tr>
        </tbody>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cash</td>
                <td class="text-right">{{ (int) ($grandMetode['cash'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>Transfer</td>
                <td class="text-right">{{ (int) ($grandMetode['transfer'] ?? 0) }}</td>
            </tr>
            <tr>
                <td>QRIS</td>
                <td class="text-right">{{ (int) ($grandMetode['qris'] ?? 0) }}</td>
            </tr>
        </tbody>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Produk (Rp)</th>
                <th class="text-right">Membership (Rp)</th>
                <th class="text-right">Harian (Rp)</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daily as $d)
                <tr>
                    <td>{{ Carbon::parse($d['tanggal'])->translatedFormat('d M Y') }}</td>
                    <td class="text-right">{{ (int) ($d['produk'] ?? 0) }}</td>
                    <td class="text-right">{{ (int) ($d['membership'] ?? 0) }}</td>
                    <td class="text-right">{{ (int) ($d['harian'] ?? 0) }}</td>
                    <td class="text-right">{{ (int) ($d['total'] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
