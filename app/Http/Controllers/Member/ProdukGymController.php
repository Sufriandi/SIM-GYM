<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\InfoRekening;
use App\Models\InfoQris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProdukGymController extends Controller
{
    /**
     * Nomor WA admin dari .env (dipakai di halaman cart).
     */
    public function getAdminNumber(): string
    {
        $waRaw = env('WHATSAPP_NUMBER', '');
        $waDigits = preg_replace('/\D+/', '', (string) $waRaw);

        if ($waDigits !== '') {
            if (Str::startsWith($waDigits, '0')) $waDigits = '62' . substr($waDigits, 1);
            if (Str::startsWith($waDigits, '8')) $waDigits = '62' . $waDigits;
        }

        return $waDigits ?: '6281234567890';
    }

    /**
     * INDEX: Katalog Produk (member) - sama behavior seperti guest.
     */
    public function index(Request $request)
    {
        $search   = $request->query('q');
        $kategori = $request->query('kategori');

        $query = Produk::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($kategori && $kategori !== 'all') {
            $query->where('kategori', $kategori);
        }

        // Sorting: stok ready dulu, lalu terbaru
        $products = $query->orderByRaw('CASE WHEN stok > 0 THEN 1 ELSE 0 END DESC')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Supaya pagination angka tidak melebar
        $products->onEachSide(1);

        $categories = Produk::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('member.produk_gym.index', [
            'pageTitle'  => 'Official Store',
            'products'   => $products,
            'categories' => $categories,
            'currentQ'   => $search,
            'currentKat' => $kategori,
        ]);
    }

    /**
     * SHOW: Detail Produk (member)
     */
    public function show(string $slug)
    {
        $id = (int) explode('-', $slug)[0];
        $product = Produk::findOrFail($id);

        $relatedProducts = Produk::where('kategori', $product->kategori)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('member.produk_gym.show', [
            'pageTitle'       => $product->nama,
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * CART: Halaman Keranjang (member) - TANPA biaya admin
     */
    public function cart()
    {
        $cart = Session::get('cart', []);

        [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

        $orderId = 'ORD-' . strtoupper(Str::random(9));

        $rekenings = InfoRekening::all();
        $qris = InfoQris::first();

        return view('member.produk_gym.cart', [
            'pageTitle'  => 'Checkout',
            'cart'       => $cart,
            'subtotal'   => $subtotal,
            'adminFee'   => $adminFee, // akan selalu 0
            'total'      => $total,
            'itemsCount' => $itemsCount,
            'orderId'    => $orderId,
            'rekenings'  => $rekenings,
            'qris'       => $qris,
            'waAdmin'    => $this->getAdminNumber(),
        ]);
    }

    /**
     * ADD TO CART
     */
    public function addToCart(Request $request, int $id)
    {
        $product = Produk::findOrFail($id);

        if ((int)$product->stok < 1) {
            return redirect()->back()->with('error', 'Maaf, stok produk ini habis.');
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $nextQty = (int)($cart[$id]['quantity'] ?? 1) + 1;
            if ($nextQty > (int)$product->stok) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk menambah jumlah.');
            }
            $cart[$id]['quantity'] = $nextQty;
        } else {
            $cart[$id] = [
                'id'       => $product->id,
                'name'     => $product->nama,
                'quantity' => 1,
                'price'    => (float)$product->harga,
                'photo'    => $product->foto,
                'category' => $product->kategori,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->route('member.produk_gym.cart')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * REMOVE FROM CART (AJAX-friendly)
     */
    public function removeFromCart(Request $request, int $id)
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
                'ok'          => true,
                'removed'     => $removed,
                'id'          => (string)$id,
                'subtotal'    => $subtotal,
                'admin_fee'   => $adminFee,
                'total'       => $total,
                'items_count' => $itemsCount,
                'message'     => $removed ? 'Item dihapus.' : 'Item tidak ditemukan.',
                'message_type'=> $removed ? 'success' : 'warning',
            ]);
        }

        return redirect()->route('member.produk_gym.cart')
            ->with($removed ? 'success' : 'error', $removed ? 'Item dihapus.' : 'Item tidak ditemukan.');
    }

    /**
     * UPDATE QTY (inc/dec/manual) - AJAX-friendly
     */
    public function updateCartQuantity(Request $request, int $id)
    {
        $cart = Session::get('cart', []);

        if (!isset($cart[$id])) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'], 404);
            }
            return redirect()->route('member.produk_gym.cart')->with('error', 'Item tidak ditemukan di keranjang.');
        }

        $product = Produk::find($id);
        if (!$product) {
            unset($cart[$id]);
            Session::put('cart', $cart);

            if ($request->expectsJson()) {
                [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);
                return response()->json([
                    'ok'          => true,
                    'removed'     => true,
                    'id'          => (string)$id,
                    'subtotal'    => $subtotal,
                    'admin_fee'   => $adminFee,
                    'total'       => $total,
                    'items_count' => $itemsCount,
                    'message'     => 'Produk sudah tidak tersedia. Item dihapus dari keranjang.',
                    'message_type'=> 'warning',
                ]);
            }

            return redirect()->route('member.produk_gym.cart')
                ->with('error', 'Produk sudah tidak tersedia. Item dihapus dari keranjang.');
        }

        $current = (int)($cart[$id]['quantity'] ?? 1);
        $op      = $request->input('op');  // inc | dec
        $qtyIn   = $request->input('qty'); // manual

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
                    'ok'          => true,
                    'removed'     => true,
                    'id'          => (string)$id,
                    'subtotal'    => $subtotal,
                    'admin_fee'   => $adminFee,
                    'total'       => $total,
                    'items_count' => $itemsCount,
                    'message'     => 'Item dihapus dari keranjang.',
                    'message_type'=> 'success',
                ]);
            }

            return redirect()->route('member.produk_gym.cart')->with('success', 'Item dihapus dari keranjang.');
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
                'ok'          => true,
                'removed'     => false,
                'id'          => (string)$id,
                'quantity'    => (int)$cart[$id]['quantity'],
                'line_total'  => $lineTotal,
                'subtotal'    => $subtotal,
                'admin_fee'   => $adminFee,
                'total'       => $total,
                'items_count' => $itemsCount,
                'message'     => $message,
                'message_type'=> $messageType,
            ]);
        }

        return redirect()->route('member.produk_gym.cart')->with($messageType, $message);
    }

    /**
     * TOTALS: TANPA biaya admin
     */
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

        $adminFee = 0;          // <<<< TANPA BIAYA ADMIN
        $total    = $subtotal;

        return [$subtotal, $adminFee, $total, $itemsCount];
    }
}
