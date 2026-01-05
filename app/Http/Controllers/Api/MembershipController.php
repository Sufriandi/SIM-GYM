<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaketMembership;
use App\Models\TransaksiMembership;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * GET /api/membership/dashboard/{member}
     *
     * Return:
     *  - membership aktif (berdasarkan transaksi_memberships + participants)
     *  - list paket membership
     */
    public function dashboard($memberId)
    {
        $today = Carbon::today();

        // Query transaksi untuk member:
        // - dia buyer_member_id
        // - atau jadi participant di transaksi_membership_members
        $baseQuery = TransaksiMembership::with('paket')
            ->where(function ($q) use ($memberId) {
                $q->where('buyer_member_id', $memberId)
                  ->orWhereHas('participants', function ($p) use ($memberId) {
                      $p->where('member_id', $memberId);
                  });
            });

        // Transaksi yang sedang aktif hari ini (not canceled, periode mencakup hari ini)
        $aktif = (clone $baseQuery)
            ->notCanceled()
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_akhir', '>=', $today)
            ->orderByDesc('tanggal_mulai')
            ->first();

        $aktifData = null;

        if ($aktif) {
            $sisaHari = $today->diffInDays($aktif->tanggal_akhir, false);

            $paket = $aktif->paket;

            $aktifData = [
                'id' => $aktif->id,
                'paket' => $paket ? [
                    'id'              => $paket->id,
                    'nama'            => $paket->nama,
                    'tipe'            => $paket->tipe,
                    'durasi'          => $paket->durasi,
                    'harga'           => $paket->harga,
                    'deskripsi'       => $paket->deskripsi,
                    'harga_formatted' => 'Rp ' . number_format($paket->harga, 0, ',', '.'),
                ] : null,
                'tanggal_transaksi' => $aktif->tanggal_transaksi?->toDateTimeString(),
                'tanggal_mulai'     => $aktif->tanggal_mulai?->toDateString(),
                'tanggal_akhir'     => $aktif->tanggal_akhir?->toDateString(),
                'metode_pembayaran' => $aktif->metode_pembayaran,
                'status'            => $aktif->status, // accessor di TransaksiMembership
                'sisa_hari'         => max($sisaHari, 0),
            ];
        }

        // Semua paket membership
        $paket = PaketMembership::orderBy('durasi')
            ->get()
            ->map(function ($p) {
                return [
                    'id'              => $p->id,
                    'nama'            => $p->nama,
                    'tipe'            => $p->tipe,
                    'durasi'          => $p->durasi,
                    'harga'           => $p->harga,
                    'deskripsi'       => $p->deskripsi,
                    'harga_formatted' => 'Rp ' . number_format($p->harga, 0, ',', '.'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data membership dashboard',
            'data' => [
                'aktif' => $aktifData,
                'paket' => $paket,
            ],
        ]);
    }

    /**
     * GET /api/membership/history/{member}
     *
     * Riwayat transaksi membership berdasarkan transaksi_memberships.
     */
    public function historyByMember($memberId)
    {
        $history = TransaksiMembership::with('paket')
            ->where(function ($q) use ($memberId) {
                $q->where('buyer_member_id', $memberId)
                  ->orWhereHas('participants', function ($p) use ($memberId) {
                      $p->where('member_id', $memberId);
                  });
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TransaksiMembership $t) {
                $tanggalTransaksi = $t->tanggal_transaksi ?? $t->created_at;
                $harga = optional($t->paket)->harga ?? 0;

                return [
                    'id'                         => $t->id,
                    'nama_paket'                 => optional($t->paket)->nama,
                    'status'                     => $t->status, // aktif|belum_aktif|expired|canceled
                    'tanggal_beli'               => $tanggalTransaksi?->toDateString(),
                    'tanggal_mulai'              => $t->tanggal_mulai?->toDateString(),
                    'tanggal_akhir'              => $t->tanggal_akhir?->toDateString(),
                    'metode_pembayaran'          => $t->metode_pembayaran,
                    'total_pembayaran'           => $harga,
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
