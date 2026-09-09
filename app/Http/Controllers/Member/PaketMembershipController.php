<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\InfoQris;
use App\Models\InfoRekening;
use App\Models\PaketMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PaketMembershipController extends Controller
{
    public function index(Request $request)
    {
        $pakets = PaketMembership::query()
            ->where('is_public', true)
            ->orderBy('harga', 'asc')
            ->get();

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

        return view('member.membership.index', [
            'pageTitle'     => 'Paket Membership',
            'pageSubtitle'  => 'Pilih paket membership sesuai ritme dan target latihan Anda.',
            'pakets'        => $pakets,
            'recommendedId' => $recommendedId,
            'longestId'     => $longestId,
        ]);
    }

    public function show(PaketMembership $paketMembership)
    {
        abort_if(!$paketMembership->is_public, 404);

        return view('member.membership.show', compact('paketMembership'));
    }

    public function checkout(PaketMembership $paketMembership)
    {
        abort_if(!$paketMembership->is_public, 404);

        $totalTagihan = (int) $paketMembership->harga;
        $paketNama    = (string) $paketMembership->nama;

        // Gunakan session agar Order ID konsisten saat refresh laman
        $sessionKey = 'pending_mbr_order_' . $paketMembership->id;
        if (!Session::has($sessionKey)) {
            Session::put($sessionKey, 'MBR-' . now()->format('ymd') . '-' . strtoupper(
                Str::of(Str::random(12))->replaceMatches('/[^A-Za-z]/', '')->substr(0, 6)
            ));
        }
        $orderId = Session::get($sessionKey);

        $rekenings = InfoRekening::query()->orderBy('nama_bank')->get();
        $qris      = InfoQris::query()->first();

        $waAdminRaw = User::query()
            ->where('role', 'admin')
            ->whereNotNull('no_hp')
            ->orderBy('id', 'asc')
            ->value('no_hp');
        $waAdmin = $waAdminRaw ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $waAdminRaw)) : '6281234567890';
        $merchantName = 'BETA GYM';
        $merchantLogo = asset('images/logo.webp');

        return view('member.membership.checkout', [
            'pageTitle'       => 'Payment Gateway – ' . $paketNama,
            'pageSubtitle'    => 'Selesaikan pembayaran untuk mengaktifkan membership.',
            'paketMembership' => $paketMembership,
            'totalTagihan'    => $totalTagihan,
            'paketNama'       => $paketNama,
            'orderId'         => $orderId,
            'rekenings'       => $rekenings,
            'qris'            => $qris,
            'waAdmin'         => $waAdmin,
            'merchantName'    => $merchantName,
            'merchantLogo'    => $merchantLogo,
        ]);
    }
}
