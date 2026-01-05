<?php

namespace App\Observers;

use App\Models\TransaksiMembershipMember;
use App\Models\Notification;
use App\Models\Member;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TransaksiMembershipMemberObserver
{
    public function created(TransaksiMembershipMember $row): void
    {
        try {
            $trx = $row->transaksi()->first();
            if (!$trx) return;

            // Jika transaksi dibatalkan atau belum ada periode, skip
            if ($trx->canceled_at) return;
            if (empty($trx->tanggal_mulai) || empty($trx->tanggal_akhir)) return;

            $memberId = (int) $row->member_id;
            if (!$memberId) return;

            // Anti-spam: cek notif untuk transaksi ini sudah ada atau belum
            if ($this->alreadyNotifiedMember($memberId, (int) $trx->id)) {
                return;
            }

            $title = 'Membership Diaktifkan';
            $body  = 'Membership anda sudah diaktifkan. Selamat berlatih!';

            $data = [
                'route' => 'membership',
                'transaksi_membership_id' => (string) $trx->id,
                'tanggal_mulai' => (string) optional($trx->tanggal_mulai)->toDateString(),
                'tanggal_akhir' => (string) optional($trx->tanggal_akhir)->toDateString(),
            ];

            /** @var NotificationService $svc */
            $svc = app(NotificationService::class);

            $notif = $svc->toMemberId(
                $memberId,
                $title,
                $body,
                'membership',
                $data,
                $trx->created_by
            );

            if (!$notif) {
                Log::warning('[TransaksiMembershipMemberObserver] toMemberId null', [
                    'trx_id' => $trx->id,
                    'member_id' => $memberId,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[TransaksiMembershipMemberObserver] created failed: ' . $e->getMessage());
        }
    }

    private function alreadyNotifiedMember(int $memberId, int $trxId): bool
    {
        try {
            $userId = null;

            if (Schema::hasColumn('members', 'user_id')) {
                $userId = Member::where('id', $memberId)->value('user_id');
            }

            if (!$userId && Schema::hasColumn('users', 'member_id')) {
                $userId = User::where('member_id', $memberId)->value('id');
            }

            if (!$userId) return false;

            return Notification::query()
                ->where('target', 'user')
                ->where('target_user_id', (int) $userId)
                ->where('type', 'membership')
                ->where('data->transaksi_membership_id', (string) $trxId)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
