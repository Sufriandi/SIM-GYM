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

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'absensi_periode',
            ['absensi_periode_id' => $p->id, 'route' => 'scan']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'scan', 'absensi_periode_id' => (string)$p->id]
        );
    }

    public function updated(AbsensiPeriode $p): void
    {
        $changes = $p->getChanges();

        // Deteksi pola umum penutupan sesi/periode
        $closedSignals = ['closed_at', 'tanggal_tutup', 'is_closed', 'status'];

        $hit = false;
        foreach ($closedSignals as $k) {
            if (array_key_exists($k, $changes)) { $hit = true; break; }
        }
        if (!$hit) return;

        // Jika ada status, pastikan memang tutup
        if (isset($p->status)) {
            $s = strtolower((string)$p->status);
            $isClosed = in_array($s, ['tutup', 'closed', 'selesai', 'done'], true);
            if (!$isClosed) return;
        }

        $title = 'Sesi absensi ditutup';
        $body  = 'Sesi absensi sudah ditutup. Terima kasih.';

        app(NotificationService::class)->toAll(
            $title,
            $body,
            'absensi_periode',
            ['absensi_periode_id' => $p->id, 'route' => 'dashboard']
        );

        app(FcmHttpV1Service::class)->sendToTopic(
            'all_users',
            $title,
            $body,
            ['route' => 'dashboard', 'absensi_periode_id' => (string)$p->id]
        );
    }
}
