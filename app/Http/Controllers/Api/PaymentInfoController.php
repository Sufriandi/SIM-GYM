<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InfoQris;
use App\Models\InfoRekening;

class PaymentInfoController extends Controller
{
    /**
     * GET /api/payment-info
     *
     * Return:
     *  - list rekening
     *  - list qris (bukan hanya terbaru)
     */
    public function index()
    {
        $rekening = InfoRekening::query()
            ->orderBy('nama_bank')
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'nama_bank'      => $r->nama_bank,
                'nomor_rekening' => $r->nomor_rekening,
                'nama_pemilik'   => $r->nama_pemilik,
            ]);

        // FIX: ambil SEMUA QRIS (urutan terbaru di atas, bisa Anda ubah)
        $qrisList = InfoQris::query()
            ->orderByDesc('id')
            ->get()
            ->map(fn($q) => [
                'id'          => $q->id,
                'nama_qris'   => $q->nama_qris,
                'path_gambar' => $q->path_gambar,
                'keterangan'  => $q->keterangan,
                'image_url'   => $q->path_gambar
                    ? asset('storage/' . ltrim($q->path_gambar, '/'))
                    : null,
            ])
            ->values(); // memastikan array index rapi (0..n-1)

        return response()->json([
            'success' => true,
            'message' => 'Info pembayaran',
            'data' => [
                'rekening' => $rekening,
                // DULU: 'qris' => $qrisData (object/null)
                // SEKARANG: 'qris' => [] (array)
                'qris'     => $qrisList,
            ],
        ]);
    }
}
