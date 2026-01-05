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

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'profil_gym',
            ['profil_gym_id' => $p->id, 'route' => 'bantuan_dukung']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'bantuan_dukung', 'profil_gym_id' => (string)$p->id]
        );
    }
}
