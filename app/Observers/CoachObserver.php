<?php

namespace App\Observers;

use App\Models\Coach;
use App\Services\NotificationService;
use App\Services\FcmHttpV1Service;

class CoachObserver
{
    public function created(Coach $coach): void
    {
        // sesuaikan field nama coach Anda jika berbeda
        $nama = $coach->nama ?? $coach->name ?? 'Coach baru';

        app(NotificationService::class)->toAll(
            'Coach baru tersedia',
            "Coach baru: {$nama}. Cek sekarang!",
            'coach',
            ['coach_id' => $coach->id, 'route' => 'coach_detail']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            'Coach baru tersedia',
            "Coach baru: {$coach->nama}. Cek sekarang!",
            ['route' => 'coach_detail', 'id' => (string)$coach->id]
        );
    }
}
