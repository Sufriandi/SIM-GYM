<?php

namespace App\Observers;

use App\Models\Coach;
use App\Services\NotificationService;
use App\Services\FcmHttpV1Service;

class CoachObserver
{
    public function created(Coach $coach): void
    {
        $nama = $coach->nama ?? $coach->name ?? 'Coach baru';

        // Payload standar untuk navigasi dari notifikasi (dipakai Android)
        $data = [
            'type'       => 'coach',
            'route'      => 'coach_detail',             // nama tujuan di app
            'id'         => (string) $coach->id,        // id generik (konsisten untuk semua)
            'coach_id'   => (string) $coach->id,        // id spesifik (opsional, kalau mau)
            'deeplink'   => 'betagym://coach/' . $coach->id, // opsional (kalau app support)
        ];

        // Simpan notifikasi ke database (kalau NotificationService Anda menyimpan)
        app(NotificationService::class)->toAll(
            'Coach baru tersedia',
            "Coach baru: {$nama}. Cek sekarang!",
            'coach',
            $data
        );

        // Kirim push FCM (sertakan data yang sama)
        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            'Coach baru tersedia',
            "Coach baru: {$nama}. Cek sekarang!",
            $data
        );
    }
}
