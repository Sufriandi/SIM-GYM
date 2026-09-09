<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoachController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $coaches = Coach::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString(); // biar query ?search=... ikut di pagination

        $pageTitle = 'Daftar Coach';

        return view('admin.coach.index', compact('coaches', 'pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.coach.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'no_hp'     => 'required|string|max:20',
            'alamat'    => 'required|string',
            'deskripsi' => 'nullable|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        // Upload foto jika ada (kompres & convert WebP)
        if ($request->hasFile('foto')) {
            $validated['foto'] = ImageUploadService::uploadAsWebp($request->file('foto'), 'coach', null, 1000, 85);
        }

        Coach::create($validated);

        return redirect()->route('admin.coaches.index')->with('success', 'Data coach berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coach $coach)
    {
        return view('admin.coach.show', compact('coach'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coach $coach)
    {
        return view('admin.coach.edit', compact('coach'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coach $coach)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'no_hp'     => 'required|string|max:20',
            'alamat'    => 'required|string',
            'deskripsi' => 'nullable|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        // Jika upload foto baru (kompres & convert WebP, hapus foto lama)
        if ($request->hasFile('foto')) {
            $validated['foto'] = ImageUploadService::uploadAsWebp($request->file('foto'), 'coach', $coach->foto, 1000, 85);
        }

        $coach->update($validated);

        return redirect()->route('admin.coaches.index')->with('success', 'Data coach berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coach $coach)
    {
        // Hapus foto jika ada
        if ($coach->foto) {
            ImageUploadService::delete($coach->foto);
        }

        $coach->delete();

        return redirect()->route('admin.coaches.index')->with('success', 'Data coach berhasil dihapus.');
    }
}
