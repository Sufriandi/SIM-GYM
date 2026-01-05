<?php

namespace App\Observers;

use App\Models\IzinLatihan;
use App\Models\User;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class IzinLatihanObserver
{
    /**
     * 1) ADMIN hanya dapat notifikasi saat MEMBER mengajukan izin (created).
     */
    public function created(IzinLatihan $izin): void
    {
        try {
            Log::info('[IzinLatihanObserver.created] fired', [
                'izin_id'   => $izin->id,
                'member_id' => $izin->member_id,
            ]);

            $memberId = (int) ($izin->member_id ?? 0);
            if ($memberId <= 0) return;

            // Cari semua admin (case-insensitive)
            $adminIds = User::query()
                ->whereRaw('LOWER(role) = ?', ['admin'])
                ->pluck('id');

            if ($adminIds->isEmpty()) {
                Log::warning('[IzinLatihanObserver.created] adminIds empty (cek users.role)', [
                    'izin_id'   => $izin->id,
                    'member_id' => $memberId,
                ]);
                return;
            }

            $title = 'Pengajuan izin latihan baru';
            $body  = "Ada pengajuan izin latihan baru dari member ID #{$memberId}. Silakan cek dan proses.";

            foreach ($adminIds as $adminId) {
                app(NotificationService::class)->toUser(
                    (int) $adminId,
                    $title,
                    $body,
                    'admin_izin_latihan',
                    [
                        'route'           => 'admin_izin_latihan',
                        'izin_latihan_id' => (string) $izin->id,
                        'member_id'       => (string) $memberId,
                    ]
                );
            }

            // OPTIONAL: push ke admin via FCM topic (kalau Anda mau)
            // app(FcmHttpV1Service::class)->sendToTopic(
            //     'admin_users',
            //     $title,
            //     $body,
            //     ['route' => 'admin_izin_latihan', 'izin_latihan_id' => (string)$izin->id]
            // );

        } catch (\Throwable $e) {
            Log::error('[IzinLatihanObserver.created] error: ' . $e->getMessage(), [
                'izin_id' => $izin->id ?? null,
            ]);
        }
    }

    /**
     * 2) MEMBER saja yang dapat notifikasi saat status berubah (approve/reject).
     */
    public function updated(IzinLatihan $izin): void
    {
        try {
            $changes = $izin->getChanges();

            // Deteksi perubahan status
            $statusKey = null;
            foreach (['status', 'status_izin', 'status_pengajuan'] as $k) {
                if (array_key_exists($k, $changes)) {
                    $statusKey = $k;
                    break;
                }
            }

            if (!$statusKey) return;

            $status      = (string) ($izin->{$statusKey} ?? '');
            $statusLower = strtolower($status);

            $body = match (true) {
                in_array($statusLower, ['disetujui', 'approved', 'approve'], true)
                    => 'Izin latihan Anda disetujui.',
                in_array($statusLower, ['ditolak', 'rejected', 'reject'], true)
                    => 'Izin latihan Anda ditolak. Silakan cek detail.',
                default
                    => "Status izin latihan Anda berubah: {$status}"
            };

            $memberId = (int) ($izin->member_id ?? 0);
            if ($memberId <= 0) return;

            $title = 'Update izin latihan';

            // In-app untuk member
            app(NotificationService::class)->toMemberId(
                $memberId,
                $title,
                $body,
                'izin_latihan',
                [
                    'izin_latihan_id' => (string) $izin->id,
                    'status'          => (string) $status,
                    'route'           => 'izin_riwayat',
                ]
            );

            // Push untuk member
            app(FcmHttpV1Service::class)->sendToMemberId(
                $memberId,
                $title,
                $body,
                [
                    'route'           => 'izin_riwayat',
                    'izin_latihan_id' => (string) $izin->id,
                    'status'          => (string) $status,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('[IzinLatihanObserver.updated] error: ' . $e->getMessage(), [
                'izin_id' => $izin->id ?? null,
            ]);
        }
    }
}
