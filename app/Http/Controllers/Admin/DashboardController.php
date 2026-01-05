<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use App\Models\KehadiranMember;
use App\Models\Member;
use App\Models\TransaksiMembership;
use App\Models\TransaksiProduk;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Dashboard Admin
     * - KPI utama
     * - Statistik aktivitas (check-in 7 hari)
     * - Penjualan/pendapatan produk bulan ini
     * - Produk terlaris bulan ini
     * - Recent izin latihan (member soft delete aman)
     */
    public function index()
    {
        // =========================================================
        // 0) RANGE WAKTU
        // =========================================================
        $today      = Carbon::today();
        $todayStr   = $today->toDateString();

        $monthStart = $today->copy()->startOfMonth()->startOfDay();
        $monthEnd   = $today->copy()->endOfMonth()->endOfDay();

        // =========================================================
        // A) KPI UTAMA (yang dipakai dashboard)
        // =========================================================
        $totalMembers = $this->getTotalMembers();

        // membership aktif: distinct buyer + participant (pada hari ini)
        $activeMemberships = $this->countActiveMemberships($today);

        // check-in valid hari ini
        $todayCheckins = $this->getTodayValidCheckins($today);

        // izin pending
        $izinPending = $this->getIzinPendingCount();

        // total produk (jika model Produk ada)
        $totalProducts = $this->getTotalProducts();

        // rasio aktif
        $activeRate = $this->calcRate($activeMemberships, $totalMembers);

        // =========================================================
        // B) RECENT IZIN (FIX: member soft delete + nama dari user)
        // =========================================================
        $recentIzin = $this->getRecentIzin(10);

        // =========================================================
        // C) STATISTIK AKTIVITAS
        // - statCheckin7Hari (labels/values/max) untuk kompatibilitas lama
        // - dashboardStatsPayload (series.checkinsLast7Days) untuk Blade baru Anda
        // =========================================================
        $statCheckin7Hari = $this->buildCheckin7Days($today);
        $dashboardStatsPayload = $this->buildDashboardStatsPayload($statCheckin7Hari);

        // =========================================================
        // D) PENJUALAN / MARKETPLACE
        // - pendapatanProdukBulanIni (nama yang dipakai Blade Anda)
        // - pendapatanBulanIni (alias kompatibilitas)
        // =========================================================
        $pendapatanProdukBulanIni = $this->sumPendapatanProdukBulanIni($monthStart, $monthEnd);

        // Produk terlaris bulan ini (butuh transaksi_produk_items + qty)
        $produkTerlaris = $this->getProdukTerlarisBulanIni($monthStart, $monthEnd);

        // tambahan/alias jika view lain masih memerlukan
        $produkTerjualBulanIni = (int)($produkTerlaris->terjual ?? 0);

        // transaksi membership bulan ini (opsional)
        $membershipTrxThisMonth = (int) TransaksiMembership::query()
            ->valid()
            ->whereBetween('tanggal_transaksi', [$monthStart, $monthEnd])
            ->count();

        // alias lama jika masih dipakai view lama
        $latestIzin = $recentIzin;

        // =========================================================
        // E) RENDER VIEW (tanpa view()->first)
        // =========================================================
        $view = $this->resolveDashboardView();

        return view($view, [
            // KPI
            'totalMembers'        => $totalMembers,
            'activeMemberships'   => $activeMemberships,
            'todayCheckins'       => $todayCheckins,
            'totalProducts'       => $totalProducts,
            'izinPending'         => $izinPending,
            'activeRate'          => $activeRate,

            // Izin
            'recentIzin'          => $recentIzin,
            'latestIzin'          => $latestIzin, // alias kompatibilitas

            // Statistik (alias kompatibilitas lama)
            'statCheckin7Hari'    => $statCheckin7Hari,
            'series'              => [
                'labels'   => $statCheckin7Hari['labels'],
                'checkins' => $statCheckin7Hari['values'],
            ],
            'checkinLabels'       => $statCheckin7Hari['labels'],
            'checkinValues'       => $statCheckin7Hari['values'],

            // Statistik (format yang dipakai Blade baru Anda)
            'dashboardStatsPayload' => $dashboardStatsPayload,

            // Penjualan/Marketplace
            'pendapatanProdukBulanIni' => $pendapatanProdukBulanIni,
            'pendapatanBulanIni'       => $pendapatanProdukBulanIni, // alias kompatibilitas
            'produkTerlaris'           => $produkTerlaris,
            'produkTerjualBulanIni'    => $produkTerjualBulanIni,

            // tambahan
            'membershipTrxThisMonth'   => $membershipTrxThisMonth,
        ]);
    }

    // =========================================================
    // SECTION: VIEW RESOLVER
    // =========================================================

    protected function resolveDashboardView(): string
    {
        if (view()->exists('admin.dashboard.index')) return 'admin.dashboard.index';
        if (view()->exists('admin.dashboard')) return 'admin.dashboard';
        return 'admin.dashboard.index';
    }

    // =========================================================
    // SECTION: KPI HELPERS
    // =========================================================

    protected function getTotalMembers(): int
    {
        return (int) Member::query()->count();
    }

    protected function getTodayValidCheckins(Carbon $today): int
    {
        return (int) KehadiranMember::query()
            ->whereDate('tanggal', $today->toDateString())
            ->where('is_valid', true)
            ->count();
    }

    protected function getIzinPendingCount(): int
    {
        return (int) IzinLatihan::query()
            ->where('status', 'pending')
            ->count();
    }

    protected function getTotalProducts(): int
    {
        if (!class_exists(\App\Models\Produk::class)) return 0;
        return (int) \App\Models\Produk::query()->count();
    }

    protected function calcRate(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) return 0;
        return (int) round(($numerator / max($denominator, 1)) * 100);
    }

    // =========================================================
    // SECTION: IZIN HELPERS (SOFT DELETE AMAN)
    // =========================================================

    /**
     * Recent izin latihan:
     * - with member termasuk soft-deleted
     * - with user termasuk soft-deleted
     * - inject member.nama dari users.name agar blade lama aman
     */
    protected function getRecentIzin(int $limit = 10): Collection
    {
        $izin = IzinLatihan::query()
            ->with([
                'member' => function ($q) {
                    $q->withTrashed()
                      ->with([
                          'user' => function ($uq) {
                              $uq->withTrashed();
                          }
                      ]);
                }
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        // Inject atribut "nama" pada member (kompatibilitas blade lama)
        $izin->each(function ($row) {
            if ($row->member) {
                $row->member->setAttribute('nama', $row->member->user?->name);
            }
        });

        return $izin;
    }

    // =========================================================
    // SECTION: MEMBERSHIP AKTIF (BUYER + PARTICIPANT)
    // =========================================================

    /**
     * Hitung jumlah member aktif (distinct buyer + participant) pada tanggal $date.
     */
    protected function countActiveMemberships(Carbon $date): int
    {
        $d = $date->toDateString();

        $activeTrx = TransaksiMembership::query()
            ->valid()
            ->whereDate('tanggal_mulai', '<=', $d)
            ->whereDate('tanggal_akhir', '>=', $d);

        $trxIds = (clone $activeTrx)->pluck('id');

        // buyer ids
        $buyerIds = (clone $activeTrx)
            ->pluck('buyer_member_id')
            ->filter()
            ->unique()
            ->values();

        // participant ids (pivot)
        $participantIds = collect();

        if (class_exists(\App\Models\TransaksiMembershipMember::class)) {
            $pivotTable = (new \App\Models\TransaksiMembershipMember)->getTable();

            if ($trxIds->isNotEmpty() && Schema::hasTable($pivotTable)) {
                $participantIds = DB::table($pivotTable)
                    ->whereIn('transaksi_membership_id', $trxIds)
                    ->pluck('member_id')
                    ->filter()
                    ->unique()
                    ->values();
            }
        }

        return $buyerIds->merge($participantIds)->unique()->count();
    }

    // =========================================================
    // SECTION: STATISTIK CHECK-IN 7 HARI
    // =========================================================

    /**
     * Data check-in valid 7 hari terakhir (format legacy):
     * return:
     *  - labels: ["24 Des", ...]
     *  - values: [3, 0, 5, ...]
     *  - max: max value (buat "Max referensi")
     *  - dates: ["2025-12-24", ...]
     */
    protected function buildCheckin7Days(Carbon $today): array
    {
        $start = $today->copy()->subDays(6)->startOfDay();
        $end   = $today->copy()->endOfDay();

        // group by tanggal (DATE)
        $grouped = KehadiranMember::query()
            ->selectRaw('DATE(tanggal) as d, COUNT(*) as c')
            ->whereBetween('tanggal', [$start, $end])
            ->where('is_valid', true)
            ->groupBy('d')
            ->pluck('c', 'd');

        $labels = [];
        $values = [];
        $dates  = [];

        // urutan: 6 hari lalu ... hari ini
        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $key = $day->toDateString();

            $dates[]  = $key;
            $labels[] = $day->translatedFormat('d M');
            $values[] = (int) ($grouped[$key] ?? 0);
        }

        $max = 0;
        foreach ($values as $v) {
            if ($v > $max) $max = $v;
        }

        return [
            'dates'  => $dates,
            'labels' => $labels,
            'values' => $values,
            'max'    => $max,
        ];
    }

    /**
     * Payload statistik untuk Blade baru:
     * $dashboardStatsPayload['series']['checkinsLast7Days'] = [
     *   ['date'=>'YYYY-MM-DD','label'=>'24 Des','value'=>3], ...
     * ]
     */
    protected function buildDashboardStatsPayload(array $statCheckin7Hari): array
    {
        $dates  = $statCheckin7Hari['dates'] ?? [];
        $labels = $statCheckin7Hari['labels'] ?? [];
        $values = $statCheckin7Hari['values'] ?? [];

        $out = [];
        $n = max(count($dates), count($labels), count($values));

        for ($i = 0; $i < $n; $i++) {
            $out[] = [
                'date'  => (string)($dates[$i] ?? ''),
                'label' => (string)($labels[$i] ?? ''),
                'value' => (int)($values[$i] ?? 0),
            ];
        }

        return [
            'series' => [
                'checkinsLast7Days' => $out,
            ],
            'meta' => [
                'maxCheckin' => (int)($statCheckin7Hari['max'] ?? 0),
            ],
        ];
    }

    // =========================================================
    // SECTION: PENJUALAN / PENDAPATAN PRODUK
    // =========================================================

    /**
     * Sum pendapatan produk bulan ini dari transaksi_produks.total
     * (nama variabel output: pendapatanProdukBulanIni)
     */
    protected function sumPendapatanProdukBulanIni(Carbon $monthStart, Carbon $monthEnd): int
    {
        // Pastikan range Carbon dipakai sesuai cast model (tanggal_transaksi: datetime)
        $sum = TransaksiProduk::query()
            ->whereBetween('tanggal_transaksi', [$monthStart, $monthEnd])
            ->sum('total');

        // sum bisa balik string numeric tergantung driver
        return (int) $sum;
    }

    /**
     * Produk terlaris bulan ini.
     * - Mengutamakan tabel/model TransaksiProdukItem jika tersedia
     * - Mendeteksi nama kolom qty yang umum: qty/jumlah/quantity
     * - Return object { nama, terjual } atau null
     */
    protected function getProdukTerlarisBulanIni(Carbon $monthStart, Carbon $monthEnd): ?object
    {
        // Guard: butuh Produk
        if (!class_exists(\App\Models\Produk::class)) {
            return null;
        }

        // Tentukan tabel produk
        $produkTable = (new \App\Models\Produk)->getTable();

        // Prioritas 1: pakai model item jika ada
        if (class_exists(\App\Models\TransaksiProdukItem::class)) {
            $itemModel  = new \App\Models\TransaksiProdukItem();
            $itemsTable = $itemModel->getTable();

            $top = $this->queryProdukTerlarisViaItemsTable($itemsTable, $monthStart, $monthEnd);
            if ($top) {
                $produk = \App\Models\Produk::query()->find($top->produk_id);
                if ($produk) {
                    return (object)[
                        'nama'    => $produk->nama,
                        'terjual' => (int) $top->terjual,
                    ];
                }
            }
        }

        // Prioritas 2: fallback tabel default transaksi_produk_items
        if (Schema::hasTable('transaksi_produk_items')) {
            $top = $this->queryProdukTerlarisViaItemsTable('transaksi_produk_items', $monthStart, $monthEnd);
            if ($top) {
                $produk = \App\Models\Produk::query()->find($top->produk_id);
                if ($produk) {
                    return (object)[
                        'nama'    => $produk->nama,
                        'terjual' => (int) $top->terjual,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Query helper untuk cari produk terlaris dari tabel items
     * Return: object {produk_id, terjual} atau null
     */
    protected function queryProdukTerlarisViaItemsTable(string $itemsTable, Carbon $monthStart, Carbon $monthEnd): ?object
    {
        if (!Schema::hasTable($itemsTable)) return null;

        // Deteksi kolom qty yang umum
        $qtyCol = $this->detectQtyColumn($itemsTable);
        if (!$qtyCol) return null;

        // Kolom wajib
        if (!Schema::hasColumn($itemsTable, 'produk_id')) return null;
        if (!Schema::hasColumn($itemsTable, 'transaksi_produk_id')) return null;

        // Query TOP
        $top = DB::table($itemsTable . ' as i')
            ->join('transaksi_produks as t', 't.id', '=', 'i.transaksi_produk_id')
            ->whereBetween('t.tanggal_transaksi', [$monthStart, $monthEnd])
            ->selectRaw('i.produk_id, SUM(i.' . $qtyCol . ') as terjual')
            ->groupBy('i.produk_id')
            ->orderByDesc('terjual')
            ->first();

        // Pastikan ada hasil & produk_id valid
        if (!$top || empty($top->produk_id)) return null;

        return $top;
    }

    /**
     * Deteksi nama kolom qty pada tabel item.
     */
    protected function detectQtyColumn(string $table): ?string
    {
        foreach (['qty', 'jumlah', 'quantity'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }
}
