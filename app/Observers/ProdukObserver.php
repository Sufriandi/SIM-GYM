<?php

namespace App\Observers;

use App\Models\Produk;
use App\Models\DeviceToken;
use App\Services\NotificationService;
use App\Services\FcmHttpV1Service;
use Illuminate\Support\Facades\Log;

class ProdukObserver
{
    public function created(Produk $produk): void
    {
        try {
            Log::info('[ProdukObserver.created] fired', [
                'produk_id' => $produk->id,
                'nama'      => $produk->nama ?? null,
            ]);

            $title = 'Produk baru masuk';
            $body  = "Produk baru: {$produk->nama}. Buruan pesan sekarang!";

            // 1) In-app (DB) tetap broadcast
            app(NotificationService::class)->toAll(
                $title,
                $body,
                'produk',
                [
                    'route'     => 'produk_detail',
                    'produk_id' => (string) $produk->id,
                ]
            );

            // 2) Push Android: kirim ke SEMUA token (token-based), bukan topic
            $tokens = DeviceToken::query()
                ->pluck('token')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($tokens)) {
                Log::warning('[ProdukObserver.created] no device tokens found (device_tokens kosong)');
                return;
            }

            $res = app(FcmHttpV1Service::class)->sendToTokens(
                $tokens,
                $title,
                $body,
                [
                    'route'     => 'produk_detail',
                    'produk_id' => (string) $produk->id, // samakan key dengan in-app
                ]
            );

            Log::info('[ProdukObserver.created] push results', $res);

        } catch (\Throwable $e) {
            Log::error('[ProdukObserver.created] error: ' . $e->getMessage(), [
                'produk_id' => $produk->id ?? null,
            ]);
        }
    }
}
