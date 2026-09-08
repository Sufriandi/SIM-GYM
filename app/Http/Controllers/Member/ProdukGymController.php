<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\InfoRekening;
use App\Models\InfoQris;
use App\Models\User;
use App\Models\MemberCart;
use App\Models\MemberCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProdukGymController extends Controller
{
    /**
     * INDEX: Katalog Produk (Member)
     * - Search: q
     * - Filter: kategori (all|minuman|suplemen|lainnya)
     * - Sort: popular|newest|price_low|price_high
     * - Default: stok ready dulu, lalu terbaru
     */
    public function index(Request $request)
    {
        $search   = trim((string) $request->query('q', ''));
        $kategori = (string) $request->query('kategori', 'all');
        $sort     = (string) $request->query('sort', 'popular');

        $query = Produk::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($kategori !== '' && $kategori !== 'all') {
            $query->where('kategori', $kategori);
        }

        // Always prioritize in-stock first
        $query->orderByRaw('CASE WHEN stok > 0 THEN 0 ELSE 1 END ASC');

        // Sorting
        switch ($sort) {
            case 'price_low':
                $query->orderBy('harga', 'asc')->orderBy('created_at', 'desc');
                break;

            case 'price_high':
                $query->orderBy('harga', 'desc')->orderBy('created_at', 'desc');
                break;

            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;

            case 'popular':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $products->onEachSide(1);

        $categories = Produk::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('member.produk_gym.index', [
            'pageTitle'    => 'Marketplace',
            'pageSubtitle' => 'Produk resmi untuk member',
            'products'     => $products,
            'categories'   => $categories,
            'currentQ'     => $search,
            'currentKat'   => $kategori,
            'currentSort'  => $sort,
        ]);
    }

    /**
     * SHOW: Detail Produk (Member)
     * slug format: {id}-{slugNama}
     */
    public function show(string $slug)
    {
        $id = (int) explode('-', $slug)[0];
        $product = Produk::findOrFail($id);

        $relatedProducts = Produk::query()
            ->where('kategori', $product->kategori)
            ->where('id', '!=', $product->id)
            ->orderByRaw('CASE WHEN stok > 0 THEN 0 ELSE 1 END ASC')
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
     * CART: Keranjang & Checkout (Member)
     * - Reconcile cart with DB (stok, harga, nama, dll)
     * - Sync hasil reconcile ke DB agar persistent cart tetap akurat
     */
    public function cart()
    {
        $cart = Session::get('cart', []);
        $changed = false;

        if (!empty($cart)) {
            foreach ($cart as $id => $item) {
                $pid = (int) $id;

                $product = Produk::find($pid);

                // Produk sudah tidak ada -> hapus
                if (!$product) {
                    unset($cart[$id]);
                    $changed = true;
                    continue;
                }

                $stokDb = (int) ($product->stok ?? 0);

                // Stok habis -> hapus
                if ($stokDb < 1) {
                    unset($cart[$id]);
                    $changed = true;
                    continue;
                }

                $qty = (int) ($item['quantity'] ?? 1);

                // Clamp qty
                if ($qty < 1) $qty = 1;
                if ($qty > $stokDb) {
                    $qty = $stokDb;
                    $changed = true;
                }

                // Refresh snapshot
                $cart[$id] = [
                    'id'       => (int) $product->id,
                    'name'     => (string) $product->nama,
                    'quantity' => (int) $qty,
                    'price'    => (float) $product->harga,
                    'photo'    => $product->foto,
                    'category' => $product->kategori,
                ];
            }

            if ($changed) {
                Session::put('cart', $cart);
            }
        }

        // Sinkron hasil reconcile ke DB (agar persistent cart ikut bersih)
        $this->syncSessionCartToDb($cart);

        [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

        $orderId = 'TP-' . now()->format('ymd') . '-' . strtoupper(
            Str::of(Str::random(12))->replaceMatches('/[^A-Za-z]/', '')->substr(0, 6)
        );

        $rekenings = InfoRekening::query()->orderBy('nama_bank')->get();
        $qris = InfoQris::query()->first();

        $waAdminRaw = User::query()
            ->where('role', 'admin')
            ->whereNotNull('no_hp')
            ->orderBy('id', 'asc')
            ->value('no_hp');

        $merchantName = 'BETA GYM';
        $merchantLogo = asset('images/logo.webp');

        return view('member.produk_gym.cart', [
            'pageTitle'    => 'Checkout',
            'cart'         => $cart,
            'subtotal'     => $subtotal,
            'adminFee'     => $adminFee,
            'total'        => $total,
            'itemsCount'   => $itemsCount,
            'orderId'      => $orderId,
            'rekenings'    => $rekenings,
            'qris'         => $qris,
            'waAdmin'      => $waAdminRaw ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $waAdminRaw)) : null,
            'merchantName' => $merchantName,
            'merchantLogo' => $merchantLogo,
        ]);
    }

    /**
     * ADD TO CART (Member)
     * - tetap di halaman produk (tidak redirect ke cart)
     * - simpan ke session + DB (persistent)
     */
    public function addToCart(Request $request, int $id)
    {
        $product = Produk::findOrFail($id);

        $stok = (int) ($product->stok ?? 0);
        if ($stok < 1) {
            return back()->with('error', 'Maaf, stok produk ini habis.');
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $newQty = (int) ($cart[$id]['quantity'] ?? 1) + 1;

            if ($newQty > $stok) {
                return back()->with('error', 'Stok tidak mencukupi untuk menambah jumlah.');
            }

            $cart[$id]['quantity'] = $newQty;
        } else {
            $cart[$id] = [
                'id'       => (int) $product->id,
                'name'     => (string) ($product->nama ?? ''),
                'quantity' => 1,
                'price'    => (float) ($product->harga ?? 0),
                'photo'    => $product->foto,
                'category' => $product->kategori,
            ];
        }

        // refresh snapshot selalu
        $cart[$id]['price']    = (float) ($product->harga ?? 0);
        $cart[$id]['name']     = (string) ($product->nama ?? '');
        $cart[$id]['photo']    = $product->foto;
        $cart[$id]['category'] = $product->kategori;

        Session::put('cart', $cart);

        // persist ke DB
        $this->upsertCartItemToDb($product, (int) $cart[$id]['quantity']);

        // tetap di halaman produk
        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * UPDATE QTY (AJAX/POST) (Member)
     * - op: inc|dec, atau qty manual
     * - Sync session + DB
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

        // Produk sudah tidak ada -> hapus
        if (!$product) {
            unset($cart[$id]);
            Session::put('cart', $cart);

            $this->deleteCartItemFromDb($id);

            [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'           => true,
                    'removed'      => true,
                    'id'           => (string) $id,
                    'subtotal'     => $subtotal,
                    'admin_fee'    => $adminFee,
                    'total'        => $total,
                    'items_count'  => $itemsCount,
                    'message'      => 'Produk sudah tidak tersedia. Item dihapus dari keranjang.',
                    'message_type' => 'warning',
                ]);
            }

            return redirect()->route('member.produk_gym.cart')
                ->with('error', 'Produk sudah tidak tersedia. Item dihapus dari keranjang.');
        }

        $stok = (int) ($product->stok ?? 0);

        // Stok habis -> hapus
        if ($stok < 1) {
            unset($cart[$id]);
            Session::put('cart', $cart);

            $this->deleteCartItemFromDb($id);

            [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'           => true,
                    'removed'      => true,
                    'id'           => (string) $id,
                    'subtotal'     => $subtotal,
                    'admin_fee'    => $adminFee,
                    'total'        => $total,
                    'items_count'  => $itemsCount,
                    'message'      => 'Stok produk habis. Item dihapus dari keranjang.',
                    'message_type' => 'warning',
                ]);
            }

            return redirect()->route('member.produk_gym.cart')
                ->with('error', 'Stok produk habis. Item dihapus dari keranjang.');
        }

        $current = (int) ($cart[$id]['quantity'] ?? 1);
        $op      = $request->input('op');   // inc | dec
        $qtyIn   = $request->input('qty');  // manual

        if ($qtyIn !== null && $op === null) {
            $newQty = (int) $qtyIn;
        } else {
            if ($op === 'inc') $newQty = $current + 1;
            elseif ($op === 'dec') $newQty = $current - 1;
            else $newQty = $current;
        }

        // qty <= 0 => remove
        if ($newQty <= 0) {
            unset($cart[$id]);
            Session::put('cart', $cart);

            $this->deleteCartItemFromDb($id);

            [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'           => true,
                    'removed'      => true,
                    'id'           => (string) $id,
                    'subtotal'     => $subtotal,
                    'admin_fee'    => $adminFee,
                    'total'        => $total,
                    'items_count'  => $itemsCount,
                    'message'      => 'Item dihapus dari keranjang.',
                    'message_type' => 'success',
                ]);
            }

            return redirect()->route('member.produk_gym.cart')->with('success', 'Item dihapus dari keranjang.');
        }

        $message = 'Jumlah item diperbarui.';
        $messageType = 'success';

        if ($newQty > $stok) {
            $newQty = $stok;
            $message = 'Stok tidak mencukupi. Qty disesuaikan ke stok maksimum.';
            $messageType = 'warning';
        }

        // Refresh snapshot
        $cart[$id]['quantity'] = max(1, (int) $newQty);
        $cart[$id]['price']    = (float) $product->harga;
        $cart[$id]['name']     = (string) $product->nama;
        $cart[$id]['photo']    = $product->foto;
        $cart[$id]['category'] = $product->kategori;

        Session::put('cart', $cart);

        // persist ke DB
        $this->upsertCartItemToDb($product, (int) $cart[$id]['quantity']);

        [$subtotal, $adminFee, $total, $itemsCount] = $this->computeCartTotals($cart);

        $lineTotal = (float) ($cart[$id]['price'] ?? 0) * (int) ($cart[$id]['quantity'] ?? 1);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'           => true,
                'removed'      => false,
                'id'           => (string) $id,
                'quantity'     => (int) $cart[$id]['quantity'],
                'line_total'   => $lineTotal,
                'subtotal'     => $subtotal,
                'admin_fee'    => $adminFee,
                'total'        => $total,
                'items_count'  => $itemsCount,
                'message'      => $message,
                'message_type' => $messageType,
            ]);
        }

        return redirect()->route('member.produk_gym.cart')->with($messageType, $message);
    }

    // =========================================================
    // HELPERS: DB persistent cart
    // =========================================================

    private function getMemberCartModel(): ?MemberCart
    {
        $member = Auth::user()?->member;
        if (!$member) return null;

        return MemberCart::firstOrCreate([
            'member_id' => (int) $member->id,
        ]);
    }

    private function upsertCartItemToDb(Produk $product, int $qty): void
    {
        $cartModel = $this->getMemberCartModel();
        if (!$cartModel) return;

        MemberCartItem::updateOrCreate(
            [
                'cart_id'   => (int) $cartModel->id,
                'produk_id' => (int) $product->id,
            ],
            [
                'quantity' => max(1, (int) $qty),
                'price'    => (float) ($product->harga ?? 0),
                'name'     => (string) ($product->nama ?? ''),
                'photo'    => $product->foto,
                'category' => $product->kategori,
            ]
        );
    }

    private function deleteCartItemFromDb(int $produkId): void
    {
        $cartModel = $this->getMemberCartModel();
        if (!$cartModel) return;

        MemberCartItem::where('cart_id', (int) $cartModel->id)
            ->where('produk_id', (int) $produkId)
            ->delete();
    }

    /**
     * Sinkron session cart -> DB (untuk reconcile)
     * - hapus item DB yang tidak ada di session
     * - upsert semua item session ke DB
     */
    private function syncSessionCartToDb(array $cart): void
    {
        $cartModel = $this->getMemberCartModel();
        if (!$cartModel) return;

        $idsInSession = array_map('intval', array_keys($cart));

        // Hapus yang tidak ada di session
        MemberCartItem::where('cart_id', (int) $cartModel->id)
            ->when(!empty($idsInSession), fn($q) => $q->whereNotIn('produk_id', $idsInSession))
            ->when(empty($idsInSession), fn($q) => $q) // jika session kosong, hapus semua item
            ->delete();

        // Upsert session items
        foreach ($cart as $pid => $item) {
            $produkId = (int) $pid;
            $qty = (int) ($item['quantity'] ?? 1);
            if ($qty < 1) $qty = 1;

            // Ambil produk untuk snapshot yang konsisten
            $product = Produk::find($produkId);
            if (!$product) {
                continue;
            }

            MemberCartItem::updateOrCreate(
                [
                    'cart_id'   => (int) $cartModel->id,
                    'produk_id' => (int) $product->id,
                ],
                [
                    'quantity' => $qty,
                    'price'    => (float) ($product->harga ?? 0),
                    'name'     => (string) ($product->nama ?? ''),
                    'photo'    => $product->foto,
                    'category' => $product->kategori,
                ]
            );
        }
    }

    /**
     * TOTALS (NO ADMIN FEE)
     */
    private function computeCartTotals(array $cart): array
    {
        $subtotal = 0;
        $itemsCount = 0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);

            if ($qty < 1) $qty = 1;

            $itemsCount += $qty;
            $subtotal   += $qty * $price;
        }

        $adminFee = 0; // no admin fee
        $total    = $subtotal;

        return [$subtotal, $adminFee, $total, $itemsCount];
    }
}
