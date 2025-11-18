<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IzinLatihan;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class IzinLatihanController extends Controller
{
    public function __construct()
    {
        // Menghitung izin pending dan membagikannya ke semua view
        $izinPending = IzinLatihan::where('status', 'pending')->count();
        View::share('izinPending', $izinPending);
    }

    /**
     * Menampilkan daftar permintaan izin yang HANYA berstatus 'pending'.
     */
    public function index()
    {
        $pageTitle = 'Permintaan Izin Baru';
        $daftar_izin = IzinLatihan::with('member')
            ->where('status', 'pending') // Filter hanya yang pending
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('admin.izin_latihan.index', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan riwayat izin yang telah disetujui atau ditolak.
     */
    public function history()
    {
        $pageTitle = 'Riwayat Persetujuan Izin';
        $riwayat_izin = IzinLatihan::with('member')
            ->whereIn('status', ['disetujui', 'ditolak']) // Filter yang sudah diproses
            ->orderBy('tanggal_persetujuan', 'desc')
            ->paginate(15);

        return view('admin.izin_latihan.history', compact('riwayat_izin', 'pageTitle'));
    }

    /**
     * Menampilkan detail satu izin latihan untuk ditinjau oleh Admin (tanpa aksi).
     */
    public function show($id)
    {
        $pageTitle = 'Detail Izin Member';
        $izin = IzinLatihan::with('member')->findOrFail($id);

        return view('admin.izin_latihan.detail', compact('izin', 'pageTitle'));
    }

    /**
     * Menampilkan formulir persetujuan izin (Setujui) dari halaman index.
     */
    public function approveForm(IzinLatihan $izinLatihan)
    {
        if ($izinLatihan->status !== 'pending') {
            return redirect()->route('admin.izin_latihan.index')->with('error', 'Izin sudah diproses.');
        }

        $pageTitle = 'Formulir Persetujuan Izin';
        $izin = $izinLatihan;

        return view('admin.izin_latihan.approve_form', compact('izin', 'pageTitle'));
    }

    /**
     * Menyelesaikan alur persetujuan izin, termasuk perpanjangan masa membership.
     */
    public function approveIzin(Request $request, IzinLatihan $izinLatihan)
    {
        // 1. Validasi Input Admin
        $request->validate([
            'approved_days' => 'required|integer|min:0|max:' . $izinLatihan->jumlah_hari,
            'keterangan_admin' => 'nullable|string|max:1000',
        ], [
            'approved_days.max' => 'Hari yang disetujui tidak boleh melebihi durasi permintaan member (' . $izinLatihan->jumlah_hari . ' hari).'
        ]);

        $approvedDays = (int) $request->approved_days;

        // Cek status izin dan member
        if ($izinLatihan->status !== 'pending') {
            return redirect()->route('admin.izin_latihan.index')->with('error', 'Izin sudah diproses.');
        }

        $member = $izinLatihan->member;
        if (!$member) {
            return redirect()->route('admin.izin_latihan.index')->with('error', 'Member tidak ditemukan.');
        }

        DB::beginTransaction();
        try {

            // 3. Update Status Izin Latihan
            $izinLatihan->update([
                'status' => 'disetujui',
                'durasi_izin_disetujui' => $approvedDays,
                'keterangan_admin' => $request->keterangan_admin,
                'tanggal_persetujuan' => now(),
            ]);

            // 4. Hitung dan Update Masa Membership
            $newEndDate = $member->tanggal_akhir;

            if ($approvedDays > 0) {
                $currentEndDate = Carbon::parse($member->tanggal_akhir);
                $newEndDate = $currentEndDate->addDays($approvedDays)->toDateString();
                $member->update([
                    'tanggal_akhir' => $newEndDate,
                ]);

                $message = "Membership {$member->nama} diperpanjang {$approvedDays} hari.";
            } else {
                $message = "Izin {$member->nama} disetujui. Tanpa perpanjangan membership (0 hari).";
            }

            DB::commit();

            // 5. Redirect ke halaman Index dengan pesan singkat
            return redirect()->route('admin.izin_latihan.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses persetujuan. Terjadi kesalahan sistem.');
        }
    }

    /**
     * Proses untuk menolak izin latihan.
     */
    public function reject(Request $request, $id)
    {
        $izin = IzinLatihan::findOrFail($id);

        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';

            // Simpan keterangan admin jika ada di request (dari form)
            if ($request->has('keterangan_admin')) {
                 $izin->keterangan_admin = $request->keterangan_admin;
            }
            $izin->tanggal_persetujuan = now();
            $izin->save();

            return redirect()->route('admin.izin_latihan.index')
                             ->with('error', 'Izin member ' . ($izin->member?->nama ?? '') . ' telah DITOLAK.');
        }

        // Jika statusnya bukan pending, tetap di index
        return redirect()->route('admin.izin_latihan.index')
                         ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }
}
