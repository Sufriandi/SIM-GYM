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
        // Trigger ketika admin mengubah periode / batalin / aktifin lagi
        if ($this->shouldNotifyOnUpdate($trx)) {
            $this->notifyMembersFromTransaction($trx, 'updated');
        }
    }

    private function shouldNotifyOnCreate(TransaksiMembership $trx): bool
    {
        // Notif “membership aktif” biasanya dimaksudkan ketika admin sudah set periode
        if ($trx->canceled_at) return false;
        if (empty($trx->tanggal_mulai) || empty($trx->tanggal_akhir)) return false;

        return true;
    }

    private function shouldNotifyOnUpdate(TransaksiMembership $trx): bool
    {
        // Kalau dibatalkan -> biasanya tidak perlu notif "diaktifkan"
        if ($trx->canceled_at) return false;

        // Hanya saat ada perubahan pada hal-hal yang relevan dengan aktivasi
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
            $title = 'Membership Diaktifkan';
            $body  = 'Membership anda sudah diaktifkan. Selamat berlatih!';

            $data = [
                'route' => 'membership',
                'transaksi_membership_id' => (string) $trx->id,
                'tanggal_mulai' => (string) optional($trx->tanggal_mulai)->toDateString(),
                'tanggal_akhir' => (string) optional($trx->tanggal_akhir)->toDateString(),
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

            foreach ($memberIds as $memberId) {
                // Anti-spam: kalau notif untuk transaksi ini sudah pernah dibuat untuk user yang sama, skip
                if ($this->alreadyNotifiedMember($memberId, (int) $trx->id)) {
                    continue;
                }

                $svc->toMemberIdWithPush(
                    $memberId,
                    $title,
                    $body,
                    'membership',
                    $data,
                    $trx->created_by
                );

                if (!$notif) {
                    Log::warning('[TransaksiMembershipObserver] Gagal membuat notif (toMemberId null)', [
                        'trx_id' => $trx->id,
                        'member_id' => $memberId,
                        'source' => $source,
                    ]);
                }
            }

            Log::info('[TransaksiMembershipObserver] Notifikasi membership diproses', [
                'trx_id' => $trx->id,
                'member_ids' => $memberIds,
                'source' => $source,
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
     * Kita cek apakah sudah ada Notification type=membership data->transaksi_membership_id = trx_id untuk user terkait.
     * Kalau mapping user_id dari member tidak ketemu, fallback: tidak dicek (anggap belum notif).
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
                // tidak bisa cek -> biarkan kirim (agar tidak gagal total)
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
