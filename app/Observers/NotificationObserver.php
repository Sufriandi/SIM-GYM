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
            // Hanya notif personal (target=user)
            if (($notification->target ?? null) !== 'user') return;

            $adminId = (int) ($notification->target_user_id ?? 0);
            if ($adminId <= 0) return;

            // Pastikan targetnya admin (case-insensitive)
            $u = User::select('id', 'role')->find($adminId);
            if (!$u || strtolower((string)$u->role) !== 'admin') return;

            broadcast(new AdminNotificationCreated($notification, $adminId))->toOthers();
        } catch (\Throwable $e) {
            Log::error('[NotificationObserver.created] error: ' . $e->getMessage(), [
                'notif_id' => $notification->id ?? null,
            ]);
        }
    }
}
