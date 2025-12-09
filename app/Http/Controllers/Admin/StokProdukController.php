<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class StokProdukController extends Controller
{
    /**
     * Menampilkan daftar stok produk.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Daftar Stok Produk';

        // Mengambil parameter filter dari request
        $search = $request->get('q');
        $filterKategori = $request->get('kategori');
        $sort = $request->get('sort', 'newest');
        $perPage = 15;

        // Query Awal: Ambil semua Produk (yang memiliki stok)
        $query = Produk::query();
        
        // --- 1. Terapkan Filter Pencarian (Search/Q) ---
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%');
            });
        }

        // --- 2. Terapkan Filter Kategori ---
        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }

        // --- 3. Terapkan Urutan (Sort) ---
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('nama', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('nama', 'desc');
                break;
            case 'stok_asc':
                $query->orderBy('stok', 'asc');
                break;
            case 'stok_desc':
                $query->orderBy('stok', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Dapatkan Hasil dengan Pagination
        $produks = $query->paginate($perPage)->withQueryString();

        // Mengambil semua produk untuk dropdown di modal Tambah Stok
        $allProduk = Produk::select('id', 'nama', 'stok', 'kategori', 'deskripsi')->orderBy('nama')->get();
        
        // Menghitung total entri riwayat stok untuk tombol 'Riwayat'
        $totalRiwayatStok = StokProduk::count(); 
        View::share('totalRiwayatStok', $totalRiwayatStok);

        // Mengirimkan parameter filter kembali ke view agar Live Search dan Filter Lanjutan terisi dengan benar
        return view('admin.stok_produk.index', compact('pageTitle', 'produks', 'allProduk'));
        // Catatan: $search, $filterKategori, dan $sort sudah otomatis tersedia di view karena ada di $request.
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

        $produk = Produk::findOrFail($validated['produk_id']);
        $jumlah = (int)$validated['stok'];
        $successMessage = '';

        // --- LOGIKA UTAMA PERBAIKAN: Cek dan Update Log Inisialisasi NOL ---
        $initialLog = StokProduk::where('produk_id', $produk->id)
            ->where('jumlah', 0)
            ->where('keterangan', 'Inisialisasi produk baru.') 
            ->orderBy('tanggal', 'desc')
            ->first();

        DB::beginTransaction();

        try {
            // 1. Tambah stok di tabel produk
            $produk->increment('stok', $jumlah);

            // 2. Cek apakah log inisialisasi (jumlah 0) ditemukan
            if ($initialLog) {
                // UPDATE log yang sudah ada (mengubah status dari PENYESUAIAN (0) menjadi MASUK)
                $initialLog->update([
                    'jumlah' => $jumlah, 
                    'tanggal' => now(),
                    'keterangan' => $validated['keterangan'] ?? 'Stok awal dimasukkan.' // Keterangan yang menunjukkan stok masuk
                ]);
                $successMessage = "Stok awal {$produk->nama} berhasil diupdate dan ditambahkan sebanyak {$jumlah} unit.";

            } else {
                // CREATE log baru (Standar Penambahan Stok)
                StokProduk::create([
                    'produk_id' => $produk->id,
                    'jumlah' => $jumlah,
                    'tanggal' => now(),
                    'keterangan' => $validated['keterangan'] ?? 'Penambahan stok.'
                ]);
                $successMessage = "Stok {$produk->nama} berhasil ditambahkan sebanyak {$jumlah} unit.";
            }

            DB::commit();

            return redirect()->route('admin.stok_produk.index')
                ->with('success', $successMessage);
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
        // Karena modal edit sekarang di halaman history, kita kembalikan ke index.
        return redirect()->route('admin.stok_produk.index');
    }

    /**
     * Proses Penyesuaian Stok Akhir (Dipicu oleh tombol Edit di baris riwayat atau index).
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'stok_baru' => 'required|integer', // Nilai stok akhir yang diinput user
            'keterangan' => 'required|string|max:255' // Dibuat WAJIB diisi karena ini penyesuaian manual
        ]);
        
        $produk = Produk::findOrFail($id); 
        $jumlahBaru = (int)$validated['stok_baru'];
        $jumlahLama = $produk->stok;

        $selisih = $jumlahBaru - $jumlahLama; // Selisih bisa positif (masuk) atau negatif (keluar)
        
        // Menentukan rute kembali. Jika ada 'history_back' di request, kembali ke history.
        $redirectRoute = $request->has('history_back') ? 'admin.stok_produk.history' : 'admin.stok_produk.index';

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
                    'keterangan' => $validated['keterangan']
                ]);
            }

            DB::commit();

            return redirect()->route($redirectRoute)
                ->with('success', "Stok {$produk->nama} berhasil disesuaikan dari {$jumlahLama} menjadi {$jumlahBaru} unit (Perubahan: {$selisih}).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui stok: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus produk master beserta stoknya. (Destroy untuk Produk Master)
     * Ini adalah fungsi asli yang sesuai dengan Route::resource.
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

    // ===============================================
    // METHOD UNTUK RIWAYAT STOK
    // ===============================================

    /**
     * Menampilkan riwayat pergerakan stok (StokProduk log).
     */
    public function history()
    {
        $pageTitle = 'Riwayat Stok Produk';

        // Order berdasarkan tanggal dan ID terbaru (paling baru di atas)
        $riwayat_stok = StokProduk::with('produk')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc') 
            ->paginate(15);
        
        return view('admin.stok_produk.history', compact('riwayat_stok', 'pageTitle'));
    }

    /**
     * Menampilkan detail satu log pergerakan stok.
     * @param \App\Models\StokProduk $stokProduk
     */
    public function showHistoryDetail(StokProduk $stokProduk)
    {
        $pageTitle = 'Detail Pergerakan Stok';
        
        // Load data produk
        $stokProduk->load('produk');

        return view('admin.stok_produk.history_detail', compact('stokProduk', 'pageTitle'));
    }
}