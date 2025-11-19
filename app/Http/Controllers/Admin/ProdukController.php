<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Tambahkan ini
use Illuminate\Validation\Rule; // Tambahkan ini

class ProdukController extends Controller
{
    // Kategori yang Sesuai dengan ENUM di Migrasi
    private $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    /**
     * Menampilkan daftar semua produk (Index).
     */
    public function index()
    {
        $pageTitle = 'Data Produk';
        
        $produks = Produk::orderBy('created_at', 'desc')->paginate(10);

        // Catatan: Pastikan Anda menggunakan view 'admin.products.index' atau 'admin.produk.index' yang benar
        return view('admin.produk.index', compact('produks', 'pageTitle')); 
    }

    // Metode create() Dihapus karena menggunakan modal di index

    /**
     * Menyimpan produk baru ke database (Store).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            // PERBAIKAN: Gunakan 'produks' dan tambahkan validasi foto
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nama' => 'required|string|max:255|unique:produks,nama', 
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions), 
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0', 
            'deskripsi' => 'nullable|string',
        ]);
        
        // **PERBAIKAN KRUSIAL:** Jika validasi gagal, kembalikan ke halaman sebelumnya
        // dan set session 'modal_create_open' agar modal terbuka otomatis.
        if (is_null($validatedData)) {
            return back()->withInput()->withErrors($request->validator)->with('modal_create_open', true);
        }

        // 2. Upload Foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }

        // 3. Simpan Produk
        try {
            // Gabungkan data yang divalidasi dengan path foto
            Produk::create(array_merge($validatedData, [
                'foto' => $fotoPath,
            ]));
            
            return redirect()->route('admin.produk.index')->with('success', 'Produk baru berhasil ditambahkan.');

        } catch (\Exception $e) {
            // Hapus foto jika terjadi kegagalan DB setelah upload
            if ($fotoPath) {
                 Storage::disk('public')->delete($fotoPath);
            }
            // Tambahkan 'modal_create_open' untuk membuka modal error
            return back()->withInput()->with('error', 'Gagal menambahkan produk. Terjadi kesalahan sistem: ' . $e->getMessage())->with('modal_create_open', true);
        }
    }

    // Metode show() dan edit() tidak perlu diubah

    /**
     * Memperbarui produk yang ditentukan di database (Update).
     */
    public function update(Request $request, Produk $produk)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            // PERBAIKAN: Menggunakan Rule::unique dengan nama tabel 'produks'
            'nama' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('produks', 'nama')->ignore($produk->id)
            ],
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions),
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0', 
            'deskripsi' => 'nullable|string',
            // Menambahkan validasi foto
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // **PERBAIKAN KRUSIAL:** Jika validasi gagal, gunakan Error Bag 'updateProduct'
        if (is_null($validatedData)) {
            // Gunakan error bag 'updateProduct' untuk memicu display error di modal edit
            return redirect()->back()->withInput()->withErrors($request->validator, 'updateProduct'); 
        }
        
        // 2. Proses Perubahan Foto
        $fotoPath = $produk->foto; 
        
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }
            // Upload foto baru
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }
        
        // 3. Update Produk
        try {
            $produk->update(array_merge($validatedData, [
                'foto' => $fotoPath,
            ]));

            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            // Tambahkan Error Bag untuk membuka kembali modal
            return back()->withInput()->with('error', 'Gagal memperbarui produk. Terjadi kesalahan sistem: ' . $e->getMessage())->withErrors(['system_error' => 'Gagal memperbarui produk. Terjadi kesalahan sistem: ' . $e->getMessage()], 'updateProduct');
        }
    }

    /**
     * Menghapus produk dari database (Destroy).
     */
    public function destroy(Produk $produk)
    {
        // PERBAIKAN: Menggunakan nama relasi yang benar: penjualan() dan stok()
        if ($produk->penjualan()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat transaksi penjualan.');
        }

        if ($produk->stok()->exists()) {
             return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat perubahan stok.');
        }
        
        // Hapus Foto terkait jika aman
        if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
            Storage::disk('public')->delete($produk->foto);
        }

        // Hapus produk
        try {
            $produk->delete();
            return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus produk. Terjadi kesalahan sistem.');
        }
    }
}