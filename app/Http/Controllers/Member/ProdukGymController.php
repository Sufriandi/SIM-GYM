<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\User; 

class ProdukGymController extends Controller
{
    private $adminWhatsappNumber = '6282390694731'; // GANTI DENGAN NOMOR WA ADMIN NYA
    private $kategoriOptions = ['minuman', 'suplemen', 'lainnya']; // Asumsi kategori dari Model Produk

    /**
     * Display a listing of the resource (Marketplace View) dengan fitur Pencarian dan Filter.
     * Rute: member.produk_gym.index
     */
    public function index(Request $request)
    {
        $pageTitle = 'Produk Gym';
        
        // Ambil filter dari request
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        
        // 1. Inisialisasi Query Produk
        $produksQuery = Produk::where('stok', '>', 0);
        
        // 2. Terapkan Pencarian
        if ($search) {
            $produksQuery->where(function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('deskripsi', 'like', '%' . $search . '%');
            });
        }

        // 3. Terapkan Filter Kategori
        if ($kategori && $kategori !== 'all') {
            $produksQuery->where('kategori', $kategori);
        }

        // 4. Ambil dan paginate data
        $produks = $produksQuery->orderBy('nama', 'asc')->paginate(12);
        
        // Kategori yang tersedia
        $kategoriOptions = $this->kategoriOptions;


        return view('member.produk_gym.index', compact('produks', 'pageTitle', 'search', 'kategori', 'kategoriOptions'));
    }
    
    /**
     * Helper untuk mendapatkan nomor WA Admin
     */
    public function getAdminNumber()
    {
        return $this->adminWhatsappNumber;
    }
}