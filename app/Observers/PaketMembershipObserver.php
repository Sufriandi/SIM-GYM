<?php

namespace App\Observers;

use App\Models\PaketMembership;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class PaketMembershipObserver
{
    public function created(PaketMembership $paket): void
    {
        $nama = $paket->nama ?? 'Paket baru';

        $title = 'Paket membership baru';
        $body  = "Paket baru tersedia: {$nama}. Cek dan aktifkan sekarang!";

        $data = [
            'type'                => 'paket_membership',
            'route'               => 'membership',               // tujuan di app
            'id'                  => (string) $paket->id,        // id generik (konsisten)
            'paket_membership_id' => (string) $paket->id,        // id spesifik (opsional)
            'deeplink'            => 'betagym://membership',     // opsional
        ];

        // Simpan notifikasi ke DB (untuk in-app)
        app(NotificationService::class)->toAll(
            $title,
            $body,
            'paket_membership',
            $data
        );

        // Kirim push (DATA-ONLY sesuai FcmHttpV1Service terbaru)
        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            $data
        );
    }
}
