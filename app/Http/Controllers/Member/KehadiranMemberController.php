<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KehadiranMemberController extends Controller
{
    /**
     * Halaman riwayat kehadiran member.
     * Route: member.kehadiran.index (GET /member/kehadiran)
     */
    public function index()
    {
        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mengakses fitur ini.');
        }

        // GUNAKAN NAMA VARIABEL: $kehadiran
        $kehadiran = KehadiranMember::with('absensiPeriode')
            ->where('member_id', $member->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->paginate(10);

        return view('member.kehadiran.index', compact('kehadiran'));
    }

    /**
     * Halaman yang dibuka setelah scan QR.
     * Route: member.absensi.scan (GET /member/absensi/scan?token=xxx)
     */
    public function scan(Request $request)
    {
        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mengakses fitur ini.');
        }

        $token = $request->query('token');

        if (! $token) {
            // Token tidak ada / QR tidak valid
            return view('member.kehadiran.scan_invalid');
        }

        $today = now()->toDateString();

        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $token)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        if (! $periodeAktif) {
            // Periode tidak ditemukan atau sudah tidak aktif
            return view('member.kehadiran.scan_invalid', compact('token'));
        }

        // Cek apakah member sudah absen pada periode + hari ini
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
            ->where('absensi_periode_id', $periodeAktif->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAbsen) {
            return view('member.kehadiran.scan_sudah', compact('periodeAktif'));
        }

        // Tampilkan halaman konfirmasi
        return view('member.kehadiran.scan', [
            'periodeAktif' => $periodeAktif,
            'token'        => $token,
        ]);
    }

    /**
     * Simpan kehadiran setelah konfirmasi.
     * Route: member.absensi.store (POST /member/absensi)
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mencatat kehadiran.');
        }

        $today   = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        // Cari periode absensi aktif berdasarkan token
        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $request->input('token'))
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->firstOrFail();

        // Cegah absen dobel di hari yang sama dalam periode yang sama
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
            ->where('absensi_periode_id', $periodeAktif->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAbsen) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Anda sudah mencatat kehadiran pada periode ini.');
        }

        // Simpan kehadiran
        KehadiranMember::create([
            'member_id'          => $member->id,
            'absensi_periode_id' => $periodeAktif->id,
            'tanggal'            => $today,
            'jam_masuk'          => $nowTime,
            'ip_address'         => $request->ip(),
            'device_info'        => $request->userAgent(), // MASUK ke kolom device_info
        ]);

        return redirect()
            ->route('member.kehadiran.index')
            ->with('success', 'Kehadiran berhasil dicatat.');
    }
}
