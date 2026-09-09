<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\InfoRekening;
use App\Models\InfoQris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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
     * Diarahkan langsung ke laman login
     */
    public function cart()
    {
        return redirect()->route('login');
    }
    
    
    public function removeFromCart(Request $request, $id)
{
    $cart = Session::get('cart', []);
    $removed = isset($cart[$id]);

    if ($removed) {
        unset($cart[$id]);
        Session::put('cart', $cart);
    }

    if ($request->expectsJson()) {
        [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);
        return response()->json([
            'ok' => true,
            'removed' => $removed,
            'id' => (string)$id,
            'subtotal' => $subtotal,
            'admin_fee' => $adminFee,
            'total' => $total,
            'items_count' => $itemsCount,
            'message' => $removed ? 'Item dihapus.' : 'Item tidak ditemukan.',
            'message_type' => $removed ? 'success' : 'warning',
        ]);
    }

    return redirect()->route('guest.marketplace.cart')
        ->with($removed ? 'success' : 'error', $removed ? 'Item dihapus.' : 'Item tidak ditemukan.');
}

    /**
     * Alias method untuk addToCart
     */
    public function add(Request $request, $id)
    {
        return $this->addToCart($request, $id);
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
    public function updateCartQuantity(Request $request, $id)
{
    $cart = Session::get('cart', []);

    if (!isset($cart[$id])) {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'], 404);
        }
        return redirect()->route('guest.marketplace.cart')->with('error', 'Item tidak ditemukan di keranjang.');
    }

    $product = Produk::find($id);
    if (!$product) {
        unset($cart[$id]);
        Session::put('cart', $cart);

        if ($request->expectsJson()) {
            [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);
            return response()->json([
                'ok' => true,
                'removed' => true,
                'id' => (string)$id,
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'total' => $total,
                'items_count' => $itemsCount,
                'message' => 'Produk sudah tidak tersedia. Item dihapus dari keranjang.',
            ]);
        }

        return redirect()->route('guest.marketplace.cart')
            ->with('error', 'Produk sudah tidak tersedia. Item dihapus dari keranjang.');
    }

    $current = (int)($cart[$id]['quantity'] ?? 1);
    $op    = $request->input('op');   // inc | dec
    $qtyIn = $request->input('qty');  // manual

    if ($qtyIn !== null && $op === null) {
        $newQty = (int)$qtyIn;
    } else {
        if ($op === 'inc') $newQty = $current + 1;
        elseif ($op === 'dec') $newQty = $current - 1;
        else $newQty = $current;
    }

    if ($newQty <= 0) {
        unset($cart[$id]);
        Session::put('cart', $cart);

        if ($request->expectsJson()) {
            [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);
            return response()->json([
                'ok' => true,
                'removed' => true,
                'id' => (string)$id,
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'total' => $total,
                'items_count' => $itemsCount,
                'message' => 'Item dihapus dari keranjang.',
            ]);
        }

        return redirect()->route('guest.marketplace.cart')->with('success', 'Item dihapus dari keranjang.');
    }

    $stok = (int)($product->stok ?? 0);
    $message = 'Jumlah item diperbarui.';
    $messageType = 'success';

    if ($stok > 0 && $newQty > $stok) {
        $newQty = $stok;
        $message = 'Stok tidak mencukupi. Qty disesuaikan ke stok maksimum.';
        $messageType = 'warning';
    }

    $cart[$id]['quantity'] = max(1, $newQty);
    Session::put('cart', $cart);

    [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);
    $lineTotal = (float)($cart[$id]['price'] ?? 0) * (int)$cart[$id]['quantity'];

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'removed' => false,
            'id' => (string)$id,
            'quantity' => (int)$cart[$id]['quantity'],
            'line_total' => $lineTotal,
            'subtotal' => $subtotal,
            'admin_fee' => $adminFee,
            'total' => $total,
            'items_count' => $itemsCount,
            'message' => $message,
            'message_type' => $messageType,
        ]);
    }

    return redirect()->route('guest.marketplace.cart')->with($messageType, $message);
}

    private function computeCartTotals(array $cart): array
{
    $subtotal = 0;
    $itemsCount = 0;

    foreach ($cart as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);

        $itemsCount += $qty;
        $subtotal   += $qty * $price;
    }

    $adminFee = $itemsCount > 0 ? 2500 : 0;
    $total    = $subtotal + $adminFee;

    return [$subtotal, $adminFee, $total, $itemsCount];
}
}