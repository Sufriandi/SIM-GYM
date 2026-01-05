<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $token = (string) $request->token;
        $platform = $request->platform ?? 'android';

        // 1) Token ini mungkin sebelumnya tersimpan di user lain (karena ganti akun di device yang sama).
        //    Hapus dulu supaya token "berpindah" ke user yang sekarang.
        DeviceToken::query()
            ->where('token', $token)
            ->where('user_id', '!=', $user->id)
            ->delete();

        // 2) Upsert token untuk user saat ini
        DeviceToken::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'token'   => $token,
            ],
            [
                'platform' => $platform,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token saved',
        ]);
    }
}
