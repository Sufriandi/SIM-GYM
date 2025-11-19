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
     * Metode Pembayaran yang sesuai dengan ENUM di migrasi.
     */
    private $metodePembayaran = ['Cash', 'Transfer', 'QRIS'];

    /**
     * Menampilkan daftar riwayat semua transaksi penjualan produk (READ/Index).
     */
    public function index()
    {
        $pageTitle = 'Riwayat Penjualan Produk';
        
        $daftar_penjualan = PenjualanProduk::with('produk')
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(15);

        // Catatan: Pastikan view ini ada: resources/views/admin/penjualan_produk/index.blade.php
        return view('admin.penjualan_produk.index', compact('daftar_penjualan', 'pageTitle'));
    }

    /**
     * Menampilkan formulir untuk mencatat transaksi penjualan produk baru (CREATE).
     */
    public function create()
    {
        $pageTitle = 'Catat Penjualan Baru';
        // Hanya ambil produk yang memiliki stok lebih dari 0
        $produks = Produk::where('stok', '>', 0)->orderBy('nama')->get(); 
        $metodePembayaran = $this->metodePembayaran;

        // Catatan: Pastikan view ini ada: resources/views/admin/penjualan_produk/create.blade.php
        return view('admin.penjualan_produk.create', compact('pageTitle', 'produks', 'metodePembayaran'));
    }

    /**
     * Memproses dan menyimpan transaksi penjualan produk baru (STORE).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            // PERBAIKAN: Pastikan nama tabel di exists:produk,id adalah benar ('produk')
            'produk_id' => 'required|exists:produk,id', 
            'jumlah' => 'required|integer|min:1',
            // PERBAIKAN: Sinkronkan opsi metode pembayaran dengan properti Controller
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
            // 'tanggal_transaksi' tidak divalidasi karena menggunakan now()
        ]);

        $produk = Produk::findOrFail($validatedData['produk_id']);
        $jumlahBeli = (int) $validatedData['jumlah'];

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
                'metode_pembayaran' => $validatedData['metode_pembayaran'],
                'keterangan' => $validatedData['keterangan'],
                'tanggal_transaksi' => now(), // Menggunakan helper Laravel/Carbon
            ]);

            // 5. Kurangi Stok pada Tabel Produk
            $produk->decrement('stok', $jumlahBeli);

            // 6. Catat perubahan ini ke Tabel StokProduk (Log OUT)
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
            // Tampilkan pesan error yang lebih detail di lingkungan development jika perlu
            return back()->withInput()->with('error', 'Gagal mencatat penjualan. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail satu transaksi penjualan (READ/Show).
     */
    public function show(PenjualanProduk $penjualanProduk)
    {
        $pageTitle = 'Detail Transaksi Penjualan';
        $penjualanProduk->load('produk');

        return view('admin.penjualan_produk.show', compact('penjualanProduk', 'pageTitle'));
    }

    /**
     * Edit dan Update TIDAK diizinkan demi integritas data keuangan/stok.
     */
    public function edit(PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.index')
                         ->with('info', 'Edit transaksi penjualan tidak diizinkan.');
    }

    public function update(Request $request, PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.show', $penjualanProduk)
                         ->with('error', 'Update transaksi penjualan tidak diimplementasikan demi integritas data.');
    }

    /**
     * Menghapus transaksi penjualan (DESTROY/Pembatalan).
     */
    public function destroy(PenjualanProduk $penjualanProduk)
    {
        DB::beginTransaction();

        try {
            // 1. Tambah kembali stok produk
            $produk = $penjualanProduk->produk;
            $produk->increment('stok', $penjualanProduk->jumlah);

            // 2. Catat penambahan stok (Log Retur)
            StokProduk::create([
                'produk_id' => $penjualanProduk->produk_id,
                'jumlah' => $penjualanProduk->jumlah, // Nilai positif untuk pengembalian stok
                'tanggal' => now(),
                'keterangan' => 'Pembatalan transaksi penjualan (Stok dikembalikan).',
            ]);

            // 3. Hapus transaksi penjualan
            $penjualanProduk->delete();

            DB::commit();
            return redirect()->route('admin.penjualan_produk.index')->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}