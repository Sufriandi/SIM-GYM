<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiAbsensi;
use App\Models\KehadiranMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class SesiAbsensiController extends Controller
{
    /**
     * Daftar semua sesi absensi (dengan filter sederhana).
     *
     * GET  /admin/absensi/sesi
     * Route name (contoh): admin.absensi.sesi.index
     */
    public function index(Request $request)
    {
        $pageTitle = 'Sesi Absensi';

        $tanggal = $request->query('tanggal');   // optional filter tanggal
        $status  = $request->query('status');    // aktif / ditutup / null

        $query = SesiAbsensi::query()
            ->withCount('kehadiran')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai');

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        if ($status && in_array($status, ['aktif', 'ditutup'], true)) {
            $query->where('status', $status);
        }

        $sesiList = $query->paginate(15)->withQueryString();

        return view('admin.absensi.sesi.index', compact(
            'pageTitle',
            'sesiList',
            'tanggal',
            'status'
        ));
    }

    /**
     * Form buat sesi absensi baru.
     *
     * GET  /admin/absensi/sesi/create
     */
    public function create()
    {
        $pageTitle = 'Buat Sesi Absensi';

        return view('admin.absensi.sesi.create', compact('pageTitle'));
    }

    /**
     * Simpan sesi absensi baru.
     *
     * POST /admin/absensi/sesi
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'nama_sesi'   => 'nullable|string|max:255',
                'tanggal'     => 'required|date',
                'jam_mulai'   => 'nullable|date_format:H:i',
                'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            ],
            [
                'tanggal.required'     => 'Tanggal sesi wajib diisi.',
                'tanggal.date'         => 'Format tanggal tidak valid.',
                'jam_mulai.date_format'=> 'Format jam mulai tidak valid (gunakan HH:ii).',
                'jam_selesai.date_format' => 'Format jam selesai tidak valid (gunakan HH:ii).',
                'jam_selesai.after'    => 'Jam selesai harus setelah jam mulai.',
            ]
        );

        // Kode QR acak dan unik
        $kodeQr = Str::uuid()->toString();

        $sesi = SesiAbsensi::create([
            'kode_qr'    => $kodeQr,
            'nama_sesi'  => $validated['nama_sesi'] ?: null,
            'tanggal'    => $validated['tanggal'],
            'jam_mulai'  => $validated['jam_mulai'] ?: null,
            'jam_selesai'=> $validated['jam_selesai'] ?: null,
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.absensi.sesi.show', $sesi->id)
            ->with('success', 'Sesi absensi baru berhasil dibuat. QR code siap ditampilkan.');
    }

    /**
     * Detail sesi + tampilan QR code.
     *
     * GET /admin/absensi/sesi/{sesi}
     */
    public function show(SesiAbsensi $sesi)
    {
        $pageTitle = 'Detail Sesi Absensi';

        $sesi->load(['creator', 'kehadiran.member.user']);

        $totalHadir     = $sesi->kehadiran()->count();
        $totalTerlambat = $sesi->kehadiran()->where('status', 'terlambat')->count();

        // URL yang akan di-encode ke QR (di-scan oleh member)
        $qrUrl = route('member.kehadiran.scan', $sesi->kode_qr);

        return view('admin.absensi.sesi.show', compact(
            'pageTitle',
            'sesi',
            'totalHadir',
            'totalTerlambat',
            'qrUrl'
        ));
    }

    /**
     * Menutup sesi absensi (tidak bisa di-scan lagi).
     *
     * POST /admin/absensi/sesi/{sesi}/close
     */
    public function close(SesiAbsensi $sesi)
    {
        if ($sesi->status === 'ditutup') {
            return back()->with('info', 'Sesi ini sudah ditutup sebelumnya.');
        }

        $sesi->update(['status' => 'ditutup']);

        return back()->with('success', 'Sesi absensi berhasil ditutup. QR code tidak dapat digunakan lagi.');
    }

    /**
     * (Opsional) Buka kembali sesi yang sudah ditutup.
     *
     * POST /admin/absensi/sesi/{sesi}/reopen
     */
    public function reopen(SesiAbsensi $sesi)
    {
        if ($sesi->status === 'aktif') {
            return back()->with('info', 'Sesi ini sudah dalam status aktif.');
        }

        $sesi->update(['status' => 'aktif']);

        return back()->with('success', 'Sesi absensi berhasil diaktifkan kembali.');
    }

    /**
     * (Opsional) Regenerasi kode QR untuk sesi tertentu.
     * Berguna jika kode lama dianggap bocor.
     *
     * POST /admin/absensi/sesi/{sesi}/regenerate-qr
     */
    public function regenerateQr(SesiAbsensi $sesi)
    {
        $sesi->update([
            'kode_qr' => Str::uuid()->toString(),
        ]);

        return back()->with('success', 'Kode QR sesi berhasil diganti. Gunakan kode baru untuk absensi.');
    }

    /**
     * Hapus sesi (hanya jika belum punya data kehadiran).
     *
     * DELETE /admin/absensi/sesi/{sesi}
     */
    public function destroy(SesiAbsensi $sesi)
    {
        if ($sesi->kehadiran()->exists()) {
            return back()->with('error', 'Sesi tidak dapat dihapus karena sudah memiliki data kehadiran.');
        }

        $sesi->delete();

        return redirect()
            ->route('admin.absensi.sesi.index')
            ->with('success', 'Sesi absensi berhasil dihapus.');
    }
}
