<?php

namespace App\Observers;

use App\Models\TransaksiProduk;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class TransaksiProdukObserver
{
    public function created(TransaksiProduk $trx): void
    {
        // =========================
        // 1) NOTIF KE PEMBELI (MEMBER) - PERSONAL
        // =========================
        $buyerMemberId = (int) ($trx->buyer_member_id ?? 0);

        $titleBuyer = 'Pesanan produk dibuat';
        $bodyBuyer  = "Pesanan #{$trx->no_nota} berhasil dibuat. Silakan cek Riwayat Pembelian.";

        if ($buyerMemberId > 0) {
            $dataBuyer = [
                'type'               => 'transaksi_produk',
                'route'              => 'riwayat_pembelian',       // tujuan di app
                'id'                 => (string) $trx->id,         // id generik (konsisten)
                'transaksi_produk_id'=> (string) $trx->id,         // id spesifik
                'no_nota'            => (string) ($trx->no_nota ?? ''),
                'total'              => (string) ($trx->total ?? ''),
                'deeplink'           => 'betagym://pembelian',     // opsional
            ];

            // In-app (DB)
            app(NotificationService::class)->toMemberId(
                $buyerMemberId,
                $titleBuyer,
                $bodyBuyer,
                'transaksi_produk',
                $dataBuyer
            );

            // Push status bar (DATA-ONLY sesuai FcmHttpV1Service terbaru)
            app(FcmHttpV1Service::class)->sendToMemberId(
                $buyerMemberId,
                $titleBuyer,
                $bodyBuyer,
                $dataBuyer
            );
        }

        // =========================
        // 2) NOTIF KE ADMIN (TOPIC KHUSUS ADMIN) - OPSIONAL
        // =========================
        // Jika Anda tidak memiliki aplikasi admin Android, bagian ini boleh dihapus.
        $titleAdmin = 'Transaksi produk masuk';
        $bodyAdmin  = "Transaksi baru: #{$trx->no_nota} (Total: {$trx->total}).";

        $dataAdmin = [
            'type'               => 'admin_transaksi_produk',
            'route'              => 'admin_transaksi_produk',
            'id'                 => (string) $trx->id,
            'transaksi_produk_id'=> (string) $trx->id,
            'no_nota'            => (string) ($trx->no_nota ?? ''),
            'total'              => (string) ($trx->total ?? ''),
        ];

        app(FcmHttpV1Service::class)->sendToTopic(
            'admin_users',
            $titleAdmin,
            $bodyAdmin,
            $dataAdmin
        );
    }
}
