<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaketMembership;
use Illuminate\Http\Request;

class PaketMembershipController extends Controller
{
    public function index(Request $request)
    {
        $q          = $request->get('q');
        $tipe       = $request->get('tipe');
        $sort       = $request->get('sort', 'newest');
        $visibility = $request->get('visibility'); // public|internal|null

        $query = PaketMembership::query()
            ->when($q, fn($s) => $s->where('nama', 'like', "%{$q}%"))
            ->when($tipe, fn($s) => $s->where('tipe', $tipe))
            ->when($visibility, function ($s) use ($visibility) {
                if ($visibility === 'public') {
                    return $s->where('is_public', true);
                }
                if ($visibility === 'internal') {
                    return $s->where('is_public', false);
                }
                return $s;
            });

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('harga', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('harga', 'desc');
                break;
            case 'duration_asc':
                $query->orderBy('durasi', 'asc');
                break;
            case 'duration_desc':
                $query->orderBy('durasi', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $paketMemberships = $query->paginate(20)->withQueryString();

        return view('admin.paket_memberships.index', compact('paketMemberships'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100'],
            'tipe'      => ['required', 'in:single,double,triple'],
            'durasi'    => ['required', 'integer', 'min:1', 'max:3650'],
            'harga'     => ['required', 'integer', 'min:0', 'max:2000000000'],
            'deskripsi' => ['nullable', 'string', 'max:255'],

            // dari checkbox + hidden 0 => akan selalu terkirim
            'is_public' => ['required', 'boolean'],
        ]);

        PaketMembership::create($validated);

        return back()->with('success', 'Paket membership berhasil ditambahkan.');
    }

    public function update(Request $request, PaketMembership $paketMembership)
    {
        $validated = $request->validate([
            'nama'      => ['required', 'string', 'max:100'],
            'tipe'      => ['required', 'in:single,double,triple'],
            'durasi'    => ['required', 'integer', 'min:1', 'max:3650'],
            'harga'     => ['required', 'integer', 'min:0', 'max:2000000000'],
            'deskripsi' => ['nullable', 'string', 'max:255'],

            // dari checkbox + hidden 0 => akan selalu terkirim
            'is_public' => ['required', 'boolean'],
        ]);

        $paketMembership->update($validated);

        return back()->with('success', 'Paket membership berhasil diperbarui.');
    }

    public function destroy(PaketMembership $paketMembership)
    {
        // Soft delete
        $paketMembership->delete();

        return back()->with('success', 'Paket membership berhasil dinonaktifkan.');
    }

    public function restore($id)
    {
        $paket = PaketMembership::withTrashed()->findOrFail($id);
        $paket->restore();

        return back()->with('success', 'Paket membership berhasil diaktifkan kembali.');
    }
}
