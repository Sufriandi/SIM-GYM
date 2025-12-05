<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukGymController extends Controller
{
    /**
     * Nomor WhatsApp admin untuk pemesanan.
     */
    private string $adminWhatsappNumber = '6282390694731'; // sesuaikan dengan nomor WA admin Anda

    /**
     * Daftar kategori yang diizinkan untuk filter.
     */
    private array $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    /**
     * Halaman marketplace produk gym (area member).
     * Route: member.produk_gym.index
     */
    public function index(Request $request)
    {
        $pageTitle = 'Produk Gym';

        // Ambil nilai filter dari query string
        $search   = trim($request->query('search', ''));
        $kategori = $request->query('kategori', 'all');

        // Query dasar: ambil semua produk (TANPA filter stok)
        // -> stok 0 tetap ditampilkan, hanya tombol beli yang dinonaktifkan di view
        $query = Produk::query();

        // PENCARIAN: nama / deskripsi
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // FILTER KATEGORI: hanya jika bukan "all" dan nilai valid
        if ($kategori !== 'all' && in_array($kategori, $this->kategoriOptions, true)) {
            $query->where('kategori', $kategori);
        }

        // Ambil data dengan pagination
        $produks = $query
            ->orderBy('nama', 'asc')   // bisa diganti 'created_at', 'desc' kalau mau produk terbaru duluan
            ->paginate(12)
            ->withQueryString();       // supaya search & kategori tetap ada di URL saat pindah halaman

        $kategoriOptions = $this->kategoriOptions;

        return view('member.produk_gym.index', compact(
            'produks',
            'pageTitle',
            'search',
            'kategori',
            'kategoriOptions'
        ));
    }

    /**
     * Helper untuk mendapatkan nomor WA admin.
     */
    public function getAdminNumber(): string
    {
        return $this->adminWhatsappNumber;
    }
}
