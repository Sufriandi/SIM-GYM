<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class PenjualanProdukController extends Controller
{
    /**
     * Menampilkan daftar riwayat semua transaksi penjualan produk (READ/Index).
     */
    public function index()
    {
        $pageTitle = 'Riwayat Penjualan Produk';
        
        $daftar_penjualan = PenjualanProduk::with('produk')
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(15);

        return view('admin.penjualan_produk.index', compact('daftar_penjualan', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk mencatat transaksi penjualan produk baru (CREATE).
     */
    public function create()
    {
        $pageTitle = 'Catat Penjualan Baru';
        $produks = Produk::where('stok', '>', 0)->get(); 
        $metodePembayaran = ['Cash', 'Debit', 'Transfer', 'QRIS'];

        return view('admin.penjualan_produk.create', compact('pageTitle', 'produks', 'metodePembayaran'));
    }

    /**
     * Memproses dan menyimpan transaksi penjualan produk baru (STORE).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'metode_pembayaran' => 'required|in:Cash,Transfer,QRIS', // Sesuaikan dengan ENUM
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $produk = Produk::findOrFail($request->produk_id);
        $jumlahBeli = (int) $request->jumlah;

        // 2. Cek Ketersediaan Stok
        if ($jumlahBeli > $produk->stok) {
            return back()->withInput()->with('error', "Stok {$produk->nama} tidak cukup. Tersedia: {$produk->stok}. Permintaan: {$jumlahBeli}.");
        }
        
        // 3. Hitung Total Harga
        $totalHarga = $produk->harga * $jumlahBeli;

        DB::beginTransaction();

        try {
            // 4. Catat Transaksi Penjualan (Tabel penjualan_produk)
            PenjualanProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => $jumlahBeli,
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $request->metode_pembayaran,
                'keterangan' => $request->keterangan,
                'tanggal_transaksi' => now(),
            ]);

            // 5. Kurangi Stok pada Tabel Produk (Kolom 'stok' di tabel 'produk')
            $produk->decrement('stok', $jumlahBeli);

            // 6. Catat perubahan ini ke Tabel StokProduk (sebagai LOG Penjualan OUT)
            StokProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => -$jumlahBeli, // Nilai negatif menandakan pengurangan
                'tanggal' => now(),
                'keterangan' => 'Penjualan produk dicatat.',
            ]);
            
            DB::commit();

            return redirect()->route('admin.penjualan_produk.index')->with('success', "Transaksi penjualan {$produk->nama} berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Gagal mencatat penjualan. Terjadi kesalahan sistem.');
        }
    }

    /**
     * Menampilkan detail satu transaksi penjualan (READ/Show).
     */
    public function show(PenjualanProduk $penjualanProduk)
    {
        $pageTitle = 'Detail Transaksi Penjualan';
        
        // Muat relasi produk
        $penjualanProduk->load('produk');

        // Menggunakan nama view sesuai konvensi: resources/views/admin/penjualan_produk/detail.blade.php
        return view('admin.penjualan_produk.show', compact('penjualanProduk', 'pageTitle'));
    }

    /**
     * Show the form for editing the specified resource.
     * * CATATAN: Mengedit Riwayat Penjualan sangat tidak disarankan karena melanggar prinsip 
     * akuntansi/keuangan. Jika tetap dibutuhkan, pertimbangkan untuk membuat Transaksi Balik (Reverse Transaction).
     */
    public function edit(PenjualanProduk $penjualanProduk)
    {
        // Untuk tujuan keamanan dan integritas, Admin biasanya tidak diizinkan
        // untuk mengedit transaksi penjualan yang sudah terjadi.
        return redirect()->route('admin.penjualan_produk.index')
                         ->with('info', 'Edit transaksi penjualan tidak diizinkan.');

        // JIKA Anda harus mengizinkan edit, kodenya akan seperti ini:
        /*
        $pageTitle = 'Edit Penjualan';
        $produks = Produk::all(); 
        $metodePembayaran = ['Cash', 'Debit', 'Transfer', 'QRIS'];
        return view('admin.penjualan_produk.edit', compact('penjualanProduk', 'pageTitle', 'produks', 'metodePembayaran'));
        */
    }

    /**
     * Update the specified resource in storage.
     * * CATATAN: Melakukan update di sini akan memerlukan logika yang sangat kompleks 
     * untuk mengembalikan stok lama, memproses stok baru, dan mengupdate log StokProduk.
     */
    public function update(Request $request, PenjualanProduk $penjualanProduk)
    {
        // Update transaksi penjualan tidak disarankan.
        return redirect()->route('admin.penjualan_produk.show', $penjualanProduk)
                         ->with('error', 'Update transaksi penjualan tidak diimplementasikan demi integritas data.');
    }

    /**
     * Remove the specified resource from storage.
     * * CATATAN: Menghapus penjualan akan memerlukan pengembalian stok ke produk
     * dan mencatat log pengembalian stok di StokProduk.
     */
    public function destroy(PenjualanProduk $penjualanProduk)
    {
        // Memulai Transaksi untuk mengembalikan stok
        DB::beginTransaction();

        try {
            // 1. Tambah kembali stok produk
            $produk = $penjualanProduk->produk;
            $produk->increment('stok', $penjualanProduk->jumlah);

            // 2. Catat penambahan stok (sebagai LOG Pembatalan/Retur)
            StokProduk::create([
                'produk_id' => $penjualanProduk->produk_id,
                'jumlah' => $penjualanProduk->jumlah, // Nilai positif untuk pengembalian stok
                'tanggal' => now(),
                'keterangan' => 'Pembatalan transaksi penjualan.',
            ]);

            // 3. Hapus transaksi penjualan
            $penjualanProduk->delete();

            DB::commit();
            return redirect()->route('admin.penjualan_produk.index')->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi. Terjadi kesalahan sistem.');
        }
    }
}