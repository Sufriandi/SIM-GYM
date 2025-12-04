<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaketMembership;
use Illuminate\Http\Request;

class PaketMembershipController extends Controller
{
    public function index()
    {
        $paketMemberships = PaketMembership::orderBy('tipe')
            ->orderBy('durasi')
            ->paginate(20);

        return view('admin.paket_memberships.index', compact('paketMemberships'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100'],
            'tipe'      => ['required', 'in:single,double,triple'],
            'durasi'    => ['required', 'integer', 'min:1'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        PaketMembership::create($validated);

        return back()->with('success', 'Paket membership berhasil ditambahkan.');
    }

    public function update(Request $request, PaketMembership $paketMembership)
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100'],
            'tipe'      => ['required', 'in:single,double,triple'],
            'durasi'    => ['required', 'integer', 'min:1'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $paketMembership->update($validated);

        return back()->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function destroy(PaketMembership $paketMembership)
    {
        $paketMembership->delete();

        return back()->with('success', 'Paket membership berhasil dihapus.');
    }
}
