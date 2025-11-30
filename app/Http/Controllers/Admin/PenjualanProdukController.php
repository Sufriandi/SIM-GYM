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
// use Illuminate\Support\Facades\View; // Tidak diperlukan karena tidak menggunakan View::share

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
        
        // --- LOGIKA FILTER SERVER-SIDE ---
        $search = $request->input('q'); // Menggunakan 'q' untuk pencarian Live Search di Blade
        $filterMetode = $request->input('metode_pembayaran', '');
        $filterProduk = $request->input('produk_id', '');

        // 1. Filter Pencarian (Q)
        if ($search) {
            $daftar_penjualan->where(function ($query) use ($search) {
                // Pencarian berdasarkan Nama Produk
                $query->whereHas('produk', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                // Pencarian berdasarkan Nama Member
                ->orWhereHas('member', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                // Pencarian berdasarkan Keterangan
                ->orWhere('keterangan', 'like', '%' . $search . '%');
            });
        }
        
        // 2. Filter Produk
        if ($request->filled('produk_id')) {
            $daftar_penjualan->where('produk_id', $request->input('produk_id'));
        }

        // 3. Filter Metode Pembayaran
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

        // Mendapatkan semua member untuk dropdown filter dan modal
        $members = User::where('role', 'member')->get(['id', 'name']);

        // Mengirimkan parameter filter kembali ke view
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
     */
    public function store(Request $request)
    {
        // ... (Kode Store Anda yang tidak berubah)
        // [Kode Store]
        $validatedData = $request->validate([
            'member_id' => 'nullable|exists:users,id',
            'metode_pembayaran' => 'required|in:' . implode(',', $this->metodePembayaran), 
            'keterangan' => 'nullable|string|max:1000',
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
            foreach ($itemsToProcess as $item) {
                $produk = $produkCollection->get($item['produk_id']);
                $jumlahBeli = (int) $item['jumlah'];

                if (!$produk || $jumlahBeli > $produk->stok) {
                    DB::rollBack();
                    $stokTersedia = $produk ? $produk->stok : '0';
                    return back()->withInput()->with('error', "Stok produk '{$produk->nama}' tidak cukup. Tersedia: {$stokTersedia}. Permintaan: {$jumlahBeli}.")
                                    ->with('modal_create_open', true);
                }
                
                $totalHargaItem = $produk->harga * $jumlahBeli;
                $totalKeseluruhanHarga += $totalHargaItem;
                
                $penjualan = PenjualanProduk::create([
                    'produk_id' => $produk->id,
                    'member_id' => $validatedData['member_id'] ?? null,
                    'jumlah' => $jumlahBeli,
                    'total_harga' => $totalHargaItem,
                    'metode_pembayaran' => $validatedData['metode_pembayaran'],
                    'keterangan' => $validatedData['keterangan'] ?? '-',
                    'tanggal_transaksi' => now(), 
                ]);

                $produk->decrement('stok', $jumlahBeli);
                
                $keteranganDisplay = 'Produk dijual.';
                $keteranganTeknis = $keteranganDisplay . " [ID PENJUALAN:{$penjualan->id}]";

                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => -$jumlahBeli, 
                    'tanggal' => now(),
                    'keterangan' => $keteranganTeknis, 
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

    // ... (Kode show, edit, update, destroy lainnya tidak berubah)
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

                $jumlahLogBaru = -$jumlahBaru; 
                $penandaRahasia = '[ID PENJUALAN:'.$penjualanProduk->id.']';
                
                $originalLog = StokProduk::where('produk_id', $produk->id)
                    ->where('keterangan', 'like', "%{$penandaRahasia}%")
                    ->first(); 

                if ($originalLog) {
                    $originalLog->update([
                        'jumlah' => $jumlahLogBaru, 
                        'tanggal' => now(), 
                        'keterangan' => 'Penjualan produk diperbarui.' . " {$penandaRahasia}", 
                    ]);
                } else {
                    StokProduk::create([
                        'produk_id' => $produk->id,
                        'jumlah' => -$selisih, 
                        'tanggal' => now(),
                        'keterangan' => 'Penyesuaian transaksi penjualan ID ' . $penjualanProduk->id . ' (Log asli tidak ditemukan)',
                    ]);
                }
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
        DB::beginTransaction();
        try {
            $jumlahJual = $penjualanProduk->jumlah; 
            $produk = $penjualanProduk->produk; 
            $produkNama = $produk->nama ?? 'Produk'; 

            $produk->increment('stok', $jumlahJual); 

            StokProduk::where('produk_id', $penjualanProduk->produk_id)
                     ->where('keterangan', 'like', '%[ID PENJUALAN:'.$penjualanProduk->id.']%')
                     ->delete();
            
            $penjualanProduk->delete();
            
            DB::commit();
            
            return redirect()->route('admin.penjualan_produk.index')
                             ->with('success', "Penjualan {$produkNama} telah dibatalkan. Stok telah dikembalikan.");
                             
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function history()
    {
        // ... (Kode history tidak berubah)
        $pageTitle = 'Riwayat Stok Produk';

        $riwayat_stok = StokProduk::with('produk')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc') 
            ->paginate(15);
        
        return view('admin.stok_produk.history', compact('riwayat_stok', 'pageTitle'));
    }

    public function showHistoryDetail(StokProduk $stokProduk)
    {
        $pageTitle = 'Detail Pergerakan Stok';
        
        $stokProduk->load('produk');

        return view('admin.stok_produk.history_detail', compact('stokProduk', 'pageTitle'));
    }
}