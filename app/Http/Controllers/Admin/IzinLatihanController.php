<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use Illuminate\Http\Request;

class IzinLatihanController extends Controller
{
    /**
     * Menampilkan daftar semua izin latihan dari semua member.
     * Admin melihat halaman ini.
     */
    public function index()
    {
        $pageTitle = 'Manajemen Izin Latihan'; // <-- 1. DEFINISIKAN JUDUL
        // Ambil semua data izin, diurutkan berdasarkan status 'pending' terlebih dahulu
        // Gunakan 'with('member')' (Eager Loading) agar tidak terjadi N+1 query
        $daftar_izin = IzinLatihan::with('member')
            ->orderByRaw("FIELD(status, 'pending', 'disetujui', 'ditolak')")
            ->orderBy('created_at', 'desc')
            ->paginate(15); // Paginasi agar halaman tidak berat

        return view('admin.izin_latihan.index', compact('daftar_izin', 'pageTitle'));
    }

    /**
     * Menampilkan detail satu izin latihan untuk ditinjau oleh Admin.
     */
    public function detail($id)
    {
        $izin = IzinLatihan::with('member')->findOrFail($id);

        return view('admin.izin_latihan.detail', compact('izin'));
    }

    /**
     * Proses untuk menyetujui izin latihan.
     */
    public function approve(Request $request, $id)
    {
        $izin = IzinLatihan::findOrFail($id);
        
        // Cek apakah sudah 'pending' sebelum diubah
        if ($izin->status === 'pending') {
            $izin->status = 'disetujui';
            $izin->save();
            
            // TODO: Kirim notifikasi ke member (Opsional)
            
            return redirect()->route('admin.izin_latihan.detail', $izin->id)
                             ->with('success', 'Izin latihan telah berhasil DISATUJUI.');
        }

        return redirect()->route('admin.izin_latihan.detail', $izin->id)
                         ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }

    /**
     * Proses untuk menolak izin latihan.
     */
    public function reject(Request $request, $id)
    {
        $izin = IzinLatihan::findOrFail($id);
        
        // Cek apakah masih 'pending'
        if ($izin->status === 'pending') {
            $izin->status = 'ditolak';
            $izin->save();

            // TODO: Kirim notifikasi ke member (Opsional)

            return redirect()->route('admin.izin_latihan.detail', $izin->id)
                             ->with('danger', 'Izin latihan telah DITOLAK.'); // Gunakan 'danger' untuk warna merah
        }

        return redirect()->route('admin.izin_latihan.detail', $izin->id)
                         ->with('info', 'Izin latihan ini sudah diproses sebelumnya.');
    }
}