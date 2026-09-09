<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use App\Models\Member;
use App\Models\TransaksiMembership;
use App\Models\TransaksiMembershipMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;

class IzinLatihanController extends Controller
{
    /**
     * List izin latihan (untuk admin / history).
     * GET /api/izin-latihan
     */
    public function index(Request $request)
    {
        $query = IzinLatihan::query()->with('member');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $izin = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data izin latihan berhasil diambil',
            'data'    => $izin,
        ]);
    }

    /**
     * Riwayat izin latihan untuk 1 member (dipakai Android).
     * GET /api/izin-latihan/member/{member}
     */
    public function historyByMember($member)
    {
        $izinList = IzinLatihan::where('member_id', $member)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                    => $item->id,
                    'member_id'             => $item->member_id,
                    'tanggal_mulai'         => $item->tanggal_mulai,
                    'tanggal_selesai'       => $item->tanggal_selesai,
                    'jumlah_hari'           => $item->jumlah_hari,
                    'alasan'                => $item->alasan,
                    'status'                => $item->status,
                    'created_at'            => optional($item->created_at)->format('Y-m-d H:i:s'),
                    'bukti_url'             => $item->bukti_alasan
                        ? Storage::disk('public')->url($item->bukti_alasan)
                        : null,
                    'keterangan_admin'      => $item->keterangan_admin,
                    'durasi_izin_disetujui' => $item->durasi_izin_disetujui,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat izin latihan member',
            'data'    => $izinList,
        ]);
    }

    /**
     * Ajukan izin dari Android.
     * POST /api/izin-latihan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id'       => 'required|integer|exists:members,id',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'     => 'required|integer|min:1',
            'alasan'          => 'required|string',
            'bukti_alasan'    => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_alasan')) {
            $buktiPath = ImageUploadService::uploadOrConvertAsWebp(
                $request->file('bukti_alasan'),
                'izin_bukti',
                null,
                1600,
                85
            );
        }

        $izin = IzinLatihan::create([
            'member_id'       => $request->member_id,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari'     => $request->jumlah_hari,
            'alasan'          => $request->alasan,
            'bukti_alasan'    => $buktiPath,
            'status'          => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan izin latihan berhasil dikirim',
            'data'    => $izin,
        ], 201);
    }

    /**
     * Update status oleh admin via API.
     * PUT /api/izin-latihan/{id}/status
     *
     * IMPORTANT:
     * - Jika status = disetujui dan durasi_izin_disetujui > 0,
     *   maka buat transaksi membership kompensasi (agar muncul di history mobile).
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'                => 'required|in:pending,disetujui,ditolak',
            'durasi_izin_disetujui' => 'nullable|integer|min:0',
            'keterangan_admin'      => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $id) {

                $izin = IzinLatihan::whereKey($id)->lockForUpdate()->firstOrFail();

                // anti double proses
                if ($izin->status !== 'pending') {
                    return [
                        'code' => 409,
                        'payload' => [
                            'success' => false,
                            'message' => 'Izin sudah diproses sebelumnya.',
                            'data' => $izin,
                        ],
                    ];
                }

                $status = $request->status;

                // durasi disetujui:
                // - kalau tidak dikirim, default ke jumlah_hari (opsional; kamu bisa ubah jadi 0 jika mau)
                $approvedDays = $request->has('durasi_izin_disetujui')
                    ? (int) $request->durasi_izin_disetujui
                    : (int) ($izin->jumlah_hari ?? 0);

                // clamp biar aman (tidak melebihi permintaan)
                $max = (int) ($izin->jumlah_hari ?? 0);
                if ($approvedDays < 0) $approvedDays = 0;
                if ($max > 0 && $approvedDays > $max) $approvedDays = $max;

                $izin->status                = $status;
                $izin->durasi_izin_disetujui = ($status === 'disetujui') ? $approvedDays : null;
                $izin->keterangan_admin      = $request->keterangan_admin;
                $izin->tanggal_persetujuan   = Carbon::now();
                $izin->save();

                // jika disetujui + hari > 0 => buat kompensasi
                if ($status === 'disetujui' && $approvedDays > 0) {

                    $memberId = (int) $izin->member_id;

                    // Gate: membership aktif di tanggal mulai izin (opsi A seperti admin)
                    $izinStart = Carbon::parse($izin->tanggal_mulai)->startOfDay();
                    if (! $this->isMembershipActiveAtDate($memberId, $izinStart)) {
                        return [
                            'code' => 422,
                            'payload' => [
                                'success' => false,
                                'message' => 'Membership member tidak aktif pada tanggal mulai izin. Kompensasi tidak dibuat.',
                                'data' => $izin,
                            ],
                        ];
                    }

                    // idempotent: jangan buat dobel jika sudah ada trx kompensasi untuk izin ini
                    $exists = TransaksiMembership::query()
                        ->where('buyer_member_id', $memberId)
                        ->where('jenis_transaksi', TransaksiMembership::JENIS_KOMPENSASI)
                        ->where('keterangan', 'like', "%izin_id={$izin->id}%")
                        ->exists();

                    if (! $exists) {
                        $lastPaidTx = $this->getLastPaidTransactionForMember($memberId);
                        if (! $lastPaidTx) {
                            return [
                                'code' => 422,
                                'payload' => [
                                    'success' => false,
                                    'message' => 'Member belum pernah transaksi membership (pembayaran). Kompensasi tidak bisa dibuat.',
                                    'data' => $izin,
                                ],
                            ];
                        }

                        [$mulaiKomp, $akhirKomp] = $this->buildKompensasiPeriodIndividu($memberId, $approvedDays);

                        $trx = TransaksiMembership::create([
                            'buyer_member_id'   => $memberId,
                            'created_by'        => null, // via API, bisa null (atau set user admin kalau ada auth)
                            'paket_id'          => $lastPaidTx->paket_id,
                            'tanggal_transaksi' => now(),
                            'tanggal_mulai'     => $mulaiKomp,
                            'tanggal_akhir'     => $akhirKomp,
                            'jenis_transaksi'   => TransaksiMembership::JENIS_KOMPENSASI,
                            'metode_pembayaran' => null,
                            'keterangan'        => $request->keterangan_admin
                                ? "Kompensasi izin (izin_id={$izin->id}). {$request->keterangan_admin}"
                                : "Kompensasi izin (izin_id={$izin->id}) {$approvedDays} hari.",
                            'canceled_at'       => null,
                        ]);

                        TransaksiMembershipMember::create([
                            'transaksi_membership_id' => $trx->id,
                            'member_id'               => $memberId,
                            'role'                    => 'primary',
                            // IMPORTANT: isi tanggal supaya konsisten pivot-based
                            'tanggal_mulai'           => $mulaiKomp,
                            'tanggal_akhir'           => $akhirKomp,
                        ]);
                    }
                }

                return [
                    'code' => 200,
                    'payload' => [
                        'success' => true,
                        'message' => 'Status izin latihan berhasil diperbarui',
                        'data'    => $izin,
                    ],
                ];
            });

            return response()->json($result['payload'], $result['code']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses status izin.',
            ], 500);
        }
    }

    /**
     * TRANSAKSI PEMBAYARAN terakhir yang relevan untuk individu:
     * - melibatkan member sebagai buyer ATAU participant
     */
    private function getLastPaidTransactionForMember(int $memberId): ?TransaksiMembership
    {
        return TransaksiMembership::query()
            ->valid()
            ->pembayaran()
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->orderByDesc('tanggal_akhir')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Cek membership aktif pada tanggal tertentu.
     */
    private function isMembershipActiveAtDate(int $memberId, Carbon $date): bool
    {
        $d = $date->toDateString();

        return TransaksiMembership::query()
            ->valid()
            ->whereDate('tanggal_mulai', '<=', $d)
            ->whereDate('tanggal_akhir', '>=', $d)
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->exists();
    }

    /**
     * Periode kompensasi untuk INDIVIDU (inklusif):
     * start = (end_terakhir >= today) ? end_terakhir + 1 : today
     * end   = start + (days - 1)
     */
    private function buildKompensasiPeriodIndividu(int $memberId, int $days): array
    {
        $today = now()->startOfDay();

        $lastEnd = TransaksiMembership::query()
            ->valid()
            ->where(function ($w) use ($memberId) {
                $w->where('buyer_member_id', $memberId)
                    ->orWhereHas('participants', fn($p) => $p->where('member_id', $memberId));
            })
            ->max('tanggal_akhir');

        $start = $today->copy();

        if ($lastEnd) {
            $end = Carbon::parse($lastEnd)->startOfDay();
            $start = $end->gte($today) ? $end->copy()->addDay() : $today->copy();
        }

        $finish = $start->copy()->addDays($days - 1);

        return [$start->toDateString(), $finish->toDateString()];
    }
}
