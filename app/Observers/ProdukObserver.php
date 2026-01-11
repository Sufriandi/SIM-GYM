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

            $nama = $produk->nama ?? 'Produk baru';

            $title = 'Produk baru masuk';
            $body  = "Produk baru: {$nama}. Buruan pesan sekarang!";

            $data = [
                'type'     => 'produk',
                'route'    => 'produk_detail',
                'id'       => (string) $produk->id,
                'produk_id'=> (string) $produk->id,
                'deeplink' => 'betagym://produk/' . $produk->id,
            ];

            // 1) In-app (DB)
            app(NotificationService::class)->toAll(
                $title,
                $body,
                'produk',
                $data
            );

            // 2) Push Android: kirim ke semua token
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
                $data
            );

            Log::info('[ProdukObserver.created] push results', $res);

        } catch (\Throwable $e) {
            Log::error('[ProdukObserver.created] error: ' . $e->getMessage(), [
                'produk_id' => $produk->id ?? null,
            ]);
        }
    }
}
