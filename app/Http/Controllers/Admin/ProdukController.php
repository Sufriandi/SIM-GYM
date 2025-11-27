<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokProduk; // Digunakan untuk mencatat stok awal
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator; // Tambahkan ini jika validasi di-handle manual

class ProdukController extends Controller
{
    private $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    /**
     * Menampilkan daftar semua produk (Index).
     */
    public function index()
    {
        $pageTitle = 'Daftar Produk';
        
        $produks = Produk::orderBy('created_at', 'desc')->paginate(15);
        $kategoriOptions = $this->kategoriOptions;

        return view('admin.produk.index', compact('produks', 'pageTitle', 'kategoriOptions')); 
    }

    /**
     * Menyimpan produk baru ke database (Store).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (HILANGKAN 'stok')
        $validatedData = $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nama' => 'required|string|max:255', 
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions), 
            'harga' => 'required|numeric|min:0',
            // 'stok' DIHAPUS DARI SINI
            'deskripsi' => 'nullable|string',
        ]);
        
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }

        try {
            // 2. Stok diinisialisasi 0 saat membuat produk baru
            $produk = Produk::create(array_merge($validatedData, [
                'foto' => $fotoPath,
                'stok' => 0, // <-- SET STOK AWAL KE 0
            ]));
            
            // 3. Catat stok awal 0 ke log StokProduk
            StokProduk::create([
                'produk_id' => $produk->id,
                'jumlah' => 0,
                'tanggal' => now(),
                'keterangan' => 'Inisialisasi produk baru.',
            ]);

            return redirect()->route('admin.produk.index')->with('success', 'Produk baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            return back()->withInput()->with('error', 'Gagal menambahkan produk. Terjadi kesalahan sistem: ' . $e->getMessage())->with('modal_create_open', true);
        }
    }

    /**
     * Memperbarui produk yang ditentukan di database (Update).
     */
    public function update(Request $request, Produk $produk)
    {
        // 1. Validasi Input (HILANGKAN 'stok')
        $validatedData = $request->validate([
            'nama' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('produks', 'nama')->ignore($produk->id)
            ],
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions),
            'harga' => 'required|numeric|min:0',
            // 'stok' DIHAPUS
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Cek jika validasi gagal
        // Note: Error handling sudah dilakukan secara default oleh Laravel pada validate()
        
        $fotoPath = $produk->foto; 
        
        if ($request->hasFile('foto')) {
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }
        
        try {
            // 2. Update Produk (Pertahankan nilai stok lama)
            $produk->update(array_merge($validatedData, [
                'foto' => $fotoPath,
                'stok' => $produk->stok, 
            ]));

            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui produk. Terjadi kesalahan sistem: ' . $e->getMessage())->withErrors(['system_error' => 'Gagal memperbarui produk. Terjadi kesalahan sistem: ' . $e->getMessage()], 'updateProduct');
        }
    }

    /**
     * Menghapus produk dari database (Destroy).
     */
    public function destroy(Produk $produk)
    {
        // ... (Kode destroy tetap, asumsikan relasi penjualan() dan stok() sudah ada di Model Produk) ...
        if ($produk->penjualan()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat transaksi penjualan.');
        }

        if ($produk->stok()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat perubahan stok.');
        }
        
        if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
            Storage::disk('public')->delete($produk->foto);
        }

        try {
            $produk->delete();
            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus produk. Terjadi kesalahan sistem.');
        }
    }
}