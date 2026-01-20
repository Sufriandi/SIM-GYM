{{-- resources/views/admin/laporan/absensi/pdf.blade.php --}}
@php
    use Carbon\Carbon;

    $start = $filters['start_date'] ?? '-';
    $end   = $filters['end_date'] ?? '-';

    $stats = $stats ?? [];
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Laporan Absensi' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .row { display: flex; gap: 10px; margin-bottom: 10px; }
        .box { border: 1px solid #ddd; padding: 10px; border-radius: 8px; flex: 1; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #eee; padding: 8px 6px; text-align: left; }
        th { background: #f6f6f6; }
    </style>
</head>
<body>
    <h1>{{ $pageTitle ?? 'Laporan Absensi' }}</h1>
    <div class="muted">
        Rentang: {{ Carbon::parse($start)->translatedFormat('d M Y') }} – {{ Carbon::parse($end)->translatedFormat('d M Y') }}
    </div>

    <div class="row" style="margin-top:10px;">
        <div class="box">
            <div class="muted">Total Check-in</div>
            <div style="font-size:18px; font-weight:700;">{{ number_format((int)($stats['total_checkins'] ?? 0)) }}</div>
        </div>
        <div class="box">
            <div class="muted">Member Unik</div>
            <div style="font-size:18px; font-weight:700;">{{ number_format((int)($stats['unique_members'] ?? 0)) }}</div>
        </div>
        <div class="box">
            <div class="muted">Rata-rata / Hari</div>
            <div style="font-size:18px; font-weight:700;">{{ (float)($stats['avg_per_day'] ?? 0) }}</div>
        </div>
        <div class="box">
            <div class="muted">Utilisasi</div>
            <div style="font-size:18px; font-weight:700;">
                {{ round(((float)($stats['utilization'] ?? 0)) * 100, 1) }}%
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th style="width:90px;">Tanggal</th>
                <th>Member</th>
                <th style="width:80px;">Jam Masuk</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
                @php
                    $tanggal = $row->tanggal ? Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-';
                    $jam = '-';
                    if (!empty($row->jam_masuk)) {
                        try { $jam = Carbon::parse($row->jam_masuk)->format('H:i'); }
                        catch (\Throwable $e) { $jam = (string)$row->jam_masuk; }
                    }
                    $nama = $row->user_name ?? $row->member_nama ?? '-';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $tanggal }}</td>
                    <td>{{ $nama }} (ID: {{ $row->member_id ?? '-' }})</td>
                    <td>{{ $jam }}</td>
                    <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="muted" style="margin-top:10px;">
        Dicetak pada: {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
