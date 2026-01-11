<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PaketMembership;
use App\Models\TransaksiMembership;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiMembershipApprovalController extends Controller
{
    public function approve($id)
    {
        // Pastikan middleware admin kamu jalan (role check)

        DB::transaction(function () use ($id) {
            $trx = TransaksiMembership::query()
                ->with(['paket', 'participants'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($trx->canceled_at) {
                abort(403, 'Transaksi sudah dibatalkan.');
            }

            if ($trx->payment_status !== TransaksiMembership::PAY_SUBMITTED) {
                abort(403, 'Hanya transaksi yang sudah upload bukti (submitted) yang bisa dikonfirmasi.');
            }

            if ($trx->expires_at && Carbon::parse($trx->expires_at)->isPast()) {
                $trx->payment_status = TransaksiMembership::PAY_EXPIRED;
                $trx->save();
                abort(403, 'Transaksi sudah kedaluwarsa.');
            }

            $paket = $trx->paket ?: PaketMembership::findOrFail($trx->paket_id);
            $durasi = (int) $paket->durasi;

            // Minimal: harus ada primary participant
            $primary = $trx->participants->firstWhere('role', 'primary')
                ?: $trx->participants()->where('role', 'primary')->first();

            if (!$primary) {
                abort(500, 'Primary participant tidak ditemukan pada transaksi.');
            }

            $buyerMember = Member::lockForUpdate()->findOrFail($primary->member_id);

            // Hitung tanggal mulai berdasarkan endDate terakhir untuk member
            $today = Carbon::today();

            $lastEnd = TransaksiMembership::endDateTerakhirUntukMember($buyerMember->id);
            $start  = $lastEnd && $lastEnd->gte($today) ? $lastEnd->copy()->addDay() : $today->copy();
            $end    = $start->copy()->addDays($durasi - 1);

            // Update tanggal pada transaksi & pivot primary
            $trx->tanggal_mulai = $start;
            $trx->tanggal_akhir = $end;

            $primary->tanggal_mulai = $start;
            $primary->tanggal_akhir = $end;
            $primary->save();

            // Update member table (agar status member global konsisten)
            $this->applyMembershipDurationToMember($buyerMember, $start, $end);

            // Konfirmasi pembayaran
            $trx->payment_status = TransaksiMembership::PAY_CONFIRMED;
            $trx->verified_by    = auth()->id();
            $trx->confirmed_at   = now();
            $trx->save();
        });

        return back()->with('success', 'Transaksi berhasil dikonfirmasi. Durasi membership member telah diperbarui.');
    }

    public function reject($id)
    {
        DB::transaction(function () use ($id) {
            $trx = TransaksiMembership::lockForUpdate()->findOrFail($id);

            if ($trx->canceled_at) {
                abort(403, 'Transaksi sudah dibatalkan.');
            }

            if (in_array($trx->payment_status, [TransaksiMembership::PAY_CONFIRMED, TransaksiMembership::PAY_EXPIRED], true)) {
                abort(403, 'Transaksi sudah selesai diproses.');
            }

            $trx->payment_status = TransaksiMembership::PAY_REJECTED;
            $trx->verified_by    = auth()->id();
            $trx->rejected_at    = now();
            $trx->save();
        });

        return back()->with('success', 'Transaksi berhasil ditolak.');
    }

    protected function applyMembershipDurationToMember(Member $member, Carbon $start, Carbon $end): void
    {
        $today = Carbon::today();

        $currentStart = $member->tanggal_mulai ? Carbon::parse($member->tanggal_mulai)->startOfDay() : null;
        $currentEnd   = $member->tanggal_akhir ? Carbon::parse($member->tanggal_akhir)->startOfDay() : null;

        if ($currentEnd !== null && $currentEnd->gte($today)) {
            $memberMulai = $currentStart ? $currentStart->copy() : $start->copy();
        } else {
            $memberMulai = $start->copy();
        }

        $member->tanggal_mulai = $memberMulai;
        $member->tanggal_akhir = $end->copy();
        $member->save();
    }
}
