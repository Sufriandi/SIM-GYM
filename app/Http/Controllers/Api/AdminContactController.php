<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminContactController extends Controller
{
    /**
     * GET /api/admin-contact
     * Return nomor admin dari table users (role admin/administrator)
     */
    public function show()
    {
        // Ambil admin yang aktif (tidak soft-deleted)
        // Jika role Anda hanya "admin", cukup pakai ->where('role','admin')
        $admin = User::query()
            ->whereIn('role', ['admin', 'administrator'])
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        if (!$admin || empty($admin->no_hp)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor admin belum diatur.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'admin_id' => $admin->id,
                'name'     => $admin->name,
                'username' => $admin->username,
                'whatsapp' => $admin->no_hp, // raw, nanti Android normalize ke 62
            ],
        ]);
    }
}
