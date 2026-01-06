<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Models\TransaksiProduk;
use App\Models\TransaksiProdukItem;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\Member;

class TransaksiProdukController extends Controller
{
    /**
     * Nilai metode pembayaran yang disimpan di DB.
     */
    private array $metodeKeys = ['cash', 'transfer', 'qris'];

    /**
     * Label metode pembayaran untuk UI.
     */
    private array $metodeLabels = [
        'cash'     => 'Cash',
        'transfer' => 'Transfer',
        'qris'     => 'QRIS',
    ];

    /**
     * INDEX (Kasir): Form input transaksi baru.
     */
    public function index()
    {
        $pageTitle = 'Transaksi Produk (Kasir)';

        $produks = Produk::orderBy('nama')->get();

        $members = Member::with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'member'))
            ->orderBy('id', 'desc')
            ->get();

        $metodeLabels = $this->metodeLabels;

        return view('admin.transaksi_produk.index', compact(
            'pageTitle',
            'produks',
            'members',
            'metodeLabels'
        ));
    }

    /**
     * HISTORY (Riwayat): List transaksi + filter + modal detail.
     * Default: hanya yang belum dibatalkan (canceled_at null).
     */
    public function history(Request $request)
    {
        $pageTitle = 'Riwayat Transaksi Produk';

        $query = TransaksiProduk::with(['buyer.user', 'creator', 'items.produk'])
            ->whereNull('canceled_at'); // NEW: exclude canceled by default

        $search       = $request->input('q');
        $filterMetode = $request->input('metode_pembayaran');
        $filterBuyer  = $request->input('buyer_member_id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_nota', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('buyer.user', fn($qq) => $qq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.produk', fn($qq) => $qq->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', strtolower($filterMetode));
        }

        if ($request->filled('buyer_member_id')) {
            $query->where('buyer_member_id', $filterBuyer);
        }

        $daftar_transaksi = $query
            ->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $members = Member::with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'member'))
            ->orderBy('id', 'desc')
            ->get();

        $metodeLabels = $this->metodeLabels;

        return view('admin.transaksi_produk.history', compact(
            'pageTitle',
            'daftar_transaksi',
            'members',
            'metodeLabels',
            'search',
            'filterMetode',
            'filterBuyer'
        ));
    }

    /**
     * STORE: Simpan transaksi + items, update stok, catat log stok.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buyer_member_id'   => 'nullable|exists:members,id',
            'metode_pembayaran' => ['required', Rule::in($this->metodeKeys)],
            'keterangan'        => 'nullable|string|max:1000',

            'items'             => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.qty'       => 'required|integer|min:1',
        ]);

        $metode = strtolower($validated['metode_pembayaran']);

        // Gabungkan jika produk sama dipilih lebih dari sekali
        $grouped = [];
        foreach ($validated['items'] as $row) {
            $pid = (int) $row['produk_id'];
            $qty = (int) $row['qty'];
            $grouped[$pid] = ($grouped[$pid] ?? 0) + $qty;
        }

        $produkIds = array_keys($grouped);

        DB::beginTransaction();
        try {
            // Lock row produk untuk mencegah race condition saat cek stok
            $produkMap = Produk::whereIn('id', $produkIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validasi stok
            foreach ($grouped as $produkId => $qty) {
                $produk = $produkMap->get($produkId);
                if (!$produk) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Produk tidak ditemukan.');
                }

                if ($qty > (int) $produk->stok) {
                    DB::rollBack();
                    return back()->withInput()->with(
                        'error',
                        "Stok '{$produk->nama}' tidak cukup. Tersedia: {$produk->stok}. Permintaan: {$qty}."
                    );
                }
            }

            // NEW: nota sistematis 16 char (TP-YYMMDD-XXXXXX)
            $noNota = $this->generateNoNota16('TP');

            $transaksi = TransaksiProduk::create([
                'no_nota'           => $noNota,
                'tanggal_transaksi' => now(),
                'buyer_member_id'   => $validated['buyer_member_id'] ?? null,
                'created_by'        => auth()->id(),
                'metode_pembayaran' => $metode,
                'total'             => 0, // update setelah items dihitung
                'keterangan'        => $validated['keterangan'] ?? null,
                'canceled_at'       => null, // NEW (optional explicit)
            ]);

            $total = 0;

            foreach ($grouped as $produkId => $qty) {
                $produk = $produkMap->get($produkId);

                $hargaSatuan = (int) $produk->harga;
                $total += ($hargaSatuan * (int) $qty);

                TransaksiProdukItem::create([
                    'transaksi_produk_id' => $transaksi->id,
                    'produk_id'           => $produk->id,
                    'qty'                 => (int) $qty,
                    'harga_satuan'        => $hargaSatuan,
                ]);

                // Kurangi stok produk
                $produk->decrement('stok', (int) $qty);

                // Log stok (keluar)
                StokProduk::create([
                    'produk_id'  => $produk->id,
                    'jumlah'     => -(int) $qty,
                    'tanggal' => now()->toDateString(),
                    'keterangan' => "Produk dijual. [NO_NOTA:{$noNota}]",
                ]);
            }

            $transaksi->update(['total' => $total]);

            DB::commit();

            return redirect()
                ->route('admin.transaksi_produk.index')
                ->with('success', "Transaksi {$noNota} berhasil dicatat. Total Rp " . number_format($total, 0, ',', '.') . ".");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mencatat transaksi. Error: ' . $e->getMessage());
        }
    }

    /**
     * DESTROY: Batalkan transaksi (set canceled_at) + kembalikan stok + buat log pembatalan.
     * (Tidak hard delete lagi).
     */
    public function destroy(TransaksiProduk $transaksiProduk)
    {
        DB::beginTransaction();
        try {
            // Lock transaksi agar tidak double-cancel (idempotent)
            $trx = TransaksiProduk::whereKey($transaksiProduk->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            $noNota = $trx->no_nota;

            if (!is_null($trx->canceled_at)) {
                DB::commit();
                return back()->with('info', "Transaksi {$noNota} sudah dibatalkan sebelumnya.");
            }

            // Lock produk terkait agar update stok aman
            $produkIds = $trx->items->pluck('produk_id')->unique()->values()->all();
            $produkMap = Produk::whereIn('id', $produkIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($trx->items as $item) {
                $qty = (int) $item->qty;
                $produk = $produkMap->get((int) $item->produk_id);

                if ($produk) {
                    // Kembalikan stok
                    $produk->increment('stok', $qty);

                    // Log stok (masuk) karena pembatalan
                    StokProduk::create([
                        'produk_id'  => $produk->id,
                        'jumlah'     => $qty,
                        'tanggal' => now()->toDateString(),
                        'keterangan' => "Pembatalan transaksi. [NO_NOTA:{$noNota}]",
                    ]);
                }
            }

            // Tandai canceled (audit trail tetap)
            $trx->canceled_at = now();
            $trx->save();

            DB::commit();

            return back()->with('success', "Transaksi {$noNota} dibatalkan. Stok dikembalikan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi. Error: ' . $e->getMessage());
        }
    }

    public function cetak(TransaksiProduk $transaksiProduk)
    {
        $transaksiProduk->load([
            'items.produk',
            'buyer.user',
            'creator',
        ]);

        return view('admin.transaksi_produk.struk', [
            'trx' => $transaksiProduk,
        ]);
    }

    /**
     * Generate no_nota 16 char sistematis:
     * TP-YYMMDD-XXXXXX (total 16 char) dan unique.
     */
    private function generateNoNota16(string $prefix = 'TP'): string
    {
        $date = now()->format('ymd'); // YYMMDD

        for ($i = 0; $i < 50; $i++) {
            $rand = Str::upper(Str::random(6)); // 6 alnum
            $code = "{$prefix}-{$date}-{$rand}"; // 16 char

            if (!TransaksiProduk::where('no_nota', $code)->exists()) {
                return $code;
            }
        }

        // fallback (sangat jarang)
        return "{$prefix}-{$date}-" . Str::upper(Str::random(6));
    }
}
