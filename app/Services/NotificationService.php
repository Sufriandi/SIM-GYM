<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private function resolveCreatedBy(?int $createdBy): ?int
    {
        return $createdBy ?? Auth::id();
    }

    public function toAll(string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        try {
            return Notification::create([
                'title' => $title,
                'body' => $body,
                'target' => 'all',
                'target_user_id' => null,
                'type' => $type,
                'data' => $data ?: null,
                'created_by' => $this->resolveCreatedBy($createdBy),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toAll failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toUser(int $userId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        try {
            return Notification::create([
                'title' => $title,
                'body' => $body,
                'target' => 'user',
                'target_user_id' => $userId,
                'type' => $type,
                'data' => $data ?: null,
                'created_by' => $this->resolveCreatedBy($createdBy),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toUser failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toMemberId(int $memberId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        try {
            $userId = $this->resolveUserIdFromMemberId($memberId);
            if (!$userId) return null;

            return $this->toUser((int) $userId, $title, $body, $type, $data, $createdBy);
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toMemberId failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toAllAdmins(string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): void
    {
        try {
            $adminIds = User::where('role', 'admin')->pluck('id')->all();
            foreach ($adminIds as $adminId) {
                $this->toUser((int) $adminId, $title, $body, $type, $data, $createdBy);
            }
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toAllAdmins failed: ' . $e->getMessage());
        }
    }

    // =========================
    // WITH PUSH (FCM HTTP v1)
    // =========================

    public function toMemberIdWithPush(int $memberId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        // 1) simpan DB
        $notif = $this->toMemberId($memberId, $title, $body, $type, $data, $createdBy);

        // 2) kirim push (jangan sampai gagal membuat DB)
        try {
            $ok = app(FcmHttpV1Service::class)->sendToMemberId($memberId, $title, $body, $data);
            if (!$ok) {
                Log::warning('[NotificationService] push membership gagal (kemungkinan token kosong)', [
                    'member_id' => $memberId
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] push exception: ' . $e->getMessage());
        }

        return $notif;
    }

    private function resolveUserIdFromMemberId(int $memberId): ?int
    {
        $userId = null;

        if (Schema::hasColumn('members', 'user_id')) {
            $userId = Member::where('id', $memberId)->value('user_id');
        }

        if (!$userId && Schema::hasColumn('users', 'member_id')) {
            $userId = User::where('member_id', $memberId)->value('id');
        }

        return $userId ? (int) $userId : null;
    }
}
