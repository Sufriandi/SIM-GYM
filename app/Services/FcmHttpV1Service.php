<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FcmHttpV1Service
{
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $this->stringifyData($data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => config('fcm.android_channel_id', 'betagym_general'),
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        return $this->send($message);
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $this->stringifyData($data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => config('fcm.android_channel_id', 'betagym_general'),
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        return $this->send($message);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = ['success' => 0, 'failed' => 0];

        foreach ($tokens as $t) {
            $ok = $this->sendToToken((string) $t, $title, $body, $data);
            if ($ok) $results['success']++;
            else $results['failed']++;
        }

        return $results;
    }

    public function sendToUserId(int $userId, string $title, string $body, array $data = []): bool
    {
        try {
            if ($userId <= 0) return false;

            $tokens = DeviceToken::query()
                ->where('user_id', $userId)
                ->pluck('token')
                ->filter()
                ->values()
                ->toArray();

            Log::info('[FCM] sendToUserId tokens', [
                'user_id' => $userId,
                'count'   => count($tokens),
            ]);

            if (empty($tokens)) return false;

            $res = $this->sendToTokens($tokens, $title, $body, $data);

            Log::info('[FCM] sendToUserId result', [
                'user_id' => $userId,
                'result'  => $res,
            ]);

            return ($res['success'] ?? 0) > 0;

        } catch (\Throwable $e) {
            Log::warning('[FCM] sendToUserId exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aman untuk 2 skema:
     * - members.user_id
     * - users.member_id (fallback)
     */
    public function sendToMemberId(int $memberId, string $title, string $body, array $data = []): bool
    {
        try {
            if ($memberId <= 0) return false;

            $userId = 0;

            if (Schema::hasColumn('members', 'user_id')) {
                $userId = (int) Member::query()
                    ->where('id', $memberId)
                    ->value('user_id');
            }

            if ($userId <= 0 && Schema::hasColumn('users', 'member_id')) {
                $userId = (int) User::query()
                    ->where('member_id', $memberId)
                    ->value('id');
            }

            if ($userId <= 0) {
                Log::warning('[FCM] sendToMemberId: userId tidak ditemukan', ['member_id' => $memberId]);
                return false;
            }

            return $this->sendToUserId($userId, $title, $body, $data);

        } catch (\Throwable $e) {
            Log::warning('[FCM] sendToMemberId exception: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // Internal: send HTTP v1
    // =========================================================

    private function send(array $payload): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) return false;

            $projectId = config('fcm.project_id');
            $url = sprintf(config('fcm.fcm_send_url'), $projectId);

            $res = Http::withToken($accessToken)
                ->acceptJson()
                ->contentType('application/json')
                ->post($url, $payload);

            if ($res->successful()) {
                Log::info('[FCM] send ok', ['name' => $res->json('name')]);
                return true;
            }

            Log::warning('[FCM] send failed', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::warning('[FCM] exception: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', now()->addMinutes(50), function () {
            $saPath = base_path(config('fcm.service_account'));
            if (!file_exists($saPath)) {
                Log::warning('[FCM] service account file not found: ' . $saPath);
                return null;
            }

            $json = json_decode(file_get_contents($saPath), true);
            if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
                Log::warning('[FCM] invalid service account json.');
                return null;
            }

            $jwt = $this->createJwt(
                $json['client_email'],
                $json['private_key'],
                config('fcm.scope'),
                config('fcm.oauth_token_url')
            );

            $res = Http::asForm()->post(config('fcm.oauth_token_url'), [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$res->successful()) {
                Log::warning('[FCM] oauth token failed', [
                    'status' => $res->status(),
                    'body'   => $res->body(),
                ]);
                return null;
            }

            $accessToken = $res->json('access_token');
            return is_string($accessToken) ? $accessToken : null;
        });
    }

    private function createJwt(string $clientEmail, string $privateKeyPem, string $scope, string $aud): string
    {
        $now = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss'   => $clientEmail,
            'scope' => $scope,
            'aud'   => $aud,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($claims));

        $signingInput = implode('.', $segments);

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        if (!$ok) throw new \RuntimeException('OpenSSL signing failed.');

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (is_scalar($v) || $v === null) $out[$k] = (string) ($v ?? '');
            else $out[$k] = json_encode($v);
        }
        return $out;
    }
}
