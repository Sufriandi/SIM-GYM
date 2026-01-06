<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LatihanHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LatihanHarianController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle    = 'Latihan Harian';
        $defaultHarga = config('gym.harga_harian');

        $sort   = $request->input('sort', 'newest'); // newest | oldest
        $metode = $request->input('metode');         // cash|transfer|qris|null
        $kategori = $request->input('kategori');     // umum|pelajar|null

        $query = LatihanHarian::query();

        // Opsional: hide canceled dari listing
        $query->whereNull('canceled_at');

        if (!empty($metode)) {
            $query->where('metode_pembayaran', $metode);
        }

        if (!empty($kategori)) {
            $query->where('kategori', $kategori);
        }

        if ($sort === 'oldest') {
            $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc');
        } else {
            $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
        }

        $data = $query->paginate(20)->withQueryString();

        return view('admin.latihan_harian.index', compact(
            'pageTitle',
            'defaultHarga',
            'data',
            'sort',
            'metode',
            'kategori',
        ));
    }

    public function store(Request $request)
    {
        $maxTotal = 10000000; // 10 juta

        $validated = $request->validate([
            'tanggal'           => ['required', 'date'],
            'nama'              => ['required', 'string', 'max:100'],
            'kategori'          => ['required', 'in:umum,pelajar'],
            'total'             => ['required', 'integer', 'min:0', 'max:' . $maxTotal],
            'metode_pembayaran' => ['required', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        // Jika input tanggal hanya Y-m-d, set jam default agar konsisten di laporan
        $validated['tanggal'] = Carbon::parse($validated['tanggal'])->setTime(12, 0, 0);
        $validated['created_by'] = (int) auth()->id();
        $validated['canceled_at'] = null;

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
        $maxTotal = 10000000;

        $validated = $request->validate([
            'tanggal'           => ['required', 'date'],
            'nama'              => ['required', 'string', 'max:100'],
            'kategori'          => ['required', 'in:umum,pelajar'],
            'total'             => ['required', 'integer', 'min:0', 'max:' . $maxTotal],
            'metode_pembayaran' => ['required', 'in:cash,transfer,qris'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ]);

        $validated['tanggal'] = Carbon::parse($validated['tanggal'])->setTime(12, 0, 0);

        $latihanHarian->update($validated);

        return redirect()
            ->route('admin.latihan_harian.index')
            ->with('success', 'Data latihan harian berhasil diperbarui.');
    }

    public function destroy(LatihanHarian $latihanHarian)
    {
        // Lebih aman audit: void, bukan hard delete
        $latihanHarian->update([
            'canceled_at' => now(),
        ]);

        return redirect()
            ->route('admin.latihan_harian.index')
            ->with('success', 'Data latihan harian berhasil dibatalkan (void).');
    }
}
