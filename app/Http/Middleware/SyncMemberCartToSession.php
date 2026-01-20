<?php

namespace App\Http\Middleware;

use App\Models\MemberCart;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SyncMemberCartToSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $member = $user->member ?? null;

        if (!$member) {
            return $next($request);
        }

        // Sync hanya sekali per user per session login
        $syncedFor = (int) Session::get('cart_synced_user_id', 0);
        if ($syncedFor !== (int) $user->id) {

            $cartArr = [];

            $cart = MemberCart::with('items')
                ->where('member_id', (int) $member->id)
                ->first();

            if ($cart) {
                foreach ($cart->items as $item) {
                    $cartArr[(int) $item->produk_id] = [
                        'id'       => (int) $item->produk_id,
                        'name'     => (string) ($item->name ?? ''),
                        'quantity' => (int) ($item->quantity ?? 1),
                        'price'    => (float) ($item->price ?? 0),
                        'photo'    => $item->photo,
                        'category' => $item->category,
                    ];
                }
            }

            Session::put('cart', $cartArr);
            Session::put('cart_synced_user_id', (int) $user->id);
        }

        return $next($request);
    }
}
