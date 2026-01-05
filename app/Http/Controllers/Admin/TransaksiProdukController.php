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
     */
    public function history(Request $request)
    {
        $pageTitle = 'Riwayat Transaksi Produk';

        $query = TransaksiProduk::with(['buyer.user', 'creator', 'items.produk']);

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

            $noNota = $this->generateNoNota16();

            $transaksi = TransaksiProduk::create([
                'no_nota'           => $noNota,
                'tanggal_transaksi' => now(),
                'buyer_member_id'   => $validated['buyer_member_id'] ?? null,
                'created_by'        => auth()->id(),
                'metode_pembayaran' => $metode,
                'total'             => 0, // update setelah items dihitung
                'keterangan'        => $validated['keterangan'] ?? null,
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

                // Log stok
                StokProduk::create([
                    'produk_id'  => $produk->id,
                    'jumlah'     => -(int) $qty,
                    'tanggal'    => now(),
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
     * DESTROY: Batalkan transaksi (kembalikan stok + hapus log terkait).
     */
    public function destroy(TransaksiProduk $transaksiProduk)
    {
        DB::beginTransaction();
        try {
            $transaksiProduk->load('items');

            $noNota = $transaksiProduk->no_nota;

            foreach ($transaksiProduk->items as $item) {
                // Kembalikan stok
                Produk::where('id', $item->produk_id)->increment('stok', (int) $item->qty);

                // Hapus log stok terkait transaksi ini
                StokProduk::where('produk_id', $item->produk_id)
                    ->where('keterangan', 'like', "%[NO_NOTA:{$noNota}]%")
                    ->delete();
            }

            // Hapus transaksi (items ikut terhapus jika FK cascade sudah benar)
            $transaksiProduk->delete();

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
            'buyer.user',   // asumsi Member punya relasi user()
            'creator',      // user kasir/admin
        ]);

        return view('admin.transaksi_produk.struk', [
            'trx' => $transaksiProduk,
        ]);
    }

    /**
     * Generate no_nota 16 char, alnum, uppercase, unique.
     */
    private function generateNoNota16(): string
    {
        for ($i = 0; $i < 30; $i++) {
            $code = Str::upper(Str::random(16));
            if (!TransaksiProduk::where('no_nota', $code)->exists()) {
                return $code;
            }
        }

        // fallback (sangat jarang)
        return Str::upper(Str::random(16));
    }
}
