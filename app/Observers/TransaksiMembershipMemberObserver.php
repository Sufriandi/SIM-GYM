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
            if (! $trx) return;

            // Jika transaksi dibatalkan atau belum ada periode, skip
            if (! empty($trx->canceled_at)) return;
            if (empty($trx->tanggal_mulai) || empty($trx->tanggal_akhir)) return;

            $memberId = (int) ($row->member_id ?? 0);
            if ($memberId <= 0) return;

            // Anti-spam: notif untuk transaksi ini sudah ada atau belum
            if ($this->alreadyNotifiedMember($memberId, (int) $trx->id)) {
                return;
            }

            $title = 'Membership diaktifkan';
            $body  = 'Membership Anda sudah diaktifkan. Selamat berlatih!';

            $data = [
                'type'                   => 'membership',
                'route'                  => 'membership',              // tujuan di app
                'id'                     => (string) $trx->id,         // id generik (konsisten)
                'transaksi_membership_id'=> (string) $trx->id,         // id spesifik (opsional)
                'tanggal_mulai'          => (string) optional($trx->tanggal_mulai)->toDateString(),
                'tanggal_akhir'          => (string) optional($trx->tanggal_akhir)->toDateString(),
                'deeplink'               => 'betagym://membership',    // opsional
            ];

            /** @var NotificationService $svc */
            $svc = app(NotificationService::class);

            /**
             * PENTING:
             * Pakai toMemberIdWithPush agar:
             * - Notif tersimpan di DB (in-app)
             * - Push terkirim dengan payload yang sama (data-only)
             * - Klik push bisa routing (route/type/id)
             */
            $notif = $svc->toMemberIdWithPush(
                $memberId,
                $title,
                $body,
                'membership',
                $data,
                $trx->created_by // optional (kalau method Anda menerima created_by)
            );

            if (! $notif) {
                Log::warning('[TransaksiMembershipMemberObserver] toMemberIdWithPush returned null/false', [
                    'trx_id'    => $trx->id,
                    'member_id' => $memberId,
                ]);
            }

        } catch (\Throwable $e) {
            Log::warning('[TransaksiMembershipMemberObserver] created failed: ' . $e->getMessage(), [
                'row_id' => $row->id ?? null,
            ]);
        }
    }

    private function alreadyNotifiedMember(int $memberId, int $trxId): bool
    {
        try {
            $userId = null;

            if (Schema::hasColumn('members', 'user_id')) {
                $userId = Member::where('id', $memberId)->value('user_id');
            }

            if (! $userId && Schema::hasColumn('users', 'member_id')) {
                $userId = User::where('member_id', $memberId)->value('id');
            }

            if (! $userId) return false;

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
