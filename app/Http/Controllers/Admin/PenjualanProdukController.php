<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PenjualanProdukController extends Controller
{
    private $metodePembayaran = ['Cash', 'Debit', 'Transfer', 'QRIS'];

    /**
     * Menampilkan daftar riwayat semua transaksi penjualan produk (Index) 
     * dan menyediakan data untuk modal CREATE.
     */
    public function index()
    {
        $pageTitle = 'Riwayat Penjualan Produk';
        
        $daftar_penjualan = PenjualanProduk::with('produk')
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(15);
        
        // Data untuk Modal CREATE
        $produks = Produk::where('stok', '>', 0)->orderBy('nama')->get(); 
        $metodePembayaran = $this->metodePembayaran;

        // Mengirimkan semua data ke view index
        return view('admin.penjualan_produk.index', compact('daftar_penjualan', 'pageTitle', 'produks', 'metodePembayaran'));
    }

    /**
     * Memproses dan menyimpan transaksi penjualan produk baru (STORE).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            // PERBAIKAN KRUSIAL: Mengubah 'produk' menjadi 'produks'
            'produk_id' => 'required|exists:produks,id', 
            'jumlah' => 'required|integer|min:1',
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $produk = Produk::findOrFail($validatedData['produk_id']);
        $jumlahBeli = (int) $validatedData['jumlah'];

        // Cek Ketersediaan Stok
        if ($jumlahBeli > $produk->stok) {
            // Menggunakan with('modal_create_open', true) untuk membuka modal jika validasi stok gagal
            return back()->withInput()->with('error', "Stok {$produk->nama} tidak cukup. Tersedia: {$produk->stok}. Permintaan: {$jumlahBeli}.")
                         ->with('modal_create_open', true);
        }
        
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
                'tanggal_transaksi' => now(), 
            ]);

            // 5. Kurangi Stok pada Tabel Produk
            $produk->decrement('stok', $jumlahBeli);

            // 6. Catat perubahan ini ke Tabel StokProduk (Log OUT)
            StokProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => -$jumlahBeli, 
                'tanggal' => now(),
                'keterangan' => 'Penjualan produk dicatat.',
            ]);
            
            DB::commit();

            return redirect()->route('admin.penjualan_produk.index')->with('success', "Transaksi penjualan {$produk->nama} berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Gagal mencatat penjualan. Terjadi kesalahan sistem: ' . $e->getMessage())
                         ->with('modal_create_open', true); // Tetap buka modal jika error sistem
        }
    }
    
    // ... (metode show, edit, update, destroy tetap sama) ...
    public function show(PenjualanProduk $penjualanProduk)
    {
        $pageTitle = 'Detail Transaksi Penjualan';
        $penjualanProduk->load('produk');
        return view('admin.penjualan_produk.show', compact('penjualanProduk', 'pageTitle'));
    }

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

    public function destroy(PenjualanProduk $penjualanProduk)
    {
        DB::beginTransaction();
        try {
            $produk = $penjualanProduk->produk;
            $produk->increment('stok', $penjualanProduk->jumlah);
            StokProduk::create([
                'produk_id' => $penjualanProduk->produk_id,
                'jumlah' => $penjualanProduk->jumlah, 
                'tanggal' => now(),
                'keterangan' => 'Pembatalan transaksi penjualan (Stok dikembalikan).',
            ]);
            $penjualanProduk->delete();
            DB::commit();
            return redirect()->route('admin.penjualan_produk.index')->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi. Terjadi kesalahan sistem.');
        }
    }
}