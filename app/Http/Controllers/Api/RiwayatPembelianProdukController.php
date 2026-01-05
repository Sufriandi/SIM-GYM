<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiProduk;
use Illuminate\Http\Request;

class RiwayatPembelianProdukController extends Controller
{
    /**
     * GET /api/transaksi-produk/member/{member}
     * Riwayat pembelian produk berdasarkan buyer_member_id.
     */
    public function historyByMember($memberId)
    {
        $rows = TransaksiProduk::query()
            ->where('buyer_member_id', $memberId)
            ->withCount('items')
            ->with([
                // Eager load items + produk supaya bisa ambil nama produk pertama untuk title.
                'items.produk'
            ])
            ->orderByDesc('tanggal_transaksi')
            ->get()
            ->map(function ($t) {
                $firstItem = $t->items->first();
                $produkNama = null;

                if ($firstItem && $firstItem->produk) {
                    // Aman: coba beberapa kemungkinan nama field produk
                    $produkNama = $firstItem->produk->nama
                        ?? $firstItem->produk->nama_produk
                        ?? $firstItem->produk->name
                        ?? null;
                }

                return [
                    'id' => $t->id,
                    'no_nota' => $t->no_nota,
                    'tanggal_transaksi' => optional($t->tanggal_transaksi)->format('Y-m-d H:i:s'),
                    'item_count' => (int) $t->items_count,
                    'total' => (int) $t->total,
                    'metode_pembayaran' => $t->metode_pembayaran, // cash/transfer/qris
                    // Karena tabel Anda tidak punya kolom status, kita set default "Selesai"
                    'status' => 'Selesai',
                    'title' => $produkNama ?: ('Transaksi ' . $t->no_nota),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * (Opsional) GET /api/transaksi-produk/{id}
     * Detail transaksi untuk halaman detail (jika nanti Anda butuh).
     */
    public function show($id)
    {
        $t = TransaksiProduk::query()
            ->with(['items.produk'])
            ->find($id);

        if (!$t) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.'
            ], 404);
        }

        $items = $t->items->map(function ($it) {
            $namaProduk = $it->produk->nama
                ?? $it->produk->nama_produk
                ?? $it->produk->name
                ?? 'Produk';

            return [
                'id' => $it->id,
                'produk_id' => $it->produk_id,
                'nama_produk' => $namaProduk,
                'qty' => (int) $it->qty,
                'harga_satuan' => (int) $it->harga_satuan,
                'subtotal' => (int) ($it->qty * $it->harga_satuan),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $t->id,
                'no_nota' => $t->no_nota,
                'tanggal_transaksi' => optional($t->tanggal_transaksi)->format('Y-m-d H:i:s'),
                'buyer_member_id' => $t->buyer_member_id,
                'metode_pembayaran' => $t->metode_pembayaran,
                'total' => (int) $t->total,
                'keterangan' => $t->keterangan,
                'items' => $items,
            ]
        ]);
    }
}
