<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\PaketMembership;
use App\Models\Produk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Statistik ringkas untuk pengunjung
        $stats = [
            'total_coaches' => Coach::query()->count(),
            'total_products' => Produk::query()->count(),
            'total_packages' => PaketMembership::query()->count(),
            'in_stock_products' => Produk::query()->where('stok', '>', 0)->count(),
        ];

        // Produk terbaru untuk highlight
        $latestProducts = Produk::query()
            ->select(['id', 'nama', 'kategori', 'harga', 'stok', 'foto'])
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        // Coach terbaru untuk highlight
        $latestCoaches = Coach::query()
            ->select(['id', 'nama', 'deskripsi', 'foto'])
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'pageTitle' => 'Dashboard Pengunjung',
            'stats' => $stats,
            'latestProducts' => $latestProducts,
            'latestCoaches' => $latestCoaches,
        ]);
    }
}
