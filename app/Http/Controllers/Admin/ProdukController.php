<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar semua produk (Index).
     */
    public function index()
    {
        $pageTitle = 'Data Produk';
        
        // Ambil semua produk dan paginate (10 item per halaman)
        $produks = Produk::orderBy('created_at', 'desc')->paginate(10);

        // Menggunakan nama view sesuai konvensi: resources/views/admin/produk/index.blade.php
        return view('admin.produk.index', compact('produks', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk membuat produk baru (Create).
     */
    public function create()
    {
        $pageTitle = 'Tambah Produk Baru';
        // Definisikan kategori yang tersedia (sesuai ENUM/aturan bisnis)
        $kategoriOptions = ['Suplemen', 'Peralatan', 'Aksesoris', 'Lain-lain']; 

        // Menggunakan nama view sesuai konvensi: resources/views/admin/produk/create.blade.php
        return view('admin.produk.create', compact('pageTitle', 'kategoriOptions'));
    }

    /**
     * Menyimpan produk baru ke database (Store).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama' => 'required|string|max:255|unique:produk,nama',
            'kategori' => 'required|in:Suplemen,Peralatan,Aksesoris,Lain-lain', // Harus sesuai kategori
            'harga' => 'required|numeric|min:0',
            // Stok awal diatur, bisa 0
            'stok' => 'required|integer|min:0', 
            'deskripsi' => 'nullable|string',
        ]);

        // 2. Simpan Produk
        try {
            Produk::create($request->all());
            
            return redirect()->route('admin.produk.index')->with('success', 'Produk baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan produk. Terjadi kesalahan sistem.');
        }
    }

    /**
     * Menampilkan detail produk (Show).
     */
    public function show(Produk $produk)
    {
        $pageTitle = 'Detail Produk: ' . $produk->nama;
        
        // Menggunakan nama view sesuai konvensi: resources/views/admin/produk/show.blade.php
        return view('admin.produk.show', compact('produk', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk mengedit produk yang ditentukan (Edit).
     */
    public function edit(Produk $produk)
    {
        $pageTitle = 'Edit Produk: ' . $produk->nama;
        $kategoriOptions = ['Suplemen', 'Peralatan', 'Aksesoris', 'Lain-lain'];

        // Menggunakan nama view sesuai konvensi: resources/views/admin/produk/edit.blade.php
        return view('admin.produk.edit', compact('produk', 'pageTitle', 'kategoriOptions'));
    }

    /**
     * Memperbarui produk yang ditentukan di database (Update).
     */
    public function update(Request $request, Produk $produk)
    {
        // 1. Validasi Input
        $request->validate([
            // Nama harus unik kecuali untuk produk yang sedang diedit
            'nama' => 'required|string|max:255|unique:produk,nama,' . $produk->id,
            'kategori' => 'required|in:Suplemen,Peralatan,Aksesoris,Lain-lain',
            'harga' => 'required|numeric|min:0',
            // Stok boleh diupdate di sini, tetapi perubahan stok yang tercatat di log (StokProduk) lebih baik di StokProdukController
            'stok' => 'required|integer|min:0', 
            'deskripsi' => 'nullable|string',
        ]);
        
        // 2. Update Produk
        try {
            $produk->update($request->all());

            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui produk. Terjadi kesalahan sistem.');
        }
    }

    /**
     * Menghapus produk dari database (Destroy).
     */
    public function destroy(Produk $produk)
    {
        // PENTING: Lakukan pengecekan apakah produk ini sudah terkait dengan PenjualanProduk atau StokProduk
        // Jika ada relasi, hapus relasi tersebut terlebih dahulu atau berikan peringatan.
        // Untuk saat ini, kita akan lakukan pengecekan sederhana:
        
        // 1. Cek Keterkaitan dengan Penjualan Produk
        if ($produk->penjualanProduks()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat transaksi penjualan.');
        }

        // 2. Cek Keterkaitan dengan Stok Produk (Log)
        if ($produk->stokProduks()->exists()) {
             // Jika ada riwayat log stok, biasanya lebih aman untuk tidak menghapus
             return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat perubahan stok.');
        }
        
        // Jika aman, hapus produk
        try {
            $produk->delete();
            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus produk. Terjadi kesalahan sistem.');
        }
    }
}