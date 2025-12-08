<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KehadiranMemberController extends Controller
{
    /**
     * Ambil atau buat periode absensi aktif yang mencakup hari ini.
     * Mode bisa dipaksa dengan parameter (harian/mingguan/bulanan).
     */
    protected function getOrCreateActivePeriodeForToday(?string $modeOverride = null): AbsensiPeriode
    {
        $today = now()->toDateString();

        // Mode dari parameter (kalau ada) atau dari config
        $mode = $modeOverride ?: config('absensi.mode', 'harian');

        if (! in_array($mode, ['harian', 'mingguan', 'bulanan'], true)) {
            $mode = 'harian';
        }

        // Cari periode aktif dengan tipe yg diminta dan mencakup hari ini
        $periode = AbsensiPeriode::aktif()
            ->where('tipe_periode', $mode)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        if ($periode) {
            return $periode;
        }

        // Belum ada -> buat baru sesuai mode
        $start = now()->copy();
        $end   = now()->copy();

        switch ($mode) {
            case 'mingguan':
                $start = now()->startOfWeek();   // Senin
                $end   = now()->endOfWeek();     // Minggu
                break;

            case 'bulanan':
                $start = now()->startOfMonth();
                $end   = now()->endOfMonth();
                break;

            case 'harian':
            default:
                // start & end = hari ini
                $start = now()->copy();
                $end   = now()->copy();
                $mode  = 'harian';
                break;
        }

        return AbsensiPeriode::create([
            'tipe_periode'    => $mode,
            'tanggal_mulai'   => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'kode_qr'         => Str::random(40),
            'status'          => 'aktif',
            'created_by'      => auth()->id(),
        ]);
    }

    /**
     * Halaman admin: QR aktif + daftar kehadiran dalam periode tersebut.
     * Route: admin.absensi.kehadiran.index
     */
    public function index(Request $request)
    {
        // mode dipilih via tombol di UI: harian / mingguan / bulanan
        $requestedMode = $request->get('mode');

        $periodeAktif = $this->getOrCreateActivePeriodeForToday($requestedMode);

        // Query kehadiran dalam periode
        $query = KehadiranMember::with('member')
            ->whereBetween('tanggal', [
                $periodeAktif->tanggal_mulai,
                $periodeAktif->tanggal_selesai,
            ]);

        // Filter tanggal (opsional)
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        // Filter nama/username member (opsional)
        if ($request->filled('member')) {
            $memberSearch = $request->input('member');
            $query->whereHas('member', function ($q) use ($memberSearch) {
                $q->where('nama', 'like', "%{$memberSearch}%")
                    ->orWhere('username', 'like', "%{$memberSearch}%");
            });
        }

        $kehadiran = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->paginate(15)
            ->appends($request->only(['mode', 'tanggal', 'member'])); // supaya filter & mode tetap

        // URL yang akan di-QR-kan (dipakai juga di view untuk generate QR)
        $qrUrl = route('member.absensi.scan', ['token' => $periodeAktif->kode_qr]);

        $pageTitle   = 'Absensi Member';
        $modeOptions = [
            'harian'   => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
        ];

        return view('admin.absensi.kehadiran.index', compact(
            'periodeAktif',
            'kehadiran',
            'qrUrl',
            'pageTitle',
            'modeOptions'
        ));
    }

    /**
     * Halaman khusus cetak (QR + daftar kehadiran).
     * Route: admin.absensi.kehadiran.print
     */
    public function print(Request $request)
    {
        // Bawa mode dari query (supaya sama seperti index)
        $requestedMode = $request->get('mode');

        $periodeAktif = $this->getOrCreateActivePeriodeForToday($requestedMode);

        // Query kehadiran dalam periode (sama seperti index, tapi tanpa paginate)
        $query = KehadiranMember::with('member')
            ->whereBetween('tanggal', [
                $periodeAktif->tanggal_mulai,
                $periodeAktif->tanggal_selesai,
            ]);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        if ($request->filled('member')) {
            $memberSearch = $request->input('member');
            $query->whereHas('member', function ($q) use ($memberSearch) {
                $q->where('nama', 'like', "%{$memberSearch}%")
                    ->orWhere('username', 'like', "%{$memberSearch}%");
            });
        }

        $kehadiran = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk')
            ->get(); // ambil semua untuk keperluan print

        $qrUrl = route('member.absensi.scan', ['token' => $periodeAktif->kode_qr]);

        $pageTitle   = 'Cetak QR Absensi Member';
        $modeOptions = [
            'harian'   => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
        ];

        return view('admin.absensi.kehadiran.print', compact(
            'periodeAktif',
            'kehadiran',
            'qrUrl',
            'pageTitle',
            'modeOptions'
        ));
    }
}
