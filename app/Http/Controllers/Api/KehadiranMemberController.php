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
     */
    public function scan(Request $request)
    {
        $request->validate([
            'member_id'    => 'required|exists:members,id',
            'kode_qr'      => 'required|string',
            'device_info'  => 'nullable|string',
            'ip_address'   => 'nullable|string|max:45'
        ]);

        // 1) cari periode aktif berdasarkan kode QR
        $periode = AbsensiPeriode::query()
            ->where('kode_qr', $request->kode_qr)
            ->where('status', 'aktif')
            ->first();

        if (! $periode) {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak ditemukan atau periode tidak aktif'
            ], 404);
        }

        // 2) validasi tanggal periode (saran: aktifkan lagi supaya tidak bisa scan QR lama)
        $today = now()->toDateString();

        if ($today < $periode->tanggal_mulai || $today > $periode->tanggal_selesai) {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak ditemukan atau periode tidak aktif'
            ], 404);
        }

        // 3) cegah dobel absen: 1x per hari (lebih aman daripada per-periode)
        $already = KehadiranMember::query()
            ->where('member_id', $request->member_id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($already) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini'
            ], 409);
        }

        // 4) simpan kehadiran
        $absen = KehadiranMember::create([
            'member_id'          => $request->member_id,
            'absensi_periode_id' => $periode->id,
            'tanggal'            => $today,
            'jam_masuk'          => now()->format('H:i:s'),
            'device_info'        => $request->device_info,
            'ip_address'         => $request->ip_address,
            'is_valid'           => true
        ]);

        $member = Member::find($request->member_id);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'nama'    => $member->nama ?? 'Member',
            'tanggal' => $today,
            'data'    => [
                'id' => $absen->id,
                'tanggal' => $today,
                'jam_masuk' => $absen->jam_masuk,
                'absensi_periode_id' => $absen->absensi_periode_id,
                'is_valid' => (bool) $absen->is_valid,
            ]
        ]);
    }

    /**
     * GET /api/absen/member/{id}
     * (alias juga dipakai oleh /api/kehadiran/member/{id} bila Anda menambahkan route alias)
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
                    'id'                => $item->id,
                    'member_id'         => $item->member_id,
                    'tanggal'           => optional($item->tanggal)->format('Y-m-d'),
                    'jam_masuk'         => $item->jam_masuk,
                    'jam_keluar'        => $item->jam_keluar,
                    'absensi_periode_id'=> $item->absensi_periode_id,
                    'is_valid'          => (bool) $item->is_valid,
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
