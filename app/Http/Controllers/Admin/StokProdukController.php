<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokProdukController extends Controller
{
    /**
     * Menampilkan daftar stok produk.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Daftar Stok Produk';

        // Mengambil semua produk untuk dropdown di modal 
        $allProduk = Produk::select('id', 'nama', 'stok', 'kategori', 'deskripsi')->orderBy('nama')->get();

        $query = Produk::query();
        
        // Cek apakah ada pencarian (untuk passing ke Blade agar tombol reset muncul)
        $search = $request->input('search');

        // Paginasi tetap dilakukan di sisi server untuk data awal.
        // Logika pencarian di sisi server DIHAPUS agar bisa menggunakan pencarian Alpine.js (Client-side)
        // Note: Karena menggunakan client-side search, hasil pencarian hanya akan memfilter data yang sudah dimuat di halaman saat ini.

        $produks = $query->orderBy('nama')->paginate(15);

        return view('admin.stok_produk.index', compact('pageTitle', 'produks', 'allProduk', 'search'));
    }

    /**
     * Mengarahkan / Mengabaikan method create (karena menggunakan modal).
     */
    public function create()
    {
        return redirect()->route('admin.stok_produk.index');
    }

    /**
     * Proses penambahan stok (Stok Masuk) dari modal/form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'stok' => 'required|integer|min:1', 
            'keterangan' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $produk = Produk::findOrFail($validated['produk_id']);
            $jumlah = (int)$validated['stok'];
            
            // Tambah stok di tabel produk
            $produk->increment('stok', $jumlah);

            // Catat log stok
            StokProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => $jumlah, // Jumlah positif
                'tanggal' => now(),
                'keterangan' => $validated['keterangan'] ?? 'Penambahan stok.'
            ]);

            DB::commit();

            return redirect()->route('admin.stok_produk.index')
                ->with('success', "Stok {$produk->nama} berhasil ditambahkan sebanyak {$jumlah} unit.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal menambah stok: ' . $e->getMessage())
                ->with('modal_stok_open', true); // Membuka kembali modal stok jika gagal
        }
    }

    /**
     * Mengarahkan / Mengabaikan method show.
     */
    public function show($id)
    {
        return redirect()->route('admin.stok_produk.index');
    }

    /**
     * Mengarahkan / Mengabaikan method edit (karena menggunakan modal).
     */
    public function edit($id)
    {
        return redirect()->route('admin.stok_produk.index');
    }

    /**
     * Proses Penyesuaian Stok Akhir (Dipicu oleh tombol Edit di baris).
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'stok_baru' => 'required|integer', // Nilai stok akhir yang diinput user
            'keterangan' => 'nullable|string|max:255' // Dibuat required karena ini penyesuaian manual
        ]);
        
        $produk = Produk::findOrFail($id); 
        $jumlahBaru = (int)$validated['stok_baru'];
        $jumlahLama = $produk->stok;

        $selisih = $jumlahBaru - $jumlahLama; // Selisih bisa positif (masuk) atau negatif (keluar)

        DB::beginTransaction();

        try {
            // Update stok produk
            $produk->update([
                'stok' => $jumlahBaru
            ]);

            // Log perubahan stok HANYA jika ada perubahan
            if ($selisih != 0) {
                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => $selisih, 
                    'tanggal' => now(),
                    'keterangan' => $validated['keterangan'] ?? 'Penyesuaian stok manual.'
                ]);
            }

            DB::commit();

            return redirect()->route('admin.stok_produk.index')
                ->with('success', "Stok {$produk->nama} berhasil disesuaikan dari {$jumlahLama} menjadi {$jumlahBaru} unit (Perubahan: {$selisih}).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui stok: ' . $e->getMessage())
                ->with('modal_edit_open', true); // Membuka kembali modal edit jika gagal
        }
    }

    /**
     * Menghapus produk beserta stoknya.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        DB::beginTransaction();

        try {
            // Catat log stok dikurangi penuh sebelum dihapus permanen
            StokProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => -$produk->stok, 
                'tanggal' => now(),
                'keterangan' => 'Produk dihapus.'
            ]);

            // Hapus produk
            $produk->delete();

            DB::commit();

            return back()->with('success', 'Produk dan stok terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus produk.');
        }
    }
}