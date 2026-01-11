<?php

namespace App\Observers;

use App\Models\ProfilGym;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class ProfilGymObserver
{
    public function updated(ProfilGym $p): void
    {
        $title = 'Informasi gym diperbarui';
        $body  = 'Ada pembaruan informasi gym. Silakan cek di menu Bantuan & Dukungan.';

        $data = [
            'type'         => 'profil_gym',
            'route'        => 'bantuan_dukung',           // tujuan di app
            'id'           => (string) $p->id,            // id generik (konsisten)
            'profil_gym_id'=> (string) $p->id,            // id spesifik (opsional)
            'deeplink'     => 'betagym://bantuan',        // opsional jika app support
        ];

        // Simpan notifikasi ke DB (in-app)
        app(NotificationService::class)->toAll(
            $title,
            $body,
            'profil_gym',
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
