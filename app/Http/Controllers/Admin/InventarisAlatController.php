<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventarisAlat;
use Illuminate\Http\Request;

class InventarisAlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = InventarisAlat::orderBy('id', 'DESC')->get();

        // view: resources/views/admin/inventaris/index.blade.php
        return view('admin.inventaris.index', compact('data'));
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
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kondisi'     => 'required|in:Baik,Maintenance,Rusak',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'kondisi']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('inventaris', 'public');
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
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'kondisi'     => 'required|in:Baik,Maintenance,Rusak',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'kondisi']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('inventaris', 'public');
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
        $inventaris->delete();

        return redirect()
            ->route('admin.inventaris.index')
            ->with('success', 'Data telah dihapus.');
    }
}
