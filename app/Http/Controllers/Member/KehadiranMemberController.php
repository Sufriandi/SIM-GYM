<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KehadiranMemberController extends Controller
{
    /**
     * Helper: cek apakah membership member sedang aktif hari ini.
     */
    protected function hasActiveMembership($member): bool
    {
        if (! $member->tanggal_mulai || ! $member->tanggal_akhir) {
            return false;
        }

        $today = Carbon::today();
        $mulai = Carbon::parse($member->tanggal_mulai)->startOfDay();
        $akhir = Carbon::parse($member->tanggal_akhir)->endOfDay();

        return $today->betweenIncluded($mulai, $akhir);
    }

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
     * Route: member.absensi.scan (GET /member/absensi/scan?token=xxx)
     *
     * Skenario A:
     * - Cek "sudah absen" berdasarkan member + tanggal saja,
     *   supaya 1 hari hanya boleh 1 kali absen, meskipun QR / periode berbeda.
     */
    public function scan(Request $request)
    {
        $user   = Auth::user();
        $member = $user->member ?? null;

        if (! $member) {
            abort(403, 'Hanya akun yang terhubung dengan data member yang dapat mengakses fitur ini.');
        }

        // ===== BATASAN MEMBERSHIP AKTIF =====
        if (! $this->hasActiveMembership($member)) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Membership Anda tidak aktif. Silakan perpanjang membership terlebih dahulu sebelum melakukan absensi.');
        }

        $token = $request->query('token');

        if (! $token) {
            // Token tidak ada / QR tidak valid
            return view('member.kehadiran.scan_invalid');
        }

        $today = now()->toDateString();

        // Cari periode aktif yang cocok dengan token + tanggal hari ini
        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $token)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        if (! $periodeAktif) {
            // Periode tidak ditemukan atau sudah tidak aktif
            return view('member.kehadiran.scan_invalid', compact('token'));
        }

        // Cek apakah member sudah punya kehadiran apa pun di tanggal hari ini
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
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
     *
     * Skenario A:
     * - Tetap cek "sudah absen" berdasarkan member + tanggal saja
     *   untuk menghindari race condition / double submit.
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

        // ===== BATASAN MEMBERSHIP AKTIF (lagi) =====
        // Supaya kalau user skip halaman scan dan langsung kirim POST,
        // tetap tertahan di sini.
        if (! $this->hasActiveMembership($member)) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Membership Anda tidak aktif. Silakan perpanjang membership terlebih dahulu sebelum melakukan absensi.');
        }

        $today   = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        // Cari periode absensi aktif berdasarkan token + tanggal hari ini
        $periodeAktif = AbsensiPeriode::aktif()
            ->where('kode_qr', $request->input('token'))
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->firstOrFail();

        // Cegah absen dobel di hari yang sama (mode apa pun / periode apa pun)
        $sudahAbsen = KehadiranMember::where('member_id', $member->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAbsen) {
            return redirect()
                ->route('member.kehadiran.index')
                ->with('error', 'Anda sudah mencatat kehadiran hari ini.');
        }

        // Simpan kehadiran. absensi_periode_id tetap diisi
        KehadiranMember::create([
            'member_id'          => $member->id,
            'absensi_periode_id' => $periodeAktif->id,
            'tanggal'            => $today,
            'jam_masuk'          => $nowTime,
            'ip_address'         => $request->ip(),
            'device_info'        => $request->userAgent(),
            // 'is_valid'        => true, // bisa diaktifkan kalau mau
        ]);

        return redirect()
            ->route('member.kehadiran.index')
            ->with('success', 'Kehadiran berhasil dicatat.');
    }
}
