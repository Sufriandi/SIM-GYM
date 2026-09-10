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
    $sumMetode = array_sum(array_map('intval', $grandMetode));
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan - Ringkasan</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
        }

        h2 {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            margin: 12px 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 10px;
        }

        .kpi-table td {
            padding: 8px 10px;
            border-radius: 6px;
            vertical-align: top;
        }

        .kpi-hero {
            background-color: #0f172a;
            color: #ffffff;
            border: 1px solid #1e293b;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .kpi-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .kpi-sub {
            font-size: 7.5px;
            margin-top: 3px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 4px;
        }

        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            padding: 5px 7px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table.data-table td {
            padding: 4.5px 7px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        table.data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: none;
            color: #0f172a;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge-cash {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-transfer {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-qris {
            background-color: #faf5ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 18px;
        }

        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>

<body>
    @include('admin.laporan.partials.pdf_header', [
        'reportTitle' => 'Laporan Keuangan - Ringkasan Eksekutif',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])

    {{-- KPI SUMMARY TABLE --}}
    <table class="kpi-table" style="margin-left: -6px; margin-right: -6px; width: calc(100% + 12px);">
        <tr>
            <td class="kpi-hero" style="width: 25%;">
                <div class="kpi-title" style="color: #94a3b8;">Total Pendapatan</div>
                <div class="kpi-value" style="color: #fbbf24;">{{ $rupiah($grandTotal) }}</div>
                <div class="kpi-sub" style="color: #cbd5e1;">Gabungan 3 Sumber</div>
            </td>
            <td class="kpi-card" style="width: 25%; border-left: 3px solid #3b82f6;">
                <div class="kpi-title" style="color: #64748b;">Membership</div>
                <div class="kpi-value" style="color: #1e293b;">{{ $rupiah($totalMembership) }}</div>
                <div class="kpi-sub" style="color: #64748b;">
                    Porsi: {{ $grandTotal > 0 ? round(($totalMembership / $grandTotal) * 100, 1) : 0 }}%
                </div>
            </td>
            <td class="kpi-card" style="width: 25%; border-left: 3px solid #10b981;">
                <div class="kpi-title" style="color: #64748b;">Produk Retail</div>
                <div class="kpi-value" style="color: #1e293b;">{{ $rupiah($totalProduk) }}</div>
                <div class="kpi-sub" style="color: #64748b;">
                    Porsi: {{ $grandTotal > 0 ? round(($totalProduk / $grandTotal) * 100, 1) : 0 }}%
                </div>
            </td>
            <td class="kpi-card" style="width: 25%; border-left: 3px solid #f59e0b;">
                <div class="kpi-title" style="color: #64748b;">Visit Harian</div>
                <div class="kpi-value" style="color: #1e293b;">{{ $rupiah($totalHarian) }}</div>
                <div class="kpi-sub" style="color: #64748b;">
                    Porsi: {{ $grandTotal > 0 ? round(($totalHarian / $grandTotal) * 100, 1) : 0 }}%
                </div>
            </td>
        </tr>
    </table>

    {{-- METODE PEMBAYARAN TABLE --}}
    <h2>1. Rekapitulasi Metode Pembayaran</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">No</th>
                <th>Metode Pembayaran</th>
                <th style="width: 120px;" class="text-center">Tipe Transaksi</th>
                <th style="width: 90px;" class="text-right">Persentase</th>
                <th style="width: 150px;" class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $metodeList = [
                    ['name' => 'CASH', 'key' => 'cash', 'badge' => 'badge-cash', 'desc' => 'Tunai / Kasir Langsung'],
                    ['name' => 'TRANSFER', 'key' => 'transfer', 'badge' => 'badge-transfer', 'desc' => 'Transfer Rekening Bank'],
                    ['name' => 'QRIS', 'key' => 'qris', 'badge' => 'badge-qris', 'desc' => 'Pembayaran Digital QRIS'],
                ];
            @endphp
            @foreach ($metodeList as $idx => $m)
                @php
                    $val = (int) ($grandMetode[$m['key']] ?? 0);
                    $pct = $sumMetode > 0 ? round(($val / $sumMetode) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $m['name'] }}</strong> <span style="color: #64748b; font-size: 8px;">({{ $m['desc'] }})</span></td>
                    <td class="text-center"><span class="{{ $m['badge'] }}">{{ $m['name'] }}</span></td>
                    <td class="text-right">{{ $pct }}%</td>
                    <td class="text-right" style="font-weight: bold;">{{ $rupiah($val) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Penerimaan:</td>
                <td class="text-right">100.0%</td>
                <td class="text-right" style="color: #0f172a; font-size: 9.5px;">{{ $rupiah($grandTotal) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- TREN HARIAN TABLE --}}
    <h2>2. Rekapitulasi Pendapatan Harian</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th style="width: 90px;">Tanggal</th>
                <th class="text-right">Produk Retail</th>
                <th class="text-right">Membership</th>
                <th class="text-right">Latihan Harian</th>
                <th class="text-right" style="width: 120px;">Total Harian</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totP = 0;
                $totM = 0;
                $totH = 0;
                $totAll = 0;
            @endphp
            @forelse ($daily as $idx => $d)
                @php
                    $tglLabel = Carbon::parse($d['tanggal'])->translatedFormat('d M Y');
                    $rowP = (int) ($d['produk'] ?? 0);
                    $rowM = (int) ($d['membership'] ?? 0);
                    $rowH = (int) ($d['harian'] ?? 0);
                    $rowTotal = $rowP + $rowM + $rowH;

                    $totP += $rowP;
                    $totM += $rowM;
                    $totH += $rowH;
                    $totAll += $rowTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td style="font-weight: 600;">{{ $tglLabel }}</td>
                    <td class="text-right">{{ $rowP > 0 ? $rupiah($rowP) : '-' }}</td>
                    <td class="text-right">{{ $rowM > 0 ? $rupiah($rowM) : '-' }}</td>
                    <td class="text-right">{{ $rowH > 0 ? $rupiah($rowH) : '-' }}</td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">{{ $rupiah($rowTotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 12px; color: #64748b; font-style: italic;">
                        Tidak ada transaksi keuangan pada periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; text-transform: uppercase;">Grand Total:</td>
                <td class="text-right">{{ $rupiah($totP) }}</td>
                <td class="text-right">{{ $rupiah($totM) }}</td>
                <td class="text-right">{{ $rupiah($totH) }}</td>
                <td class="text-right" style="color: #0f172a; font-size: 9.5px;">{{ $rupiah($totAll) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURE SECTION --}}
    <table class="signature-table">
        <tr>
            <td style="width: 55%; padding-right: 20px;">
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; font-size: 7.5px; color: #64748b; line-height: 1.4;">
                    <strong>Catatan Sistem:</strong><br>
                    Laporan ini digenerate secara otomatis oleh Sistem Informasi BETA GYM.<br>
                    Dokumen ini merupakan rekapitulasi resmi ringkasan finansial yang valid dan dapat dipertanggungjawabkan.
                </div>
            </td>
            <td style="width: 45%; text-align: center;">
                <div style="font-size: 9px; color: #334155;">Padang, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="font-size: 9px; font-weight: bold; color: #0f172a; margin-top: 2px;">Mengetahui, Manajemen BETA GYM</div>
                <div style="height: 40px;"></div>
                <div style="font-size: 9px; font-weight: bold; color: #0f172a; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 3px; min-width: 150px;">
                    ( Penanggung Jawab Keuangan )
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
