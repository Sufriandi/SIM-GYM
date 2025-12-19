<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use App\Models\Member;
use Illuminate\Http\Request;

class KehadiranMemberController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'kode_qr'   => 'required|string',
            'device_info' => 'nullable|string',
            'ip_address'  => 'nullable|string|max:45'
        ]);

        // 1. CARI QR DI absensi_periodes (INI YANG DIPAKAI ADMIN)
        $periode = AbsensiPeriode::where('kode_qr', $request->kode_qr)
                    ->where('status', 'aktif')
                    ->first();

        if (!$periode) {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak ditemukan atau periode tidak aktif'
            ], 404);
        }

        // 2. CEK TANGGAL PERIODE MASIH VALID
        $today = now()->format('Y-m-d');

        //if ($today < $periode->tanggal_mulai || $today > $periode->tanggal_selesai) {
        //    return response()->json([
        //        'success' => false,
        //        'message' => 'Periode absensi tidak berlaku hari ini'
        //    ], 400);
        //}

        // 3. CEK ABSENSI HARI INI
        $existing = KehadiranMember::where('member_id', $request->member_id)
                    ->where('absensi_periode_id', $periode->id)
                    ->where('tanggal', $today)
                    ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini'
            ]);
        }

        // 4. SIMPAN ABSENSI
        $absen = KehadiranMember::create([
            'member_id' => $request->member_id,
            'absensi_periode_id' => $periode->id,
            'tanggal' => $today,
            'jam_masuk' => date('H:i:s'),
            'device_info' => $request->device_info,
            'ip_address' => $request->ip_address,
            'is_valid' => true
        ]);

        $member = Member::find($request->member_id);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'nama' => $member->nama ?? 'Member',
            'tanggal' => $today,
            'data' => $absen
        ]);
    }
}
