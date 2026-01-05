<?php

namespace App\Observers;

use App\Models\TransaksiProduk;
use App\Services\FcmHttpV1Service;
use App\Services\NotificationService;

class TransaksiProdukObserver
{
    public function created(TransaksiProduk $trx): void
    {
        $titleBuyer = 'Pesanan produk dibuat';
        $bodyBuyer  = "Pesanan #{$trx->no_nota} berhasil dibuat. Silakan cek Riwayat Pembelian.";

        // =========================
        // 1) NOTIF KE PEMBELI (PERSONAL)
        // =========================
        $buyerMemberId = (int) ($trx->buyer_member_id ?? 0);
        if ($buyerMemberId > 0) {
            // in-app
            app(NotificationService::class)->toMemberId(
                $buyerMemberId,
                $titleBuyer,
                $bodyBuyer,
                'transaksi_produk',
                ['transaksi_produk_id' => $trx->id, 'route' => 'riwayat_pembelian']
            );

            // push status bar
            app(FcmHttpV1Service::class)->sendToMemberId(
                $buyerMemberId,
                $titleBuyer,
                $bodyBuyer,
                ['route' => 'riwayat_pembelian', 'transaksi_produk_id' => (string)$trx->id]
            );
        }

        // =========================
        // 2) NOTIF KE ADMIN (TOPIC KHUSUS ADMIN)
        // =========================
        $titleAdmin = 'Transaksi produk masuk';
        $bodyAdmin  = "Transaksi baru: #{$trx->no_nota} (Total: {$trx->total}).";

        // push ke topic admin
        app(FcmHttpV1Service::class)->sendToTopic(
            'admin_users',
            $titleAdmin,
            $bodyAdmin,
            ['route' => 'admin_transaksi_produk', 'transaksi_produk_id' => (string)$trx->id]
        );
    }
}
