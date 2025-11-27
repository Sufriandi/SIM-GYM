<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use App\Models\StokProduk;
use App\Models\User;
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
    public function index(Request $request)
    {
        $pageTitle = 'Penjualan Produk';
        
        $daftar_penjualan = PenjualanProduk::with(['produk', 'member']);
        
        // --- LOGIKA FILTER SERVER-SIDE (Dipertahankan untuk filter dropdown) ---
        
        // Filter Produk
        if ($request->filled('produk_id')) {
            $daftar_penjualan->where('produk_id', $request->input('produk_id'));
        }

        // Filter Metode Pembayaran
        if ($request->filled('metode_pembayaran')) {
            $daftar_penjualan->where('metode_pembayaran', $request->input('metode_pembayaran'));
        }
        
        $daftar_penjualan = $daftar_penjualan
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Data untuk Modal
        $produks = Produk::orderBy('nama')->get(); 
        $metodePembayaran = $this->metodePembayaran;

        // Mendapatkan semua member untuk dropdown filter
        $members = User::where('role', 'member')->get(['id', 'name']);

        // Data filter saat ini, untuk mengisi ulang form filter
        $search = $request->input('search', '');
        $filterMetode = $request->input('metode_pembayaran', '');
        $filterProduk = $request->input('produk_id', '');


        return view('admin.penjualan_produk.index', compact(
            'daftar_penjualan', 
            'pageTitle', 
            'produks', 
            'metodePembayaran',
            'members',
            'search',
            'filterMetode',
            'filterProduk'
        ));
    }

    /**
     * Memproses dan menyimpan transaksi penjualan produk baru (STORE).
     * Menerima array produk untuk Multi-Item Transaction.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Data Transaksi Utama
        $validatedData = $request->validate([
            'member_id' => 'nullable|exists:users,id',
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
            
            // Validasi untuk Array Produk (Minimal 1 item)
            'produks' => 'required|array|min:1',
            'produks.*.produk_id' => 'required|exists:produks,id',
            'produks.*.jumlah' => 'required|integer|min:1',
        ]);

        $totalKeseluruhanHarga = 0;
        $itemsToProcess = $validatedData['produks'];
        $produkIds = array_column($itemsToProcess, 'produk_id');
        $produkCollection = Produk::whereIn('id', $produkIds)->get()->keyBy('id');

        DB::beginTransaction();

        try {
            // 2. Loop dan Proses Setiap Item Produk
            foreach ($itemsToProcess as $item) {
                $produk = $produkCollection->get($item['produk_id']);
                $jumlahBeli = (int) $item['jumlah'];

                // Cek Ketersediaan Stok (Penting!)
                if (!$produk || $jumlahBeli > $produk->stok) {
                    DB::rollBack();
                    $stokTersedia = $produk ? $produk->stok : '0';
                    return back()->withInput()->with('error', "Stok produk '{$produk->nama}' tidak cukup. Tersedia: {$stokTersedia}. Permintaan: {$jumlahBeli}.")
                                 ->with('modal_create_open', true);
                }
                
                $totalHargaItem = $produk->harga * $jumlahBeli;
                $totalKeseluruhanHarga += $totalHargaItem;
                
                // 3. Catat Transaksi Penjualan per Item (Tabel penjualan_produk)
                PenjualanProduk::create([
                    'produk_id' => $produk->id,
                    'member_id' => $validatedData['member_id'] ?? null,
                    'jumlah' => $jumlahBeli,
                    'total_harga' => $totalHargaItem, // Total harga per item
                    'metode_pembayaran' => $validatedData['metode_pembayaran'],
                    'keterangan' => $validatedData['keterangan'] ?? '-',
                    'tanggal_transaksi' => now(), 
                ]);

                // 4. Kurangi Stok pada Tabel Produk
                $produk->decrement('stok', $jumlahBeli);

                // 5. Catat perubahan ini ke Tabel StokProduk (Log OUT)
                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => -$jumlahBeli, 
                    'tanggal' => now(),
                    'keterangan' => 'Penjualan multi-item dicatat.',
                ]);
            }
            
            DB::commit();

            return redirect()->route('admin.penjualan_produk.index')
                ->with('success', "Transaksi multi-item senilai Rp " . number_format($totalKeseluruhanHarga, 0, ',', '.') . " berhasil dicatat.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Gagal mencatat penjualan. Terjadi kesalahan sistem: ' . $e->getMessage())
                         ->with('modal_create_open', true);
        }
    }
    
    // METHOD LAINNYA TIDAK BERUBAH
    public function show(PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.index');
    }

    public function edit(PenjualanProduk $penjualanProduk)
    {
        return redirect()->route('admin.penjualan_produk.index');
    }

    public function update(Request $request, PenjualanProduk $penjualanProduk)
    {
        // ... (Logika update tetap sama) ...
        $validatedData = $request->validate([
            'id' => 'required|exists:penjualan_produks,id', 
            'jumlah' => 'required|integer|min:1',
            'member_id' => 'nullable|exists:users,id', 
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
        ]);
        
        $produk = $penjualanProduk->produk;
        $jumlahLama = $penjualanProduk->jumlah;
        $jumlahBaru = (int) $validatedData['jumlah'];
        $selisih = $jumlahBaru - $jumlahLama;
        
        $stokMaksimum = $produk->stok + $jumlahLama;

        if ($jumlahBaru > $stokMaksimum) {
            return back()->withInput()->with('error', "Stok {$produk->nama} tidak cukup untuk perubahan ini.")
                         ->with('modal_edit_open', true);
        }

        DB::beginTransaction();

        try {
            $totalHargaBaru = $produk->harga * $jumlahBaru;
            
            $penjualanProduk->update([
                'member_id' => $validatedData['member_id'] ?? null,
                'jumlah' => $jumlahBaru,
                'total_harga' => $totalHargaBaru,
                'metode_pembayaran' => $validatedData['metode_pembayaran'],
                'keterangan' => $validatedData['keterangan'],
            ]);
            
            if ($selisih != 0) {
                DB::table('produks')->where('id', $produk->id)->decrement('stok', $selisih);

                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => -$selisih, 
                    'tanggal' => now(),
                    'keterangan' => 'Penyesuaian transaksi penjualan ID ' . $penjualanProduk->id,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.penjualan_produk.index')->with('success', "Transaksi penjualan berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui penjualan. Terjadi kesalahan sistem: ' . $e->getMessage())
                         ->with('modal_edit_open', true);
        }
    }

    public function destroy(PenjualanProduk $penjualanProduk)
    {
        // ... (Logika destroy tetap sama) ...
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