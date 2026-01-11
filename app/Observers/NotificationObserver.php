<?php

namespace App\Observers;

use App\Events\AdminNotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    public function created(Notification $notification): void
    {
        try {
            // Hanya notifikasi personal (target=user)
            $target = strtolower((string) ($notification->target ?? ''));
            if ($target !== 'user') {
                return;
            }

            $adminId = (int) ($notification->target_user_id ?? 0);
            if ($adminId <= 0) {
                Log::warning('[NotificationObserver.created] target_user_id invalid', [
                    'notif_id' => $notification->id ?? null,
                    'target_user_id' => $notification->target_user_id ?? null,
                ]);
                return;
            }

            // Pastikan target user benar-benar admin (case-insensitive)
            $u = User::query()
                ->select('id', 'role')
                ->find($adminId);

            if (! $u) {
                Log::warning('[NotificationObserver.created] target user not found', [
                    'notif_id' => $notification->id ?? null,
                    'admin_id' => $adminId,
                ]);
                return;
            }

            $role = strtolower((string) ($u->role ?? ''));
            if ($role !== 'admin') {
                return;
            }

            // Broadcast ke admin panel (realtime)
            broadcast(new AdminNotificationCreated($notification, $adminId))->toOthers();

        } catch (\Throwable $e) {
            Log::error('[NotificationObserver.created] error: ' . $e->getMessage(), [
                'notif_id' => $notification->id ?? null,
            ]);
        }
    }
}
