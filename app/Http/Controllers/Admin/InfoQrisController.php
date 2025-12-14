<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoQris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InfoQrisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_qris'  => ['required', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'gambar'     => ['required', 'image', 'max:2048'], // input file name="gambar"
        ]);

        $path = $request->file('gambar')->store('qris', 'public');

        InfoQris::create([
            'nama_qris'   => $validated['nama_qris'],
            'keterangan'  => $validated['keterangan'] ?? null,
            'path_gambar' => $path,
        ]);

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'QRIS berhasil ditambahkan.');
    }

    public function update(Request $request, InfoQris $infoQris)
    {
        $validated = $request->validate([
            'nama_qris'  => ['required', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'gambar'     => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'nama_qris'  => $validated['nama_qris'],
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if ($request->hasFile('gambar')) {
            if ($infoQris->path_gambar && Storage::disk('public')->exists($infoQris->path_gambar)) {
                Storage::disk('public')->delete($infoQris->path_gambar);
            }

            $data['path_gambar'] = $request->file('gambar')->store('qris', 'public');
        }

        $infoQris->update($data);

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'QRIS berhasil diperbarui.');
    }

    public function destroy(InfoQris $infoQris)
    {
        // Jika soft delete, file sebetulnya boleh dipertahankan.
        // Tapi Anda minta menghindari penumpukan -> kita hapus file saat delete.
        if ($infoQris->path_gambar && Storage::disk('public')->exists($infoQris->path_gambar)) {
            Storage::disk('public')->delete($infoQris->path_gambar);
        }

        $infoQris->delete(); // soft delete

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'QRIS berhasil dihapus.');
    }

    // Tidak dipakai (UI modal)
    public function index() {}
    public function create() {}
    public function show(InfoQris $infoQris) {}
    public function edit(InfoQris $infoQris) {}
}
