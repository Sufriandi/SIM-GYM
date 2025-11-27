<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;

class CoachController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Coach::query();

        // Pencarian berdasarkan nama, alamat, atau deskripsi
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $coaches = $query->paginate(9)->withQueryString();

        $pageTitle = 'Daftar Coach';

        return view('member.coach.index', [
            'coaches'    => $coaches,
            'search'     => $search,
            'pageTitle'  => $pageTitle,
        ]);
    }
}
