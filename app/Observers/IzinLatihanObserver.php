<?php

namespace App\Observers;

use App\Models\IzinLatihan;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class IzinLatihanObserver
{
    /**
     * ADMIN: notifikasi saat MEMBER mengajukan izin (created).
     * Catatan:
     * - Ini bukan untuk aplikasi member.
     * - Jangan broadcast ke all_users.
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
            $body  = 'Member mengajukan izin latihan. Silakan cek dan proses.';

            foreach ($adminIds as $adminId) {
                app(NotificationService::class)->toUser(
                    (int) $adminId,
                    $title,
                    $body,
                    'admin_izin_latihan',
                    [
                        // Standarisasi payload (walau ini admin)
                        'type'            => 'admin_izin_latihan',
                        'route'           => 'admin_izin_latihan',
                        'id'              => (string) $izin->id,
                        'izin_latihan_id' => (string) $izin->id,
                        'member_id'       => (string) $memberId,
                    ]
                );
            }

        } catch (\Throwable $e) {
            Log::error('[IzinLatihanObserver.created] error: ' . $e->getMessage(), [
                'izin_id' => $izin->id ?? null,
            ]);
        }
    }

    /**
     * MEMBER: notifikasi saat status berubah (approve/reject).
     * Ini untuk aplikasi member, jadi payload harus kompatibel dengan routing mobile.
     */
    public function updated(IzinLatihan $izin): void
    {
        try {
            $changes = $izin->getChanges();

            // Deteksi perubahan kolom status
            $statusKey = null;
            foreach (['status', 'status_izin', 'status_pengajuan'] as $k) {
                if (array_key_exists($k, $changes)) {
                    $statusKey = $k;
                    break;
                }
            }
            if (!$statusKey) return;

            $status      = (string) ($izin->{$statusKey} ?? '');
            $statusLower = strtolower(trim($status));

            $memberId = (int) ($izin->member_id ?? 0);
            if ($memberId <= 0) return;

            $isApproved = in_array($statusLower, ['disetujui', 'approved', 'approve'], true);
            $isRejected = in_array($statusLower, ['ditolak', 'rejected', 'reject'], true);

            $title = match (true) {
                $isApproved => 'Izin latihan disetujui',
                $isRejected => 'Izin latihan ditolak',
                default     => 'Status izin latihan berubah',
            };

            $body = match (true) {
                $isApproved => 'Izin latihan Anda disetujui. Silakan cek riwayat izin.',
                $isRejected => 'Izin latihan Anda ditolak. Silakan cek detail pengajuan.',
                default     => "Status izin latihan Anda berubah: {$status}. Silakan cek riwayat.",
            };

            /**
             * ROUTE MOBILE:
             * - Gunakan route yang dipahami MainActivity (mapping tab).
             * - Umumnya riwayat izin ada di Akun -> set 'akun'
             *   Jika di app Anda ada tab khusus izin, silakan ganti route ini.
             */
            $payload = [
                'type'            => 'izin_latihan',
                'route'           => 'akun',                 // << ini yang dipakai mobile untuk buka tab
                'id'              => (string) $izin->id,
                'izin_id'         => (string) $izin->id,
                'izin_latihan_id' => (string) $izin->id,
                'status'          => (string) $status,
                'deeplink'        => 'betagym://akun',       // opsional
            ];

            /**
             * toMemberIdWithPush:
             * Pastikan di NotificationService method ini mengirim push via FcmHttpV1Service
             * dan mengikutsertakan payload 'route/type/id' dalam data push.
             */
            app(NotificationService::class)->toMemberIdWithPush(
                $memberId,
                $title,
                $body,
                'izin_latihan',
                $payload
            );

        } catch (\Throwable $e) {
            Log::error('[IzinLatihanObserver.updated] error: ' . $e->getMessage(), [
                'izin_id' => $izin->id ?? null,
            ]);
        }
    }
}
