<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LatihanHarian;
use App\Models\TransaksiMembership;
use App\Models\TransaksiProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganController extends Controller
{
    /**
     * DASHBOARD UTAMA (Ringkasan)
     */
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // 1. Query Dasar (Filter Tanggal & Status Aktif)
        $produkBase = TransaksiProduk::whereNull('canceled_at')
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        $memberBase = TransaksiMembership::whereNull('canceled_at')
            ->where('jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        $harianBase = LatihanHarian::whereNull('canceled_at')
            ->whereBetween('tanggal', [$from, $to]);

        // 2. Hitung Total Omzet Per Kategori
        $totalProduk = (int) $produkBase->sum('total');
        $totalMembership = (int) $memberBase->sum('total');
        $totalHarian = (int) $harianBase->sum('total'); // Asumsi kolom total sudah benar
        $grandTotal = $totalProduk + $totalMembership + $totalHarian;

        // 3. Data Tren Harian (Untuk Stacked Bar Chart)
        $produkDaily = $produkBase->clone()
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')->pluck('total', 'tgl')->toArray();

        $memberDaily = $memberBase->clone()
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')->pluck('total', 'tgl')->toArray();

        $harianDaily = $harianBase->clone()
            ->selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->groupBy('tgl')->pluck('total', 'tgl')->toArray();

        // Gabungkan data harian menjadi array lengkap (Helper mergeDaily3)
        $daily = $this->mergeDaily3($produkDaily, $memberDaily, $harianDaily, $from, $to);

        // 4. [PENTING] Data Metode Pembayaran Gabungan (Untuk Donut Chart Dashboard)
        // Kita hitung manual penjumlahan array dari 3 sumber
        $metodeProduk = $produkBase->clone()
            ->select('metode_pembayaran', DB::raw('sum(total) as total'))
            ->groupBy('metode_pembayaran')->pluck('total', 'metode_pembayaran')->toArray();

        $metodeMember = $memberBase->clone()
            ->select('metode_pembayaran', DB::raw('sum(total) as total'))
            ->groupBy('metode_pembayaran')->pluck('total', 'metode_pembayaran')->toArray();

        $metodeHarian = $harianBase->clone()
            ->select('metode_pembayaran', DB::raw('sum(total) as total'))
            ->groupBy('metode_pembayaran')->pluck('total', 'metode_pembayaran')->toArray();

        // Gabungkan nilai cash, transfer, qris dari ketiga sumber
        $grandMetode = [
            'cash'     => ($metodeProduk['cash'] ?? 0) + ($metodeMember['cash'] ?? 0) + ($metodeHarian['cash'] ?? 0),
            'transfer' => ($metodeProduk['transfer'] ?? 0) + ($metodeMember['transfer'] ?? 0) + ($metodeHarian['transfer'] ?? 0),
            'qris'     => ($metodeProduk['qris'] ?? 0) + ($metodeMember['qris'] ?? 0) + ($metodeHarian['qris'] ?? 0),
        ];

        return view('admin.laporan.keuangan.index', compact(
            'from',
            'to',
            'totalProduk',
            'totalMembership',
            'totalHarian',
            'grandTotal',
            'daily',
            'grandMetode'
        ));
    }

    /**
     * LAPORAN PRODUK (Detail & Top Seller)
     */
    public function produk(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode');

        // Base query (untuk total, count, daily, byMetode)
        $base = TransaksiProduk::query()
            ->whereNull('canceled_at')
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%")
                    ->orWhereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if (!empty($metode)) {
            $base->where('metode_pembayaran', $metode);
        }

        // Summary
        $total = (int) (clone $base)->sum('total');
        $count = (int) (clone $base)->count();
        $avg   = $count > 0 ? (int) round($total / $count) : 0;

        // Breakdown metode pembayaran
        $byMetode = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(metode_pembayaran), ''), 'unknown') as metode, SUM(total) as total")
            ->groupBy('metode')
            ->orderByDesc('total')
            ->pluck('total', 'metode')
            ->map(fn($v) => (int) $v)
            ->toArray();

        // Tren harian (chart)
        $daily = (clone $base)
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->map(fn($r) => ['tanggal' => (string) $r->tgl, 'total' => (int) $r->total])
            ->toArray();

        // Rows (table) + eager load
        $rows = (clone $base)
            ->with(['buyer.user', 'creator'])
            ->orderByDesc('tanggal_transaksi')
            ->paginate(20)
            ->withQueryString();

        // Top Produk (Top 5 Qty) - pastikan kolom quantity benar
        $topProduk = DB::table('transaksi_produk_items as tpi')
            ->join('transaksi_produks as tp', 'tp.id', '=', 'tpi.transaksi_produk_id')
            ->join('produks as p', 'p.id', '=', 'tpi.produk_id')
            ->whereNull('tp.canceled_at')
            ->whereBetween('tp.tanggal_transaksi', [$from, $to])
            ->selectRaw('p.nama, SUM(tpi.qty) as qty')
            ->groupBy('p.nama')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Top Produk per Kategori (Top 5 Qty per kategori)
        $kategoriList = ['suplemen', 'minuman', 'lainnya'];

        $topProdukByKategori = [];
        foreach ($kategoriList as $kat) {
            $topProdukByKategori[$kat] = DB::table('transaksi_produk_items as tpi')
                ->join('transaksi_produks as tp', 'tp.id', '=', 'tpi.transaksi_produk_id')
                ->join('produks as p', 'p.id', '=', 'tpi.produk_id')
                ->whereNull('tp.canceled_at')
                ->whereBetween('tp.tanggal_transaksi', [$from, $to])
                ->where('p.kategori', $kat)
                ->selectRaw('p.id, p.nama, SUM(tpi.qty) as qty')
                ->groupBy('p.id', 'p.nama')
                ->orderByDesc('qty')
                ->limit(5)
                ->get();
        }


        return view('admin.laporan.keuangan.produk', compact(
            'from',
            'to',
            'q',
            'metode',
            'rows',
            'total',
            'count',
            'avg',
            'byMetode',
            'daily',
            'topProduk',
            'topProdukByKategori'
        ));
    }

    /**
     * LAPORAN MEMBERSHIP
     */
    public function membership(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode');

        $base = TransaksiMembership::query()
            ->whereNull('canceled_at')
            ->where('jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tanggal_transaksi', [$from, $to]);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                    ->orWhereHas('buyer.user', fn($u) => $u->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('paket', fn($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        }

        if (!empty($metode)) {
            $base->where('metode_pembayaran', $metode);
        }

        // Summary
        $total = (int) (clone $base)->sum('total');
        $count = (int) (clone $base)->count();
        $avg   = $count > 0 ? (int) round($total / $count) : 0;

        // Breakdown metode pembayaran
        $byMetode = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(metode_pembayaran), ''), 'unknown') as metode, SUM(total) as total")
            ->groupBy('metode')
            ->orderByDesc('total')
            ->pluck('total', 'metode')
            ->map(fn($v) => (int) $v)
            ->toArray();

        // Tren Harian Membership
        $daily = (clone $base)
            ->selectRaw('DATE(tanggal_transaksi) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->map(fn($r) => ['tanggal' => (string) $r->tgl, 'total' => (int) $r->total])
            ->toArray();

        // Rows (table)
        $rows = (clone $base)
            ->with(['buyer.user', 'creator', 'paket'])
            ->orderByDesc('tanggal_transaksi')
            ->paginate(20)
            ->withQueryString();

        // Top Paket (Top 5 by omzet)
        $topPaket = DB::table('transaksi_memberships as tm')
            ->join('paket_memberships as p', 'p.id', '=', 'tm.paket_id')
            ->whereNull('tm.canceled_at')
            ->where('tm.jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tm.tanggal_transaksi', [$from, $to])
            ->selectRaw('p.nama as paket, SUM(tm.total) as total, COUNT(*) as trx')
            ->groupBy('p.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.laporan.keuangan.membership', compact(
            'from',
            'to',
            'q',
            'metode',
            'rows',
            'total',
            'count',
            'avg',
            'byMetode',
            'daily',
            'topPaket'
        ));
    }

    /**
     * LAPORAN HARIAN
     */
    public function harian(Request $request)
    {
        [$from, $to] = $this->range($request);

        $q = trim((string) $request->get('q', ''));
        $metode = $request->get('metode');
        $kategori = $request->get('kategori');

        $base = LatihanHarian::query()
            ->whereNull('canceled_at')
            ->whereBetween('tanggal', [$from, $to]);

        if ($q !== '') {
            $base->where('nama', 'like', "%{$q}%");
        }
        if (!empty($metode)) {
            $base->where('metode_pembayaran', $metode);
        }
        if (!empty($kategori)) {
            $base->where('kategori', $kategori);
        }

        // Summary
        $total = (int) (clone $base)->sum('total');
        $count = (int) (clone $base)->count();
        $avg   = $count > 0 ? (int) round($total / $count) : 0;

        // Tren harian
        $daily = (clone $base)
            ->selectRaw('DATE(tanggal) as tgl, SUM(total) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->map(fn($r) => ['tanggal' => (string) $r->tgl, 'total' => (int) $r->total])
            ->toArray();

        // Breakdown kategori
        $byKategori = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(kategori), ''), 'unknown') as kategori, SUM(total) as total")
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori')
            ->map(fn($v) => (int) $v)
            ->toArray();

        // Breakdown metode pembayaran
        $byMetode = (clone $base)
            ->selectRaw("COALESCE(NULLIF(TRIM(metode_pembayaran), ''), 'unknown') as metode, SUM(total) as total")
            ->groupBy('metode')
            ->orderByDesc('total')
            ->pluck('total', 'metode')
            ->map(fn($v) => (int) $v)
            ->toArray();

        // Rows (table)
        $rows = (clone $base)
            ->with(['creator']) // relasi ada di model kamu
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        return view('admin.laporan.keuangan.harian', compact(
            'from',
            'to',
            'q',
            'metode',
            'kategori',
            'rows',
            'total',
            'count',
            'avg',
            'daily',
            'byKategori',
            'byMetode'
        ));
    }


    /**
     * LAPORAN GABUNGAN (Audit Table)
     */
    public function gabungan(Request $request)
    {
        [$from, $to] = $this->range($request);
        $sumber = $request->get('sumber');

        // 1. Produk
        $produkSel = DB::table('transaksi_produks as tp')
            ->leftJoin('members as mb', 'mb.id', '=', 'tp.buyer_member_id')
            ->leftJoin('users as ub', 'ub.id', '=', 'mb.user_id')
            ->whereNull('tp.canceled_at')
            ->whereBetween('tp.tanggal_transaksi', [$from, $to])
            ->selectRaw("tp.tanggal_transaksi, tp.no_nota, tp.total, tp.metode_pembayaran, COALESCE(ub.name, 'Guest') as buyer_name, 'produk' as sumber");

        // 2. Membership
        $memberSel = DB::table('transaksi_memberships as tm')
            ->join('members as mb2', 'mb2.id', '=', 'tm.buyer_member_id')
            ->join('users as ub2', 'ub2.id', '=', 'mb2.user_id')
            ->whereNull('tm.canceled_at')
            ->where('tm.jenis_transaksi', TransaksiMembership::JENIS_PEMBAYARAN)
            ->whereBetween('tm.tanggal_transaksi', [$from, $to])
            ->selectRaw("tm.tanggal_transaksi, tm.no_nota, tm.total, tm.metode_pembayaran, ub2.name as buyer_name, 'membership' as sumber");

        // 3. Harian
        $harianSel = DB::table('latihan_harian as lh')
            ->whereNull('lh.canceled_at')
            ->whereBetween('lh.tanggal', [$from, $to])
            ->selectRaw("lh.tanggal as tanggal_transaksi, CONCAT('LH-', lh.id) as no_nota, lh.total, lh.metode_pembayaran, lh.nama as buyer_name, 'harian' as sumber");

        // Filter Sumber & Union
        if ($sumber === 'produk') $union = $produkSel;
        elseif ($sumber === 'membership') $union = $memberSel;
        elseif ($sumber === 'harian') $union = $harianSel;
        else $union = $produkSel->unionAll($memberSel)->unionAll($harianSel);

        $wrapped = DB::query()->fromSub($union, 'u');
        $rows = $wrapped->orderByDesc('tanggal_transaksi')->paginate(20)->withQueryString();
        $total = (int) $wrapped->clone()->sum('total');

        return view('admin.laporan.keuangan.gabungan', compact('from', 'to', 'rows', 'total', 'sumber'));
    }

    // --- HELPER ---
    private function range(Request $request): array
    {
        $to = $request->get('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $from = $request->get('from') ? Carbon::parse($request->from)->startOfDay() : now()->subDays(29)->startOfDay();
        return [$from, $to];
    }

    private function mergeDaily3(array $p, array $m, array $h, Carbon $from, Carbon $to): array
    {
        $out = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $k = $cursor->toDateString();
            $pVal = (int) ($p[$k] ?? 0);
            $mVal = (int) ($m[$k] ?? 0);
            $hVal = (int) ($h[$k] ?? 0);
            $out[] = [
                'tanggal' => $k,
                'produk' => $pVal,
                'membership' => $mVal,
                'harian' => $hVal,
                'total' => $pVal + $mVal + $hVal
            ];
            $cursor->addDay();
        }
        return $out;
    }
}
