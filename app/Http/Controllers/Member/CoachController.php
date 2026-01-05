<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    public function show(string $slug)
    {
        // Ambil ID dari bagian sebelum "-" (contoh: "1-coach-tari" => 1)
        $idPart = Str::before($slug, '-');
        $id     = ctype_digit($idPart) ? (int) $idPart : 0;

        abort_if($id <= 0, 404);

        $coach = Coach::query()->findOrFail($id);

        // Optional: kalau slug-nama tidak cocok, redirect ke slug yang benar (SEO/rapi)
        $expectedSlug = $coach->id . '-' . Str::slug($coach->nama ?? 'coach');
        if ($slug !== $expectedSlug) {
            return redirect()
                ->route('member.coach.show', $expectedSlug);
        }

        // related coaches (4)
        $relatedCoaches = Coach::query()
            ->where('id', '!=', $coach->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('member.coach.show', compact('coach', 'relatedCoaches'));
    }
}
