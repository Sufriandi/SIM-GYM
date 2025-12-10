<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPeriode;
use App\Models\KehadiranMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
     * Halaman admin: QR aktif + daftar kehadiran.
     *
     * - QR & kartu periode tetap ikut mode (harian/mingguan/bulanan).
     * - Tabel "Daftar Kehadiran" SELALU menampilkan data 1 bulan
     *   (bulan dari tanggal filter, atau bulan hari ini jika kosong).
     */
    public function index(Request $request)
    {
        // Mode periode untuk QR
        $requestedMode = $request->get('mode');
        $periodeAktif  = $this->getOrCreateActivePeriodeForToday($requestedMode);

        // ==========================
        // RANGE BULAN UNTUK TABEL
        // ==========================
        if ($request->filled('tanggal')) {
            $baseDate = Carbon::parse($request->input('tanggal'));
        } else {
            $baseDate = now();
        }

        $startOfMonth = $baseDate->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $baseDate->copy()->endOfMonth()->toDateString();

        // Query dasar: semua kehadiran dalam 1 bulan tersebut
        $query = KehadiranMember::with(['member.user'])
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        // Filter tanggal spesifik (optional) tapi tetap dalam bulan yang sama
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        // Sorting
        $sort = $request->input('sort', 'newest');

        if ($sort === 'oldest') {
            $query->orderBy('tanggal', 'asc')
                  ->orderBy('jam_masuk', 'asc');
        } else {
            // default: terbaru
            $query->orderBy('tanggal', 'desc')
                  ->orderBy('jam_masuk', 'desc');
        }

        // Paginate 15 data / halaman
        $kehadiran = $query
            ->paginate(15)
            ->appends($request->only(['mode', 'tanggal', 'sort']));

        // URL yang akan di-QR-kan
        $qrUrl = route('member.absensi.scan', ['token' => $periodeAktif->kode_qr]);

        $pageTitle   = 'Absensi Member';
        $modeOptions = [
            'harian'   => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
        ];

        return view('admin.absensi.index', compact(
            'periodeAktif',
            'kehadiran',
            'qrUrl',
            'pageTitle',
            'modeOptions',
            'sort'
        ));
    }

    /**
     * Halaman khusus cetak (QR + daftar kehadiran).
     * Dataset sama dengan index(), tetapi tanpa pagination.
     */
    public function print(Request $request)
    {
        $requestedMode = $request->get('mode');
        $periodeAktif  = $this->getOrCreateActivePeriodeForToday($requestedMode);

        // Bulan yang sama dengan index()
        if ($request->filled('tanggal')) {
            $baseDate = Carbon::parse($request->input('tanggal'));
        } else {
            $baseDate = now();
        }

        $startOfMonth = $baseDate->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $baseDate->copy()->endOfMonth()->toDateString();

        $query = KehadiranMember::with(['member.user'])
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        $sort = $request->input('sort', 'newest');

        if ($sort === 'oldest') {
            $query->orderBy('tanggal', 'asc')
                  ->orderBy('jam_masuk', 'asc');
        } else {
            $query->orderBy('tanggal', 'desc')
                  ->orderBy('jam_masuk', 'desc');
        }

        $kehadiran = $query->get();

        $qrUrl = route('member.absensi.scan', ['token' => $periodeAktif->kode_qr]);

        $pageTitle   = 'Cetak QR Absensi Member';
        $modeOptions = [
            'harian'   => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
        ];

        return view('admin.absensi.print', compact(
            'periodeAktif',
            'kehadiran',
            'qrUrl',
            'pageTitle',
            'modeOptions',
            'sort'
        ));
    }
}
