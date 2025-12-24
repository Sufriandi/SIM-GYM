<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use Carbon\Carbon;
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

        // Riwayat tetap boleh dilihat meskipun membership sudah tidak aktif
        $kehadiran = KehadiranMember::with('absensiPeriode')
            ->where('member_id', $member->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->paginate(10);

        return view('member.kehadiran.index', compact('kehadiran'));
    }

    /**
     * Halaman yang dibuka setelah scan QR.
     * Route: member.absensi.scan (GET /member/absensi/scan/{token})
     */
    public function scan(string $token)
    {
        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mengakses fitur ini.');
        }

        $tanggalAbsen = now()->toDateString();

        // ===== GATE: membership harus aktif pada tanggal absen =====
        if (! $member->hasActiveMembershipOn($tanggalAbsen)) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Membership Anda tidak aktif. Silakan perpanjang membership terlebih dahulu sebelum melakukan absensi.');
        }

        if (! $token) {
            return view('member.kehadiran.scan_invalid');
        }

        // Cari periode aktif yang cocok dengan token + tanggal absen
        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $token)
            ->whereDate('tanggal_mulai', '<=', $tanggalAbsen)
            ->whereDate('tanggal_selesai', '>=', $tanggalAbsen)
            ->first();

        if (! $periodeAktif) {
            return view('member.kehadiran.scan_invalid', compact('token'));
        }

        // Cek apakah member sudah punya kehadiran apa pun di tanggal hari ini (1x per hari)
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
            ->whereDate('tanggal', $tanggalAbsen)
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
     * Route: member.absensi.store (POST /member/absensi/scan/{token})
     */
    public function store(Request $request, string $token)
    {
        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mencatat kehadiran.');
        }

        $tanggalAbsen = now()->toDateString();

        // ===== GATE: membership harus aktif pada tanggal absen =====
        if (! $member->hasActiveMembershipOn($tanggalAbsen)) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Membership Anda tidak aktif. Silakan perpanjang membership terlebih dahulu sebelum melakukan absensi.');
        }

        if (! $token) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'QR tidak valid.');
        }

        // Cari periode absensi aktif berdasarkan token + tanggal hari ini
        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $token)
            ->whereDate('tanggal_mulai', '<=', $tanggalAbsen)
            ->whereDate('tanggal_selesai', '>=', $tanggalAbsen)
            ->first();

        if (! $periodeAktif) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'QR tidak valid atau periode sudah tidak aktif.');
        }

        // Cegah dobel absen di hari yang sama (mode apa pun / periode apa pun)
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
            ->whereDate('tanggal', $tanggalAbsen)
            ->exists();

        if ($sudahAbsen) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Anda sudah mencatat kehadiran hari ini.');
        }

        KehadiranMember::create([
            'member_id'          => $member->id,
            'absensi_periode_id' => $periodeAktif->id,
            'tanggal'            => $tanggalAbsen,
            'jam_masuk'          => now()->format('H:i:s'),
            'ip_address'         => $request->ip(),
            'device_info'        => substr((string) $request->userAgent(), 0, 255),
            'is_valid'           => true,
        ]);

        return redirect()
            ->route('member.kehadiran.index')
            ->with('success', 'Kehadiran berhasil dicatat.');
    }
}
