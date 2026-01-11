<?php

namespace App\Observers;

use App\Models\InfoQris;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class InfoQrisObserver
{
    public function created(InfoQris $q): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin menambahkan QRIS baru untuk pembayaran.';

        $data = [
            'type'        => 'payment',
            'route'       => 'payment_info',        // tujuan di app
            'id'          => (string) $q->id,       // id generik (konsisten)
            'info_qris_id'=> (string) $q->id,       // id spesifik (opsional)
            'deeplink'    => 'betagym://payment',   // opsional jika app support
        ];

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            $data
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            $data
        );
    }

    public function updated(InfoQris $q): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin memperbarui QRIS pembayaran.';

        $data = [
            'type'        => 'payment',
            'route'       => 'payment_info',
            'id'          => (string) $q->id,
            'info_qris_id'=> (string) $q->id,
            'deeplink'    => 'betagym://payment',
        ];

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            $data
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            $data
        );
    }
}
