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
     * - Tabel "Daftar Kehadiran" SELALU menampilkan data 1 bulan penuh,
     */
    public function index(Request $request)
{
    $requestedMode = $request->get('mode');
    $periodeAktif  = $this->getOrCreateActivePeriodeForToday($requestedMode);

    // =========================
    // FILTER BULAN + TANGGAL (AND)
    // bulan = YYYY-MM (wajib untuk memilih bulan tertentu, default bulan ini)
    // tanggal (opsional) = mulai dari tanggal tsb sampai akhir bulan
    // =========================
    $bulanInput   = $request->input('bulan');   // contoh: "2025-12"
    $tanggalInput = $request->input('tanggal'); // contoh: "2025-12-17"

    // Tentukan bulan yang ditampilkan:
    // - jika bulan dipilih => pakai bulan itu
    // - jika tidak => default bulan ini (now)
    $baseMonth = $bulanInput
        ? Carbon::createFromFormat('Y-m-d', $bulanInput . '-01')
        : now();

    $monthStart = $baseMonth->copy()->startOfMonth();
    $monthEnd   = $baseMonth->copy()->endOfMonth();

    // Range awal default = awal bulan
    $rangeStart = $monthStart->copy();

    // Jika tanggal dipilih => range mulai dari tanggal tersebut (HARUS masih dalam bulan yg dipilih)
    if ($tanggalInput) {
        $tanggal = Carbon::parse($tanggalInput)->startOfDay();

        // Jika tanggal di luar bulan terpilih => kosongkan hasil (kombinasi invalid)
        if ($tanggal->lt($monthStart) || $tanggal->gt($monthEnd)) {
            $query = KehadiranMember::with(['member.user'])->whereRaw('1=0');
        } else {
            $rangeStart = $tanggal;
            $query = KehadiranMember::with(['member.user'])
                ->whereBetween('tanggal', [$rangeStart->toDateString(), $monthEnd->toDateString()]);
        }
    } else {
        $query = KehadiranMember::with(['member.user'])
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()]);
    }

    // Sorting
    $sort = $request->input('sort', 'newest');
    if ($sort === 'oldest') {
        $query->orderBy('tanggal', 'asc')->orderBy('jam_masuk', 'asc');
    } else {
        $query->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc');
    }

    $kehadiran = $query->paginate(20)->appends(
        $request->only(['mode', 'bulan', 'tanggal', 'sort'])
    );

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
        'sort',
        'monthStart',
        'monthEnd',
        'rangeStart'
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
