<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\User; // Digunakan untuk filter Member di view
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PenjualanProdukController extends Controller
{
    private $metodePembayaran = ['Cash', 'Transfer', 'QRIS'];

    /**
     * Menampilkan daftar riwayat semua transaksi penjualan produk (Index) 
     * dan menyediakan data untuk modal CREATE/EDIT.
     */
    public function index()
    {
        $pageTitle = 'Riwayat Penjualan Produk';
        
        $daftar_penjualan = PenjualanProduk::with(['produk', 'member']) // Load relasi member
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(10);
        
        // Data untuk Modal
        $produks = Produk::orderBy('nama')->get(); 
        $metodePembayaran = $this->metodePembayaran;

        return view('admin.penjualan_produk.index', compact('daftar_penjualan', 'pageTitle', 'produks', 'metodePembayaran'));
    }

    /**
     * Memproses dan menyimpan transaksi penjualan produk baru (STORE).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'produk_id' => 'required|exists:produks,id', 
            'jumlah' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:users,id', // Validasi member
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $produk = Produk::findOrFail($validatedData['produk_id']);
        $jumlahBeli = (int) $validatedData['jumlah'];

        // Cek Ketersediaan Stok
        if ($jumlahBeli > $produk->stok) {
            return back()->withInput()->with('error', "Stok {$produk->nama} tidak cukup. Tersedia: {$produk->stok}. Permintaan: {$jumlahBeli}.")
                         ->with('modal_create_open', true);
        }
        
        $totalHarga = $produk->harga * $jumlahBeli;

        DB::beginTransaction();

        try {
            // 4. Catat Transaksi Penjualan (Tabel penjualan_produk)
            PenjualanProduk::create([
                'produk_id' => $produk->id,
                'member_id' => $validatedData['member_id'] ?? null, // Simpan member_id
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
                         ->with('modal_create_open', true);
        }
    }
    
    // METHOD INI TIDAK MEMBUTUHKAN VIEW TERPISAH
    public function show(PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.index');
    }

    // METHOD INI TIDAK MEMBUTUHKAN VIEW TERPISAH
    public function edit(PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.index');
    }

    /**
     * Memperbarui transaksi penjualan produk (UPDATE).
     */
    public function update(Request $request, PenjualanProduk $penjualanProduk)
    {
        // PENTING: Jika validasi gagal, Laravel akan mencoba redirect kembali
        // dengan input lama. Kita perlu membawa ID untuk membuka modal kembali.
        
        $validatedData = $request->validate([
            // ID dari hidden input (untuk error handling di view)
            'id' => 'required|exists:penjualan_produks,id', 
            'jumlah' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:users,id', // Validasi member
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
        ]);
        
        $produk = $penjualanProduk->produk;
        $jumlahLama = $penjualanProduk->jumlah;
        $jumlahBaru = (int) $validatedData['jumlah'];
        $selisih = $jumlahBaru - $jumlahLama;
        
        // Stok saat ini + jumlah yang dikembalikan (yaitu jumlah lama)
        $stokMaksimum = $produk->stok + $jumlahLama;

        // Cek Ketersediaan Stok BARU
        if ($jumlahBaru > $stokMaksimum) {
            return back()->withInput()->with('error', "Stok {$produk->nama} tidak cukup untuk perubahan ini.")
                         ->with('modal_edit_open', true);
        }

        DB::beginTransaction();

        try {
            // 1. Hitung ulang total harga dan update transaksi
            $totalHargaBaru = $produk->harga * $jumlahBaru;
            
            $penjualanProduk->update([
                'member_id' => $validatedData['member_id'] ?? null,
                'jumlah' => $jumlahBaru,
                'total_harga' => $totalHargaBaru,
                'metode_pembayaran' => $validatedData['metode_pembayaran'],
                'keterangan' => $validatedData['keterangan'],
            ]);
            
            // 2. Sesuaikan Stok di tabel Produk
            if ($selisih != 0) {
                // Gunakan raw DB statement untuk adjust stok
                DB::table('produks')->where('id', $produk->id)->decrement('stok', $selisih);

                // 3. Catat perubahan stok (Log OUT/Penyesuaian)
                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => -$selisih, // Minus karena mengurangi stok
                    'tanggal' => now(),
                    'keterangan' => 'Penyesuaian transaksi penjualan ID ' . $penjualanProduk->id,
                ]);
            }

            DB::commit();

            // REDIRECT SUKSES: Selalu kembalikan ke index
            return redirect()->route('admin.penjualan_produk.index')->with('success', "Transaksi penjualan berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui penjualan. Terjadi kesalahan sistem: ' . $e->getMessage())
                         ->with('modal_edit_open', true);
        }
    }

    /**
     * Membatalkan transaksi penjualan produk (DESTROY).
     */
    public function destroy(PenjualanProduk $penjualanProduk)
    {
        DB::beginTransaction();
        try {
            $produk = $penjualanProduk->produk;
            // Kembalikan stok
            $produk->increment('stok', $penjualanProduk->jumlah); 

            // Catat log pengembalian stok
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