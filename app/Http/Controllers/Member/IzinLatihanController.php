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
     * Menampilkan formulir pengajuan izin latihan.
     */
    public function index()
    {
        return view('member.izin_latihan.form');
    }

    /**
     * Menampilkan riwayat izin latihan milik member yang sedang login.
     */
    public function riwayat()
    {
        // Ambil ID member yang sedang login
        $memberId = Auth::id(); 
        
        $riwayat_izin = IzinLatihan::where('member_id', $memberId)
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
                            
        return view('member.izin_latihan.riwayat', compact('riwayat_izin'));
    }

    /**
     * Menyimpan data pengajuan izin baru ke database.
     */
    public function submit(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|min:10|max:1000',
            'bukti_alasan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Maks 2MB
        ]);

        $path_bukti = null;

        // 2. Proses Upload File (Jika ada)
        if ($request->hasFile('bukti_alasan')) {
            // Simpan file ke storage/app/public/uploads/bukti_izin
            // Pastikan Anda sudah menjalankan `php artisan storage:link`
            $path_bukti = $request->file('bukti_alasan')->store('uploads/bukti_izin', 'public');
        }

        // 3. Hitung Jumlah Hari
        // (Sesuai dengan Accessor di Model Anda)
        $tglMulai = Carbon::parse($request->tanggal_mulai);
        $tglSelesai = Carbon::parse($request->tanggal_selesai);
        $jumlahHari = $tglMulai->diffInDays($tglSelesai) + 1;

        // 4. Simpan ke Database
        IzinLatihan::create([
            'member_id' => Auth::id(), // Ambil ID member yang login
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari' => $jumlahHari,
            'alasan' => $request->alasan,
            'bukti_alasan' => $path_bukti,
            'status' => 'pending', // Status default saat pengajuan
        ]);

        // 5. Redirect ke halaman riwayat dengan pesan sukses
        return redirect()->route('member.izin_latihan.riwayat')
                         ->with('success', 'Formulir izin latihan Anda telah berhasil diajukan.');
    }
}