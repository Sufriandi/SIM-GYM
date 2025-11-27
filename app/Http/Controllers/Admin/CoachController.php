<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Coach;
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
            ->paginate(10)
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
            'foto'      => 'nullable|image|max:2048', // 2MB
        ]);

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('coach', 'public');
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
            'foto'      => 'nullable|image|max:2048',
        ]);

        // Jika upload foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($coach->foto && Storage::disk('public')->exists($coach->foto)) {
                Storage::disk('public')->delete($coach->foto);
            }

            $validated['foto'] = $request->file('foto')->store('coach', 'public');
        }

        $coach->update($validated);

        return redirect()->route('admin.coaches.index')->with('success', 'Data coach berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coach $coach)
    {
        // Hapus foto juga
        if ($coach->foto && Storage::disk('public')->exists($coach->foto)) {
            Storage::disk('public')->delete($coach->foto);
        }

        $coach->delete();

        return redirect()->route('admin.coaches.index')->with('success', 'Data coach berhasil dihapus.');
    }
}
