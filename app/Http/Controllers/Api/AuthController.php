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
    /**
     * POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'    => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_hp'    => ['nullable', 'string', 'max:15', 'unique:users,no_hp'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Disarankan hash di controller agar pasti aman,
        // walau di User model ada mutator juga tidak masalah (double hash tidak terjadi jika mutator benar).
        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'no_hp'    => $request->no_hp,
            'password' => Hash::make((string) $request->password),
            'role'     => 'member',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil. Silakan login.',
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
            ],
        ], 201);
    }

    /**
     * POST /api/login
     * Body:
     * - email (string) : bisa email/username/no_hp
     * - password (string)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email dan password wajib diisi',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $identifier = trim((string) $request->email);
        $password   = (string) $request->password;

        // Bisa login pakai email ATAU username ATAU no_hp
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('no_hp', $identifier)
            ->first();

        if (! $user) {
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
        if (! $ok) {
            $stored = (string) $user->getOriginal('password');

            $looksPlain = (strlen($stored) < 55)
                || (!str_starts_with($stored, '$2y$') && !str_starts_with($stored, '$argon'));

            if ($looksPlain && hash_equals($stored, $password)) {
                // Upgrade password => hash
                $user->password = Hash::make($password);
                $user->save();

                $ok = true;
            }
        }

        if (! $ok) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah',
            ], 401);
        }

        // optional (tidak wajib untuk sanctum token)
        Auth::login($user);

        // Buat token sanctum
        $token = $user->createToken('auth_token_mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'username'  => $user->username,
                'email'     => $user->email,
                'no_hp'     => $user->no_hp,
                'role'      => $user->role,
                'member_id' => optional($user->member)->id,
            ],
        ], 200);
    }

    /**
     * POST /api/change-password
     * Wajib auth:sanctum
     *
     * Body JSON:
     * {
     *   "old_password": "xxx",
     *   "new_password": "yyy",
     *   "new_password_confirmation": "yyy"
     * }
     */
    public function changePassword(Request $request)
    {
        $user = $request->user(); // dari auth:sanctum

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            // confirmed => butuh new_password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $old = (string) $request->old_password;
        $new = (string) $request->new_password;

        // Cek password lama
        if (! Hash::check($old, (string) $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama salah.',
            ], 400);
        }

        // Tidak boleh sama
        if (hash_equals($old, $new)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.',
            ], 422);
        }

        // Update password (hash)
        $user->password = Hash::make($new);
        $user->save();

        // Opsional: paksa logout semua device (hapus semua token)
        // $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diubah.',
        ], 200);
    }
}
