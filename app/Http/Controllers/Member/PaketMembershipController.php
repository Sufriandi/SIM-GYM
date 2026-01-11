<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PaketMembership;
use Illuminate\Http\Request;

class PaketMembershipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', $request->get('q'));
        $tipe   = $request->get('tipe'); // single|double|triple|null

        $query = PaketMembership::query()
            ->public()
            ->when($search, fn($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($tipe, fn($q) => $q->where('tipe', $tipe))
            ->orderBy('tipe')
            ->orderBy('durasi');

        $pakets = $query->paginate(16)->withQueryString();

        return view('member.paket_membership.index', [
            'pakets'      => $pakets,
            'search'      => $search,
            'currentTipe' => $tipe,
        ]);
    }

    public function show(PaketMembership $paketMembership)
    {
        abort_unless((bool) $paketMembership->is_public, 404);

        return view('member.paket_membership.show', [
            'paketMembership' => $paketMembership,
        ]);
    }

    public function checkout(PaketMembership $paketMembership)
    {
        abort_unless((bool) $paketMembership->is_public, 404);

        return view('member.paket_membership.checkout', [
            'paketMembership' => $paketMembership,
        ]);
    }
}
