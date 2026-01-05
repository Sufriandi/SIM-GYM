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

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'paket_membership',
            ['paket_membership_id' => $paket->id, 'route' => 'membership']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'membership', 'paket_membership_id' => (string)$paket->id]
        );
    }
}
