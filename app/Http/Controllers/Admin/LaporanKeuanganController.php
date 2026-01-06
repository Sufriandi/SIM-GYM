<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransaksiMembership;
use App\Models\TransaksiProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganController extends Controller
{
    /**
     * RINGKASAN:
     * - Total produk, total membership (revenue only), total gabungan
     * - Breakdown metode pembayaran
     * - Tren harian gabungan
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // Produk: transaksi valid (tidak canceled)
        $produkBase = TransaksiProduk::query()
            ->whereNull('canceled_at')
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        $totalProduk = (int) $produkBase->sum('total');

        $produkByMetode = $produkBase->clone()
            ->selectRaw('metode_pembayaran, SUM(total) as total')
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();

        // Membership: revenue only (pembayaran, tidak canceled)
        $memberBase = TransaksiMembership::query()
            ->whereNull('canceled_at')
            ->where('jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        $totalMembership = (int) $memberBase->sum('total');

        $membershipByMetode = $memberBase->clone()
            ->selectRaw('metode_pembayaran, SUM(total) as total')
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();

        $grandTotal = $totalProduk + $totalMembership;

        // Tren harian (gabungan)
        $produkDaily = $produkBase->clone()
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl')
            ->toArray();

        $membershipDaily = $memberBase->clone()
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl')
            ->toArray();

        $daily = $this->mergeDaily($produkDaily, $membershipDaily, $from, $to);

        return view('admin.laporan.keuangan.index', compact(
            'from',
            'to',
            'totalProduk',
            'totalMembership',
            'grandTotal',
            'produkByMetode',
            'membershipByMetode',
            'daily'
        ));
    }

    /**
     * LAPORAN PRODUK (detail list + total + breakdown metode)
     */
    public function produk(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode'); // cash|transfer|qris|null

        $query = TransaksiProduk::with(['buyer.user', 'creator'])
            ->whereNull('canceled_at')
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%")
                    ->orWhereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if (!empty($metode)) {
            $query->where('metode_pembayaran', $metode);
        }

        $rows = $query->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $total = (int) $query->clone()->sum('total');

        $byMetode = $query->clone()
            ->selectRaw('metode_pembayaran, SUM(total) as total')
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();

        return view('admin.laporan.keuangan.produk', compact(
            'from',
            'to',
            'q',
            'metode',
            'rows',
            'total',
            'byMetode'
        ));
    }

    /**
     * LAPORAN MEMBERSHIP (revenue only: pembayaran)
     */
    public function membership(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode'); // cash|transfer|qris|null

        $query = TransaksiMembership::with(['buyer.user', 'creator', 'paket'])
            ->whereNull('canceled_at')
            ->where('jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%")
                    ->orWhereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('paket', fn($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        }

        if (!empty($metode)) {
            $query->where('metode_pembayaran', $metode);
        }

        $rows = $query->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $total = (int) $query->clone()->sum('total');

        $byMetode = $query->clone()
            ->selectRaw('metode_pembayaran, SUM(total) as total')
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();

        return view('admin.laporan.keuangan.membership', compact(
            'from',
            'to',
            'q',
            'metode',
            'rows',
            'total',
            'byMetode'
        ));
    }

    /**
     * LAPORAN GABUNGAN:
     * Produk + Membership (revenue only) dalam satu tabel untuk audit kas.
     * Menggunakan UNION ALL + fromSub agar count/sum/pagination stabil.
     */
    public function gabungan(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode'); // cash|transfer|qris|null
        $sumber = $request->get('sumber'); // produk|membership|null

        // ---------- Produk ----------
        $produkQ = DB::table('transaksi_produks as tp')
            ->leftJoin('members as mb', 'mb.id', '=', 'tp.buyer_member_id')
            ->leftJoin('users as ub', 'ub.id', '=', 'mb.user_id')
            ->join('users as uc', 'uc.id', '=', 'tp.created_by')
            ->whereNull('tp.canceled_at')
            ->whereBetween('tp.tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $produkQ->where(function ($w) use ($q) {
                $w->where('tp.no_nota', 'like', "%{$q}%")
                    ->orWhere('tp.keterangan', 'like', "%{$q}%")
                    ->orWhere('ub.name', 'like', "%{$q}%")
                    ->orWhere('uc.name', 'like', "%{$q}%");
            });
        }

        if (!empty($metode)) {
            $produkQ->where('tp.metode_pembayaran', $metode);
        }

        $produkSel = $produkQ->selectRaw("
            tp.id as id,
            tp.tanggal_transaksi as tanggal_transaksi,
            tp.no_nota as no_nota,
            tp.metode_pembayaran as metode_pembayaran,
            tp.total as total,
            COALESCE(ub.name, 'Guest') as buyer_name,
            uc.name as creator_name,
            'produk' as sumber
        ");

        // ---------- Membership (revenue only) ----------
        $memberQ = DB::table('transaksi_memberships as tm')
            ->join('members as mb2', 'mb2.id', '=', 'tm.buyer_member_id')
            ->join('users as ub2', 'ub2.id', '=', 'mb2.user_id')
            ->join('users as uc2', 'uc2.id', '=', 'tm.created_by')
            ->whereNull('tm.canceled_at')
            ->where('tm.jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tm.tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $memberQ->where(function ($w) use ($q) {
                $w->where('tm.no_nota', 'like', "%{$q}%")
                    ->orWhere('tm.keterangan', 'like', "%{$q}%")
                    ->orWhere('ub2.name', 'like', "%{$q}%")
                    ->orWhere('uc2.name', 'like', "%{$q}%");
            });
        }

        if (!empty($metode)) {
            $memberQ->where('tm.metode_pembayaran', $metode);
        }

        $memberSel = $memberQ->selectRaw("
            tm.id as id,
            tm.tanggal_transaksi as tanggal_transaksi,
            tm.no_nota as no_nota,
            tm.metode_pembayaran as metode_pembayaran,
            tm.total as total,
            ub2.name as buyer_name,
            uc2.name as creator_name,
            'membership' as sumber
        ");

        // ---------- Filter sumber ----------
        if ($sumber === 'produk') {
            $union = $produkSel;
        } elseif ($sumber === 'membership') {
            $union = $memberSel;
        } else {
            $union = $produkSel->unionAll($memberSel);
        }

        // Wrap UNION agar count/sum/pagination stabil
        $wrapped = DB::query()->fromSub($union, 'u');

        $total = (int) $wrapped->clone()->sum('total');
        $totalRows = (int) $wrapped->clone()->count();

        $rows = $wrapped
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $byMetode = $wrapped->clone()
            ->selectRaw('metode_pembayaran, SUM(total) as total')
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();

        $bySumber = $wrapped->clone()
            ->selectRaw('sumber, SUM(total) as total')
            ->groupBy('sumber')
            ->pluck('total', 'sumber')
            ->toArray();


        return view('admin.laporan.keuangan.gabungan', compact(
            'from',
            'to',
            'q',
            'metode',
            'sumber',
            'rows',
            'total',
            'totalRows',
            'byMetode',
            'bySumber'
        ));
    }

    // =========================================================
    // Helpers
    // =========================================================

    private function range(Request $request): array
    {
        // Default: 30 hari terakhir
        $to = $request->get('to')
            ? Carbon::parse($request->get('to'))->endOfDay()
            : now()->endOfDay();

        $from = $request->get('from')
            ? Carbon::parse($request->get('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        return [$from, $to];
    }

    private function mergeDaily(array $produkDaily, array $membershipDaily, Carbon $from, Carbon $to): array
    {
        $out = [];

        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $k = $cursor->toDateString();

            $p = (int) ($produkDaily[$k] ?? 0);
            $m = (int) ($membershipDaily[$k] ?? 0);

            $out[] = [
                'tanggal'    => $k,
                'produk'     => $p,
                'membership' => $m,
                'total'      => $p + $m,
            ];

            $cursor->addDay();
        }

        return $out;
    }
}
