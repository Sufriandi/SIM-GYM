<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\PaketMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * GET /api/membership/dashboard/{member}
     *
     * Return:
     *  - membership aktif (jika ada)
     *  - list paket membership
     */
    public function dashboard($memberId)
    {
        // Ambil membership aktif berdasarkan scope di model
        $aktif = Membership::with('paket')
            ->aktif()
            ->where('member_id', $memberId)
            ->latest('tanggal_mulai')
            ->first();

        $aktifData = null;

        if ($aktif) {
            $today = Carbon::today();
            $sisaHari = $today->diffInDays($aktif->tanggal_akhir, false);

            $aktifData = [
                'id'                => $aktif->id,
                'paket'             => [
                    'id'        => $aktif->paket->id,
                    'nama'      => $aktif->paket->nama,
                    'tipe'      => $aktif->paket->tipe,
                    'durasi'    => $aktif->paket->durasi,
                    'harga'     => $aktif->paket->harga,
                    'deskripsi' => $aktif->paket->deskripsi,
                    'harga_formatted' => 'Rp ' . number_format($aktif->paket->harga, 0, ',', '.'),
                ],
                'tanggal_transaksi' => $aktif->tanggal_transaksi?->toDateTimeString(),
                'tanggal_mulai'     => $aktif->tanggal_mulai?->toDateString(),   // yyyy-MM-dd
                'tanggal_akhir'     => $aktif->tanggal_akhir?->toDateString(),   // yyyy-MM-dd
                'metode_pembayaran' => $aktif->metode_pembayaran,
                'status'            => $aktif->status,        // accessor di model
                'sisa_hari'         => max($sisaHari, 0),
            ];
        }

        // Semua paket membership
        $paket = PaketMembership::orderBy('durasi')
            ->get()
            ->map(function ($p) {
                return [
                    'id'        => $p->id,
                    'nama'      => $p->nama,
                    'tipe'      => $p->tipe,
                    'durasi'    => $p->durasi,
                    'harga'     => $p->harga,
                    'deskripsi' => $p->deskripsi,
                    'harga_formatted' => 'Rp ' . number_format($p->harga, 0, ',', '.'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data membership dashboard',
            'data'    => [
                'aktif' => $aktifData,
                'paket' => $paket,
            ],
        ]);
    }

    /**
     * GET /api/membership/history/{member}
     *
     * Mengembalikan riwayat semua membership yang pernah dibeli member.
     *
     * Response:
     *  [
     *    {
     *      "id": 1,
     *      "nama_paket": "Paket 1 Bulan",
     *      "status": "aktif|berakhir|canceled|belum_aktif",
     *      "tanggal_beli": "2025-11-01",
     *      "tanggal_mulai": "2025-11-01",
     *      "tanggal_akhir": "2025-11-30",
     *      "metode_pembayaran": "Pembayaran Offline",
     *      "total_pembayaran": 250000,
     *      "total_pembayaran_formatted": "Rp 250.000"
     *    },
     *    ...
     *  ]
     */
    public function historyByMember($memberId)
    {
        $history = Membership::with('paket')
            ->where('member_id', $memberId)
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Membership $m) {
                // kalau tanggal_transaksi null, fallback ke created_at
                $tanggalTransaksi = $m->tanggal_transaksi ?? $m->created_at;

                $harga = optional($m->paket)->harga ?? 0;

                return [
                    'id'                       => $m->id,
                    'nama_paket'               => optional($m->paket)->nama,
                    'status'                   => $m->status, // gunakan accessor status di model
                    'tanggal_beli'             => $tanggalTransaksi?->toDateString(),   // yyyy-MM-dd
                    'tanggal_mulai'            => $m->tanggal_mulai?->toDateString(),
                    'tanggal_akhir'            => $m->tanggal_akhir?->toDateString(),
                    'metode_pembayaran'        => $m->metode_pembayaran,
                    'total_pembayaran'         => $harga,
                    'total_pembayaran_formatted' => 'Rp ' . number_format($harga, 0, ',', '.'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat membership member',
            'data'    => $history,
        ]);
    }
}
