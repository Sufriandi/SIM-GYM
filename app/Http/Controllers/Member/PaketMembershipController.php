<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\InfoQris;
use App\Models\InfoRekening;
use App\Models\PaketMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaketMembershipController extends Controller
{
    public function index(Request $request)
    {
        $tipe = $request->get('tipe');
        $sort = $request->get('sort', 'recommended');

        $query = PaketMembership::public()
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe));

        switch ($sort) {
            case 'price_low':
                $query->orderBy('harga', 'asc');
                break;
            case 'price_high':
                $query->orderBy('harga', 'desc');
                break;
            case 'duration_long':
                $query->orderBy('durasi', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $pakets = $query->get();

        return view('member.membership.index', compact('pakets'));
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
        $orderId      = 'MBR-' . strtoupper(Str::random(9));

        $rekenings = InfoRekening::query()->orderBy('nama_bank')->get();
        $qris      = InfoQris::query()->first();

        $waAdminRaw = User::query()
        ->where('role', 'admin')
        ->whereNotNull('no_hp')
        ->orderBy('id', 'asc')
        ->value('no_hp');
        $waAdmin = $waAdminRaw ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $waAdminRaw)) : null;
        $merchantName = 'BETA GYM'; 
$merchantLogo = asset('images/logo.png'); 


        return view('member.membership.checkout', compact(
            'paketMembership',
            'totalTagihan',
            'paketNama',
            'orderId',
            'rekenings',
            'qris',
            'waAdmin',
            'merchantName',
            'merchantLogo',
        ));
    }
}
