<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    private $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    /**
     * Menampilkan daftar semua produk (Index), dengan fitur pencarian dan filter.
     */
    public function index(Request $request) // <--- Tambahkan Request di sini
    {
        $pageTitle = 'Daftar Produk';

        // --- START: LOGIKA PENCARIAN DAN FILTER BARU ---
        $search = $request->query('search');
        $filterKategori = $request->query('kategori');

        $produks = Produk::query()
            // Logika Pencarian: Berdasarkan nama atau deskripsi
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            // Logika Filter: Berdasarkan kategori
            ->when($filterKategori && in_array($filterKategori, $this->kategoriOptions), function ($query) use ($filterKategori) {
                // Gunakan where() jika $filterKategori ada dan valid
                $query->where('kategori', $filterKategori);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString(); // Agar parameter query tetap ada saat pindah halaman

        // --- END: LOGIKA PENCARIAN DAN FILTER BARU ---
        
        $kategoriOptions = $this->kategoriOptions;

        return view('admin.produk.index', compact('produks', 'pageTitle', 'kategoriOptions')); 
    }

    /**
     * Menyimpan produk baru ke database (Store).
     */
    public function store(Request $request)
    {
        // ... (Fungsi store tidak berubah) ...
        $validatedData = $request->validate([
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nama' => 'required|string|max:255', 
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions), 
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);
        
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }

        try {
            $produk = Produk::create(array_merge($validatedData, [
                'foto' => $fotoPath,
                'stok' => 0,
            ]));
            
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
        // ... (Fungsi update tidak berubah) ...
        $validatedData = $request->validate([
            'nama' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('produks', 'nama')->ignore($produk->id)
            ],
            'kategori' => 'required|in:' . implode(',', $this->kategoriOptions),
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $fotoPath = $produk->foto; 
        
        if ($request->hasFile('foto')) {
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }
            $fotoPath = $request->file('foto')->store('photos/produks', 'public');
        }
        
        try {
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
        // ... (Fungsi destroy tidak berubah) ...
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