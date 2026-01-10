<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use App\Models\Member;
use Illuminate\Http\Request;

class KehadiranMemberController extends Controller
{
    /**
     * POST /api/absen/scan
     *
     * Body:
     * - member_id (required jika tidak pakai Sanctum)
     * - kode_qr (required)
     * - device_info (optional)
     * - ip_address (optional)
     */
    public function scan(Request $request)
    {
        $request->validate([
            'member_id'   => ['nullable', 'integer', 'exists:members,id'],
            'kode_qr'     => ['required', 'string'],
            'device_info' => ['nullable', 'string'],
            'ip_address'  => ['nullable', 'string', 'max:45'],
        ]);

        $today = now()->toDateString();
        $kode  = trim((string) $request->kode_qr);

        // =========================
        // 1) Resolve member (samakan web)
        // =========================
        // Jika suatu saat endpoint ini dipindah ke auth:sanctum,
        // otomatis pakai member dari user login.
        $authUser = $request->user();
        $member = null;

        if ($authUser && $authUser->member) {
            $member = $authUser->member;
        } else {
            // fallback: pakai member_id yang dikirim Android
            $mid = (int) $request->member_id;
            if ($mid > 0) {
                $member = Member::find($mid);
            }
        }

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan atau belum terhubung ke akun.',
            ], 403);
        }

        // =========================
        // 2) Gate membership harus aktif (SAMA persis seperti Web)
        // =========================
        if (method_exists($member, 'hasActiveMembershipOn')) {
            if (! $member->hasActiveMembershipOn($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Membership Anda tidak aktif. Silakan perpanjang membership terlebih dahulu sebelum melakukan absensi.',
                ], 403);
            }
        }

        // =========================
        // 3) Cari periode aktif yang cocok dengan token + tanggal hari ini
        //    (SAMAKAN dengan Web: aktif + range tanggal)
        // =========================
        $periode = AbsensiPeriode::query()
            // jika kamu punya scope aktif() di model AbsensiPeriode, pakai ini:
            ->when(method_exists(AbsensiPeriode::class, 'scopeAktif'), function ($q) {
                return $q->aktif();
            }, function ($q) {
                // fallback kalau scope aktif() tidak ada
                return $q->where('status', 'aktif');
            })
            ->where('kode_qr', $kode)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->orderByDesc('id') // PENTING: ambil yang terbaru jika ada duplikat token
            ->first();

        if (! $periode) {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak valid atau periode sudah tidak aktif.',
            ], 404);
        }

        // =========================
        // 4) Cegah dobel absen (1x per hari) - sama dengan Web
        // =========================
        $already = KehadiranMember::query()
            ->where('member_id', $member->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($already) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini',
            ], 409);
        }

        // =========================
        // 5) Simpan kehadiran
        // =========================
        $deviceInfo = $request->device_info;
        if (empty($deviceInfo)) {
            // fallback: pakai user agent
            $deviceInfo = substr((string) $request->userAgent(), 0, 255);
        } else {
            $deviceInfo = substr((string) $deviceInfo, 0, 255);
        }

        $ip = $request->ip_address ?: $request->ip();

        $absen = KehadiranMember::create([
            'member_id'          => $member->id,
            'absensi_periode_id' => $periode->id,
            'tanggal'            => $today,
            'jam_masuk'          => now()->format('H:i:s'),
            'device_info'        => $deviceInfo,
            'ip_address'         => $ip,
            'is_valid'           => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'nama'    => $member->nama ?? 'Member',
            'tanggal' => $today,
            'periode' => [
                'id'             => $periode->id,
                'tipe_periode'   => $periode->tipe_periode,     // harian|mingguan|bulanan
                'tanggal_mulai'  => $periode->tanggal_mulai,
                'tanggal_selesai'=> $periode->tanggal_selesai,
            ],
            'data' => [
                'id'                 => $absen->id,
                'tanggal'            => $today,
                'jam_masuk'          => $absen->jam_masuk,
                'absensi_periode_id' => $absen->absensi_periode_id,
                'is_valid'           => (bool) $absen->is_valid,
            ],
        ], 200);
    }

    /**
     * GET /api/absen/member/{id}
     */
    public function historyMember($id)
    {
        $member = Member::find($id);
        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'Member tidak ditemukan',
                'data'    => [],
            ], 404);
        }

        $list = KehadiranMember::query()
            ->where('member_id', $id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->limit(200)
            ->get()
            ->map(function ($item) {
                return [
                    'id'                 => $item->id,
                    'member_id'          => $item->member_id,
                    'tanggal'            => optional($item->tanggal)->format('Y-m-d'),
                    'jam_masuk'          => $item->jam_masuk,
                    'jam_keluar'         => $item->jam_keluar,
                    'absensi_periode_id' => $item->absensi_periode_id,
                    'is_valid'           => (bool) $item->is_valid,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat kehadiran member',
            'data'    => $list,
        ]);
    }

    /**
     * GET /api/absen/sesi/{id}
     */
    public function listBySesi($id)
    {
        $list = KehadiranMember::query()
            ->with(['member'])
            ->where('absensi_periode_id', $id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kehadiran per sesi/periode',
            'data'    => $list,
        ]);
    }
}
