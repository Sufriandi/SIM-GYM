<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilGym;
use Illuminate\Http\Request;

class ProfilGymController extends Controller
{
    /**
     * GET /api/profil-gym
     * Ambil 1 profil gym (yang terbaru / satu-satunya).
     */
    public function show()
    {
        $profil = ProfilGym::orderByDesc('id')->first();

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil gym belum diisi oleh admin.',
                'data'    => null,
            ], 404);
        }

        $data = [
            'id' => $profil->id,
            'nama' => $profil->nama,
            'deskripsi' => $profil->deskripsi,

            // File (kalau disimpan di storage/app/public)
            'logo' => $profil->logo,
            'logo_url' => $profil->logo ? asset('storage/' . ltrim($profil->logo, '/')) : null,

            'favicon' => $profil->favicon,
            'favicon_url' => $profil->favicon ? asset('storage/' . ltrim($profil->favicon, '/')) : null,

            'hero_image' => $profil->hero_image,
            'hero_image_url' => $profil->hero_image ? asset('storage/' . ltrim($profil->hero_image, '/')) : null,

            // Sosial
            'instagram' => $profil->instagram,
            'tiktok' => $profil->tiktok,
            'youtube' => $profil->youtube,
            'facebook' => $profil->facebook,
            'whatsapp' => $profil->whatsapp,

            // Operasional
            'lokasi' => $profil->lokasi,
            'jam_buka' => $profil->jam_buka ? substr((string)$profil->jam_buka, 0, 5) : null,   // HH:mm
            'jam_tutup' => $profil->jam_tutup ? substr((string)$profil->jam_tutup, 0, 5) : null, // HH:mm
            'maps_url' => $profil->maps_url,

            // Kontak
            'email_kontak' => $profil->email_kontak,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Profil gym',
            'data'    => $data,
        ]);
    }
}
