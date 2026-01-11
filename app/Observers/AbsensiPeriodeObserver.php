<?php

namespace App\Observers;

use App\Models\AbsensiPeriode;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class AbsensiPeriodeObserver
{
    public function created(AbsensiPeriode $p): void
    {
        $title = 'Sesi absensi dibuka';
        $body  = 'Sesi absensi sudah dibuka. Silakan absen melalui menu Scan.';

        $data = [
            'type'               => 'absensi_periode',
            'route'              => 'scan',                 // tujuan: menu/halaman scan
            'id'                 => (string) $p->id,        // id generik (konsisten)
            'absensi_periode_id' => (string) $p->id,        // id spesifik (opsional)
            'deeplink'           => 'betagym://scan',       // opsional (jika app support)
        ];

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'absensi_periode',
            $data
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            $data
        );
    }

    public function updated(AbsensiPeriode $p): void
    {
        $changes = $p->getChanges();

        /**
         * Deteksi penutupan sesi/periode.
         * Anda sudah punya beberapa kandidat field, kita pertahankan.
         */
        $closedSignals = ['closed_at', 'tanggal_tutup', 'is_closed', 'status'];

        $hit = false;
        foreach ($closedSignals as $k) {
            if (array_key_exists($k, $changes)) { $hit = true; break; }
        }
        if (! $hit) return;

        // Jika ada status, pastikan memang tutup/selesai
        if (isset($p->status)) {
            $s = strtolower((string) $p->status);
            $isClosed = in_array($s, ['tutup', 'closed', 'selesai', 'done'], true);
            if (! $isClosed) return;
        }

        $title = 'Sesi absensi ditutup';
        $body  = 'Sesi absensi sudah ditutup. Terima kasih.';

        $data = [
            'type'               => 'absensi_periode',
            'route'              => 'dashboard',            // tujuan: kembali ke beranda
            'id'                 => (string) $p->id,
            'absensi_periode_id' => (string) $p->id,
            'deeplink'           => 'betagym://home',       // opsional (jika app support)
        ];

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'absensi_periode',
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
