<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IzinLatihanController extends Controller
{
    /**
     * Menampilkan daftar izin yang HANYA berstatus 'pending'.
     */
    public function index()
    {
        $pageTitle = 'Izin Sedang Diajukan';
        $user_id = Auth::user()->id;

        // 🚨 PERBAIKAN: Filter hanya yang pending
        $daftar_izin = IzinLatihan::where('user_id', $user_id)
                                 ->where('status', 'pending')
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(10);

        return view('member.izin_latihan.index', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan riwayat LENGKAP semua izin (Pending, Disetujui, Ditolak).
     */
    public function history()
    {
        $pageTitle = 'Riwayat Pengajuan Izin Lengkap';
        $user_id = Auth::user()->id;

        // Mengambil semua status tanpa filter
        $daftar_izin = IzinLatihan::where('user_id', $user_id)
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(10);

        return view('member.izin_latihan.history', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk membuat izin baru.
     */
    public function create()
    {
        $pageTitle = 'Formulir Izin Baru';
        return view('member.izin_latihan.form', compact('pageTitle'));
    }

    /**
     * Menyimpan data pengajuan izin baru ke database. (Logika tidak berubah, tetap benar)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:1000',
            'bukti_alasan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // 🚨 PERBAIKAN ANTI-SPAM: CEK OVERLAP TANGGAL
        $tglMulaiBaru = $request->tanggal_mulai;
        $tglSelesaiBaru = $request->tanggal_selesai;

        // 1. Ambil izin yang statusnya masih aktif/pending
        $existingIzin = IzinLatihan::where('user_id', Auth::id())
                                    ->whereIn('status', ['pending', 'disetujui']) // Cek yang pending DAN yang sudah disetujui
                                    ->get();

        foreach ($existingIzin as $izin) {
            // Logika Overlap: (Tanggal Mulai Baru <= Tanggal Selesai Lama) AND (Tanggal Selesai Baru >= Tanggal Mulai Lama)
            $overlap = (
                $tglMulaiBaru <= $izin->tanggal_selesai &&
                $tglSelesaiBaru >= $izin->tanggal_mulai
            );

            if ($overlap) {
                // Format tanggal overlap untuk pesan error
                $izinMulai = Carbon::parse($izin->tanggal_mulai)->format('d M Y');
                $izinSelesai = Carbon::parse($izin->tanggal_selesai)->format('d M Y');

                return redirect()->route('member.izin_latihan.index')
                    ->with('error', "Gagal! Periode izin Anda bertabrakan dengan izin yang sudah ada pada tanggal {$izinMulai} s/d {$izinSelesai}.");
            }
        }
        // ---------------------------------------------------

        $path_bukti = null;

        // 2. Proses Upload File
        if ($request->hasFile('bukti_alasan')) {
            $path_bukti = $request->file('bukti_alasan')->store('uploads/bukti_izin', 'public');
        }

        // 3. Hitung Jumlah Hari
        $tglMulai = Carbon::parse($request->tanggal_mulai);
        $tglSelesai = Carbon::parse($request->tanggal_selesai);
        $jumlahHari = $tglMulai->diffInDays($tglSelesai) + 1;

        // 4. Simpan ke Database
        IzinLatihan::create([
            'user_id' => Auth::user()->id, // atau Auth::id()
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari' => $jumlahHari,
            'alasan' => $request->alasan,
            'bukti_alasan' => $path_bukti,
            'status' => 'pending',
        ]);

        // 5. Redirect ke halaman 'index' (riwayat)
        return redirect()->route('member.izin_latihan.index')
                         ->with('success', 'Formulir izin latihan Anda telah berhasil diajukan dan sedang menunggu persetujuan.');
    }
}
