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
        // Untuk badge notifikasi di navbar dsb
        $izinPending = IzinLatihan::where('status', 'pending')->count();
        View::share('izinPending', $izinPending);
    }

    /**
     * Halaman utama: daftar izin pending.
     * Data list + tambah + approve sekarang di-handle Livewire.
     */
    public function index()
    {
        $pageTitle = 'Permintaan Izin Baru';

        return view('admin.izin_latihan.index', compact('pageTitle'));
    }

    /**
     * Riwayat izin yang sudah disetujui / ditolak.
     */
    public function history()
    {
        $pageTitle = 'Riwayat Persetujuan Izin';

        $riwayat_izin = IzinLatihan::with('member')
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->orderBy('tanggal_persetujuan', 'desc')
            ->paginate(15);

        return view('admin.izin_latihan.history', compact('riwayat_izin', 'pageTitle'));
    }

    /**
     * Detail satu izin (biasanya dipanggil dari riwayat).
     */
    public function show($id)
    {
        $pageTitle = 'Detail Izin Member';

        $izin = IzinLatihan::with('member')->findOrFail($id);

        return view('admin.izin_latihan.detail', compact('izin', 'pageTitle'));
    }

    /**
     * Proses tolak izin latihan (pending -> ditolak).
     */
    public function reject(Request $request, $id)
    {
        $izin = IzinLatihan::with('member')->findOrFail($id);

        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';

            if ($request->filled('keterangan_admin')) {
                $izin->keterangan_admin = $request->keterangan_admin;
            }

            $izin->tanggal_persetujuan = now();
            $izin->save();

            return redirect()
                ->route('admin.izin_latihan.index')
                ->with('error', 'Izin member ' . ($izin->member?->nama ?? '') . ' telah DITOLAK.');
        }

        return redirect()
            ->route('admin.izin_latihan.index')
            ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }
}
