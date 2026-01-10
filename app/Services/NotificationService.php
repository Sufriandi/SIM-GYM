<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class NotificationService
{
    /** Cache agar Schema::hasColumn tidak dieksekusi berulang (observer bisa sering memanggil) */
    private static ?bool $membersHasUserId = null;
    private static ?bool $usersHasMemberId = null;

    private function resolveCreatedBy(?int $createdBy): ?int
    {
        return $createdBy ?? Auth::id();
    }

    /**
     * Broadcast ke semua user (admin + member) dengan filter di API memakai created_at user.
     * Hati-hati: ini akan tampil untuk admin & member.
     */
    public function toAll(string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        try {
            return Notification::create([
                'title'          => $title,
                'body'           => $body,
                'target'         => 'all',
                'target_user_id' => null,
                'type'           => $type,
                'data'           => $data ?: null,
                'created_by'     => $this->resolveCreatedBy($createdBy),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toAll failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notifikasi personal untuk users.id
     * (Ini yang dipakai oleh web member & API mobile kamu)
     */
    public function toUser(int $userId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        try {
            return Notification::create([
                'title'          => $title,
                'body'           => $body,
                'target'         => 'user',
                'target_user_id' => $userId,
                'type'           => $type,
                'data'           => $data ?: null,
                'created_by'     => $this->resolveCreatedBy($createdBy),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toUser failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias: kalau kamu sudah punya userId member (Auth::id()).
     */
    public function toMemberUserId(int $userId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        return $this->toUser($userId, $title, $body, $type, $data, $createdBy);
    }

    /**
     * Notifikasi personal berdasarkan members.id.
     * Service akan resolve users.id dari memberId (support 2 skema relasi).
     */
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

    /**
     * Broadcast ke semua admin (users.role = admin)
     */
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

    /**
     * Broadcast khusus member (users.role = member)
     * (Opsional, tapi sangat berguna jika kamu tidak mau target=all masuk admin)
     */
    public function toAllMembers(string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): void
    {
        try {
            $memberUserIds = User::where('role', 'member')->pluck('id')->all();
            foreach ($memberUserIds as $uid) {
                $this->toUser((int) $uid, $title, $body, $type, $data, $createdBy);
            }
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] toAllMembers failed: ' . $e->getMessage());
        }
    }

    // =========================================================
    // WITH PUSH (FCM HTTP v1)
    // =========================================================

    /**
     * Notifikasi + Push untuk users.id (paling direct, dipakai jika kamu sudah punya userId).
     */
    public function toUserWithPush(int $userId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        // 1) simpan DB
        $notif = $this->toUser($userId, $title, $body, $type, $data, $createdBy);

        // 2) push (jangan sampai gagal membuat DB)
        try {
            $ok = app(FcmHttpV1Service::class)->sendToUserId($userId, $title, $body, $data);
            if (!$ok) {
                Log::warning('[NotificationService] push gagal (token kosong?)', ['user_id' => $userId]);
            }
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] push exception: ' . $e->getMessage());
        }

        return $notif;
    }

    /**
     * Notifikasi + Push untuk memberId (members.id).
     * Penting: resolve userId dulu, lalu push pakai sendToUserId agar konsisten.
     */
    public function toMemberIdWithPush(int $memberId, string $title, string $body, ?string $type = null, array $data = [], ?int $createdBy = null): ?Notification
    {
        // resolve userId (support 2 skema relasi)
        $userId = $this->resolveUserIdFromMemberId($memberId);
        if (!$userId) {
            Log::warning('[NotificationService] toMemberIdWithPush: userId tidak ketemu', ['member_id' => $memberId]);
            return null;
        }

        // 1) simpan DB
        $notif = $this->toUser((int) $userId, $title, $body, $type, $data, $createdBy);

        // 2) push
        try {
            $ok = app(FcmHttpV1Service::class)->sendToUserId((int) $userId, $title, $body, $data);
            if (!$ok) {
                Log::warning('[NotificationService] push gagal (token kosong?)', [
                    'member_id' => $memberId,
                    'user_id'   => $userId,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[NotificationService] push exception: ' . $e->getMessage());
        }

        return $notif;
    }

    /**
     * Resolve users.id dari members.id.
     * Support:
     * - members.user_id
     * - users.member_id (fallback)
     */
    private function resolveUserIdFromMemberId(int $memberId): ?int
    {
        if ($memberId <= 0) return null;

        if (self::$membersHasUserId === null) {
            self::$membersHasUserId = Schema::hasColumn('members', 'user_id');
        }
        if (self::$usersHasMemberId === null) {
            self::$usersHasMemberId = Schema::hasColumn('users', 'member_id');
        }

        $userId = null;

        if (self::$membersHasUserId) {
            $userId = Member::where('id', $memberId)->value('user_id');
        }

        if (!$userId && self::$usersHasMemberId) {
            $userId = User::where('member_id', $memberId)->value('id');
        }

        return $userId ? (int) $userId : null;
    }
}
