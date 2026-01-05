<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'jenis_kelamin' => $user->jenis_kelamin,
                'foto' => $user->foto,
                'foto_url' => $user->foto ? asset('storage/' . ltrim($user->foto, '/')) : null,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * POST /api/profile
     * Bisa menerima JSON atau multipart (foto).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'no_hp'         => 'nullable|string|max:50|unique:users,no_hp,' . $user->id,
            'alamat'        => 'nullable|string',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $dataUpdate = [
            'name'          => $request->name,
            'email'         => $request->email,
            'no_hp'         => $request->no_hp,
            'alamat'        => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,
        ];

        // handle upload foto
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('profile', 'public'); // storage/app/public/profile/xxx
            $dataUpdate['foto'] = $path;
        }

        $user->update($dataUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'jenis_kelamin' => $user->jenis_kelamin,
                'foto' => $user->foto,
                'foto_url' => $user->foto ? asset('storage/' . ltrim($user->foto, '/')) : null,
                'role' => $user->role,
            ]
        ]);
    }
}
