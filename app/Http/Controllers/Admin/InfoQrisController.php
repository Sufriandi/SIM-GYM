<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoQris;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InfoQrisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_qris'  => ['required', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'gambar'     => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // input file name="gambar"
        ]);

        $path = ImageUploadService::uploadAsWebp($request->file('gambar'), 'qris', null, 1200, 90);

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
            'gambar'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $data = [
            'nama_qris'  => $validated['nama_qris'],
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if ($request->hasFile('gambar')) {
            $data['path_gambar'] = ImageUploadService::uploadAsWebp($request->file('gambar'), 'qris', $infoQris->path_gambar, 1200, 90);
        }

        $infoQris->update($data);

        return redirect()
            ->route('admin.rekening.index')
            ->with('success', 'QRIS berhasil diperbarui.');
    }

    public function destroy(InfoQris $infoQris)
    {
        // Hapus file saat delete
        if ($infoQris->path_gambar) {
            ImageUploadService::delete($infoQris->path_gambar);
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
