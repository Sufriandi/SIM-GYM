<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventarisAlat;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class InventarisAlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil input filter & search dari request
        $search  = $request->get('search');   // untuk cari berdasarkan NAMA
        $kondisi = $request->get('kondisi');  // untuk filter kondisi

        // Base query
        $query = InventarisAlat::query()->orderBy('id', 'DESC');

        // FILTER: Pencarian berdasarkan NAMA saja
        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        // FILTER: Berdasarkan kondisi (Baik / Maintenance / Rusak)
        if ($kondisi && in_array($kondisi, ['Baik', 'Maintenance', 'Rusak'])) {
            $query->where('kondisi', $kondisi);
        }

        // Ambil hasil (kalau mau bisa diganti ->paginate(20))
        $data = $query->paginate(20)->appends($request->query());

        // Kirim juga nilai search & kondisi ke view supaya bisa dipakai ulang di form
        return view('admin.inventaris.index', [
            'data'    => $data,
            'search'  => $search,
            'kondisi' => $kondisi,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * (saat ini kamu pakai popup di index, tapi route create tetap boleh ada)
     */
    public function create()
    {
        // Kalau tidak dipakai, boleh dikosongkan atau redirect
        return view('admin.inventaris.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required',
            'deskripsi'   => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'kondisi'     => 'required|in:Baik,Maintenance,Rusak',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'kondisi']);

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageUploadService::uploadAsWebp($request->file('foto'), 'inventaris', null, 1200, 85);
        }

        InventarisAlat::create($data);

        return redirect()
            ->route('admin.inventaris.index')
            ->with('success', 'Inventaris alat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventarisAlat $inventaris)
    {
        return view('admin.inventaris.show', compact('inventaris'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventarisAlat $inventaris)
    {
        // view: resources/views/admin/inventaris/edit.blade.php
        return view('admin.inventaris.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventarisAlat $inventaris)
    {
        $request->validate([
            'nama'        => 'required',
            'deskripsi'   => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'kondisi'     => 'required|in:Baik,Maintenance,Rusak',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'kondisi']);

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageUploadService::uploadAsWebp($request->file('foto'), 'inventaris', $inventaris->foto, 1200, 85);
        }

        $inventaris->update($data);

        return redirect()
            ->route('admin.inventaris.index')
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventarisAlat $inventaris)
    {
        if ($inventaris->foto) {
            ImageUploadService::delete($inventaris->foto);
        }

        $inventaris->delete();

        return redirect()
            ->route('admin.inventaris.index')
            ->with('success', 'Data telah dihapus.');
    }
}
