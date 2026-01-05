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

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            ['info_qris_id' => $q->id, 'route' => 'payment_info']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'payment_info', 'info_qris_id' => (string)$q->id]
        );
    }

    public function updated(InfoQris $q): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin memperbarui QRIS pembayaran.';

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            ['info_qris_id' => $q->id, 'route' => 'payment_info']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'payment_info', 'info_qris_id' => (string)$q->id]
        );
    }
}
