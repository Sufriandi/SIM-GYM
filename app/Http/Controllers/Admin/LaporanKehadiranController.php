<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaporanKehadiranController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);

        $report = $this->buildRingkasan($filters);

        return view('admin.laporan.kehadiran.index', [
            'pageTitle' => 'Laporan Absensi & Kompensasi',
            'filters'   => $filters,
            'rows'      => $report['rows'],
            'stats'     => $report['stats'],
            'series'    => $report['series'],
        ]);
    }

    public function absensi(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        [$startAt, $endAt] = [$filters['start_at'], $filters['end_at']];

        // Member name expression (dinamis sesuai kolom members)
        $memberNameExpr = $this->memberNameExpr('m', 'u');

        $kmBase = DB::table('kehadiran_members as km')
            ->whereBetween('km.tanggal', [$startAt->toDateString(), $endAt->toDateString()]);

        if (!empty($filters['member_id'])) {
            $kmBase->where('km.member_id', $filters['member_id']);
        }

        $rowsQuery = (clone $kmBase)
            ->leftJoin('members as m', 'm.id', '=', 'km.member_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->select([
                'km.id',
                'km.member_id',
                'km.tanggal',
                'km.jam_masuk',
                'km.jam_keluar',
                'km.ip_address',
                'km.device_info',
                'km.is_valid',
                'km.created_at',
                DB::raw($memberNameExpr . ' as user_name'),
                DB::raw("CASE WHEN km.is_valid = 1 THEN 'Check-in' ELSE 'Check-in (Invalid)' END as keterangan"),
                DB::raw("COALESCE(NULLIF(TRIM(km.ip_address), ''), NULLIF(TRIM(km.device_info), ''), '-') as sumber"),
            ]);

        // Sorting
        if (($filters['sort'] ?? 'newest') === 'oldest') {
            $rowsQuery->orderBy('km.tanggal', 'asc')->orderBy('km.created_at', 'asc');
        } else {
            $rowsQuery->orderBy('km.tanggal', 'desc')->orderBy('km.created_at', 'desc');
        }

        $rows  = $rowsQuery->paginate($filters['per_page'])->withQueryString();
        $total = (clone $kmBase)->count();

        return view('admin.laporan.kehadiran.absensi', [
            'pageTitle' => 'Data Absensi',
            'filters'   => $filters,
            'rows'      => $rows,
            'total'     => (int) $total,
        ]);
    }

    public function kompensasi(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        [$startAt, $endAt] = [$filters['start_at'], $filters['end_at']];

        $status = strtolower((string) $request->query('status', ''));
        $status = in_array($status, ['pending', 'disetujui', 'ditolak'], true) ? $status : '';

        // Member name expression (dinamis sesuai kolom members)
        $memberNameExpr = $this->memberNameExpr('m', 'u');

        // Overlap range:
        // izin dianggap masuk range jika:
        // tanggal_mulai <= end AND (tanggal_selesai atau tanggal_mulai) >= start
        $ilBase = DB::table('izin_latihan as il')
            ->leftJoin('members as m', 'm.id', '=', 'il.member_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->whereRaw(
                "(il.tanggal_mulai <= ? AND COALESCE(il.tanggal_selesai, il.tanggal_mulai) >= ?)",
                [$endAt->toDateString(), $startAt->toDateString()]
            );

        if ($status !== '') {
            $ilBase->where('il.status', $status);
        }

        if (!empty($filters['member_id'])) {
            $ilBase->where('il.member_id', $filters['member_id']);
        }

        // durasi_hari: prioritas durasi_izin_disetujui, lalu jumlah_hari, lalu hitung dari tanggal
        $durExpr = "COALESCE(
            il.durasi_izin_disetujui,
            il.jumlah_hari,
            (DATEDIFF(COALESCE(il.tanggal_selesai, il.tanggal_mulai), il.tanggal_mulai) + 1)
        )";

        $rowsQuery = (clone $ilBase)->select([
            'il.id',
            'il.member_id',
            'il.tanggal_mulai',
            'il.tanggal_selesai',
            'il.jumlah_hari',
            'il.alasan',
            'il.status',
            'il.bukti_alasan',
            'il.durasi_izin_disetujui',
            'il.keterangan_admin',
            'il.tanggal_persetujuan',
            'il.created_at',
            DB::raw($memberNameExpr . ' as user_name'),
            DB::raw($durExpr . ' as durasi_hari'),
        ]);

        // sorting
        if (($filters['sort'] ?? 'newest') === 'oldest') {
            $rowsQuery->orderBy('il.created_at', 'asc');
        } else {
            $rowsQuery->orderBy('il.created_at', 'desc');
        }

        $rows  = $rowsQuery->paginate($filters['per_page'])->withQueryString();
        $total = (clone $ilBase)->count();

        /**
         * PENTING:
         * JANGAN pakai ->toBase() di sini.
         * Karena $ilBase adalah Query\Builder (DB::table), jadi langsung value().
         */
        $sumHari = (int) ((clone $ilBase)
            ->selectRaw("SUM($durExpr) as sum_hari")
            ->value('sum_hari') ?? 0);

        return view('admin.laporan.kehadiran.kompensasi', [
            'pageTitle' => 'Data Kompensasi / Izin',
            'filters'   => array_merge($filters, ['status' => $status]),
            'rows'      => $rows,
            'total'     => (int) $total,
            'sumHari'   => $sumHari,
        ]);
    }

    public function audit(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        [$startAt, $endAt] = [$filters['start_at'], $filters['end_at']];

        $memberNameExpr = $this->memberNameExpr('m', 'u');

        // Audit: contoh fokus ke data yang invalid / berpotensi anomali
        $kmBase = DB::table('kehadiran_members as km')
            ->whereBetween('km.tanggal', [$startAt->toDateString(), $endAt->toDateString()])
            ->where('km.is_valid', 0);

        $rowsQuery = (clone $kmBase)
            ->leftJoin('members as m', 'm.id', '=', 'km.member_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->select([
                'km.id',
                'km.member_id',
                'km.tanggal',
                'km.jam_masuk',
                'km.jam_keluar',
                'km.ip_address',
                'km.device_info',
                'km.is_valid',
                'km.created_at',
                DB::raw($memberNameExpr . ' as user_name'),
                DB::raw("COALESCE(NULLIF(TRIM(km.ip_address), ''), NULLIF(TRIM(km.device_info), ''), '-') as sumber"),
            ]);

        if (($filters['sort'] ?? 'newest') === 'oldest') {
            $rowsQuery->orderBy('km.tanggal', 'asc')->orderBy('km.created_at', 'asc');
        } else {
            $rowsQuery->orderBy('km.tanggal', 'desc')->orderBy('km.created_at', 'desc');
        }

        $rows  = $rowsQuery->paginate($filters['per_page'])->withQueryString();
        $total = (clone $kmBase)->count();

        return view('admin.laporan.kehadiran.audit', [
            'pageTitle' => 'Audit Kehadiran',
            'filters'   => $filters,
            'rows'      => $rows,
            'total'     => (int) $total,
        ]);
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function normalizeFilters(Request $request): array
    {
        $sort = strtolower((string) $request->query('sort', 'newest'));
        $sort = in_array($sort, ['newest', 'oldest'], true) ? $sort : 'newest';

        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(10, min($perPage, 200));

        $memberId = $request->filled('member_id') ? (int) $request->query('member_id') : null;
        $memberId = ($memberId && $memberId > 0) ? $memberId : null;

        $now = Carbon::now();

        $startRaw = $request->query('start_date');
        $endRaw   = $request->query('end_date');

        try {
            $startAt = $startRaw ? Carbon::parse($startRaw)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
        } catch (\Throwable $e) {
            $startAt = $now->copy()->startOfMonth()->startOfDay();
        }

        try {
            $endAt = $endRaw ? Carbon::parse($endRaw)->endOfDay() : $now->copy()->endOfMonth()->endOfDay();
        } catch (\Throwable $e) {
            $endAt = $now->copy()->endOfMonth()->endOfDay();
        }

        if ($endAt->lt($startAt)) {
            [$startAt, $endAt] = [$endAt->copy()->startOfDay(), $startAt->copy()->endOfDay()];
        }

        // guard range max 366 hari
        if ($startAt->diffInDays($endAt) > 366) {
            $endAt = $startAt->copy()->addYear()->endOfDay();
        }

        return [
            'sort'       => $sort,
            'per_page'   => $perPage,
            'member_id'  => $memberId,
            'start_date' => $startAt->toDateString(),
            'end_date'   => $endAt->toDateString(),
            'start_at'   => $startAt,
            'end_at'     => $endAt,
        ];
    }

    private function memberNameExpr(string $memberAlias = 'm', string $userAlias = 'u'): string
    {
        // users.name biasanya ada
        $parts = ["NULLIF(TRIM($userAlias.name), '')"];

        // members bisa beda-beda kolomnya, coba beberapa kandidat umum
        $memberCandidates = ['nama', 'name', 'nama_lengkap', 'full_name'];
        foreach ($memberCandidates as $col) {
            if (Schema::hasColumn('members', $col)) {
                $parts[] = "NULLIF(TRIM($memberAlias.$col), '')";
            }
        }

        $parts[] = "'-'";
        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    private function buildRingkasan(array $filters): array
    {
        $startAt = $filters['start_at'];
        $endAt   = $filters['end_at'];

        $memberNameExpr = $this->memberNameExpr('m', 'u');

        $kmBase = DB::table('kehadiran_members as km')
            ->whereBetween('km.tanggal', [$startAt->toDateString(), $endAt->toDateString()]);

        if (!empty($filters['member_id'])) {
            $kmBase->where('km.member_id', $filters['member_id']);
        }

        $totalCheckins = (clone $kmBase)->count();
        $uniqueMembers = (clone $kmBase)->distinct('km.member_id')->count('km.member_id');

        $dailyRaw = (clone $kmBase)
            ->selectRaw("km.tanggal as d, COUNT(*) as total")
            ->groupBy('d')
            ->orderBy('d', 'asc')
            ->get();

        $daily = [];
        foreach ($dailyRaw as $r) {
            $date = (string) $r->d;
            $daily[] = [
                'date'    => $date,
                'label'   => Carbon::parse($date)->translatedFormat('d M'),
                'total'   => (int) $r->total,
                'ma7'     => null,
                'is_peak' => false,
            ];
        }

        $totalDays    = $startAt->copy()->startOfDay()->diffInDays($endAt->copy()->startOfDay()) + 1;
        $daysWithData = count($daily);

        $utilization = $totalDays > 0 ? ($daysWithData / $totalDays) : 0.0;
        $avgPerDay   = $daysWithData > 0 ? round($totalCheckins / $daysWithData, 2) : 0.0;

        // Peak
        $peakDayIdx = -1;
        $peakDayTotal = -1;
        foreach ($daily as $i => $d) {
            if ($d['total'] > $peakDayTotal) {
                $peakDayTotal = $d['total'];
                $peakDayIdx   = $i;
            }
        }
        if ($peakDayIdx >= 0) {
            $daily[$peakDayIdx]['is_peak'] = true;
        }

        $peakDay = null;
        if ($peakDayIdx >= 0) {
            $peakDate = $daily[$peakDayIdx]['date'];
            $peakDay = [
                'date'  => $peakDate,
                'label' => Carbon::parse($peakDate)->translatedFormat('d M Y'),
                'total' => $daily[$peakDayIdx]['total'],
            ];
        }

        // Trend
        $trendDelta = 0;
        $trendPercent = null;
        if (count($daily) >= 2) {
            $first = $daily[0]['total'];
            $last  = $daily[count($daily) - 1]['total'];
            $trendDelta = (int) ($last - $first);
            if ($first > 0) {
                $trendPercent = round((($last - $first) / $first) * 100, 1);
            }
        }

        // MA7
        $ma7 = $this->movingAverage(array_map(fn ($x) => $x['total'], $daily), 7);
        foreach ($daily as $i => $d) {
            $daily[$i]['ma7'] = $ma7[$i];
        }

        $longestStreak = $this->computeLongestStreak(array_map(fn ($x) => $x['date'], $daily));

        // Hourly
        $hourlyRaw = (clone $kmBase)
            ->selectRaw("HOUR(COALESCE(km.jam_masuk, TIME(km.created_at))) as h, COUNT(*) as total")
            ->groupBy('h')
            ->get();

        $hourMap = [];
        foreach ($hourlyRaw as $r) $hourMap[(int) $r->h] = (int) $r->total;

        $hourly = [];
        $peakHour = ['hour' => 0, 'total' => 0];
        for ($h = 0; $h < 24; $h++) {
            $val = $hourMap[$h] ?? 0;
            $hourly[] = [
                'hour'  => $h,
                'label' => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => $val,
            ];
            if ($val > $peakHour['total']) $peakHour = ['hour' => $h, 'total' => $val];
        }

        // Weekday (0=Mon..6=Sun)
        $weekdayRaw = (clone $kmBase)
            ->selectRaw("WEEKDAY(km.tanggal) as wd, COUNT(*) as total")
            ->groupBy('wd')
            ->get();

        $wdLabels = [0=>'Senin',1=>'Selasa',2=>'Rabu',3=>'Kamis',4=>'Jumat',5=>'Sabtu',6=>'Minggu'];
        $wdMap = [];
        foreach ($weekdayRaw as $r) $wdMap[(int) $r->wd] = (int) $r->total;

        $weekday = [];
        for ($wd = 0; $wd <= 6; $wd++) {
            $weekday[] = [
                'wd'    => $wd,
                'label' => $wdLabels[$wd],
                'total' => $wdMap[$wd] ?? 0,
            ];
        }

        // Top members
        $topMembers = (clone $kmBase)
            ->leftJoin('members as m', 'm.id', '=', 'km.member_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->selectRaw("km.member_id as member_id, {$memberNameExpr} as nama, COUNT(*) as total")
            ->groupBy('km.member_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => (object)[
                'member_id' => (int) $r->member_id,
                'nama'      => (string) $r->nama,
                'total'     => (int) $r->total,
            ]);

        // Ringkasan kompensasi (izin) untuk ditampilkan di ringkasan index jika dibutuhkan
        $ilBase = DB::table('izin_latihan as il')
            ->whereRaw(
                "(il.tanggal_mulai <= ? AND COALESCE(il.tanggal_selesai, il.tanggal_mulai) >= ?)",
                [$endAt->toDateString(), $startAt->toDateString()]
            );

        $totalIzin = (int) (clone $ilBase)->count();

        $durExpr = "COALESCE(
            il.durasi_izin_disetujui,
            il.jumlah_hari,
            (DATEDIFF(COALESCE(il.tanggal_selesai, il.tanggal_mulai), il.tanggal_mulai) + 1)
        )";

        $totalKompHari = (int) ((clone $ilBase)
            ->selectRaw("SUM($durExpr) as sum_hari")
            ->value('sum_hari') ?? 0);

        // Rows ringkasan: tampilkan detail check-in (biar konsisten kalau index butuh)
        $rowsQuery = (clone $kmBase)
            ->leftJoin('members as m', 'm.id', '=', 'km.member_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->select([
                'km.id',
                'km.member_id',
                'km.tanggal',
                'km.jam_masuk',
                'km.jam_keluar',
                'km.is_valid',
                'km.created_at',
                DB::raw($memberNameExpr . ' as user_name'),
            ]);

        if (($filters['sort'] ?? 'newest') === 'oldest') {
            $rowsQuery->orderBy('km.tanggal', 'asc')->orderBy('km.created_at', 'asc');
        } else {
            $rowsQuery->orderBy('km.tanggal', 'desc')->orderBy('km.created_at', 'desc');
        }

        $rows = $rowsQuery->paginate($filters['per_page'])->withQueryString();

        return [
            'rows' => $rows,
            'stats' => [
                'total_checkins'        => (int) $totalCheckins,
                'unique_members'        => (int) $uniqueMembers,
                'avg_per_day'           => (float) $avgPerDay,

                'utilization'           => (float) $utilization,
                'active_days'           => (int) $daysWithData,
                'days_in_range'         => (int) $totalDays,
                'longest_streak'        => (int) $longestStreak,

                'peak_day'              => $peakDay,
                'peak_hour'             => $peakHour,

                'trend_delta'           => (int) $trendDelta,
                'trend_percent'         => $trendPercent,

                'top_members'           => $topMembers,

                // tambahan ringkasan kompensasi
                'total_izin'            => (int) $totalIzin,
                'total_kompensasi_hari' => (int) $totalKompHari,
            ],
            'series' => [
                'daily'   => $daily,
                'hourly'  => $hourly,
                'weekday' => $weekday,
            ],
        ];
    }

    private function computeLongestStreak(array $dates): int
    {
        if (count($dates) === 0) return 0;

        $best = 1;
        $cur  = 1;
        $prev = Carbon::parse($dates[0])->startOfDay();

        for ($i = 1; $i < count($dates); $i++) {
            $now = Carbon::parse($dates[$i])->startOfDay();
            if ($prev->copy()->addDay()->equalTo($now)) $cur++;
            else { $best = max($best, $cur); $cur = 1; }
            $prev = $now;
        }
        return max($best, $cur);
    }

    private function movingAverage(array $values, int $window = 7): array
    {
        $n = count($values);
        if ($n === 0) return [];

        $out = array_fill(0, $n, null);
        $sum = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $sum += (float) $values[$i];

            if ($i >= $window) {
                $sum -= (float) $values[$i - $window];
            }

            $out[$i] = ($i >= $window - 1) ? round($sum / $window, 2) : null;
        }

        return $out;
    }
}
