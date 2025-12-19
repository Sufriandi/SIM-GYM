<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AbsensiReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $report  = $this->buildReport($filters);

        return view('admin.laporan.absensi.index', [
            'pageTitle' => 'Laporan Absensi',
            'filters'   => $filters,
            'rows'      => $report['rows'],
            'stats'     => $report['stats'],
            'series'    => $report['series'],
        ]);
    }

    private function normalizeFilters(Request $request): array
    {
        $mode = strtolower((string) $request->input('mode', 'bulanan'));
        if (!in_array($mode, ['harian', 'mingguan', 'bulanan', 'custom'], true)) {
            $mode = 'bulanan';
        }

        $sort = strtolower((string) $request->input('sort', 'newest'));
        $sort = in_array($sort, ['newest', 'oldest'], true) ? $sort : 'newest';

        $perPage = (int) $request->input('per_page', 25);
        $perPage = max(10, min($perPage, 200));

        $memberId = $request->filled('member_id') ? (int) $request->input('member_id') : null;
        $memberId = ($memberId && $memberId > 0) ? $memberId : null;

        // Quick range (opsional dari tombol UI kamu)
        $quick = (string) $request->input('quick', '');

        $now = Carbon::now();
        $startAt = $now->copy()->startOfMonth()->startOfDay();
        $endAt   = $now->copy()->endOfMonth()->endOfDay();

        if ($quick === '7_hari') {
            $startAt = $now->copy()->subDays(6)->startOfDay();
            $endAt   = $now->copy()->endOfDay();
            $mode    = 'custom';
        } elseif ($quick === '30_hari') {
            $startAt = $now->copy()->subDays(29)->startOfDay();
            $endAt   = $now->copy()->endOfDay();
            $mode    = 'custom';
        } elseif ($quick === 'bulan_ini') {
            $startAt = $now->copy()->startOfMonth()->startOfDay();
            $endAt   = $now->copy()->endOfMonth()->endOfDay();
            $mode    = 'custom';
        } else {
            if ($mode === 'harian') {
                $startAt = $now->copy()->startOfDay();
                $endAt   = $now->copy()->endOfDay();
            } elseif ($mode === 'mingguan') {
                $startAt = $now->copy()->startOfWeek()->startOfDay();
                $endAt   = $now->copy()->endOfWeek()->endOfDay();
            } elseif ($mode === 'bulanan') {
                $startAt = $now->copy()->startOfMonth()->startOfDay();
                $endAt   = $now->copy()->endOfMonth()->endOfDay();
            } else {
                $startRaw = $request->input('start_date');
                $endRaw   = $request->input('end_date');

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
            }
        }

        // Guard biar tidak berat
        if ($startAt->diffInDays($endAt) > 366) {
            $endAt = $startAt->copy()->addYear()->endOfDay();
        }

        return [
            'mode'       => $mode,
            'sort'       => $sort,
            'per_page'   => $perPage,
            'member_id'  => $memberId,
            'start_date' => $startAt->toDateString(),
            'end_date'   => $endAt->toDateString(),
            'start_at'   => $startAt,
            'end_at'     => $endAt,
        ];
    }

    private function buildReport(array $filters): array
    {
        $startAt = $filters['start_at'];
        $endAt   = $filters['end_at'];

        // kolom opsional di kehadiran_members (sesuai DB kamu yang dinamis)
        $hasTanggal    = Schema::hasColumn('kehadiran_members', 'tanggal');
        $hasJamMasuk   = Schema::hasColumn('kehadiran_members', 'jam_masuk');
        $hasKeterangan = Schema::hasColumn('kehadiran_members', 'keterangan');

        $dateExprSQL = $hasTanggal
            ? "COALESCE(DATE(km.tanggal), DATE(km.created_at))"
            : "DATE(km.created_at)";

        $timeExprSQL = $hasJamMasuk
            ? "COALESCE(km.jam_masuk, TIME(km.created_at))"
            : "TIME(km.created_at)";

        $hourExprSQL = $hasJamMasuk
            ? "HOUR(COALESCE(km.jam_masuk, TIME(km.created_at)))"
            : "HOUR(km.created_at)";

        // base query
        $kmBase = DB::table('kehadiran_members as km')
            ->whereBetween('km.created_at', [$startAt, $endAt]);

        if (!empty($filters['member_id'])) {
            $kmBase->where('km.member_id', $filters['member_id']);
        }

        // join name dari users (DB kamu: name ada di users)
        $hasMembers = Schema::hasTable('members');
        $hasUsers   = Schema::hasTable('users');
        $hasMemberUserId = $hasMembers ? Schema::hasColumn('members', 'user_id') : false;

        $rowsQuery = (clone $kmBase);

        if ($hasMembers) {
            $rowsQuery->leftJoin('members as m', 'm.id', '=', 'km.member_id');
        }
        if ($hasMembers && $hasUsers && $hasMemberUserId) {
            $rowsQuery->leftJoin('users as u', 'u.id', '=', 'm.user_id');
        }

        $rowsQuery->select([
            'km.id',
            'km.member_id',
            DB::raw("$dateExprSQL as tanggal"),
            DB::raw("$timeExprSQL as jam_masuk"),
            $hasKeterangan ? 'km.keterangan' : DB::raw('NULL as keterangan'),
            'km.created_at',
            ($hasMembers && $hasUsers && $hasMemberUserId)
                ? DB::raw("u.name as user_name")
                : DB::raw("NULL as user_name"),
        ]);

        $direction = $filters['sort'] === 'oldest' ? 'asc' : 'desc';
        $rowsQuery->orderBy('km.created_at', $direction);

        $rows = $rowsQuery->paginate($filters['per_page'])->withQueryString();

        // ========= STAT =========
        $totalCheckins = (clone $kmBase)->count();

        $uniqueMembers = (clone $kmBase)
            ->distinct('km.member_id')
            ->count('km.member_id');

        // ===== Daily raw (date + total)
        $dailyRaw = (clone $kmBase)
            ->selectRaw("$dateExprSQL as d, COUNT(*) as total")
            ->groupBy('d')
            ->orderBy('d', 'asc')
            ->get();

        $daily = [];
        foreach ($dailyRaw as $r) {
            $date = (string) $r->d;
            $daily[] = [
                'date'  => $date,
                'label' => Carbon::parse($date)->translatedFormat('d M'),
                'total' => (int) $r->total,
                'ma7'   => null,
                'is_peak' => false,
            ];
        }

        $totalDays    = $startAt->copy()->startOfDay()->diffInDays($endAt->copy()->startOfDay()) + 1;
        $daysWithData = count($daily);

        // utilization DI BLADE kamu dipakai sebagai FRAKSI (0..1)
        $utilization = $totalDays > 0 ? ($daysWithData / $totalDays) : 0.0;
        $avgPerDay   = $daysWithData > 0 ? round($totalCheckins / $daysWithData, 2) : 0.0;

        // Peak day
        $peakDayIdx = -1;
        $peakDayTotal = -1;
        foreach ($daily as $i => $d) {
            if ($d['total'] > $peakDayTotal) {
                $peakDayTotal = $d['total'];
                $peakDayIdx = $i;
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

        // Trend delta & percent (sesuai Blade kamu)
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

        // MA-7
        $ma7 = $this->movingAverage(array_map(fn($x) => $x['total'], $daily), 7);
        foreach ($daily as $i => $d) {
            $daily[$i]['ma7'] = $ma7[$i];
        }

        // Longest streak dari tanggal yang ada data
        $longestStreak = $this->computeLongestStreak(array_map(fn($x) => $x['date'], $daily));

        // ===== Hourly
        $hourlyRaw = (clone $kmBase)
            ->selectRaw("$hourExprSQL as h, COUNT(*) as total")
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
                'label' => str_pad((string)$h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => $val,
            ];
            if ($val > $peakHour['total']) $peakHour = ['hour' => $h, 'total' => $val];
        }

        // ===== Weekday
        $weekdayRaw = (clone $kmBase)
            ->selectRaw("DAYOFWEEK($dateExprSQL) as wd, COUNT(*) as total")
            ->groupBy('wd')
            ->get();

        $wdLabels = [1=>'Minggu',2=>'Senin',3=>'Selasa',4=>'Rabu',5=>'Kamis',6=>'Jumat',7=>'Sabtu'];
        $wdMap = [];
        foreach ($weekdayRaw as $r) $wdMap[(int) $r->wd] = (int) $r->total;

        $weekday = [];
        for ($wd = 1; $wd <= 7; $wd++) {
            $weekday[] = [
                'wd'    => $wd,
                'label' => $wdLabels[$wd],
                'total' => $wdMap[$wd] ?? 0,
            ];
        }

        // Top member (users.name)
        $topMembers = collect();
        if ($hasMembers && $hasUsers && $hasMemberUserId) {
            $topMembers = (clone $kmBase)
                ->leftJoin('members as m', 'm.id', '=', 'km.member_id')
                ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
                ->selectRaw("km.member_id as member_id, COALESCE(u.name, '-') as nama, COUNT(*) as total")
                ->groupBy('km.member_id', 'u.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn ($r) => (object)[
                    'member_id' => (int) $r->member_id,
                    'nama'      => (string) $r->nama,
                    'total'     => (int) $r->total,
                ]);
        }

        return [
            'rows' => $rows,
            'stats' => [
                // key-key yang DIPAKAI Blade kamu
                'total_checkins' => (int) $totalCheckins,
                'unique_members' => (int) $uniqueMembers,
                'avg_per_day'    => (float) $avgPerDay,

                'utilization'    => (float) $utilization, // 0..1 (Blade mengali 100)
                'active_days'    => (int) $daysWithData,
                'days_in_range'  => (int) $totalDays,
                'longest_streak' => (int) $longestStreak,

                'peak_day'       => $peakDay,
                'peak_hour'      => $peakHour,

                'trend_delta'    => (int) $trendDelta,
                'trend_percent'  => $trendPercent,

                'top_members'    => $topMembers,
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
