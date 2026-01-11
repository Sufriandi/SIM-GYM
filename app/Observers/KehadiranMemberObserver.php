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

        $data = [
            'type'        => 'absen',
            'route'       => 'absen_riwayat',          // tujuan di app (riwayat kehadiran)
            'id'          => (string) $absen->id,      // id generik (konsisten)
            'kehadiran_id'=> (string) $absen->id,      // id spesifik (opsional)
            'member_id'   => (string) $memberId,
            'deeplink'    => 'betagym://absen',        // opsional
        ];

        // Simpan ke DB notifikasi
        app(NotificationService::class)->toMemberId(
            $memberId,
            $title,
            $body,
            'absen',
            $data
        );

        // Kirim push (DATA-ONLY sesuai FcmHttpV1Service terbaru)
        app(FcmHttpV1Service::class)->sendToMemberId(
            $memberId,
            $title,
            $body,
            $data
        );
    }
}
