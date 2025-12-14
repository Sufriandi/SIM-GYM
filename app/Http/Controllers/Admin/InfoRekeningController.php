<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoRekening;
use Illuminate\Http\Request;

class InfoRekeningController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bank'      => ['required', 'string', 'max:100'],
            'nomor_rekening' => ['required', 'string', 'max:50'],
            'nama_pemilik'   => ['required', 'string', 'max:150'],
        ]);

        InfoRekening::create($validated);

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(Request $request, InfoRekening $infoRekening)
    {
        $validated = $request->validate([
            'nama_bank'      => ['required', 'string', 'max:100'],
            'nomor_rekening' => ['required', 'string', 'max:50'],
            'nama_pemilik'   => ['required', 'string', 'max:150'],
        ]);

        $infoRekening->update($validated);

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(InfoRekening $infoRekening)
    {
        $infoRekening->delete(); // soft delete

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'Rekening berhasil dihapus.');
    }

    // Tidak dipakai (UI modal)
    public function index() {}
    public function create() {}
    public function show(InfoRekening $infoRekening) {}
    public function edit(InfoRekening $infoRekening) {}
}
