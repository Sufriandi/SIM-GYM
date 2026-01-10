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
            $body  = "Member mengajukan izin latihan. Silakan cek dan proses.";

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

        } catch (\Throwable $e) {
            Log::error('[IzinLatihanObserver.created] error: ' . $e->getMessage(), [
                'izin_id' => $izin->id ?? null,
            ]);
        }
    }

    /**
     * MEMBER: notifikasi saat status berubah (approve/reject).
     * Dibuat agar gaya tampilannya konsisten seperti notifikasi produk:
     * - title tegas (bukan "Update ...")
     * - body jelas + ajakan cek detail
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

            // === Buat title/body yang "sekelas produk" ===
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
             * PENTING:
             * Pakai toMemberIdWithPush agar:
             * - DB notifikasi tersimpan
             * - Push terkirim dengan title/body yang sama
             * - tidak ada perbedaan format antara in-app & push
             */
            app(NotificationService::class)->toMemberIdWithPush(
                $memberId,
                $title,
                $body,
                'izin_latihan',
                [
                    // route untuk web member (dipakai MemberNotifikasiController::go)
                    'route'           => 'member_izin_latihan_index',
                    // simpan id untuk kebutuhan future (jika nanti ingin detail)
                    'izin_id'         => (string) $izin->id,
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
