<?php

namespace App\Observers;

use App\Models\TransaksiMembership;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Member;
use App\Models\User;

class TransaksiMembershipObserver
{
    public function created(TransaksiMembership $trx): void
    {
        // Saat transaksi dibuat, kalau sudah punya periode tanggal, kirim notif
        if ($this->shouldNotifyOnCreate($trx)) {
            $this->notifyMembersFromTransaction($trx, 'created');
        }
    }

    public function updated(TransaksiMembership $trx): void
    {
        // Trigger ketika admin mengubah periode / paket (aktivasi ulang)
        if ($this->shouldNotifyOnUpdate($trx)) {
            $this->notifyMembersFromTransaction($trx, 'updated');
        }
    }

    private function shouldNotifyOnCreate(TransaksiMembership $trx): bool
    {
        // Notif “membership aktif” hanya jika tidak dibatalkan dan periode lengkap
        if (!empty($trx->canceled_at)) return false;
        if (empty($trx->tanggal_mulai) || empty($trx->tanggal_akhir)) return false;

        return true;
    }

    private function shouldNotifyOnUpdate(TransaksiMembership $trx): bool
    {
        // Kalau dibatalkan -> tidak kirim notif "diaktifkan"
        if (!empty($trx->canceled_at)) return false;

        // Hanya saat ada perubahan relevan
        $fields = ['tanggal_mulai', 'tanggal_akhir', 'canceled_at', 'paket_id'];

        foreach ($fields as $f) {
            if (method_exists($trx, 'wasChanged') && $trx->wasChanged($f)) {
                // Pastikan periode lengkap
                return !empty($trx->tanggal_mulai) && !empty($trx->tanggal_akhir);
            }
        }

        return false;
    }

    private function notifyMembersFromTransaction(TransaksiMembership $trx, string $source): void
    {
        try {
            $title = 'Membership diaktifkan';
            $body  = 'Membership Anda sudah diaktifkan. Selamat berlatih!';

            // Payload standar untuk mobile routing
            $data = [
                'type'                   => 'membership',
                'route'                  => 'membership',
                'id'                     => (string) $trx->id,
                'transaksi_membership_id'=> (string) $trx->id,
                'tanggal_mulai'          => (string) optional($trx->tanggal_mulai)->toDateString(),
                'tanggal_akhir'          => (string) optional($trx->tanggal_akhir)->toDateString(),
                'deeplink'               => 'betagym://membership', // opsional
            ];

            // kumpulkan semua member: buyer + participants
            $memberIds = [];

            if (!empty($trx->buyer_member_id)) {
                $memberIds[] = (int) $trx->buyer_member_id;
            }

            // participants mungkin belum eager loaded, ambil dari relasi
            $participantIds = $trx->participants()->pluck('member_id')->all();
            foreach ($participantIds as $mid) {
                if (!empty($mid)) $memberIds[] = (int) $mid;
            }

            $memberIds = array_values(array_unique($memberIds));

            if (empty($memberIds)) {
                Log::warning('[TransaksiMembershipObserver] Tidak ada memberId target', [
                    'trx_id' => $trx->id,
                    'source' => $source,
                ]);
                return;
            }

            /** @var NotificationService $svc */
            $svc = app(NotificationService::class);

            $sentTo = [];
            $skipped = [];

            foreach ($memberIds as $memberId) {
                // Anti-spam: kalau notif untuk transaksi ini sudah pernah dibuat untuk user yang sama, skip
                if ($this->alreadyNotifiedMember($memberId, (int) $trx->id)) {
                    $skipped[] = $memberId;
                    continue;
                }

                // FIX: simpan return value ke $notif
                $notif = $svc->toMemberIdWithPush(
                    $memberId,
                    $title,
                    $body,
                    'membership',
                    $data,
                    $trx->created_by
                );

                if (!$notif) {
                    Log::warning('[TransaksiMembershipObserver] Gagal membuat notif (toMemberIdWithPush null/false)', [
                        'trx_id'    => $trx->id,
                        'member_id' => $memberId,
                        'source'    => $source,
                    ]);
                } else {
                    $sentTo[] = $memberId;
                }
            }

            Log::info('[TransaksiMembershipObserver] Notifikasi membership diproses', [
                'trx_id'      => $trx->id,
                'sent_to'     => $sentTo,
                'skipped'     => $skipped,
                'source'      => $source,
            ]);

        } catch (\Throwable $e) {
            Log::warning('[TransaksiMembershipObserver] notifyMembersFromTransaction failed: ' . $e->getMessage(), [
                'trx_id' => $trx->id ?? null,
                'source' => $source,
            ]);
        }
    }

    /**
     * Cegah notif dobel saat transaksi di-update berkali-kali.
     * Cek apakah sudah ada Notification type=membership data->transaksi_membership_id = trx_id untuk user terkait.
     * Kalau mapping user_id dari member tidak ketemu, fallback: anggap belum notif.
     */
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

            if (!$userId) {
                return false;
            }

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
