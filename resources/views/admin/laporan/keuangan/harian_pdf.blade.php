{{-- resources/views/admin/laporan/keuangan/harian_pdf.blade.php --}}
@php
    use Carbon\Carbon;

    $fromDate = isset($from) && $from instanceof \Carbon\CarbonInterface
        ? $from->format('Y-m-d')
        : (string) ($fromDate ?? ($from ?? now()->subDays(29)->format('Y-m-d')));

    $toDate = isset($to) && $to instanceof \Carbon\CarbonInterface
        ? $to->format('Y-m-d')
        : (string) ($toDate ?? ($to ?? now()->format('Y-m-d')));

    $rupiah = function ($n) {
        return 'Rp ' . number_format((int) $n, 0, ',', '.');
    };

    $avg = (int) ($avg ?? ($count > 0 ? round($total / $count) : 0));
    $sumKategoriOmzet = $byKategori ? $byKategori->sum('total') : 0;
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Latihan Harian</title>
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
            background-color: #78350f;
            color: #ffffff;
            border: 1px solid #92400e;
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

        .badge-kategori {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
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
        'reportTitle' => 'Laporan Pendapatan Latihan Harian',
        'periodeAwal' => $fromDate,
        'periodeAkhir' => $toDate,
    ])

    {{-- KPI SUMMARY TABLE --}}
    <table class="kpi-table" style="margin-left: -6px; margin-right: -6px; width: calc(100% + 12px);">
        <tr>
            <td class="kpi-hero" style="width: 34%;">
                <div class="kpi-title" style="color: #fde68a;">Total Omzet Harian</div>
                <div class="kpi-value" style="color: #fcd34d;">{{ $rupiah($total) }}</div>
                <div class="kpi-sub" style="color: #fef3c7;">Penerimaan Kunjungan Non-Member</div>
            </td>
            <td class="kpi-card" style="width: 33%; border-left: 3px solid #f59e0b;">
                <div class="kpi-title" style="color: #64748b;">Volume Kunjungan</div>
                <div class="kpi-value" style="color: #1e293b;">{{ number_format($count, 0, ',', '.') }} <span style="font-size: 9px; font-weight: normal; color: #64748b;">Pengunjung</span></div>
                <div class="kpi-sub" style="color: #64748b;">Total Karcis / Tiket Terjual</div>
            </td>
            <td class="kpi-card" style="width: 33%; border-left: 3px solid #10b981;">
                <div class="kpi-title" style="color: #64748b;">Rata-rata Tarif</div>
                <div class="kpi-value" style="color: #1e293b;">{{ $rupiah($avg) }}</div>
                <div class="kpi-sub" style="color: #64748b;">Rata-rata Nilai per Tiket</div>
            </td>
        </tr>
    </table>

    {{-- REKAPITULASI BERDASARKAN KATEGORI --}}
    <h2>1. Rekapitulasi Berdasarkan Kategori Pengunjung</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th>Kategori Pengunjung</th>
                <th style="width: 120px;" class="text-center">Jumlah Kunjungan</th>
                <th style="width: 140px;" class="text-right">Total Penerimaan</th>
                <th style="width: 90px;" class="text-right">Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byKategori as $index => $k)
                @php
                    $kTotal = (int) $k->total;
                    $pct = $sumKategoriOmzet > 0 ? round(($kTotal / $sumKategoriOmzet) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ strtoupper($k->kategori) }}</td>
                    <td class="text-center">{{ number_format((int) $k->jumlah, 0, ',', '.') }} orang</td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">{{ $rupiah($kTotal) }}</td>
                    <td class="text-right">{{ $pct }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 8px; color: #64748b; font-style: italic;">
                        Belum ada data kunjungan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; text-transform: uppercase;">Total:</td>
                <td class="text-center">{{ number_format($count, 0, ',', '.') }} orang</td>
                <td class="text-right" style="color: #0f172a; font-size: 9.5px;">{{ $rupiah($total) }}</td>
                <td class="text-right">100.0%</td>
            </tr>
        </tfoot>
    </table>

    {{-- DETAIL KUNJUNGAN HARIAN --}}
    <h2>2. Rincian Riwayat Kunjungan Latihan Harian</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 100px;">Waktu</th>
                <th>Nama Pengunjung</th>
                <th style="width: 80px;" class="text-center">Kategori</th>
                <th style="width: 85px;">Petugas</th>
                <th style="width: 65px;" class="text-center">Metode</th>
                <th style="width: 95px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $calcTotal = 0; @endphp
            @forelse($rows as $idx => $r)
                @php
                    $petugas = $r->creator?->name ?? '-';
                    $calcTotal += (int) $r->total;
                    $met = strtolower((string) $r->metode_pembayaran);
                    $badgeClass = match($met) {
                        'cash' => 'badge-cash',
                        'transfer' => 'badge-transfer',
                        default => 'badge-qris',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ Carbon::parse($r->tanggal)->translatedFormat('d M Y, H:i') }}</td>
                    <td style="font-weight: 500;">{{ $r->nama }}</td>
                    <td class="text-center"><span class="badge-kategori">{{ strtoupper($r->kategori) }}</span></td>
                    <td style="color: #64748b;">{{ $petugas }}</td>
                    <td class="text-center"><span class="{{ $badgeClass }}">{{ strtoupper($r->metode_pembayaran ?: 'CASH') }}</span></td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">{{ $rupiah($r->total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #64748b; font-style: italic;">
                        Tidak ada data latihan harian yang sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right; text-transform: uppercase;">Total Transaksi:</td>
                <td class="text-right" style="color: #0f172a; font-size: 9.5px;">{{ $rupiah($calcTotal ?: $total) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURE SECTION --}}
    <table class="signature-table">
        <tr>
            <td style="width: 55%; padding-right: 20px;">
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; font-size: 7.5px; color: #64748b; line-height: 1.4;">
                    <strong>Catatan Sistem:</strong><br>
                    Laporan ini memuat pencatatan kunjungan harian gym (tiket umum & pelajar) di BETA GYM.<br>
                    Data direkam secara real-time saat registrasi pengunjung di meja resepsionis / kasir.
                </div>
            </td>
            <td style="width: 45%; text-align: center;">
                <div style="font-size: 9px; color: #334155;">Padang, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="font-size: 9px; font-weight: bold; color: #0f172a; margin-top: 2px;">Mengetahui, Manajemen BETA GYM</div>
                <div style="height: 40px;"></div>
                <div style="font-size: 9px; font-weight: bold; color: #0f172a; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 3px; min-width: 150px;">
                    ( Penanggung Jawab Harian )
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
