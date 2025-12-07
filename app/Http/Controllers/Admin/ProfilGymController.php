<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilGym;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilGymController extends Controller
{
    /**
     * Tampilkan profil gym (hanya 1 data).
     */
    public function index()
    {
        // Ambil data pertama (jika belum ada, null)
        $profil = ProfilGym::first();

        return view('admin.profil_gym.index', compact('profil'));
    }

    /**
     * Form untuk membuat data pertama profil gym.
     * Karena data hanya 1, jika sudah ada maka redirect ke edit.
     */
    public function create()
    {
        $profil = ProfilGym::first();
        if ($profil) {
            return redirect()->route('admin.profil_gym.edit', $profil->id);
        }

        return view('admin.profil_gym.create');
    }

    /**
     * Simpan data profil gym pertama kali.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Handle upload file (logo, favicon, hero_image)
        $uploadMap = [
            'logo'       => 'profil_gym/logo',
            'favicon'    => 'profil_gym/favicon',
            'hero_image' => 'profil_gym/hero',
        ];

        foreach ($uploadMap as $field => $folder) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store($folder, 'public');
            } else {
                // pastikan tidak menyimpan null eksplisit
                unset($data[$field]);
            }
        }

        ProfilGym::create($data);

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil dibuat.');
    }

    /**
     * Detail profil gym.
     */
    public function show(ProfilGym $profilGym)
    {
        return view('admin.profil_gym.show', compact('profilGym'));
    }

    /**
     * Form edit profil gym.
     */
    public function edit(ProfilGym $profilGym)
    {
        return view('admin.profil_gym.edit', compact('profilGym'));
    }

    /**
     * Update data profil gym.
     */
    public function update(Request $request, ProfilGym $profilGym)
    {
        $data = $this->validateData($request);

        $uploadMap = [
            'logo'       => 'profil_gym/logo',
            'favicon'    => 'profil_gym/favicon',
            'hero_image' => 'profil_gym/hero',
        ];

        foreach ($uploadMap as $field => $folder) {
            if ($request->hasFile($field)) {
                // hapus file lama kalau ada
                if ($profilGym->$field && Storage::disk('public')->exists($profilGym->$field)) {
                    Storage::disk('public')->delete($profilGym->$field);
                }

                // simpan file baru
                $data[$field] = $request->file($field)->store($folder, 'public');
            } else {
                // kalau tidak upload baru, jangan timpa path lama dengan null
                unset($data[$field]);
            }
        }

        $profilGym->update($data);

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil diperbarui.');
    }

    /**
     * Hapus profil gym (jarang digunakan dalam config).
     */
    public function destroy(ProfilGym $profilGym)
    {
        // hapus file-file terkait kalau ada
        foreach (['logo', 'favicon', 'hero_image'] as $field) {
            if ($profilGym->$field && Storage::disk('public')->exists($profilGym->$field)) {
                Storage::disk('public')->delete($profilGym->$field);
            }
        }

        $profilGym->delete();

        return redirect()
            ->route('admin.profil_gym.index')
            ->with('success', 'Profil gym berhasil dihapus.');
    }

    /**
     * Validasi data profil gym.
     */
    private function validateData(Request $request)
    {
        return $request->validate([
            'nama'         => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',

            // kontak & sosmed
            'instagram'    => 'nullable|string|max:255',
            'tiktok'       => 'nullable|string|max:255',
            'youtube'      => 'nullable|string|max:255',
            'facebook'     => 'nullable|string|max:255',
            'whatsapp'     => 'nullable|string|max:30',
            'email_kontak' => 'nullable|email|max:255',
            'maps_url'     => 'nullable|string|max:500',

            // alamat & jam operasional
            'lokasi'       => 'nullable|string',
            'jam_buka'     => 'nullable',
            'jam_tutup'    => 'nullable',

            // file image (opsional)
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'hero_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
    }
}
