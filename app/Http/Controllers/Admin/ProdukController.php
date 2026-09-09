<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    private array $kategoriOptions = ['minuman', 'suplemen', 'lainnya'];

    public function index(Request $request)
    {
        $pageTitle = 'Daftar Produk';

        $search = $request->query('q');
        $filterKategori = $request->query('kategori');

        $produks = Produk::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->when(
                $filterKategori && in_array($filterKategori, $this->kategoriOptions, true),
                fn($query) => $query->where('kategori', $filterKategori)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $kategoriOptions = $this->kategoriOptions;

        return view('admin.produk.index', compact('produks', 'pageTitle', 'kategoriOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'nama'      => ['required', 'string', 'max:255', Rule::unique('produks', 'nama')],
            'kategori'  => ['required', Rule::in($this->kategoriOptions)],
            'harga'     => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = ImageUploadService::uploadAsWebp($request->file('foto'), 'photos/produks', null, 1200, 85);
        }

        try {
            Produk::create([
                ...$validated,
                'foto' => $fotoPath,
                // stok tidak di-set; default DB = 0
            ]);

            return redirect()
                ->route('admin.produk.index')
                ->with('success', 'Produk baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            if ($fotoPath) {
                ImageUploadService::delete($fotoPath);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk. Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('produks', 'nama')->ignore($produk->id),
            ],
            'kategori'  => ['required', Rule::in($this->kategoriOptions)],
            'harga'     => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $fotoPath = $produk->foto;

        if ($request->hasFile('foto')) {
            $fotoPath = ImageUploadService::uploadAsWebp($request->file('foto'), 'photos/produks', $produk->foto, 1200, 85);
        }

        $produk->update([
            ...$validated,
            'foto' => $fotoPath,
        ]);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        // Jika Anda tetap ingin blokir hapus bila ada relasi:
        if ($produk->penjualan()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat transaksi.');
        }
        if ($produk->stok()->exists()) {
            return back()->with('error', 'Gagal menghapus produk. Produk ini sudah memiliki riwayat perubahan stok.');
        }

        // Soft delete: jangan hapus file foto agar bisa restore
        $produk->delete();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
