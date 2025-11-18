<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard utama untuk area Admin dengan data contoh (hardcoded).
     */
    public function index()
    {
        // Data Contoh Sederhana (Hardcoded)
        $data = [
            'totalMembers' => 125,
            'izinPending' => 4,
            'produkTerlaris' => (object)['nama' => 'Whey Protein X', 'terjual' => 120],
            'pendapatanBulanIni' => 50000000, // Rp. 50 Juta
            
            // Contoh data Izin Latihan Terbaru
            'latestIzin' => collect([
                (object)['member_nama' => 'Aldi Nugraha', 'tanggal' => '2025-11-15', 'status' => 'Pending'],
                (object)['member_nama' => 'Budi Santoso', 'tanggal' => '2025-11-14', 'status' => 'Disetujui'],
            ]),
            
            // Contoh Log Aktivitas
            'logAktivitas' => collect([
                (object)['waktu' => '1 jam lalu', 'deskripsi' => 'Produk Kreatin Murni telah ditambahkan.'],
                (object)['waktu' => '5 jam lalu', 'deskripsi' => 'Member Aldi Nugraha mengajukan izin.'],
            ])
        ];

        // Kembalikan View beserta data contoh
        return view('admin.dashboard.index', $data);
    }
}