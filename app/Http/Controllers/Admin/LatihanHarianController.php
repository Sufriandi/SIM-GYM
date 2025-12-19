<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LatihanHarian;
use Illuminate\Http\Request;

class LatihanHarianController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle    = 'Latihan Harian';
        $defaultHarga = config('gym.harga_harian');

        $sort = $request->input('sort', 'newest'); // newest | oldest

        $query = LatihanHarian::query();

        if ($sort === 'oldest') {
            $query->orderBy('tanggal', 'asc');
        } else {
            // default: terbaru dulu
            $query->orderBy('tanggal', 'desc');
        }

        $data = $query->paginate(15)->withQueryString();

        return view('admin.latihan_harian.index', compact(
            'pageTitle',
            'defaultHarga',
            'data',
            'sort',
        ));
    }

    public function store(Request $request)
    {
        // batasi harga agar tidak melebihi kapasitas kolom & logika bisnis
        $maxHarga = 10000000; // contoh: maksimal 10.000.000

        $validated = $request->validate([
            'tanggal'           => ['required', 'date'],
            'nama'              => ['required', 'string', 'max:100'],
            'kategori'          => ['required', 'in:umum,pelajar'],
            'harga'             => ['required', 'integer', 'min:0', 'max:' . $maxHarga],
            'metode_pembayaran' => ['nullable', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        $validated['created_by'] = auth()->id();

        LatihanHarian::create($validated);

        return redirect()
            ->route('admin.latihan_harian.index')
            ->with('success', 'Data latihan harian berhasil ditambahkan.');
    }

    public function edit(LatihanHarian $latihanHarian)
    {
        $defaultHarga = config('gym.harga_harian');

        return view('admin.latihan_harian.edit', [
            'pageTitle'    => 'Edit Latihan Harian',
            'item'         => $latihanHarian,
            'defaultHarga' => $defaultHarga,
        ]);
    }

    public function update(Request $request, LatihanHarian $latihanHarian)
    {
        $maxHarga = 10000000;

        $validated = $request->validate([
            'tanggal'           => ['required', 'date'],
            'nama'              => ['required', 'string', 'max:100'],
            'kategori'          => ['required', 'in:umum,pelajar'],
            'harga'             => ['required', 'integer', 'min:0', 'max:' . $maxHarga],
            'metode_pembayaran' => ['nullable', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        $latihanHarian->update($validated);

        return redirect()
            ->route('admin.latihan_harian.index')
            ->with('success', 'Data latihan harian berhasil diperbarui.');
    }

    public function destroy(LatihanHarian $latihanHarian)
    {
        $latihanHarian->delete();

        return redirect()
            ->route('admin.latihan_harian.index')
            ->with('success', 'Data latihan harian berhasil dihapus.');
    }
}
