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
        $memberships = Membership::with(['member.user', 'paket', 'groupMembers.member.user'])
            ->latest('tanggal_transaksi')
            ->paginate(20);

        /**
         * Dropdown member (hanya user.role = member)
         * FIX: sort by users.name (nama sudah di users)
         */
        $members = Member::query()
            ->with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'member'))
            ->join('users', 'users.id', '=', 'members.user_id')
            ->select('members.*')
            ->orderBy('users.name')
            ->get();

        $paketList = PaketMembership::orderBy('tipe')
            ->orderBy('durasi')
            ->get();

        return view('admin.memberships.index', [
            'memberships' => $memberships,
            'members'     => $members,
            'paketList'   => $paketList,
        ]);
    }

    /**
     * Simpan transaksi membership baru.
     *
     * Dampak ke tabel members mengikuti aturan:
     * - Transaksi LAMA + member MASIH AKTIF  -> hanya riwayat, member tidak diubah.
     * - Selain itu                           -> update members.tanggal_mulai & tanggal_akhir.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id'          => ['required', 'exists:members,id'],
            'paket_id'           => ['required', 'exists:paket_memberships,id'],
            'tanggal_transaksi'  => ['nullable', 'date'],
            'metode_pembayaran'  => ['required', 'in:cash,transfer,qris'],
            'keterangan'         => ['nullable', 'string', 'max:255'],

            'group_member_ids'   => ['array'],
            'group_member_ids.*' => ['nullable', 'different:member_id', 'distinct', 'exists:members,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $paket  = PaketMembership::findOrFail($validated['paket_id']);
            $member = Member::lockForUpdate()->findOrFail($validated['member_id']);

            // ====== KONTEKS WAKTU ======
            $tanggalTransaksi = isset($validated['tanggal_transaksi'])
                ? Carbon::parse($validated['tanggal_transaksi'])
                : now();

            $transDate = $tanggalTransaksi->copy()->startOfDay();
            $today     = Carbon::today();

            $currentStart = $member->tanggal_mulai
                ? Carbon::parse($member->tanggal_mulai)->startOfDay()
                : null;

            $currentEnd = $member->tanggal_akhir
                ? Carbon::parse($member->tanggal_akhir)->startOfDay()
                : null;

            // ====== CEK: TRANSAKSI INI HANYA RIWAYAT? ======
            $isHistoricalOnly =
                $transDate->lt($today) &&
                $currentEnd !== null &&
                $currentEnd->gte($today);

            // ====== HITUNG TANGGAL MULAI & AKHIR TRANSAKSI ======
            if ($isHistoricalOnly) {
                $tanggalMulai = $transDate->copy();
            } else {
                if ($currentEnd !== null && $currentEnd->gte($transDate)) {
                    $tanggalMulai = $currentEnd->copy()->addDay();
                } else {
                    $tanggalMulai = $transDate->copy();
                }
            }

            $tanggalAkhir = $tanggalMulai->copy()->addDays($paket->durasi - 1);

            // ====== 1. CATAT TRANSAKSI UTAMA ======
            $membership = Membership::create([
                'member_id'         => $member->id,
                'paket_id'          => $paket->id,
                'tanggal_transaksi' => $tanggalTransaksi,
                'tanggal_mulai'     => $tanggalMulai,
                'tanggal_akhir'     => $tanggalAkhir,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'keterangan'        => $validated['keterangan'] ?? null,
            ]);

            // ====== 2. ANGGOTA TAMBAHAN (DOUBLE / TRIPLE) ======
            $isGroupPackage = in_array($paket->tipe, ['double', 'triple'], true);

            if ($isGroupPackage && !empty($validated['group_member_ids'])) {
                foreach ($validated['group_member_ids'] as $memberId) {
                    if (!$memberId) continue;

                    $groupMember = Member::lockForUpdate()->findOrFail($memberId);

                    MembershipGroup::create([
                        'membership_id' => $membership->id,
                        'member_id'     => $groupMember->id,
                    ]);

                    if (!$isHistoricalOnly) {
                        $this->applyMembershipDurationToMember($groupMember, $tanggalMulai, $tanggalAkhir);
                    }
                }
            }

            // ====== 3. UPDATE TABEL MEMBERS (UNTUK MEMBER UTAMA) ======
            if (!$isHistoricalOnly) {
                $this->applyMembershipDurationToMember($member, $tanggalMulai, $tanggalAkhir);
            }
        });

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Membership berhasil ditambahkan dan durasi member diperbarui.');
    }

    public function show(Membership $membership)
    {
        $membership->load(['member.user', 'paket', 'groupMembers.member.user']);
        return view('admin.memberships.show', compact('membership'));
    }

    /**
     * Batalkan transaksi membership (soft cancel via canceled_at).
     */
    public function destroy(Membership $membership)
    {
        DB::transaction(function () use ($membership) {
            $membership->load(['member', 'groupMembers.member']);

            $today = Carbon::today();
            $mulai = Carbon::parse($membership->tanggal_mulai)->startOfDay();
            $akhir = Carbon::parse($membership->tanggal_akhir)->startOfDay();

            if ($membership->canceled_at) {
                abort(403, 'Transaksi ini sudah dibatalkan sebelumnya.');
            }

            if ($today->lt($mulai)) {
                $status = 'belum_aktif';
            } elseif ($today->gt($akhir)) {
                $status = 'expired';
            } else {
                $status = 'aktif';
            }

            if ($status !== 'belum_aktif') {
                abort(403, 'Hanya transaksi membership yang belum aktif yang bisa dibatalkan.');
            }

            $adaYangLebihBaru = Membership::where('member_id', $membership->member_id)
                ->whereNull('canceled_at')
                ->where('id', '!=', $membership->id)
                ->where('tanggal_mulai', '>', $membership->tanggal_mulai)
                ->exists();

            if ($adaYangLebihBaru) {
                abort(403, 'Tidak bisa membatalkan transaksi ini karena ada transaksi membership yang lebih baru.');
            }

            $membership->canceled_at = now();
            $membership->save();

            $this->recalculateMemberDuration($membership->member);

            foreach ($membership->groupMembers as $group) {
                if ($group->member) {
                    $this->recalculateMemberDuration($group->member);
                }
            }
        });

        return redirect()
            ->route('admin.memberships.index')
            ->with('success', 'Transaksi membership berhasil dibatalkan dan durasi member sudah disesuaikan.');
    }

    /**
     * Terapkan durasi membership ke tabel members.
     */
    protected function applyMembershipDurationToMember(
        Member $member,
        Carbon $tanggalMulaiMembership,
        Carbon $tanggalAkhirMembership
    ): void {
        $today = Carbon::today();

        $currentStart = $member->tanggal_mulai
            ? Carbon::parse($member->tanggal_mulai)->startOfDay()
            : null;

        $currentEnd = $member->tanggal_akhir
            ? Carbon::parse($member->tanggal_akhir)->startOfDay()
            : null;

        if ($currentEnd !== null && $currentEnd->gte($today)) {
            $memberMulai = $currentStart
                ? $currentStart->copy()
                : $tanggalMulaiMembership->copy();
        } else {
            $memberMulai = $tanggalMulaiMembership->copy();
        }

        $member->tanggal_mulai = $memberMulai;
        $member->tanggal_akhir = $tanggalAkhirMembership->copy();
        $member->save();
    }

    /**
     * Recalculate tanggal_mulai dan tanggal_akhir berdasarkan transaksi yang tidak dibatalkan.
     */
    protected function recalculateMemberDuration(Member $member): void
    {
        $validMemberships = Membership::where('member_id', $member->id)
            ->whereNull('canceled_at')
            ->orderBy('tanggal_mulai')
            ->get();

        if ($validMemberships->isEmpty()) {
            $member->tanggal_mulai = null;
            $member->tanggal_akhir = null;
        } else {
            $member->tanggal_mulai = $validMemberships->first()->tanggal_mulai;
            $member->tanggal_akhir = $validMemberships->last()->tanggal_akhir;
        }

        $member->save();
    }
}
