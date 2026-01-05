<?php

namespace App\Observers;

use App\Models\InfoRekening;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class InfoRekeningObserver
{
    public function created(InfoRekening $r): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin menambahkan info rekening baru untuk pembayaran.';

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            ['info_rekening_id' => $r->id, 'route' => 'payment_info']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'payment_info', 'info_rekening_id' => (string)$r->id]
        );
    }

    public function updated(InfoRekening $r): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin memperbarui info rekening pembayaran.';

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'payment',
            ['info_rekening_id' => $r->id, 'route' => 'payment_info']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'payment_info', 'info_rekening_id' => (string)$r->id]
        );
    }
}
