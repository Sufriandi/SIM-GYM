<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiProduk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransaksiProdukController extends Controller
{
    /**
     * GET /api/transaksi-produk/history/{memberId}
     * Riwayat pembelian produk untuk member.
     * Snapshot-safe: total dari transaksi_produks.total, item harga dari transaksi_produk_items.harga_satuan
     */
    public function historyByMember(Request $request, $memberId)
    {
        $memberId = (int) $memberId;

        $q = trim((string) $request->query('q', ''));

        $rows = TransaksiProduk::query()
            ->with(['items.produk']) // produk untuk nama/foto, bukan harga
            ->whereNull('canceled_at')
            ->where('buyer_member_id', $memberId)
            ->when($q !== '', function ($w) use ($q) {
                $w->where('no_nota', 'like', "%{$q}%")
                  ->orWhere('keterangan', 'like', "%{$q}%")
                  ->orWhereHas('items.produk', fn($p) => $p->where('nama', 'like', "%{$q}%"));
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->get()
            ->map(function (TransaksiProduk $t) {
                $tanggal = $t->tanggal_transaksi ?? $t->created_at;

                return [
                    'id' => $t->id,
                    'no_nota' => $t->no_nota,
                    'tanggal_transaksi' => $tanggal?->toDateTimeString(),
                    'metode_pembayaran' => $t->metode_pembayaran,
                    'keterangan' => $t->keterangan,
                    'total' => (int) $t->total, // SNAPSHOT
                    'total_formatted' => 'Rp ' . number_format((int) $t->total, 0, ',', '.'),
                    'items' => $t->items->map(function ($it) {
                        $qty = (int) $it->qty;
                        $harga = (int) $it->harga_satuan; // SNAPSHOT
                        $subtotal = $harga * $qty;

                        return [
                            'produk_id' => (int) $it->produk_id,
                            'nama' => $it->produk?->nama,
                            'foto' => $it->produk?->foto,
                            'qty' => $qty,
                            'harga_satuan' => $harga,
                            'harga_satuan_formatted' => 'Rp ' . number_format($harga, 0, ',', '.'),
                            'subtotal' => $subtotal,
                            'subtotal_formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat transaksi produk member',
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/transaksi-produk/detail/{id}
     * Detail 1 transaksi.
     */
    public function detail($id)
    {
        $id = (int) $id;

        $t = TransaksiProduk::query()
            ->with(['items.produk'])
            ->whereKey($id)
            ->first();

        if (!$t) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        $tanggal = $t->tanggal_transaksi ?? $t->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi produk',
            'data' => [
                'id' => $t->id,
                'no_nota' => $t->no_nota,
                'tanggal_transaksi' => $tanggal?->toDateTimeString(),
                'metode_pembayaran' => $t->metode_pembayaran,
                'keterangan' => $t->keterangan,
                'total' => (int) $t->total,
                'total_formatted' => 'Rp ' . number_format((int) $t->total, 0, ',', '.'),
                'items' => $t->items->map(function ($it) {
                    $qty = (int) $it->qty;
                    $harga = (int) $it->harga_satuan;
                    $subtotal = $harga * $qty;

                    return [
                        'produk_id' => (int) $it->produk_id,
                        'nama' => $it->produk?->nama,
                        'foto' => $it->produk?->foto,
                        'qty' => $qty,
                        'harga_satuan' => $harga,
                        'subtotal' => $subtotal,
                    ];
                })->values(),
            ],
        ]);
    }
}
