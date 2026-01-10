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
     *  - membership aktif untuk member (berdasarkan pivot transaksi_membership_members)
     *  - list paket membership (PUBLIC ONLY)
     */
    public function dashboard($memberId)
    {
        $memberId = (int) $memberId;
        $today = Carbon::today();

        $aktif = TransaksiMembership::query()
            ->with([
                'paket' => fn($q) => $q->withTrashed(),
                'participants' => fn($q) => $q->where('member_id', $memberId),
            ])
            ->notCanceled()
            ->whereHas('participants', function ($p) use ($memberId, $today) {
                $p->where('member_id', $memberId)
                    ->whereDate('tanggal_mulai', '<=', $today)
                    ->whereDate('tanggal_akhir', '>=', $today);
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->first();

        $aktifData = null;

        if ($aktif) {
            $pivot = $aktif->participants->first();

            $mulai = $pivot?->tanggal_mulai
                ? Carbon::parse($pivot->tanggal_mulai)
                : ($aktif->tanggal_mulai ? Carbon::parse($aktif->tanggal_mulai) : null);

            $akhir = $pivot?->tanggal_akhir
                ? Carbon::parse($pivot->tanggal_akhir)
                : ($aktif->tanggal_akhir ? Carbon::parse($aktif->tanggal_akhir) : null);

            $sisaHari = 0;
            if ($akhir) {
                $sisaHari = max($today->diffInDays($akhir, false), 0);
            }

            $paket = $aktif->paket;

            // =========================
            // PENTING: harga untuk transaksi pakai snapshot `transaksi_memberships.total`
            // fallback ke harga paket jika record lama belum punya total
            // kompensasi total memang 0
            // =========================
            $jenis = (string) ($aktif->jenis_transaksi ?? '');
            $hargaTransaksi = ($jenis === TransaksiMembership::JENIS_KOMPENSASI || $jenis === 'kompensasi')
                ? 0
                : (int) ($aktif->total ?? ($paket?->harga ?? 0));

            $aktifData = [
                'id' => $aktif->id,
                'no_nota' => $aktif->no_nota ?? '-',

                'paket' => $paket ? [
                    'id'        => $paket->id,
                    'nama'      => $paket->nama,
                    'tipe'      => $paket->tipe,
                    'durasi'    => $paket->durasi,
                    'deskripsi' => $paket->deskripsi,

                    // harga yang ditampilkan untuk membership aktif = snapshot transaksi
                    'harga'           => $hargaTransaksi,
                    'harga_formatted' => 'Rp ' . number_format($hargaTransaksi, 0, ',', '.'),

                    // opsional: kalau mau tetap tahu harga paket terbaru (biar admin update tidak “mengubah history”)
                    'harga_paket_terkini'           => (int) ($paket->harga ?? 0),
                    'harga_paket_terkini_formatted' => 'Rp ' . number_format((int) ($paket->harga ?? 0), 0, ',', '.'),
                ] : null,

                'tanggal_transaksi' => $aktif->tanggal_transaksi?->toDateTimeString(),
                'tanggal_mulai'     => $mulai?->toDateString(),
                'tanggal_akhir'     => $akhir?->toDateString(),
                'metode_pembayaran' => $aktif->metode_pembayaran,
                'jenis_transaksi'   => $aktif->jenis_transaksi,
                'total'             => (int) ($aktif->total ?? $hargaTransaksi),

                'status'    => $this->statusPerMember($aktif->canceled_at, $mulai, $akhir, $today),
                'sisa_hari'  => $sisaHari,
            ];
        }

        // List paket untuk mobile (public only) tetap ambil harga terkini
        $paketList = PaketMembership::query()
            ->where('is_public', true)
            ->orderBy('tipe')
            ->orderBy('durasi')
            ->get()
            ->map(function ($p) {
                $harga = (int) ($p->harga ?? 0);

                return [
                    'id'              => $p->id,
                    'nama'            => $p->nama,
                    'tipe'            => $p->tipe,
                    'durasi'          => $p->durasi,
                    'harga'           => $harga,
                    'deskripsi'       => $p->deskripsi,
                    'harga_formatted' => 'Rp ' . number_format($harga, 0, ',', '.'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data membership dashboard',
            'data' => [
                'aktif' => $aktifData,
                'paket' => $paketList,
            ],
        ]);
    }

    /**
     * GET /api/membership/history/{member}
     *
     * Riwayat transaksi membership untuk member ini,
     * tanggal_mulai/akhir & status dihitung berdasarkan pivot per member.
     * TOTAL pembayaran harus pakai snapshot transaksi (transaksi_memberships.total),
     * bukan harga paket terkini.
     */
    public function historyByMember($memberId)
    {
        $memberId = (int) $memberId;
        $today = Carbon::today();

        $history = TransaksiMembership::with([
                'paket' => fn($q) => $q->withTrashed(),
                'participants' => function ($q) use ($memberId) {
                    $q->where('member_id', $memberId);
                },
            ])
            ->where(function ($q) use ($memberId) {
                $q->where('buyer_member_id', $memberId)
                  ->orWhereHas('participants', function ($p) use ($memberId) {
                      $p->where('member_id', $memberId);
                  });
            })
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->get()
            ->map(function (TransaksiMembership $t) use ($today) {

                $tanggalTransaksi = $t->tanggal_transaksi ?? $t->created_at;

                $pivot = $t->participants->first();

                $mulai = null;
                $akhir = null;

                if ($pivot && !empty($pivot->tanggal_mulai)) {
                    $mulai = Carbon::parse($pivot->tanggal_mulai)->startOfDay();
                } elseif (!empty($t->tanggal_mulai)) {
                    $mulai = Carbon::parse($t->tanggal_mulai)->startOfDay();
                }

                if ($pivot && !empty($pivot->tanggal_akhir)) {
                    $akhir = Carbon::parse($pivot->tanggal_akhir)->startOfDay();
                } elseif (!empty($t->tanggal_akhir)) {
                    $akhir = Carbon::parse($t->tanggal_akhir)->startOfDay();
                }

                // status per transaksi (per member)
                if (!empty($t->canceled_at)) {
                    $status = 'canceled';
                } elseif ($mulai && $today->lt($mulai)) {
                    $status = 'belum_aktif';
                } elseif ($akhir && $today->gt($akhir)) {
                    $status = 'expired';
                } elseif ($mulai && $akhir && $today->betweenIncluded($mulai, $akhir)) {
                    $status = 'aktif';
                } else {
                    $status = $t->status ?? 'unknown';
                }

                $paket = $t->paket;

                $jenis = (string) ($t->jenis_transaksi ?? '');

                // =========================
                // PENTING: TOTAL pembayaran pakai snapshot transaksi `t.total`
                // fallback aman untuk data lama yang belum punya total
                // kompensasi = 0
                // =========================
                $fallbackHargaPaket = (int) ($paket?->harga ?? 0);

                $total = ($jenis === TransaksiMembership::JENIS_KOMPENSASI || $jenis === 'kompensasi')
                    ? 0
                    : (int) ($t->total ?? $fallbackHargaPaket);

                return [
                    'id'              => $t->id,
                    'no_nota'         => $t->no_nota ?? '-',
                    'jenis_transaksi' => $jenis,

                    'nama_paket'      => $paket?->nama ?? (($jenis === 'kompensasi') ? 'Kompensasi' : '-'),
                    'status'          => $status,

                    'tanggal_beli'    => $tanggalTransaksi?->toDateString(),
                    'tanggal_mulai'   => $mulai?->toDateString(),
                    'tanggal_akhir'   => $akhir?->toDateString(),

                    'metode_pembayaran'          => $t->metode_pembayaran,
                    'total_pembayaran'           => $total,
                    'total_pembayaran_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat membership member',
            'data'    => $history,
        ]);
    }

    private function statusPerMember($canceledAt, ?Carbon $mulai, ?Carbon $akhir, Carbon $today): string
    {
        if (!empty($canceledAt)) return 'canceled';
        if (!$mulai || !$akhir) return 'unknown';

        if ($today->lt($mulai->copy()->startOfDay())) return 'belum_aktif';
        if ($today->gt($akhir->copy()->startOfDay())) return 'expired';

        return 'aktif';
    }
}
