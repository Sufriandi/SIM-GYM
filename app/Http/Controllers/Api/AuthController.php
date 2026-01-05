<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'nullable|string|email|max:255|unique:users',
            'no_hp'    => 'nullable|string|max:15|unique:users,no_hp',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Tidak masalah di-hash di sini; User model juga akan meng-hash jika perlu.
        User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'no_hp'    => $request->no_hp,
            'password' => $request->password,
            'role'     => 'member',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil. Silakan login.',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email dan password wajib diisi',
            ], 422);
        }

        $identifier = trim((string) $request->email);
        $password   = (string) $request->password;

        // Bisa login pakai email ATAU username (opsional: no_hp juga)
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('no_hp', $identifier) // jika Anda tidak butuh login pakai no_hp, boleh hapus baris ini
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Username tidak ditemukan',
            ], 401);
        }

        // ===============================
        // 1) Cek normal (password hash)
        // ===============================
        $ok = Hash::check($password, (string) $user->password);

        // ===============================
        // 2) FALLBACK untuk data lama:
        //    jika password DB masih plaintext, izinkan dan upgrade.
        // ===============================
        if (!$ok) {
            $stored = (string) $user->getOriginal('password');

            $looksPlain = (strlen($stored) < 55)
                || (!str_starts_with($stored, '$2y$') && !str_starts_with($stored, '$argon'));

            if ($looksPlain && hash_equals($stored, $password)) {
                // Upgrade password: set ulang => akan ter-hash oleh mutator di User.php
                $user->password = $password;
                $user->save();

                $ok = true;
            }
        }

        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah',
            ], 401);
        }

        // (Opsional) login session; untuk API token Sanctum tidak wajib, tapi tidak masalah.
        Auth::login($user);

        // Buat token
        $token = $user->createToken('auth_token_mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'member_id' => optional($user->member)->id,
            ],
        ], 200);
    }
}
