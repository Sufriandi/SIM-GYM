{{-- resources/views/admin/laporan/absensi/excel.blade.php --}}
@php
    use Carbon\Carbon;

    $pageTitle = $pageTitle ?? 'Laporan Absensi';

    $filters = $filters ?? [];
    $stats   = $stats ?? [];
    $rows    = $rows ?? collect();

    // Support paginator / collection
    $startNo = 1;
    if (is_object($rows) && method_exists($rows, 'firstItem') && $rows->firstItem()) {
        $startNo = (int) $rows->firstItem();
    }

    $mode = $filters['mode'] ?? '-';
    $startDate = $filters['start_date'] ?? '-';
    $endDate   = $filters['end_date'] ?? '-';
    $memberId  = $filters['member_id'] ?? null;
    $sort      = $filters['sort'] ?? '-';

    $totalCheckins  = (int) ($stats['total_checkins'] ?? 0);
    $uniqueMembers  = (int) ($stats['unique_members'] ?? 0);
    $avgPerDay      = $stats['avg_per_day'] ?? 0;
    $daysWithData   = (int) ($stats['days_with_data'] ?? 0);
    $totalDays      = (int) ($stats['total_days'] ?? 0);
    $utilizationPct = $stats['utilization_pct'] ?? 0;

    $peakDay  = $stats['peak_day'] ?? null;   // ['date' => 'YYYY-MM-DD', 'total' => N]
    $peakHour = $stats['peak_hour'] ?? null;  // ['hour' => 0..23, 'total' => N]

    $trend = $stats['trend'] ?? ['delta' => 0, 'persen' => null];
    $longestStreak = (int) ($stats['longest_streak'] ?? 0);

    $exportedAt = Carbon::now()->format('d M Y H:i');
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <style>
        /* Excel-friendly styling (inline CSS supported by Laravel-Excel view export) */
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111827; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 2px; }
        .subtitle { font-size: 12px; color: #6b7280; margin-bottom: 10px; }
        .meta { font-size: 11px; color: #374151; margin: 8px 0 14px; }

        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-align: left; }
        .nowrap { white-space: nowrap; }
        .muted { color: #6b7280; font-size: 11px; }
        .section { font-weight: 700; background: #eef2ff; }
        .kpi-label { width: 240px; }
        .kpi-val { font-weight: 700; }

        /* Column widths (best-effort) */
        .w-no { width: 60px; }
        .w-date { width: 120px; }
        .w-time { width: 90px; }
        .w-id { width: 110px; }
        .w-name { width: 240px; }
        .w-notes { width: 240px; }
        .w-created { width: 150px; }
    </style>
</head>
<body>
    <div class="title">{{ $pageTitle }}</div>
    <div class="subtitle">Rekap kehadiran member berdasarkan periode dan filter.</div>
    <div class="meta">
        <div><b>Diekspor pada:</b> {{ $exportedAt }}</div>
        <div><b>Periode:</b> {{ $startDate }} s/d {{ $endDate }}</div>
        <div>
            <b>Mode:</b> {{ ucfirst($mode) }}
            &nbsp;|&nbsp;
            <b>Urutan:</b> {{ $sort === 'oldest' ? 'Terlama' : 'Terbaru' }}
            @if(!empty($memberId))
                &nbsp;|&nbsp;
                <b>Filter Member ID:</b> {{ $memberId }}
            @endif
        </div>
    </div>

    {{-- RINGKASAN / KPI --}}
    <table>
        <tr>
            <td class="section" colspan="4">Ringkasan Statistik</td>
        </tr>
        <tr>
            <td class="kpi-label">Total check-in</td>
            <td class="kpi-val">{{ number_format($totalCheckins) }}</td>
            <td class="kpi-label">Member unik (hadir)</td>
            <td class="kpi-val">{{ number_format($uniqueMembers) }}</td>
        </tr>
        <tr>
            <td class="kpi-label">Rata-rata per hari (hari ada data)</td>
            <td class="kpi-val">{{ $avgPerDay }}</td>
            <td class="kpi-label">Konsistensi hari aktif</td>
            <td class="kpi-val">
                {{ $utilizationPct }}%
                <span class="muted">({{ $daysWithData }} dari {{ $totalDays }} hari)</span>
            </td>
        </tr>
        <tr>
            <td class="kpi-label">Hari tersibuk</td>
            <td class="kpi-val">
                @if($peakDay && !empty($peakDay['date']))
                    {{ Carbon::parse($peakDay['date'])->format('d M Y') }}
                    <span class="muted">({{ (int)($peakDay['total'] ?? 0) }} check-in)</span>
                @else
                    -
                @endif
            </td>
            <td class="kpi-label">Jam tersibuk</td>
            <td class="kpi-val">
                @if($peakHour && isset($peakHour['hour']))
                    {{ str_pad((string)$peakHour['hour'], 2, '0', STR_PAD_LEFT) }}:00
                    <span class="muted">({{ (int)($peakHour['total'] ?? 0) }} check-in)</span>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="kpi-label">Tren (awal → akhir)</td>
            <td class="kpi-val" colspan="3">
                @php
                    $delta = (int) ($trend['delta'] ?? 0);
                    $persen = $trend['persen'] ?? null;
                @endphp
                {{ $delta >= 0 ? '+' : '' }}{{ $delta }}
                @if($persen !== null)
                    <span class="muted">({{ $persen >= 0 ? '+' : '' }}{{ $persen }}%)</span>
                @endif
                &nbsp;|&nbsp;
                <b>Streak terpanjang:</b> {{ $longestStreak }} hari
            </td>
        </tr>
    </table>

    <br>

    {{-- TABEL DATA --}}
    <table>
        <thead>
            <tr>
                <th class="w-no nowrap">No</th>
                <th class="w-date nowrap">Tanggal</th>
                <th class="w-id nowrap">Member ID</th>
                <th class="w-name">Nama Member</th>
                <th class="w-time nowrap">Jam Masuk</th>
                <th class="w-notes">Keterangan</th>
                <th class="w-created nowrap">Waktu Sistem</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 0; @endphp

            @forelse($rows as $row)
                @php
                    $no = $startNo + $i;
                    $i++;

                    // tanggal & jam_masuk sudah diprepare dari controller sebagai string/expr SQL
                    $tanggalLabel = '-';
                    if (!empty($row->tanggal)) {
                        try { $tanggalLabel = Carbon::parse($row->tanggal)->format('d M Y'); }
                        catch (\Throwable $e) { $tanggalLabel = (string) $row->tanggal; }
                    }

                    $jamLabel = '-';
                    if (!empty($row->jam_masuk)) {
                        try { $jamLabel = Carbon::parse($row->jam_masuk)->format('H:i'); }
                        catch (\Throwable $e) { $jamLabel = (string) $row->jam_masuk; }
                    }

                    // Skema terbaru: nama hanya dari users.name (join u.name as user_name)
                    $memberName = $row->user_name ?? '-';

                    $keterangan = $row->keterangan ?? '-';

                    $createdLabel = '-';
                    if (!empty($row->created_at)) {
                        try { $createdLabel = Carbon::parse($row->created_at)->format('d M Y H:i'); }
                        catch (\Throwable $e) { $createdLabel = (string) $row->created_at; }
                    }
                @endphp

                <tr>
                    <td class="nowrap">{{ $no }}</td>
                    <td class="nowrap">{{ $tanggalLabel }}</td>
                    <td class="nowrap">{{ $row->member_id ?? '-' }}</td>
                    <td>
                        <div><b>{{ $memberName }}</b></div>
                        <div class="muted">ID absensi: {{ $row->id ?? '-' }}</div>
                    </td>
                    <td class="nowrap">{{ $jamLabel }}</td>
                    <td>{{ $keterangan }}</td>
                    <td class="nowrap">{{ $createdLabel }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 18px; color:#6b7280;">
                        Tidak ada data absensi pada periode dan filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:10px;" class="muted">
        Catatan: Nama member diambil dari <b>users.name</b> sesuai struktur database terbaru.
    </div>
</body>
</html>
