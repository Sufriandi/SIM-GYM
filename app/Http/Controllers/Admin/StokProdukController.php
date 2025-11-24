<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StokProduk;
use App\Models\Produk; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StokProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua riwayat stok dengan relasi produk (untuk ditampilkan di index)
        $riwayat_stok = StokProduk::with('produk')
                                  ->latest('tanggal')
                                  ->paginate(15);
        
        // Ambil daftar produk untuk dropdown di modal tambah stok
        $produks = Produk::orderBy('nama')->get(['id', 'nama', 'stok']);

        return view('admin.stok_produk.index', compact('riwayat_stok', 'produks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produks,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        // Gunakan transaksi database untuk memastikan stok dan riwayat tersimpan bersamaan
        DB::transaction(function () use ($validated) {
            // 1. Tambah entri stok baru
            StokProduk::create($validated);

            // 2. Update stok di tabel produk
            Produk::where('id', $validated['produk_id'])->increment('stok', $validated['jumlah']);
        });

        return back()->with('success', 'Stok berhasil ditambahkan!');
    }

    // Karena kita menggunakan Modal untuk index/store/update,
    // method create() dan show() bisa dikosongkan atau di-redirect
    public function create()
    {
        return redirect()->route('admin.stok_produk.index');
    }
    
    public function show(StokProduk $stokProduk)
    {
        return redirect()->route('admin.stok_produk.index');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokProduk $stokProduk)
    {
        // Simpan jumlah lama sebelum update
        $jumlah_lama = $stokProduk->jumlah;

        $validated = $request->validate([
            'produk_id' => ['required', 'exists:produks,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);
        
        $jumlah_baru = $validated['jumlah'];
        $selisih = $jumlah_baru - $jumlah_lama; // Selisih stok yang harus diubah

        DB::transaction(function () use ($stokProduk, $validated, $selisih) {
            // 1. Update riwayat stok
            $stokProduk->update($validated);

            // 2. Update stok di tabel produk
            // Kita gunakan raw DB expression untuk mencegah race condition
            DB::table('produks')
                ->where('id', $stokProduk->produk_id)
                ->increment('stok', $selisih);
        });

        return back()->with('success', 'Stok berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage (Batalkan penambahan stok).
     */
    public function destroy(StokProduk $stokProduk)
    {
        // Pastikan stok tidak menjadi negatif setelah pembatalan
        $produk = $stokProduk->produk;
        if ($produk->stok < $stokProduk->jumlah) {
             return back()->with('error', 'Pembatalan gagal! Stok produk saat ini (' . $produk->stok . ') lebih sedikit dari jumlah stok yang dibatalkan (' . $stokProduk->jumlah . ').');
        }

        DB::transaction(function () use ($stokProduk) {
            // 1. Kurangi stok di tabel produk
            Produk::where('id', $stokProduk->produk_id)
                  ->decrement('stok', $stokProduk->jumlah);
            
            // 2. Hapus riwayat stok
            $stokProduk->delete();
        });

        return back()->with('success', 'Penambahan stok berhasil dibatalkan. Stok produk telah dikembalikan.');
    }
}