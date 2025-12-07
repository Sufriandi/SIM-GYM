<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Mengambil semua produk (untuk aplikasi mobile)
     * Mengembalikan JSON ARRAY sesuai kebutuhan Android
     */
    public function index(Request $request)
    {
        // Optional: mendukung pencarian di mobile
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $produk = Produk::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('deskripsi', 'like', "%$search%");
            })
            ->when($kategori, function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            })
            ->orderBy('id', 'desc')
            ->get(); // PENTING: get() mengembalikan ARRAY

        return response()->json($produk, 200);
    }



    /**
     * Menampilkan detail produk.
     * Tetap mengembalikan satu object JSON (bukan wrapper).
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'error' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json($produk, 200);
    }
}
