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

        $data = [
            'type'             => 'payment',
            'route'            => 'payment_info',          // tujuan di app
            'id'               => (string) $r->id,         // id generik (konsisten)
            'info_rekening_id' => (string) $r->id,         // id spesifik (opsional)
            'deeplink'         => 'betagym://payment',     // opsional jika app support
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

    public function updated(InfoRekening $r): void
    {
        $title = 'Metode pembayaran diperbarui';
        $body  = 'Admin memperbarui info rekening pembayaran.';

        $data = [
            'type'             => 'payment',
            'route'            => 'payment_info',
            'id'               => (string) $r->id,
            'info_rekening_id' => (string) $r->id,
            'deeplink'         => 'betagym://payment',
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
