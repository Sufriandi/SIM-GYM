<?php

namespace App\Http\Controllers;

use App\Models\PaketMembership;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Ambil paket membership untuk ditampilkan di landing.
        
        $paketMemberships = PaketMembership::query()
            ->orderBy('harga')
            ->limit(3)
            ->get();

        return view('home', [
            'pageTitle' => 'BETA GYM – Build a Better You',
            'paketMemberships' => $paketMemberships,
        ]);
    }
}
