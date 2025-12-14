<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoRekening;
use App\Models\InfoQris;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort   = $request->query('sort', 'newest');

        $order = $sort === 'oldest' ? 'asc' : 'desc';

        // ================== REKENING ==================
        $rekeningQuery = InfoRekening::query();

        if ($search !== '') {
            $rekeningQuery->where(function ($q) use ($search) {
                $q->where('nama_bank', 'like', "%{$search}%")
                    ->orWhere('nomor_rekening', 'like', "%{$search}%")
                    ->orWhere('nama_pemilik', 'like', "%{$search}%");
            });
        }

        $rekenings = $rekeningQuery
            ->orderBy('id', $order)
            ->paginate(20, ['*'], 'rekening_page')
            ->withQueryString();

        // ================== QRIS ==================
        $qrisQuery = InfoQris::query();

        if ($search !== '') {
            $qrisQuery->where(function ($q) use ($search) {
                $q->where('nama_qris', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $qrises = $qrisQuery
            ->orderBy('id', $order)
            ->paginate(20, ['*'], 'qris_page')
            ->withQueryString();

        return view('admin.rekening.index', compact('rekenings', 'qrises', 'search', 'sort'));
    }
}
