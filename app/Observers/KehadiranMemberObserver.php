<?php

namespace App\Observers;

use App\Models\KehadiranMember;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class KehadiranMemberObserver
{
    public function created(KehadiranMember $absen): void
    {
        if (empty($absen->member_id)) return;
        $memberId = (int) $absen->member_id;

        $title = 'Absensi berhasil';
        $body  = 'Absensi Anda berhasil tercatat. Selamat latihan!';

        app(NotificationService::class)->toMemberId(
            $memberId,
            $title,
            $body,
            'absen',
            ['kehadiran_id' => $absen->id, 'route' => 'absen_riwayat']
        );

        app(FcmHttpV1Service::class)->sendToMemberId(
            $memberId,
            $title,
            $body,
            ['route' => 'absen_riwayat', 'kehadiran_id' => (string)$absen->id]
        );
    }
}
