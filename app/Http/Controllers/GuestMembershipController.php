<?php

namespace App\Http\Controllers;

use App\Models\PaketMembership;
use Illuminate\Http\Request;

class GuestMembershipController extends Controller
{
    public function index(Request $request)
    {
        $pakets = PaketMembership::query()
            ->where('is_public', true)
            ->orderBy('harga', 'asc')
            ->get();

        // Tentukan paket unggulan / rekomendasi
        $recommendedId = null;
        $longestId = null;

        if ($pakets->isNotEmpty()) {
            // Paket paling populer: prioritaskan "Paket 1 Bulan" (single)
            $popularPaket = $pakets->firstWhere('nama', 'Paket 1 Bulan')
                ?? $pakets->first(fn($p) => stripos($p->nama, '1 Bulan') !== false && $p->tipe === 'single')
                ?? $pakets->first();
            $recommendedId = $popularPaket?->id;

            // Paket durasi terpanjang di luar paket populer
            $longest = $pakets->where('id', '!=', $recommendedId)->sortByDesc('durasi')->first();
            $longestId = $longest?->id;
        }

        return view('membership.index', [
            'pageTitle'     => 'Membership',
            'pakets'        => $pakets,
            'recommendedId' => $recommendedId,
            'longestId'     => $longestId,
        ]);
    }
}