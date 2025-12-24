<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\InfoRekening;
use App\Models\InfoQris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MarketplaceController extends Controller
{
    /**
     * INDEX: Katalog Produk
     */
    public function index(Request $request)
    {
        // 1. Tangkap Filter
        $search   = $request->query('q');
        $kategori = $request->query('kategori');

        $query = Produk::query();

        // 2. Logic Search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // 3. Logic Kategori
        if ($kategori && $kategori !== 'all') {
            $query->where('kategori', $kategori);
        }

        // 4. Default Sort: Stok Ready Dulu, Lalu Terbaru
        // Kita kunci sortingnya disini, user tidak bisa ubah via UI
        $products = $query->orderByRaw('CASE WHEN stok > 0 THEN 1 ELSE 0 END DESC')
                          ->latest()
                          ->paginate(12) // 12 Produk per halaman
                          ->withQueryString(); 
                          // onEachSide(1) membatasi jumlah angka pagination biar ga melebar
        $products->onEachSide(1);

        // 5. Data Kategori untuk Dropdown
        $categories = Produk::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('marketplace.index', [
            'pageTitle'  => 'Official Store',
            'products'   => $products,
            'categories' => $categories,
            'currentQ'   => $search,
            'currentKat' => $kategori
        ]);
    }

    /**
     * SHOW: Detail Produk
     */
    public function show($slug)
    {
        $id = (int) explode('-', $slug)[0];
        $product = Produk::findOrFail($id);

        $relatedProducts = Produk::where('kategori', $product->kategori)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('marketplace.show', [
            'pageTitle' => $product->nama,
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    /**
     * CART: Halaman Keranjang
     */
    public function cart()
    {
        $cart = Session::get('cart', []);
        
        // Hitung Subtotal
        $subtotal = 0;
        foreach($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Total (Bisa ditambah biaya admin/unik jika mau, saat ini flat)
        $total = $subtotal; 

        // Generate Order ID Dummy (Agar terlihat seperti real transaction)
        $orderId = 'TRX-' . strtoupper(uniqid()) . '-' . date('dmY');

        // Ambil Data Pembayaran
        $rekenings = InfoRekening::all();
        $qris = InfoQris::first();

        return view('marketplace.cart', [
            'pageTitle' => 'Checkout Secure',
            'cart'      => $cart,
            'subtotal'  => $subtotal,
            'total'     => $total,
            'orderId'   => $orderId, // ID Transaksi
            'rekenings' => $rekenings,
            'qris'      => $qris
        ]);
    }

    /**
     * Hapus Item
     */
    public function removeFromCart($id)
    {
        $cart = Session::get('cart');
        if(isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Item dihapus.');
    }
    public function addToCart(Request $request, $id)
    {
        $product = Produk::findOrFail($id);

        // Validasi Stok di Database
        if ($product->stok < 1) {
            return redirect()->back()->with('error', 'Maaf, stok produk ini habis.');
        }

        $cart = Session::get('cart', []);

        // Cek jika produk sudah ada di cart
        if(isset($cart[$id])) {
            // Validasi apakah menambah qty akan melebihi stok tersedia
            if($cart[$id]['quantity'] + 1 > $product->stok) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk menambah jumlah.');
            }
            $cart[$id]['quantity']++;
        } else {
            // Jika produk belum ada, buat item baru
            $cart[$id] = [
                "id" => $product->id,
                "name" => $product->nama,
                "quantity" => 1,
                "price" => $product->harga,
                "photo" => $product->foto,
                "category" => $product->kategori
            ];
        }

        Session::put('cart', $cart);
        
        // Redirect ke halaman keranjang (UX Enterprise)
        return redirect()->route('guest.marketplace.cart')->with('success', 'Produk berhasil ditambahkan!');
    }
}