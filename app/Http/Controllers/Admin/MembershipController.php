<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipGroup;
use App\Models\PaketMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    /**
     * List transaksi membership + form input membership.
     */
    public function index()
    {
        $memberships = Membership::with(['member', 'paket'])
            ->latest('tanggal_transaksi')
            ->paginate(15);

        $members = Member::orderBy('nama')->get();
        $paket   = PaketMembership::orderBy('tipe')
            ->orderBy('durasi')
            ->get();

        return view('admin.memberships.index', compact('memberships', 'members', 'paket'));
    }

    /**
     * Simpan transaksi membership baru,
     * lalu update tanggal_mulai & tanggal_akhir di tabel member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id'          => ['required', 'exists:members,id'],
            'paket_id'           => ['required', 'exists:paket_memberships,id'], // ✅ fixed
            'tanggal_transaksi'  => ['nullable', 'date'],
            'metode_pembayaran'  => ['required', 'in:cash,transfer,qris'],
            'keterangan'         => ['nullable', 'string', 'max:255'],

            // untuk paket double / triple
            'group_member_ids'   => ['array'],
            'group_member_ids.*' => ['different:member_id', 'distinct', 'exists:members,id'],
        ]);

        // default: sekarang
        $validated['tanggal_transaksi'] ??= now();

        DB::transaction(function () use ($validated) {
            $paket  = PaketMembership::findOrFail($validated['paket_id']);
            $member = Member::lockForUpdate()->findOrFail($validated['member_id']);

            // 1. catat transaksi membership
            $membership = Membership::create([
                'member_id'         => $member->id,
                'paket_id'          => $paket->id,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan'        => $validated['keterangan'] ?? null,
            ]);

            // 2. update durasi member utama
            $this->applyMembershipDurationToMember($member, $paket);

            // 3. kalau paketnya untuk >1 orang (double / triple)
            $isGroupPackage = in_array($paket->tipe, ['double', 'triple'], true);

            if ($isGroupPackage && !empty($validated['group_member_ids'])) {
                foreach ($validated['group_member_ids'] as $memberId) {
                    $groupMember = Member::lockForUpdate()->findOrFail($memberId);

                    MembershipGroup::create([
                        'membership_id' => $membership->id,
                        'member_id'     => $groupMember->id,
                    ]);

                    // group member juga ikut diaktifkan durasinya
                    $this->applyMembershipDurationToMember($groupMember, $paket);
                }
            }
        });

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership berhasil ditambahkan dan durasi member diperbarui.');
    }

    /**
     * Detail satu transaksi membership.
     */
    public function show(Membership $membership)
    {
        $membership->load(['member', 'paket']);

        return view('admin.memberships.show', compact('membership'));
    }

    /**
     * Hapus transaksi membership.
     * (Durasi member tidak di-rollback)
     */
    public function destroy(Membership $membership)
    {
        $membership->delete();

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership berhasil dihapus.');
    }

    /**
     * Helper: tambah durasi ke member.
     *
     * - Kalau belum punya membership / sudah expired:
     *   tanggal_mulai = hari ini
     *   tanggal_akhir = hari ini + durasi
     *
     * - Kalau masih aktif:
     *   tanggal_akhir = tanggal_akhir lama + durasi
     */
    protected function applyMembershipDurationToMember(Member $member, PaketMembership $paket): void
    {
        $today = Carbon::today();

        $currentEnd = $member->tanggal_akhir
            ? Carbon::parse($member->tanggal_akhir)
            : null;

        if (is_null($currentEnd) || $currentEnd->lt($today)) {
            // baru / sudah lewat: reset dari hari ini
            $mulai = $today;
            $akhir = (clone $today)->addDays($paket->durasi);
        } else {
            // masih aktif: extend dari tanggal_akhir lama
            $mulai = $member->tanggal_mulai
                ? Carbon::parse($member->tanggal_mulai)
                : $today;

            $akhir = $currentEnd->copy()->addDays($paket->durasi);
        }

        $member->tanggal_mulai = $mulai;
        $member->tanggal_akhir = $akhir;

        $member->save();
    }
}
