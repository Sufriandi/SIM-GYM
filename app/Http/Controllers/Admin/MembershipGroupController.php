<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipGroupController extends Controller
{
    /**
     * List semua relasi membership-group (member tambahan pada paket double/triple).
     */
    public function index()
    {
        $groups = MembershipGroup::with(['membership.member', 'membership.paket', 'member'])
            ->latest('created_at')
            ->paginate(20);

        return view('admin.membership_groups.index', compact('groups'));
    }

    /**
     * Tambah anggota ke suatu membership (input manual).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'membership_id' => ['required', 'exists:memberships,id'],
            'member_id'     => [
                'required',
                'exists:members,id',
                // Cegah duplikasi membership_id + member_id
                Rule::unique('membership_groups')->where(function ($q) use ($request) {
                    return $q->where('membership_id', $request->membership_id);
                }),
            ],
        ], [
            'member_id.unique' => 'Member ini sudah terdaftar di grup membership tersebut.',
        ]);

        $membership = Membership::with(['paket', 'member', 'groupMembers'])
            ->findOrFail($validated['membership_id']);

        $paket = $membership->paket;

        // 1) Pastikan paketnya memang group (double / triple)
        if (! in_array($paket->tipe, ['double', 'triple'], true)) {
            return back()
                ->withErrors([
                    'membership_id' => 'Membership ini bukan paket group (double/triple).',
                ])
                ->withInput();
        }

        // 2) Jangan sampai member utama dimasukkan lagi
        if ((int) $membership->member_id === (int) $validated['member_id']) {
            return back()
                ->withErrors([
                    'member_id' => 'Member utama tidak boleh diinput lagi sebagai anggota tambahan.',
                ])
                ->withInput();
        }

        // 3) Cek kapasitas maksimal (double = 2 orang, triple = 3 orang total)
        $maxPersons = $paket->tipe === 'double' ? 2 : 3;
        $currentCount = 1 + $membership->groupMembers()->count(); // 1 = member utama

        if ($currentCount >= $maxPersons) {
            return back()
                ->withErrors([
                    'membership_id' => 'Kapasitas member untuk paket ini sudah penuh.',
                ])
                ->withInput();
        }

        // 4) Simpan anggota tambahan
        MembershipGroup::create($validated);

        return back()->with('success', 'Anggota grup membership berhasil ditambahkan.');
    }

    /**
     * Hapus satu anggota dari membership group.
     */
    public function destroy(MembershipGroup $membershipGroup)
    {
        $membershipGroup->delete();

        return back()->with('success', 'Anggota grup membership berhasil dihapus.');
    }
}
